<template>
	<div class="mt-3 border rounded-3 px-4 py-2 bg-light">
		<div class="row justify-content-xl-between justify-content-xxl-around">
			<div class="col-6 col-md-4 col-lg-3 col-xl-auto py-3 text-center">
				<div class="pb-2">
					<img class="icon" src="../assets/img/bank.png" alt="">
				</div>
				<div>
					<span class="h-num" :title="formattedTotalLocked">
						{{ prettyTotalLocked }}
					</span>
					<div class="intro-title">
						{{ $t('staking_vault') }}
					</div>
				</div>
			</div>
			<div class="col-6 col-md-4 col-lg-3 col-xl-auto py-3 text-center">
				<div class="pb-2">
					<img class="icon" src="../assets/img/profits.png" alt="">
				</div>
				<div>
					<span class="h-num" :title="formattedTotalRevenue">
						{{ prettyTotalRevenue }}
					</span>
					<div class="intro-title">
						{{ $t('total_earned') }}
					</div>
				</div>
			</div>
			<div class="col-6 col-md-4 col-lg-3 col-xl-auto py-3 text-center">
				<div class="pb-2">
					<img class="icon" src="../assets/img/percentage.png" alt="">
				</div>
				<div>
					<span class="h-num">
						{{ poolInfo.apy }}
					</span>
					<div class="intro-title">
						{{ $t('expected_apy') }}
					</div>
				</div>
			</div>
			<div class="col-6 col-md-4 col-lg-3 col-xl-auto py-3 text-center">
				<div class="pb-2">
					<img class="icon" src="../assets/img/people.png" alt="">
				</div>
				<div>
					<span class="h-num">
						{{ poolInfo.stakerNumber }}
					</span>
					<div class="intro-title">
						{{ $t('staker_number') }}
					</div>
				</div>
			</div>
			<div class="col-6 col-md-4 col-lg-3 col-xl-auto py-3 text-center">
				<div class="pb-2">
					<img class="icon" src="../assets/img/empty-battery.png" alt="">
				</div>
				<div>
					<span v-if="poolInfo.status === 'error'" class="h-num text-danger">
						{{ $t('error') }}
					</span>
					<span v-else-if="poolInfo.status === 'warning'" class="h-num text-warning">
						{{ $t('warning') }}
					</span>
					<span v-else-if="poolInfo.status === 'success'" class="h-num text-success">
						{{ $t('good') }}
					</span>
					<span v-else class="h-num">&nbsp;</span>

					<div class="intro-title">
						{{ $t('pool_status') }}
					</div>
				</div>
			</div>
			<div class="col-6 col-md-4 col-lg-3 col-xl-auto py-3 text-center">
				<div class="pb-2">
					<img class="icon" src="../assets/img/servers.png" alt="">
				</div>
				<div>
					<span class="h-num text-success">
						{{ $t('great') }}
					</span>
					<div class="intro-title">
						{{ $t('server_status') }}
					</div>
				</div>
			</div>
			<div class="col-6 col-md-4 col-lg-3 col-xl-auto py-3 text-center">
				<div class="pb-2">
					<img class="icon" src="../assets/img/address.png" alt="">
				</div>
				<div>
					<a :href="posAddressLink" class="pos-address" target="_blank">
						{{ shortPosAddress }}
					</a>
					<div class="intro-title">
						{{ $t('pos_address') }}
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import {formatUnit, prettyFormat} from '../utils/index.js'

export default {
	name: 'Stats',
	props: {
		userInfo: Object,
		poolInfo: Object,
	},
	data() {
		return {

		}
	},
	computed: {
		formattedTotalLocked() {
			return formatUnit(this.poolInfo.totalLocked.toString(), "CFX");
		},
		prettyTotalLocked() {
			const totalLocked = this.poolInfo.totalLocked;
			if (Number(totalLocked) === 0) {
				return 0
			}
			return prettyFormat(totalLocked.toString());
		},
		stakingVault() {
			const totalAvailable = this.poolInfo.totalAvailable;
			if (Number(totalAvailable) === 0) {
				return 0
			}
			return formatUnit(totalAvailable.toString());
		},
		formattedTotalRevenue() {
			return formatUnit(this.poolInfo.totalRevenue.toString(), "CFX");
		},
		prettyTotalRevenue() {
			const totalRevenue = this.poolInfo.totalRevenue;
			if (Number(totalRevenue) === 0) {
				return 0
			}
			return prettyFormat(totalRevenue.toString());
		},
		posAddressLink() {
			return `https://confluxscan.io/pos/accounts/${this.poolInfo.posAddress}`;
		},
		shortPosAddress() {
			if (!this.poolInfo.posAddress) {
				return 'Loading...';
			}
			const start = this.poolInfo.posAddress.slice(0, 6);
			return `${start}...`;
		},
	},
	methods: {

	}
}
</script>

<style scoped>
.icon {
	width: 32px;
}
.pos-address {
	line-height: 27px;
	text-decoration: none;
}
</style>
