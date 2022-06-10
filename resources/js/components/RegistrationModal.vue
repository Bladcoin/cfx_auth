<template>
	<div class="modal fade" id="registrationModal" ref="modal" tabindex="-1">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header" style="border-bottom: none">
					<h5 class="modal-title">Регистрация</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body pt-0 pb-4">
					<div class="row mb-3">
						<div class="col-auto">
							Войти с помощью:
						</div>
						<div class="col">
							<a :href="googleAuth" class="mx-1">
								<img src="/images/google.svg" width="32" height="32" alt="">
							</a>
							<a :href="facebookAuth" class="mx-1">
								<img src="/images/facebook.svg" width="32" height="32" alt="">
							</a>
						</div>
					</div>
					<hr>
					<form @submit.prevent="onSubmit">
						<fieldset :disabled="success">
							<div class="mb-3">
								<input
									v-model.trim="form.name"
									type="text"
									name="name"
									class="form-control"
									:class="{'is-invalid': v$.form.name.$error && submitted}"
									placeholder="Имя"
								>
								<div v-if="v$.form.name.required.$invalid && submitted" class="invalid-feedback">
									Введите имя
								</div>
							</div>
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
							<div class="mb-3">
								<input
									v-model.trim="form.password"
									type="password"
									class="form-control"
									:class="{'is-invalid': v$.form.password.$error && submitted}"
									placeholder="Пароль (не менее 6 символов)"
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
									v-model.trim="form.repeatPassword"
									type="password"
									class="form-control"
									:class="{'is-invalid': v$.form.repeatPassword.$error && submitted}"
									placeholder="Повторите пароль"
								>
								<div v-if="v$.form.repeatPassword.required.$invalid && submitted" class="invalid-feedback">
									Введите пароль еще раз
								</div>
								<div v-else-if="v$.form.repeatPassword.minLength.$invalid && submitted" class="invalid-feedback">
									Длина не менее {{ v$.form.repeatPassword.minLength.$params.min }} символов
								</div>
								<div v-else-if="v$.form.repeatPassword.sameAsPassword.$invalid && submitted" class="invalid-feedback">
									Пароли не совпадают
								</div>
							</div>
							<button
								class="position-relative btn btn-primary"
								:disabled="isLoading"
							>
								<span :class="{invisible: isLoading}">
									Зарегистрироваться
								</span>
								<span class="spinner spinner-border spinner-border-sm" v-if="isLoading"></span>
							</button>
						</fieldset>
						<div class="alert alert-success mt-3" v-if="success">
							<div class="d-flex">
								<i class="bi-check-circle-fill text-success fs-2"></i>
								<div class="ps-3">
									На указанный электронный адрес отправлена инструкция для завершения регистрации
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import useVuelidate from '@vuelidate/core'
import { minLength, required, email } from '@vuelidate/validators'

export default {
	name: 'RegistrationModal',
	props: {
		googleAuth: String,
		facebookAuth: String,
	},
	setup() {
		return {
			v$: useVuelidate(),
		}
	},
	data() {
		return {
			isLoading: false,
			submitted: false,
			success: false,
			errorMessages: null,
			form: {
				name: '',
				email: '',
				password: '',
				repeatPassword: '',
			}
		}
	},
	validations() {
		return {
			form: {
				name: {
					required,
				},
				email: {
					required,
					email,
				},
				password: {
					required,
					minLength: minLength(6),
				},
				repeatPassword: {
					required,
					minLength: minLength(6),
					sameAsPassword: password => password === this.form.password,
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
				await this.register()
			}
		},
		async register() {
			try {
				this.isLoading = true
				await this.$api.post('/new-user', {
					name: this.form.name,
					email: this.form.email,
					password: this.form.password,
				})
				this.isLoading = false
				this.success = true
			} catch (e) {
				this.isLoading = false
				if (e.response?.status === 400) {
					console.log('error 400')
				}
				console.log(e)
			}
		},
		reset() {
			this.submitted = false
			this.success = false
			this.errorMessages = null
			this.form.name = ''
			this.form.email = ''
			this.form.password = ''
			this.form.repeatPassword = ''
		},
	}
}
</script>

<style scoped>

</style>
