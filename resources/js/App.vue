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
						:extensionPriority="extensionPriority"
						@connectWallet="connectWallet"
						@changeWallet="changeWallet"
						@deleteWallet="confirmDelete"
					/>
				</div>
			</div>
			<div class="container position-relative">
				<router-view
					:extensionPriority="extensionPriority"
					:user="user"
					:user-info="userInfo"
					:pool-address="poolAddress"
					:pool-contract="poolContract"
					@loadUserInfo="loadUserInfo"
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
	<div class="modal fade" id="deleteWalletModal" tabindex="-1" v-if="user">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header" style="border-bottom: none">
					<h5 class="modal-title">{{ $t('delete_wallet') }}</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body pt-0 pb-4">
					<div class="fw-bold text-truncate">
						{{ deletingWallet.address }}
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
						{{ $t('close') }}
					</button>
					<button
						type="button"
						class="position-relative btn btn-primary"
						:disabled="deletingWallet.loading"
						@click="deleteWallet"
					>
						<span :class="{invisible: deletingWallet.loading}">
							{{ $t('delete') }}
						</span>
						<span class="spinner spinner-border spinner-border-sm" v-if="deletingWallet.loading"></span>
					</button>
				</div>
			</div>
		</div>
	</div>
	<template v-else>
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
			extensionPriority: false,
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
				wallet: localStorage.getItem('wallet'),
				locked: BigInt(0),
				unlocked: BigInt(0),
				unlockedRaw: 0,
				userInQueue: [],
				userOutOueue: [],
			},
			wallets: [],
			deletingWallet: {
				id: null,
				address: null,
				loading: false,
			},
			eSpaceBlockNumber: 0,
		}
	},
	async created() {
		this.poolContract = this.currentSpace === 'core' ? getPosPoolContract(config.mainnet.poolAddress) : getSpaceContract(config.mainnet.spaceAddress)
		this.poolAddress = this.currentSpace === 'core' ? config.mainnet.poolAddress : config.mainnet.spaceAddress
		this.extensionPriority = !!(window.ethereum || window.conflux)
	},
	async mounted() {
		if (this.referrer.indexOf(`${window.location.origin}/email/verify/`) === 0 && !this.user) { // Редирект при верификации почты
			this.toast.success('Вы успешно зарегистрировались!')
			bootstrap.Modal.getOrCreateInstance('#loginModal').show()
		} else if (this.resetToken && !this.user) { // Редирект при сбросе пароля
			bootstrap.Modal.getOrCreateInstance('#resetPasswordModal').show()
		}

		if (this.user) {
			await Promise.all([
				await this.loadChainInfo(),
				await this.fetchWallets()
			])
		}

		if (window.conflux) {
			window.conflux.on('accountsChanged', accounts => {
				if (accounts.length === 0) {
					this.userInfo.account = ''
					this.userInfo.connected = false
					this.extensionPriority = true
					localStorage.removeItem('userConnected')
				}
			})
		}

		if (window.ethereum) {
			window.ethereum.on('chainChanged', () => {
				this.resetUserInfo()
				this.extensionPriority = true
			})

			window.ethereum.on('accountsChanged', async ([newAddress]) => {
				if (newAddress === undefined) {
					return this.resetUserInfo()
				}

				localStorage.setItem('userConnected', 'true')
				this.extensionPriority = true
				await this.requestAccount(true, utils.getAddress(newAddress))
			})
		}

		if (this.user && (window.conflux || window.ethereum) && localStorage.getItem('userConnected')) {
			await this.requestAccount(true)
		} else if (this.user && !window.conflux && !window.ethereum && this.userInfo.wallet) {
			this.extensionPriority = false
			await this.loadUserInfo()
			this.userInfo.connected = true
		}
	},
	methods: {
		async loadChainInfo() {
			this.chainStatus = await conflux.cfx.getStatus()
		},
		async connectWallet() {
			if (this.currentSpace === 'core') {
				if (!window.conflux) {
					alert(this.$t('install_conflux'));
					return;
				}
			} else {
				if (!window.ethereum) {
					alert(this.$t('install_metamask'));
					return;
				}

				if (+window.ethereum.networkVersion !== config.mainnet.eSpace.networkId) {
					alert(this.$t('switch_network', {id: config.mainnet.eSpace.networkId}));
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
		async changeWallet({wallet, type}) {
			const userForm = document.getElementById('user-form')

			localStorage.setItem('wallet', wallet)
			localStorage.setItem('space', type)

			if (this.currentSpace !== type) {
				window.location.reload()
				return
			}

			if (window.innerWidth < 768 && userForm) {
				userForm.scrollIntoView({behavior: 'smooth'})
			}

			this.userInfo.wallet = wallet
			this.extensionPriority = false
			await this.loadUserInfo()
			this.userInfo.connected = true
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
						alert(this.$t('request_account_failed'));
						return;
					}

					account = utils.getAddress(accounts[0])
					this.eSpaceBlockNumber = await provider.getBlockNumber()
				}

				this.userInfo.account = account
				this.userInfo.connected = true

				await Promise.all([
					this.loadUserInfo(),
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
			const account = this.extensionPriority ? this.userInfo.account : this.userInfo.wallet
			let userSummary, userInterest, balance, lockingList, unlockingList

			await Promise.all([
				this.poolContract.userSummary(account).then(response => { userSummary = response }),
				this.poolContract.userInterest(account).then(response => { userInterest = response }),
				this.currentSpace === 'core'
					? conflux.cfx.getBalance(account).then(response => { balance = response })
					: spaceProvider.getBalance(account).then(response => { balance = response }),
				this.poolContract['userInQueue(address)'](account).then(response => { lockingList = response }),
				this.poolContract['userOutQueue(address)'](account).then(response => { unlockingList = response }),
			])

			this.userInfo.userStaked = BigInt(userSummary[0].toString());
			this.userInfo.available = BigInt(userSummary[1].toString());
			this.userInfo.locked = BigInt(userSummary[2].toString());
			this.userInfo.unlocked = BigInt(userSummary[3].toString());
			this.userInfo.unlockedRaw = userSummary[3];
			this.userInfo.userInterest = this.trimPoints(Drip(userInterest.toString()).toCFX());
			this.userInfo.balance = this.trimPoints(Drip(balance).toCFX());
			this.userInfo.userInQueue = lockingList.map(this.mapQueueItem);
			this.userInfo.userOutOueue = unlockingList.map(this.mapQueueItem);
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
		confirmDelete({id, wallet}) {
			this.deletingWallet.id = id
			this.deletingWallet.address = wallet
			bootstrap.Modal.getOrCreateInstance('#deleteWalletModal').show()
		},
		async deleteWallet() {
			try {
				this.deletingWallet.loading = true
				await this.$api.delete(`/api/delete_wallet/${this.deletingWallet.id}`)
				this.deletingWallet.loading = false
				this.wallets = this.wallets.filter(wallet => wallet.id !== this.deletingWallet.id)
				this.deletingWallet.id = null
				this.deletingWallet.address = null
				bootstrap.Modal.getOrCreateInstance('#deleteWalletModal').hide()
				this.toast.success('Кошелек удален!')
			} catch (e) {
				this.deletingWallet.loading = false
			}
		}
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
