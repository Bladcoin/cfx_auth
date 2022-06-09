<template>
	<div class="modal fade" id="registrationModal" tabindex="-1">
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
							<a :href="googleAuth">
								<img src="/images/google.svg" width="32" height="32" alt="">
							</a>
						</div>
					</div>
					<hr>
					<div class="mb-3">
						<input type="email" class="form-control" placeholder="Email">
					</div>
					<div class="mb-3">
						<input type="password" class="form-control" placeholder="Пароль (не менее 6 символов)">
					</div>
					<div class="mb-3">
						<input type="password" class="form-control" placeholder="Повторите пароль">
					</div>
					<button class="btn btn-primary">Зарегистрироваться</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'RegistrationModal',
	props: {
		googleAuth: String,
	}
}
</script>

<style scoped>

</style>
