<template>
	<div class="modal fade" id="resetPasswordModal" ref="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header" style="border-bottom: none">
					<h5 class="modal-title">Сброс пароля</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body pt-0 pb-4">
					<form @submit.prevent="onSubmit" novalidate>
						<div class="mb-3">
							<input
								v-model.trim="form.password"
								type="password"
								class="form-control"
								:class="{'is-invalid': v$.form.password.$error && submitted}"
								placeholder="Новый пароль"
							>
							<div v-if="v$.form.password.required.$invalid && submitted" class="invalid-feedback">
								Введите пароль
							</div>
							<div v-else-if="v$.form.password.minLength.$invalid && submitted" class="invalid-feedback">
								Длина не менее {{ v$.form.password.minLength.$params.min }} символов
							</div>
						</div>
						<div class="mb-3">
							<input
								v-model.trim="form.passwordConfirmation"
								type="password"
								class="form-control"
								:class="{'is-invalid': v$.form.passwordConfirmation.$error && submitted}"
								placeholder="Повторите пароль"
							>
							<div v-if="v$.form.passwordConfirmation.required.$invalid && submitted" class="invalid-feedback">
								Введите пароль еще раз
							</div>
							<div v-else-if="v$.form.passwordConfirmation.minLength.$invalid && submitted" class="invalid-feedback">
								Длина не менее {{ v$.form.passwordConfirmation.minLength.$params.min }} символов
							</div>
							<div v-else-if="v$.form.passwordConfirmation.sameAsPassword.$invalid && submitted" class="invalid-feedback">
								Пароли не совпадают
							</div>
						</div>
						<button
							class="position-relative btn btn-primary"
							:disabled="isLoading"
						>
							<span :class="{invisible: isLoading}">
								Сохранить новый пароль
							</span>
							<span class="spinner spinner-border spinner-border-sm" v-if="isLoading"></span>
						</button>
						<div class="alert alert-danger mt-3" v-if="errorMessage">
							{{ errorMessage }}
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { useToast } from 'vue-toastification'
import useVuelidate from '@vuelidate/core'
import {required, minLength} from '@vuelidate/validators'

export default {
	name: 'ResetPasswordModal',
	props: {
		resetToken: String,
	},
	setup() {
		return {
			toast: useToast(),
			v$: useVuelidate(),
		}
	},
	data() {
		return {
			submitted: false,
			isLoading: false,
			success: false,
			errorMessage: '',
			form: {
				password: '',
				passwordConfirmation: '',
			}
		}
	},
	validations() {
		return {
			form: {
				password: {
					required,
					minLength: minLength(6),
				},
				passwordConfirmation: {
					required,
					minLength: minLength(6),
					sameAsPassword: password => password === this.form.password,
				}
			}
		}
	},
	mounted() {
		this.$refs.modal.addEventListener('hidden.bs.modal', () => {
			this.reset()
		})
	},
	methods: {
		async onSubmit() {
			this.submitted = true
			this.v$.form.$touch()

			if (!this.v$.form.$invalid) {
				await this.resetPassword()
			}
		},
		async resetPassword() {
			try {
				this.isLoading = true
				await this.$api.post('/reset-password', {
					email: this.$route.query.email,
					password: this.form.password,
					password_confirmation: this.form.passwordConfirmation,
					token: this.resetToken,
				})
				this.isLoading = false
				this.toast.success('Вы успешно изменили пароль!')
				bootstrap.Modal.getOrCreateInstance('#resetPasswordModal').hide()
				bootstrap.Modal.getOrCreateInstance('#loginModal').show()
			} catch (e) {
				this.isLoading = false
				if (e.response.status === 400) {
					this.errorMessage = 'Не удалось изменить пароль'
				} else if (e.response.status === 422) {
					this.errorMessage = e.response.data.message
				}
			}
		},
		reset() {
			this.submitted = false
			this.errorMessage = ''
			this.form.password = ''
			this.form.passwordConfirmation = ''
		},
	},
	watch: {
		form: {
			handler() {
				this.errorMessage = ''
			},
			deep: true
		},
	},
}
</script>

<style scoped>

</style>
