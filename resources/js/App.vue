<template>
	<router-view
		:user=user
		:google-auth=googleAuth
		:facebook-auth=facebookAuth
		@mounted="onMount"
	/>
	<LoginModal
		:googleAuth="googleAuth"
		:facebookAuth="facebookAuth"
		v-if="!user"
	/>
	<RegistrationModal
		:googleAuth="googleAuth"
		:facebookAuth="facebookAuth"
		v-if="!user"
	/>
	<ForgotPasswordModal v-if="!user" />
	<ResetPasswordModal v-if="!user" />
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
			} else if (this.referrer.indexOf(`${window.location.origin}/reset-password/`) === 0 && !this.user) {
				new bootstrap.Modal('#resetPasswordModal').show()
			}
		}
	}
}
</script>

<style>
@import 'assets/styles';
</style>
