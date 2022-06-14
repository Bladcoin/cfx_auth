<template>
	<div class="modal fade" id="forgotPasswordModal" ref="modal" tabindex="-1">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header" style="border-bottom: none">
					<h5 class="modal-title">Восстановление пароля</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body pt-0 pb-4">
					<form @submit.prevent="onSubmit" novalidate>
						<fieldset :disabled="success">
							<p>Введите email на который зарегистрирован ваш аккаунт</p>
							<div class="mb-3">
								<input
									v-model.trim="form.email"
									type="email"
									name="email"
									class="form-control"
									:class="{'is-invalid': v$.form.email.$error && submitted}"
									placeholder="Email"
								>
								<div v-if="v$.form.email.required.$invalid && submitted" class="invalid-feedback">
									Введите адрес электронной почты
								</div>
								<div v-else-if="v$.form.email.email.$invalid && submitted" class="invalid-feedback">
									Не верный формат электронной почты
								</div>
							</div>
							<button
								class="position-relative btn btn-primary"
								:disabled="isLoading"
							>
								<span :class="{invisible: isLoading}">
									Далее
								</span>
								<span class="spinner spinner-border spinner-border-sm" v-if="isLoading"></span>
							</button>
							<div class="alert alert-danger mt-3" v-if="errorMessage">
								{{ errorMessage }}
							</div>
						</fieldset>
						<div class="alert alert-success mt-3" v-if="success">
							На указанный электронный адрес отправлена ссылка для сброса пароля
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import useVuelidate from '@vuelidate/core'
import {required, email} from '@vuelidate/validators'

export default {
	name: 'ForgotPasswordModal',
	setup() {
		return {
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
				email: '',
			}
		}
	},
	validations() {
		return {
			form: {
				email: {
					required,
					email,
				},
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
				await this.$api.post('/forgot-password', {
					email: this.form.email,
				})
				this.isLoading = false
				this.success = true
			} catch (e) {
				this.isLoading = false
				if (e.response.status === 400) {
					this.errorMessage = 'Пользователь с таким email не найден'
				} else if (e.response.status === 422) {
					this.errorMessage = e.response.data.message
				}
				console.log(e)
			}
		},
		reset() {
			this.submitted = false
			this.success = false
			this.errorMessage = ''
			this.form.email = ''
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
