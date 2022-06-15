<template>
	<div class="mb-4">
		<div class="text-center">
			<div class="mb-2">
				<img src="../assets/img/logo.svg" class="logo img-fluid" width="377" height="203" alt="">
			</div>
			{{ $t('tagline') }}
		</div>
	</div>

	<div class="position-relative">
		<Stats :poolInfo="poolInfo" />
		<div class="preloader rounded-3" v-if="isLoading">
			<div class="spinner-border text-primary"></div>
		</div>
	</div>

	<div class="mt-4 border rounded-3 p-2 bg-light" v-show="showChart">
		<canvas ref="chart" class="chart"></canvas>
	</div>

	<Form
		:poolContract="poolContract"
		:poolInfo="poolInfo"
		:userInfo="userInfo"
		:currentSpace="currentSpace"
		v-if="userInfo.connected"
	/>

	<div
		class="mt-4 border rounded-3 p-4 bg-light"
		v-if="userInfo.connected && (userInfo.userInQueue.length || userInfo.userOutOueue.length)"
	>
		<div class="row">
			<div class="col-md-6 col-12">
				<table class="table table-striped table-bordered caption-top">
					<caption>{{ $t('locking_votes') }}</caption>
					<thead>
					<tr>
						<th class="w-50">{{ $t('amount') }} (CFX)</th>
						<th class="w-50">{{ $t('date') }}</th>
					</tr>
					</thead>
					<tbody>
					<tr v-for="item in userInfo.userInQueue">
						<th>{{item.amount}}</th>
						<td>{{item.endTime}}</td>
					</tr>
					</tbody>
				</table>
			</div>
			<div class="col-md-6 col-12">
				<table class="table table-striped table-bordered caption-top">
					<caption>{{ $t('unlocking_votes') }}</caption>
					<thead>
					<tr>
						<th class="w-50">{{ $t('amount') }} (CFX)</th>
						<th class="w-50">{{ $t('date') }}</th>
					</tr>
					</thead>
					<tbody>
					<tr v-for="item in userInfo.userOutOueue">
						<th>{{item.amount}}</th>
						<td>{{item.endTime}}</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="mt-4" v-if="!isLoading">
		<ul class="nav nav-tabs border-bottom-0">
			<li class="nav-item">
				<a
					class="nav-link"
					:class="{active: activeTabIndex === 0}"
					href="#"
					@click.prevent="activeTabIndex = 0"
				>
					{{ $t('incoming_history') }}
				</a>
			</li>
			<li class="nav-item">
				<a
					class="nav-link"
					:class="{active: activeTabIndex === 1}"
					href="#"
					@click.prevent="activeTabIndex = 1"
				>
					{{ $t('voting_history') }}
				</a>
			</li>
			<li class="nav-item">
				<a
					class="nav-link"
					:class="{active: activeTabIndex === 2}"
					href="#"
					@click.prevent="activeTabIndex = 2"
				>
					{{ $t('pending_rights_status') }}
				</a>
			</li>
		</ul>

		<div class="border rounded-bottom p-4 bg-light">
			<template v-if="activeTabIndex === 0">
				<div class="text-center" v-if="incomingHistoryLoading">
					<div class="mx-auto spinner-border text-primary"></div>
				</div>
				<table class="table table-striped table-bordered caption-top" v-else-if="incomingHistory && incomingHistory.length !== 0">
					<thead>
					<tr>
						<th>{{ $t('pow_block_hash') }}</th>
						<th>{{ $t('incoming') }}</th>
						<th>{{ $t('date') }}</th>
					</tr>
					</thead>
					<tbody>
					<tr v-for="item in incomingHistory">
						<td>
							<a class="text-decoration-none" :href="`https://confluxscan.io/block/${item.powBlockHash}`" target="_blank">
								{{item.powBlockHash.slice(0, 11)}}...
							</a>
						</td>
						<td>{{ item.reward }} CFX</td>
						<td>{{ item.age }}</td>
					</tr>
					</tbody>
				</table>
				<h5 v-else>
					{{ $t('no_matching_entries') }}
				</h5>
			</template>

			<template v-if="activeTabIndex === 1">
				<h5>{{ $t('no_matching_entries') }}</h5>
			</template>

			<template v-if="activeTabIndex === 2">
				<h5>{{ $t('no_matching_entries') }}</h5>
			</template>
		</div>
	</div>

	<div class="mt-4">
		<div class="alert alert-success">
			<h5>{{ $t('features') }}</h5>
			<div class="pre-line">
				{{ $t('features_text') }}
			</div>
		</div>
	</div>
	<div class="mt-4 border rounded-3 p-3 bg-light">
		<h5>{{ $t('staking_rules') }}</h5>
		<div class="pre-line">
			{{ $t('staking_rules_text') }}
		</div>
	</div>
