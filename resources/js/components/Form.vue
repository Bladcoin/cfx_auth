<template>
	<div class="border rounded-3 p-4 bg-light">
		<form>
			<fieldset :disabled="!extensionPriority && userInfo.wallet !== userInfo.account">
				<div class="row">
					<div class="col-lg-3 col-md-6 col-12">
						<div>
							<div><span class="h-num">{{ userStakedCFX }}</span></div>
							<span class="intro-title">{{ $t('my_staked') }}</span>
						</div>
						<div class="row mt-2">
							<div class="col-md-7 col-12 mb-2">
								<input class="form-control" type="number" v-model="stakeCount">
							</div>
							<div class="col-md-5 col-12 mb-2">
								<button
									type="button"
									class="position-relative btn btn-primary"
									@click="stake"
									:disabled="!userInfo.connected || stakeLoading"
								>
									{{ $t('stake') }}
								</button>
							</div>
						</div>
						<p class="intro-title" v-if="userInfo.connected">
							{{ $t('balance') }}: {{ userInfo.balance }} CFX
						</p>
					</div>
					<div class="col-lg-3 col-md-6 col-12">
						<div>
							<div><span class="h-num">{{ userInfo.userInterest }}</span></div>
							<span class="intro-title">{{ $t('my_rewards') }}</span>
						</div>
						<button
							type="button"
							class="position-relative btn btn-primary mt-2"
							@click="claim"
							:disabled="!userInfo.connected || claimLoading"
						>
							<span :class="{invisible: claimLoading}">
								{{ $t('claim') }}
							</span>
							<span class="spinner spinner-border spinner-border-sm" v-if="claimLoading"></span>
						</button>
						<p class="intro-title mt-2" v-if="userInfo.connected && poolInfo.lastRewardTime > 0">
							{{ $t('last_update') }}: {{ lastRewardTime }}
						</p>
					</div>
					<div class="col-lg-3 col-md-6 col-12">
						<div>
							<div><span class="h-num">{{ unstakeableCFX }}</span></div>
							<span class="intro-title">{{ $t('unstakeable') }}</span>
						</div>
						<div class="row mt-2">
							<div class="col-md-7 col-12 mb-2">
								<input class="form-control" type="number" v-model="unstakeCount">
							</div>
							<div class="col-md-5 col-12">
								<button
									type="button"
									class="position-relative btn btn-primary"
									@click="unstake"
									:disabled="!userInfo.connected || unstakeLoading"
								>
									{{ $t('unstake') }}
								</button>
							</div>
						</div>
					</div>
					<div class="col-lg-3 col-md-6 col-12 mt-3 mt-md-0">
						<div>
							<div><span class="h-num">{{ withdrawableCFX }}</span></div>
							<span class="intro-title">{{ $t('withdrawable') }}</span>
						</div>
						<button
							type="button"
							class="position-relative btn btn-primary mt-2"
							@click="withdraw"
							:disabled="!userInfo.connected || withdrawLoading"
						>
							<span :class="{invisible: withdrawLoading}">
								{{ $t('withdraw') }}
							</span>
							<span class="spinner spinner-border spinner-border-sm" v-if="withdrawLoading"></span>
						</button>
					</div>
				</div>
			</fieldset>
		</form>
	</div>

	<div class="modal fade" id="stakeModal" tabindex="-1">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">{{ $t('confirm') }}</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<div class="h6 text-center text-danger mt-2 mb-4">
						{{ $t('stake_modal_title') }}
					</div>
					<div class="pre-line">
						{{ $t('stake_modal_text') }}
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
						{{ $t('cancel') }}
					</button>
					<button
						type="button"
						class="btn btn-primary position-relative"
						@click="submit('stake')"
						:disabled="stakeLoading"
					>
						<span :class="{invisible: stakeLoading}">
							{{ $t('stake') }}
						</span>
						<span class="spinner spinner-border spinner-border-sm" v-if="stakeLoading"></span>
					</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="unstakeModal" tabindex="-1">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">{{ $t('confirm') }}</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<div class="pre-line">
						{{ $t('unstake_modal_text') }}
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
						{{ $t('cancel') }}
					</button>
					<button
						type="button"
						class="btn btn-primary position-relative"
						@click="submit('unstake')"
						:disabled="unstakeLoading"
					>
						<span :class="{invisible: unstakeLoading}">
							{{ $t('unstake') }}
						</span>
						<span class="spinner spinner-border spinner-border-sm" v-if="unstakeLoading"></span>
					</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="hashModal" tabindex="-1">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header" style="border-bottom: none">
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body pt-0 pb-5 text-center">
					<div class="mb-3"><i class="bi-check-circle-fill" style="font-size: 40px; color: rgb(100, 237, 107);"></i></div>
					<h6>{{ $t('transaction_submitted') }}</h6>
					<p>{{ $t('transaction_hash') }}: <a :href="`https://confluxscan.io/transaction/${txHash}`" target="_blank">{{ txHash.slice(0, 7) }}</a></p>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="withdrawModal" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header" style="border-bottom: none">
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body pt-0 pb-5 text-center">
					<p><i class="bi-hourglass-split" style="font-size: 40px; color: cornflowerblue;"></i></p>
					<p>{{ $t('unlock_time_text') }}</p>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import BigNumber from 'bignumber.js'
