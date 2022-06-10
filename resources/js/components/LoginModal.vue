<template>
	<div class="modal fade" id="loginModal" tabindex="-1">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header" style="border-bottom: none">
					<h5 class="modal-title">Вход</h5>
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
					<div class="mb-3">
						<input type="email" class="form-control" placeholder="Email">
					</div>
					<div class="mb-3">
						<input type="password" class="form-control" placeholder="Пароль">
					</div>
					<div class="row align-items-center">
						<div class="col-auto">
							<button class="btn btn-primary">Войти</button>
						</div>
						<div class="col-auto">
							<button class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Забыли пароль?</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'LoginModal',
	props: {
		googleAuth: String,
		facebookAuth: String,
	}
}
</script>

<style scoped>

</style>
