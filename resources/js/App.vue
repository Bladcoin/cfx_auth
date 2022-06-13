<template>
	<router-view
		:user=user
	/>
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
import LoginModal from './components/LoginModal.vue'
import RegistrationModal from './components/RegistrationModal.vue'
import ForgotPasswordModal from './components/ForgotPasswordModal.vue'
import ResetPasswordModal from './components/ResetPasswordModal.vue'

export default {
	components: {
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
	mounted() {
		this.onMount()
	},
	methods: {
		onMount() {
			if (this.referrer.indexOf(`${window.location.origin}/email/verify/`) === 0 && !this.user) {
				this.toast.success('Вы успешно зарегистрировались!')
				bootstrap.Modal.getOrCreateInstance('#loginModal').show()
			} else if (this.resetToken && !this.user) {
				bootstrap.Modal.getOrCreateInstance('#resetPasswordModal').show()
			}
		}
	}
}
</script>

<style>
@import 'assets/styles';
</style>
