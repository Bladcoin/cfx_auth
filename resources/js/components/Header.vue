<template>
	<div class="row align-items-center justify-content-between">
		<div class="col-auto">
			<div class="row align-items-center">
				<div class="col-auto">
					<router-link :to="{name: 'home'}">
						<img src="../assets/img/logo.svg" class="img-fluid" width="100" alt="">
					</router-link>
				</div>
				<div class="col-auto">
					<ul class="nav pt-2">
						<li class="nav-item">
							<button
								class="btn nav-link"
								:class="{'disabled': currentSpace === 'core'}"
								@click="changeSpace('core')"
							>
								Core
							</button>
						</li>
						<li class="nav-item">
							<button
								class="btn nav-link"
								:class="{'disabled': currentSpace === 'eSpace'}"
								@click="changeSpace('eSpace')"
							>
								eSpace
							</button>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="col-auto">
			<div class="row align-items-center">
				<div class="col-auto">
					<img
						src="../assets/img/en.svg"
						class="lang"
						:class="{active: $i18n.locale === 'en'}"
						alt="EN"
						@click="changeLanguage('en')"
					>
					<img
						src="../assets/img/ru.svg"
						class="lang"
						:class="{active: $i18n.locale === 'ru'}"
						alt="RU"
						@click="changeLanguage('ru')"
					>
				</div>
				<div class="col-auto">
					<template v-if="user">
						<button
							v-if="!userInfo.connected"
							class="btn btn-primary"
							@click="$emit('connectWallet')"
						>
							{{ currentSpace === 'core' ? $t('connect_fluent') : $t('connect_metamask') }}
						</button>
						<div class="btn text-primary" style="cursor:auto;user-select:auto;" v-else>
							{{ shortenAccount }}
						</div>
					</template>
					<template v-else>
						<button class="btn btn-outline-danger dropdown-toggle" type="button" data-bs-toggle="dropdown">
							<i class="bi bi-person-circle"></i>
						</button>
						<div class="dropdown-menu dropdown-menu-end">
							<div class="px-3">
								<button class="btn btn-primary d-block mb-1 w-100" data-bs-toggle="modal" data-bs-target="#loginModal">
									Войти
								</button>
								<button class="btn btn-block btn-link px-0 w-100" data-bs-toggle="modal" data-bs-target="#registrationModal">
									Регистрация
								</button>
							</div>
						</div>
					</template>
				</div>
				<div class="col-auto" v-if="user">
					<div class="dropdown">
						<button class="btn btn-outline-danger dropdown-toggle" type="button" data-bs-toggle="dropdown">
							<i class="bi bi-person-circle"></i>
							{{ user.name }}
						</button>
						<div class="dropdown-menu dropdown-menu-end">
							<template v-if="coreWallets.length">
								<div class="mb-1 px-3 fw-bold">
									Core wallets
								</div>
								<ul class="list-unstyled m-0">
									<li v-for="wallet in coreWallets" :key="wallet.id">
										<router-link
											:to="{name: 'address', params: {wallet: wallet.public_key}}"
											class="dropdown-item text-truncate"
										>
											{{ wallet.public_key }}
										</router-link>
									</li>
									<li><hr class="dropdown-divider"></li>
								</ul>
							</template>
							<template v-if="eSpaceWallets.length">
								<div class="mb-1 px-3 fw-bold">
									eSpace wallets
								</div>
								<ul class="list-unstyled m-0">
									<li v-for="wallet in eSpaceWallets" :key="wallet.id">
										<router-link
											:to="{name: 'address', params: {wallet: wallet.public_key}}"
											class="dropdown-item text-truncate"
										>
											{{ wallet.public_key }}
										</router-link>
									</li>
									<li><hr class="dropdown-divider"></li>
								</ul>
							</template>
							<ul class="list-unstyled m-0">
								<li>
									<button
										class="position-relative dropdown-item"
										type="button"
										@click="logout"
										:disabled="isLoggingOut"
									>
										<span :class="{invisible: isLoggingOut}">
											Выйти
										</span>
										<span class="spinner spinner-border spinner-border-sm" v-if="isLoggingOut"></span>
									</button>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import {address} from "../utils/cfx";

export default {
	name: 'Header',
	props: {
		user: Object,
		userInfo: Object,
		currentSpace: String,
		wallets: Array,
	},
	data() {
		return {
			isLoggingOut: false,
		}
	},
	computed: {
		shortenAccount() {
			const account = this.userInfo.account;
			if (account.match(':')) {
				return address.shortenCfxAddress(account);
			} else {
				return `${account.slice(0, 4)}...${account.slice(-4)}`;
			}
		},
		coreWallets() {
			return this.wallets.filter(wallet => wallet.wallet_type === 'CORE')
		},
		eSpaceWallets() {
			return this.wallets.filter(wallet => wallet.wallet_type === 'ESPACE')
		},
	},
	methods: {
		changeLanguage(locale) {
			localStorage.setItem('locale', locale)
			window.location.reload()
		},
		changeSpace(space) {
			localStorage.setItem('space', space)
			window.location.reload()
		},
		async logout() {
			try {
				this.isLoggingOut = true
				await this.$api.post('/logout')
				this.isLoggingOut = false
				window.location.href = '/'
			} catch (e) {
				this.isLoggingOut = false
			}
		}
	},
}
</script>

<style scoped>

</style>
