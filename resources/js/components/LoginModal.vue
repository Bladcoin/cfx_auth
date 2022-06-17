<template>
	<div class="modal fade" id="loginModal" ref="modal" tabindex="-1">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header" style="border-bottom: none">
					<h5 class="modal-title">
						{{ $t('login') }}
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body pt-0 pb-4">
					<div class="row mb-3">
						<div class="col-auto">
							{{ $t('login_with') }}
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
					<form @submit.prevent="onSubmit" novalidate>
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
								{{ $t('email_required') }}
							</div>
							<div v-else-if="v$.form.email.email.$invalid && submitted" class="invalid-feedback">
								{{ $t('email_invalid') }}
							</div>
						</div>
						<div class="mb-3">
							<input
								v-model.trim="form.password"
								type="password"
								class="form-control"
								:class="{'is-invalid': v$.form.password.$error && submitted}"
								:placeholder="$t('password')"
							>
							<div v-if="v$.form.password.required.$invalid && submitted" class="invalid-feedback">
								{{ $t('password_required') }}
							</div>
							<div v-else-if="v$.form.password.minLength.$invalid && submitted" class="invalid-feedback">
								{{ $t('password_min_length', { length: v$.form.password.minLength.$params.min }) }}
							</div>
						</div>
						<div class="row align-items-center">
							<div class="col-auto">
								<button
									class="position-relative btn btn-primary"
									:disabled="isLoading"
								>
									<span :class="{invisible: isLoading}">
										{{ $t('sign_in') }}
									</span>
									<span class="spinner spinner-border spinner-border-sm" v-if="isLoading"></span>
								</button>
							</div>
							<div class="col-auto">
								<button type="button" class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
									{{ $t('forgot_password') }}
								</button>
							</div>
						</div>
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
import useVuelidate from '@vuelidate/core'
import { minLength, required, email } from '@vuelidate/validators'

export default {
	name: 'LoginModal',
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
			submitted: false,
			isLoading: false,
			errorMessage: '',
			form: {
				email: '',
				password: '',
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
				password: {
					required,
					minLength: minLength(6),
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
				await this.login()
			}
		},
		async login() {
			try {
				this.isLoading = true
				await this.$api.post('/app/login', {
					email: this.form.email,
					password: this.form.password,
				})
				this.isLoading = false
				window.location.href = '/'
			} catch (e) {
				this.isLoading = false
				if (e.response.status === 400) {
					this.errorMessage = this.$t('wrong_email_password')
				} else if (e.response.status === 422) {
					this.errorMessage = e.response.data.message
				}
				console.log(e)
			}
		},
		reset() {
			this.submitted = false
			this.errorMessage = ''
			this.form.email = ''
			this.form.password = ''
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
