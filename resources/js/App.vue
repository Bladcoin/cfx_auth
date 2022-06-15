<template>
	<div class="d-table-row">
		<div class="d-table-cell pb-4 h-100">
			<div class="bg-light mb-4 py-2">
				<div class="container">
					<Header
						:user="user"
						:user-info="userInfo"
						:current-space="currentSpace"
						:wallets="wallets"
						@connectWallet="connectWallet"
					/>
				</div>
			</div>
			<div class="container position-relative">
				<router-view
					:user="user"
					:user-info="userInfo"
				/>
			</div>
		</div>
	</div>
	<div class="d-table-row">
		<div class="d-table-cell bg-light">
			<div class="container">
				<Footer />
			</div>
		</div>
	</div>
	<template v-if="!user">
		<LoginModal
			:google-auth="googleAuth"
			:facebook-auth="facebookAuth"
		/>
		<RegistrationModal
			:google-auth="googleAuth"
			:facebook-auth="facebookAuth"
		/>
		<ForgotPasswordModal />
		<ResetPasswordModal
			:reset-token="resetToken"
		/>
	</template>
</template>

<script>
import { useToast } from 'vue-toastification'
import Header from './components/Header.vue'
import Footer from './components/Footer.vue'
import LoginModal from './components/LoginModal.vue'
import RegistrationModal from './components/RegistrationModal.vue'
import ForgotPasswordModal from './components/ForgotPasswordModal.vue'
import ResetPasswordModal from './components/ResetPasswordModal.vue'
import config from './pool.config'
import {ethers, utils} from 'ethers'
import {conflux, Drip, getPosPoolContract, getSpaceContract, spaceProvider} from './utils/cfx'

const ONE_VOTE_CFX = 1000
const paddingZero = value => value < 10 ? `0${value}` : value

