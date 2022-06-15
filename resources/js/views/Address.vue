<template>
	<div class="preloader rounded-3" v-if="isLoading">
		<div class="spinner-border text-primary"></div>
	</div>
	<template v-else-if="wallet">
		<h5 class="mb-4 text-break">
			{{ wallet.wallet_typ === 'CORE' ? 'Core' : 'eSpace' }} wallet:
			<span class="text-muted fs-5">
				{{ address }}
			</span>
		</h5>
	</template>
	<h1 v-else>Кошелек не найден</h1>
</template>

<script>
export default {
	name: 'Address',
	props: {
		user: Object,
	},
	data() {
		return {
			isLoading: true,
			wallets: [],
			address: this.$route.params.wallet,
		}
	},
	async created() {
		await this.fetchWallets()
	},
	computed: {
		wallet() {
			return this.wallets.find(wallet => wallet.public_key === this.address)
		}
	},
	methods: {
		async fetchWallets() {
			try {
				this.isLoading = true
				const response = await this.$api.get(`/api/get-wallets/${this.user.id}`)
				this.wallets = response.data
				this.isLoading = false
			} catch (e) {
				this.isLoading = false
			}
		},
	},
}
</script>

<style scoped>

</style>