import { Unit, sendTransaction as sendTransactionWithFluent } from '@cfxjs/use-wallet'
import { provider as metaMaskProvider, sendTransaction as sendTransactionWithMetaMask } from '@cfxjs/use-wallet/dist/ethereum'
import { format } from '../utils/cfx'
import { utils } from 'ethers'
import { formatDateTime, calculateGasMargin } from '../utils/index.js'
import poolConfig from '../pool.config.js'
import { abi as posPoolAbi } from '../abi/IPoSPool.json'
const ONE_VOTE_CFX = 1000
const poolInterface = new utils.Interface(posPoolAbi)

export default {
	name: 'Form',
	props: {
		extensionPriority: Boolean,
		poolInfo: Object,
		poolAddress: String,
		userInfo: Object,
		poolContract: Object,
		currentSpace: String,
	},
	emits: ['loadUserInfo'],
	data() {
		return {
			stakeLoading: false,
			unstakeLoading: false,
			claimLoading: false,
			withdrawLoading: false,
			stakeModal: null,
			unstakeModal: null,
			hashModal: null,
			withdrawModal: null,
			stakeCount: 0,
			unstakeCount: 0,
			txHash: '',
		}
	},
	computed: {
		userStakedCFX() {
			return this.userInfo.userStaked * BigInt(ONE_VOTE_CFX);
		},
		unstakeableCFX() {
			return this.userInfo.locked * BigInt(ONE_VOTE_CFX);
		},
		lastRewardTime() {
			const lastTime = new Date(this.poolInfo.lastRewardTime * 1000);
			return formatDateTime(lastTime);
		},
		withdrawableCFX() {
			return this.userInfo.unlocked * BigInt(ONE_VOTE_CFX);
		},
		isFormDisabled() {

		},
	},
	mounted() {
		this.stakeModal = new bootstrap.Modal(document.getElementById('stakeModal'), {})
		this.unstakeModal = new bootstrap.Modal(document.getElementById('unstakeModal'), {})
		this.hashModal = new bootstrap.Modal(document.getElementById('hashModal'), {})
		this.withdrawModal = new bootstrap.Modal(document.getElementById('withdrawModal'), {})
	},
	methods: {
		async submit(type) {
			try {
				let data = ''
				let estimateData = {}
				let value = 0

				switch (type) {
					case 'stake':
						this.stakeLoading = true

						value = new BigNumber(this.stakeCount)
							.multipliedBy(10 ** 18)
							.toString(10)

						const stakeVote = new BigNumber(this.stakeCount)
							.dividedBy(ONE_VOTE_CFX)
							.toString(10)

						if (this.currentSpace === 'core') {
							estimateData = await this.poolContract
								.increaseStake(stakeVote)
								.estimateGasAndCollateral({
									from: this.userInfo.account,
									value,
								})

							data = this.poolContract.increaseStake(stakeVote).data
						} else {
							data = poolInterface.encodeFunctionData('increaseStake', [stakeVote])
						}

						break
					case 'unstake':
						this.unstakeLoading = true
						value = 0
						const unstakeVote = new BigNumber(this.unstakeCount)
							.dividedBy(ONE_VOTE_CFX)
							.toString(10)

						if (this.currentSpace === 'core') {
							data = this.poolContract.decreaseStake(unstakeVote).data
							estimateData = await this.poolContract
								.decreaseStake(unstakeVote)
								.estimateGasAndCollateral({
									from: this.userInfo.account,
								})
						} else {
							data = poolInterface.encodeFunctionData('decreaseStake', [
								unstakeVote,
							])
						}

						break
					case 'claim':
						this.claimLoading = true
						value = 0

						if (this.currentSpace === 'core') {
							data = this.poolContract.claimAllInterest().data
							estimateData = await this.poolContract
								.claimAllInterest()
								.estimateGasAndCollateral({
									from: this.userInfo.account,
								})
						} else {
							data = poolInterface.encodeFunctionData('claimAllInterest', [])
						}

						break
					case 'withdraw':
						this.withdrawLoading = true
						value = 0

						if (this.currentSpace === 'core') {
							data = this.poolContract.withdrawStake(
								new BigNumber(this.userInfo.unlockedRaw?._hex || this.userInfo.unlockedRaw).toString(10),
							).data
							estimateData = await this.poolContract
								.withdrawStake(new BigNumber(this.userInfo.unlockedRaw).toString(10))
								.estimateGasAndCollateral({
									from: this.userInfo.account,
								})
						} else {
							data = poolInterface.encodeFunctionData('withdrawStake', [
								new BigNumber(this.userInfo.unlockedRaw?._hex || this.userInfo.unlockedRaw).toString(10),
							])
						}

						break
					default:
						break
				}

				const txParams = {
					to:	this.currentSpace === 'core'
						? format.address(this.poolAddress, poolConfig.mainnet.core.networkId)
						: this.poolAddress,
					data,
					value: Unit.fromMinUnit(value).toHexMinUnit()
				}

				if (this.currentSpace === 'espace') {
					estimateData.gasLimit = await metaMaskProvider.request({
						method: 'eth_estimateGas',
						params: [
							{
								from: this.userInfo.account,
								data,
								to: this.poolAddress,
								value: Unit.fromMinUnit(value).toHexMinUnit(),
							},
						],
					})
				}

				if (estimateData?.gasLimit) {
					txParams.gas = Unit.fromMinUnit(calculateGasMargin(estimateData?.gasLimit || 0)).toHexMinUnit()
				}
				if (estimateData?.storageCollateralized) {
					txParams.storageLimit = Unit.fromMinUnit(calculateGasMargin(String(estimateData?.storageCollateralized || 0))).toHexMinUnit()
				}

				if (type === 'stake') {
					this.stakeModal.hide()
					this.stakeLoading = false
				}

				if (type === 'unstake') {
					this.unstakeModal.hide()
					this.unstakeLoading = false
				}

				this.txHash = this.currentSpace === 'core' ? await sendTransactionWithFluent(txParams) : await sendTransactionWithMetaMask(txParams)

				this.hashModal.show()

				if (type === 'stake') {
					this.stakeCount = 0;
				}

				if (type === 'unstake') {
					this.unstakeCount = 0;
				}

				if (type === 'claim') {
					this.claimLoading = false
					alert(this.$t('claim_success'));
				}

				if (type === 'withdraw') {
					this.withdrawLoading = false
					alert(this.$t('withdraw_success'))
				}

				this.$emit('loadUserInfo')
			} catch (error) {
				console.error('error', error)
				if (type === 'stake') {
					this.stakeLoading = false
				}
				if (type === 'unstake') {
					this.unstakeLoading = false
				}
				if (type === 'claim') {
					this.claimLoading = false
					alert(this.$t('claim_failed'));
				}
				if (type === 'withdraw') {
					this.withdrawLoading = false
					alert(this.$t('withdraw_failed'));
				}
			}
		},

		stake() {
			if (this.stakeCount === 0 || this.stakeCount % ONE_VOTE_CFX !== 0 ) {
				alert(this.$t('stake_count_multiple'));
				return;
			}
			if (Number(this.userInfo.balance) < Number(this.stakeCount)) {
				alert(this.$t('insufficient_balance'));
				return;
			}

			this.stakeModal.show()
		},

		unstake() {
			if (this.userInfo.locked === BigInt(0)) {
				alert(this.$t('no_unstakeable_funds'));
				return;
			}
			if (this.unstakeCount === 0 || this.unstakeCount % ONE_VOTE_CFX !== 0 ) {
				alert(this.$t('unstake_count_multiple'));
				return;
			}

			this.unstakeModal.show()
		},

		claim() {
			if (this.userInfo.userInterest === 0 || this.userInfo.userInterest === '0') {
				alert(this.$t('no_claimable_interest'));
				return;
			}

			this.submit('claim')
		},

		withdraw() {
			if (this.userInfo.unlocked === BigInt(0)) {
				alert(this.$t('no_withdrawable_funds'));
				return;
			}

			this.submit('withdraw')
		},
	}
}
</script>

<style scoped>

</style>
