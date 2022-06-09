<template>
	<div class="modal fade" id="forgotPasswordModal" tabindex="-1">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header" style="border-bottom: none">
					<h5 class="modal-title">Восстановление пароля</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body pt-0 pb-4">
					<p>Введите email на который зарегистрирован ваш аккаунт</p>
					<div class="mb-3">
						<input type="email" class="form-control" placeholder="Email">
					</div>
					<button class="btn btn-primary">Далее</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'ForgotEmailModal'
}
</script>

<style scoped>

</style>
