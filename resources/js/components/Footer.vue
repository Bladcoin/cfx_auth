<template>
	<div class="row justify-content-center mt-3 footer">
		<div class="col-auto footer-item text-center mb-3">
			© {{ new Date().getFullYear() }} POS-CFX
		</div>
		<div class="col-auto footer-item text-center mb-3">
			<a href="#" target="_blank">Twitter</a>
		</div>
		<div class="col-auto footer-item text-center mb-3">
			<a href="#" target="_blank">Github</a>
		</div>
		<div class="col-auto footer-item text-center mb-3">
			<a href="#">Email</a>
		</div>
	</div>
</template>

<script>
export default {
	name: 'Footer',
}
</script>

<style scoped>

</style>