export default {
	components: {
		Header,
		Footer,
		LoginModal,
		RegistrationModal,
		ForgotPasswordModal,
		ResetPasswordModal,
	},
	props: {
		resetToken: String,
		user: Object,
		googleAuth: String,
		facebookAuth: String,
		referrer: String,
	},
	setup() {
		return {
			toast: useToast(),
		}
	},
	data() {
		return {
			currentSpace: localStorage.getItem('space') || 'core',
			chainStatus: {},
			poolContract: null,
			poolAddress: null,
			userInfo: {
				balance: 0,
				connected: false,
				userStaked: BigInt(0),
				available: BigInt(0),
				userInterest: 0,
				account: '',
				locked: BigInt(0),
				unlocked: BigInt(0),
				unlockedRaw: 0,
				userInQueue: [],
				userOutOueue: [],
			},
			wallets: [],
			eSpaceBlockNumber: 0,
		}
	},
	async created() {
		this.poolContract = this.currentSpace === 'core' ? getPosPoolContract(config.mainnet.poolAddress) : getSpaceContract(config.mainnet.spaceAddress)
		this.poolAddress = this.currentSpace === 'core' ? config.mainnet.poolAddress : config.mainnet.spaceAddress
	},
	async mounted() {
		if (this.referrer.indexOf(`${window.location.origin}/email/verify/`) === 0 && !this.user) { // Редирект при верификации почты
			this.toast.success('Вы успешно зарегистрировались!')
			bootstrap.Modal.getOrCreateInstance('#loginModal').show()
		} else if (this.resetToken && !this.user) { // Редирект при сбросе пароля
			bootstrap.Modal.getOrCreateInstance('#resetPasswordModal').show()
		}

		if (this.user) {
			await this.fetchWallets()
		}

		if (window.conflux) {
			window.conflux.on('accountsChanged', accounts => {
				if (accounts.length === 0) {
					this.userInfo.account = ''
					this.userInfo.connected = false
					localStorage.removeItem('userConnected')
				}
			})
		}

		if (window.ethereum) {
			window.ethereum.on('chainChanged', () => {
				this.resetUserInfo()
			})

			window.ethereum.on('accountsChanged', async ([newAddress]) => {
				if (newAddress === undefined) {
					return this.resetUserInfo()
				}

				localStorage.setItem('userConnected', 'true')
				await this.requestAccount(true, utils.getAddress(newAddress))
			})
		}

		if (this.user && window.conflux && localStorage.getItem('userConnected')) {
			await this.requestAccount(true)
		}
	},
	methods: {
		async connectWallet() {
			if (this.currentSpace === 'core') {
				if (!window.conflux) {
					alert(this.$t('install_conflux_wallet'));
					return;
				}
			} else {
				if (!window.ethereum) {
					alert('Please install Metamask');
					return;
				}

				if (+window.ethereum.networkVersion !== config.mainnet.eSpace.networkId) {
					alert('Please switch network to ' + config.mainnet.eSpace.networkId);
					return;
				}
			}

			const account = await this.requestAccount();

			if (!account) {
				alert(this.$t('request_account_failed'));
			} else {
				localStorage.setItem('userConnected', 'true')
			}
		},
		async requestAccount(isLocalStorage, address) {
			try {
				let account = address

				if (this.currentSpace === 'core') {
					const accounts = await window.conflux.request({
						method: 'cfx_requestAccounts'
					});
					account = accounts[0];

					if (!account) {
						return null
					}
				} else if (!account) {
					const provider = new ethers.providers.Web3Provider(window.ethereum);
					const accounts = await provider.send('eth_requestAccounts');

					if (accounts.length === 0) {
						alert('Request account failed');
						return;
					}

					account = utils.getAddress(accounts[0])
					this.eSpaceBlockNumber = await provider.getBlockNumber()
				}

				this.userInfo.account = account
				this.userInfo.connected = true

				await Promise.all([
					this.loadUserInfo(),
					this.loadLockingList(),
					this.loadUnlockingList(),
					this.saveWallet(),
				])

				return account;
			} catch (e) {
				if (isLocalStorage) {
					localStorage.removeItem('userConnected')
				}
			}
		},
		async loadUserInfo() {
			const userSummary = await this.poolContract.userSummary(this.userInfo.account);
			const userInterest = await this.poolContract.userInterest(this.userInfo.account);
			const balance = this.currentSpace === 'core' ? await conflux.cfx.getBalance(this.userInfo.account) : await spaceProvider.getBalance(this.userInfo.account);

			this.userInfo.userStaked = BigInt(userSummary[0].toString());
			this.userInfo.available = BigInt(userSummary[1].toString());
			this.userInfo.locked = BigInt(userSummary[2].toString());
			this.userInfo.unlocked = BigInt(userSummary[3].toString());
			this.userInfo.unlockedRaw = userSummary[3];
			this.userInfo.userInterest = this.trimPoints(Drip(userInterest.toString()).toCFX());
			this.userInfo.balance = this.trimPoints(Drip(balance).toCFX());
		},
		resetUserInfo() {
			this.userInfo.balance = 0
			this.userInfo.connected = false
			this.userInfo.userStaked = BigInt(0)
			this.userInfo.available = BigInt(0)
			this.userInfo.userInterest = 0
			this.userInfo.account = ''
			this.userInfo.locked = BigInt(0)
			this.userInfo.unlocked = BigInt(0)
			this.userInfo.unlockedRaw = 0
			this.userInfo.userInQueue = []
			this.userInfo.userOutOueue = []
			localStorage.removeItem('userConnected')
		},
		async saveWallet() {
			try {
				if (this.wallets.find(wallet => wallet.public_key === this.userInfo.account)) {
					return
				}

				await this.$api.post('/api/new_wallet', {
					user_id: this.user.id,
					public_key: this.userInfo.account,
					wallet_type: this.currentSpace.toUpperCase()
				})
				await this.fetchWallets()
			} catch (e) {}
		},
		async fetchWallets() {
			try {
				const response = await this.$api.get(`/api/get-wallets/${this.user.id}`)
				this.wallets = response.data
			} catch (e) {}
		},
		async loadLockingList() {
			let list = await this.poolContract['userInQueue(address)'](this.userInfo.account);
			this.userInfo.userInQueue = list.map(this.mapQueueItem);
		},
		async loadUnlockingList() {
			let list = await this.poolContract['userOutQueue(address)'](this.userInfo.account);
			this.userInfo.userOutOueue = list.map(this.mapQueueItem);
		},
		mapQueueItem(item) {
			let now = new Date().getTime();
			let unlockBlockNumber = Number(item[1].toString()) - this.chainStatus.blockNumber;
			let unlockTime = new Date(now + unlockBlockNumber / 2 * 1000);
			return {
				amount: this.voteToCFX(item[0]),
				endTime: this.formatDateTime(unlockTime),
			}
		},
		voteToCFX(vote) {
			return BigInt(vote.toString()) * BigInt(ONE_VOTE_CFX);
		},
		formatDateTime(date) {
			return `${date.getFullYear()}-${paddingZero(date.getMonth() + 1)}-${paddingZero(date.getDate())} ${paddingZero(date.getHours())}:${paddingZero(date.getMinutes())}:${paddingZero(date.getSeconds())}`;
		},
		trimPoints(str) {
			const parts = str.split('.');
			if (parts.length !== 2) {
				return str;
			}
			return `${parts[0]}.${parts[1].substr(0, 4)}`;
		},
	},
	watch: {
		$route(to) {
			if (to.redirectedFrom?.meta.auth) {
				bootstrap.Modal.getOrCreateInstance('#loginModal').show()
			}
		},
	},
}
</script>

<style>
@import 'assets/styles';
</style>