</template>

<script>
import { toRaw } from 'vue'
import moment from 'moment/min/moment-with-locales'
import Stats from '../components/Stats.vue'
import Form from '../components/Form.vue'
import { conflux, confluxSpace, getPosPoolContract, getSpaceContract, posPoolManagerContract, Drip, address, getPosAccountByPowAddress, spaceProvider } from '../utils/cfx'
import { BigNumber, utils, ethers } from 'ethers'
import { StatusPosNode } from '../constants'
import {formatUnit, formatTime} from '../utils/index.js'
import config from '../pool.config.js'

const ONE_VOTE_CFX = 1000

function paddingZero(value) {
	return value < 10 ? `0${value}` : value;
}

export default {
	name: 'Home',
	components: {
		Stats,
		Form,
	},
	props: {
		user: Object,
		//userInfo: Object,
	},
	data() {
		return {
			currentSpace: localStorage.getItem('space') || 'core',
			moment: moment,
			isLoading: false,
			chainStatus: {},
			poolContract: null,
			wallets: [],
			showChart: false,
			poolInfo: {
				fee: 0,
				status: null,
				name: '',
				totalLocked: 0,
				totalRevenue: 0,
				userShareRatio: 0,
				apy: 0,
				lastRewardTime: 0,
				stakerNumber: '0',
				poolAddress: null,
				posAddress: config.mainnet.posAddress,
				inCommittee: false,
				totalAvailable: 0,
			},
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
			activeTabIndex: 0,
			incomingHistory: null,
			incomingHistoryLoading: false,
			chart: null,
			eSpaceBlockNumber: 0,
		}
	},
	async created() {
		try {
			//this.isLoading = true
			this.poolContract = this.currentSpace === 'core' ? getPosPoolContract(config.mainnet.poolAddress) : getSpaceContract(config.mainnet.spaceAddress)
			this.poolInfo.poolAddress = this.currentSpace === 'core' ? config.mainnet.poolAddress : config.mainnet.spaceAddress
			this.moment.locale(this.$i18n.locale)

			//await this.loadServerStatus()
			await Promise.all([
				this.loadPoolInfo(),
				this.loadChainInfo(),
				this.loadLastRewardInfo(),
				this.loadRewardData(),
				this.loadIncomingHistory(),
			])

			this.isLoading = false
		} catch (e) {
			this.isLoading = false
		}
	},
	async mounted() {
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
	watch: {
		async activeTabIndex(index) {
			if (index === 0) {
				await this.loadIncomingHistory()
			}
		},
	},
	methods: {
		async loadServerStatus() {
			try {
				const fetchResult = await fetch('http://217.30.173.35');
				const result = await fetchResult.json();
				if (fetchResult.ok) {
					return result;
				}
			} catch (e) {
				console.log(e)
			}
		},
		async loadChainInfo() {
			const status = await conflux.cfx.getStatus();
			this.chainStatus = status;
			return status;
		},
		async loadPoolInfo() {
			// try {
			// 	console.log(posPoolManagerContract)
			// 	const pools = await posPoolManagerContract.getPools()
			// 	console.log('loadPoolInfo done')
			// 	console.log(pools[0][3])
			// 	// const poolAddress = pools[0][3]
			// } catch (e) {
			// 	console.log(e)
			// }

			const poolAddress = this.poolInfo.poolAddress
			const poolContract = toRaw(this.poolContract)

			let poolSummary
			let stakerNumber
			let apy
			let account
			let userShareRatio

			await Promise.all([
				poolContract.poolSummary().then(response => { poolSummary = response }).catch(e => {console.log(e)}),
				poolContract.stakerNumber().then(response => { stakerNumber = response }).catch(e => {console.log(e)}),
				poolContract.poolAPY().then(response => { apy = response }).catch(e => {console.log(e)}),
				getPosAccountByPowAddress(poolAddress).then(response => { account = response }).catch(e => {console.log(e)}),
				poolContract.poolUserShareRatio().then(response => { userShareRatio = response }).catch(e => {console.log(e)}),
			])

			this.poolInfo.totalLocked = BigInt(poolSummary[0].toString()) * BigInt(ONE_VOTE_CFX) * BigInt('1000000000000000000')
			this.poolInfo.totalRevenue = BigInt(Drip(poolSummary[2].toString()))
			this.poolInfo.apy = Number(apy) / 100
			this.poolInfo.fee = Number((BigInt(10000) - BigInt(Array.isArray(userShareRatio) ? userShareRatio[0] : userShareRatio)) / BigInt(100))
			this.poolInfo.stakerNumber = stakerNumber.toString()

			this.poolInfo.status = account.status?.forceRetired === null ? StatusPosNode.success : StatusPosNode.error
			//this.poolInfo.poolAddress = poolAddress
			//this.poolInfo.posAddress = account.address
		},

		async loadIncomingHistory() {
			try {
				this.incomingHistoryLoading = true
				this.incomingHistory = null
				const url = `https://confluxscan.io/stat/list-pos-account-reward?identifier=${this.poolInfo.posAddress}&limit=10&orderBy=createdAt&reverse=true&skip=0&tab=incoming-history`;
				const response = await fetch(url)
				const history = await response.json()
				const list = history.list.map(item => {
					const formatted = formatUnit(item.reward, 'CFX');
					const onlyValue = formatted.split(' ')[0];
					return {
						age: moment(item.createdAt).fromNow(),
						reward: Number(onlyValue),
						powBlockHash: item.powBlockHash
					}
				});
				this.incomingHistory = list
				this.incomingHistoryLoading = false
			} catch (e) {
				this.incomingHistory = null
				this.incomingHistoryLoading = false
				throw e
			}
		},

		async loadRewardData() {
			const url = `https://confluxscan.io/stat/list-pos-account-reward?identifier=${this.poolInfo.posAddress}&limit=20&orderBy=createdAt&reverse=true`;
			fetch(url)
			.then(response => response.json())
			.then(data => {
				this.initChart(data);
			})
		},

		initChart(rewards) {
			const { list } = rewards;
			const labels = list.map(item => formatTime(new Date(item.createdAt)));
			const items = list.map(item => {
				const formatted = formatUnit(item.reward, 'CFX');
				const onlyValue = formatted.split(' ')[0];
				return Number(onlyValue);
			});

			const data = {
				labels: labels.reverse(),
				datasets: [{
					label: this.$t('rewards_per_hour'),
					backgroundColor: '#ffab4a',
					borderColor: '#F53838',
					data: items.reverse(),
				}]
			};

			const config = {
				type: 'line',
				data: data,
				options: {
					maintainAspectRatio: false,
					animation: {
						onComplete: function() {
							this.animationComplete = true
						}
					},
				}
			};

			this.showChart = true
			this.chart = new Chart(this.$refs.chart, config)
		},

		async loadLastRewardInfo() {
			const {epoch} = await conflux.pos.getStatus();
			let lastReward = await conflux.pos.getRewardsByEpoch(epoch - 1);
			if (!lastReward) {
				lastReward = await conflux.pos.getRewardsByEpoch(epoch - 2);
			}
			const block = await conflux.cfx.getBlockByHash(lastReward.powEpochHash, false);
			this.poolInfo.lastRewardTime = block.timestamp;
		},

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
	}
}
</script>

