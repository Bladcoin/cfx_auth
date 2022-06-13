<template>
	<router-view
		:user=user
		:google-auth=googleAuth
		:facebook-auth=facebookAuth
		:referrer=referrer
		@mounted="onMount"
	/>
</template>

<script>
import { useToast } from 'vue-toastification'
export default {
	props: {
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
	methods: {
		onMount() {
			if (this.referrer.indexOf(`${window.location.origin}/email/verify/`) === 0 && !this.user) {
				this.toast.success('Вы успешно зарегистрировались!')
				new bootstrap.Modal('#loginModal').show()
			}
		}
	}
}
</script>

<style>
@import 'assets/styles';
</style>
