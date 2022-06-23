<template>
	<div class="row align-items-center">
		<div class="col">
			<div class="row align-items-center justify-content-between">
				<div class="col-auto">
					<ul class="nav">
						<li class="nav-item">
							<button
								class="btn nav-link me-3 px-0"
								:class="{'disabled': currentSpace === 'core'}"
								@click="changeSpace('core')"
							>
								Core
							</button>
						</li>
						<li class="nav-item">
							<button
								class="btn nav-link px-0"
								:class="{'disabled': currentSpace === 'espace'}"
								@click="changeSpace('espace')"
							>
								eSpace
							</button>
						</li>
					</ul>
				</div>
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
			</div>
		</div>
		<div
			class="col-md-auto py-2 py-md-0"
			:class="user ? 'col-12' : 'col-auto'"
		>
			<div class="row align-items-center justify-content-between">
				<div class="col-12 col-sm-auto text-center">
					<template v-if="user">
						<button
							v-if="!userInfo.connected"
							class="w-100 btn btn-primary"
							@click="$emit('connectWallet')"
						>
							{{ currentSpace === 'core' ? $t('connect_fluent') : $t('connect_metamask') }}
						</button>
						<div class="btn text-primary px-0" style="cursor:auto;user-select:auto;" v-else>
							{{ $t('account') }}:
							<span class="fw-bold">{{ shortenAccount }}</span>
						</div>
					</template>
					<template v-else>
						<button class="btn btn-outline-danger dropdown-toggle" type="button" data-bs-toggle="dropdown">
							<i class="bi bi-person-circle"></i>
						</button>
						<div class="dropdown-menu dropdown-menu-end">
							<div class="px-3">
								<button class="btn btn-primary d-block mb-1 w-100" data-bs-toggle="modal" data-bs-target="#loginModal">
									{{ $t('sign_in') }}
								</button>
								<button class="btn btn-block btn-link px-0 w-100" data-bs-toggle="modal" data-bs-target="#registrationModal">
									{{ $t('sign_up') }}
								</button>
							</div>
						</div>
					</template>
				</div>
				<div class="col-12 col-sm-auto mt-2 mt-sm-0" v-if="user">
					<div class="dropdown">
						<button type="button" class="btn btn-outline-danger dropdown-toggle w-100" data-bs-toggle="dropdown">
							<i class="bi bi-person-circle"></i>
							{{ user.name }}
						</button>
						<div class="dropdown-menu dropdown-menu-end">
							<template v-if="coreWallets.length">
								<div class="mb-1 px-3 fw-bold">
									{{ $t('core_wallets') }}
								</div>
								<ul class="list-unstyled m-0">
									<li v-for="wallet in coreWallets" :key="wallet.id">
										<div class="row flex-nowrap align-items-center me-0">
											<div class="col pe-5">
												<button
													type="button"
													class="dropdown-item text-truncate"
													@click="changeWallet(wallet.public_key, wallet.wallet_type)"
												>
													{{ wallet.public_key }}
												</button>
											</div>
											<div class="col-auto px-0 w-0">
												<button
													type="button"
													class="btn btn-danger px-1 py-0"
													style="margin-left:-2.25rem"
													@click="deleteWallet(wallet.id, wallet.public_key)"
												>
													<i class="bi bi-x-lg"></i>
												</button>
											</div>
										</div>
									</li>
									<li><hr class="dropdown-divider"></li>
								</ul>
							</template>
							<template v-if="eSpaceWallets.length">
								<div class="mb-1 px-3 fw-bold">
									{{ $t('espace_wallets') }}
								</div>
								<ul class="list-unstyled m-0">
									<li v-for="wallet in eSpaceWallets" :key="wallet.id">
										<div class="row flex-nowrap align-items-center me-0">
											<div class="col pe-5">
												<button
													type="button"
													class="dropdown-item text-truncate"
													@click="changeWallet(wallet.public_key, wallet.wallet_type)"
												>
													{{ wallet.public_key }}
												</button>
											</div>
											<div class="col-auto px-0 w-0">
												<button
													type="button"
													class="btn btn-danger px-1 py-0"
													style="margin-left:-2.25rem"
													@click="deleteWallet(wallet.id, wallet.public_key)"
												>
													<i class="bi bi-x-lg"></i>
												</button>
											</div>
										</div>
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
											{{ $t('sign_out') }}
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
		extensionPriority: Boolean,
	},
	data() {
		return {
			isLoggingOut: false,
		}
	},
	computed: {
		shortenAccount() {
			const account = this.extensionPriority ? this.userInfo.account : this.userInfo.wallet
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
			if (locale !== this.$i18n.locale) {
				localStorage.setItem('locale', locale)
				window.location.reload()
			}
		},
		changeSpace(space) {
			localStorage.setItem('space', space)
			window.location.reload()
		},
		changeWallet(wallet, type) {
			this.$emit('changeWallet', {wallet, type: type.toLowerCase()})
		},
		deleteWallet(id, wallet) {
			this.$emit('deleteWallet', {id, wallet})
		},
		async logout() {
			try {
				this.isLoggingOut = true
				await this.$api.post('/logout')
				this.isLoggingOut = false
				localStorage.removeItem('wallet')
				window.location.href = '/'
			} catch (e) {
				this.isLoggingOut = false
			}
		}
	},
}
</script>
