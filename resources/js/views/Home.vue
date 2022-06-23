<template>
	<div class="mb-4">
		<div class="text-center">
			<div class="mb-2">
				<img src="../assets/img/logo.svg" class="logo img-fluid" width="377" height="203" alt="">
			</div>
			{{ translations && translations.about[$i18n.locale] }}
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

	<div id="user-form" :class="{'pt-4': userInfo.connected}">
		<Form
			v-if="userInfo.connected"
			:extensionPriority="extensionPriority"
			:poolContract="poolContract"
			:poolAddress="poolAddress"
			:poolInfo="poolInfo"
			:userInfo="userInfo"
			:currentSpace="currentSpace"
			@loadUserInfo="$emit('loadUserInfo')"
		/>
	</div>

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
				{{ translations && translations.features[$i18n.locale] }}
			</div>
		</div>
	</div>
	<div class="mt-4 border rounded-3 p-3 bg-light">
		<h5>{{ $t('staking_rules') }}</h5>
		<div class="pre-line">
			{{ translations && translations.rules[$i18n.locale] }}
		</div>
	</div>
</template>

<script>
import { toRaw } from 'vue'
import moment from 'moment/min/moment-with-locales'
import Stats from '../components/Stats.vue'
import Form from '../components/Form.vue'
import { conflux, Drip, getPosAccountByPowAddress } from '../utils/cfx'
import { StatusPosNode } from '../constants'
import {formatUnit, formatTime} from '../utils/index.js'
import config from '../pool.config.js'

const ONE_VOTE_CFX = 1000

export default {
	name: 'Home',
	components: {
		Stats,
		Form,
	},
	props: {
		translations: Object,
		extensionPriority: Boolean,
		user: Object,
		userInfo: Object,
		poolAddress: String,
		poolContract: Object,
	},
	emits: ['loadUserInfo'],
	data() {
		return {
			currentSpace: localStorage.getItem('space') || 'core',
			moment: moment,
			isLoading: false,
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
				posAddress: config.mainnet.posAddress,
				inCommittee: false,
				totalAvailable: 0,
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
			this.moment.locale(this.$i18n.locale)

			await Promise.all([
				this.loadPoolInfo(),
				this.loadLastRewardInfo(),
				this.loadRewardData(),
				this.loadIncomingHistory(),
			])

			//this.isLoading = false
		} catch (e) {
			//this.isLoading = false
		}
	},
	async mounted() {

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
		async loadPoolInfo() {
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
				getPosAccountByPowAddress(this.poolAddress).then(response => { account = response }).catch(e => {console.log(e)}),
				poolContract.poolUserShareRatio().then(response => { userShareRatio = response }).catch(e => {console.log(e)}),
			])

			this.poolInfo.totalLocked = BigInt(poolSummary[0].toString()) * BigInt(ONE_VOTE_CFX) * BigInt('1000000000000000000')
			this.poolInfo.totalRevenue = BigInt(Drip(poolSummary[2].toString()))
			this.poolInfo.apy = Number(apy) / 100
			this.poolInfo.fee = Number((BigInt(10000) - BigInt(Array.isArray(userShareRatio) ? userShareRatio[0] : userShareRatio)) / BigInt(100))
			this.poolInfo.stakerNumber = stakerNumber.toString()
			this.poolInfo.status = account.status?.forceRetired === null ? StatusPosNode.success : StatusPosNode.error
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
	}
}
</script>

