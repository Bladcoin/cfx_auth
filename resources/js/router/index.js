import { createRouter as createVueRouter, createWebHistory } from 'vue-router'

const routes = [
	{
		path: '/',
		name: 'home',
		component: () => import('../views/Home.vue')
	},
	{
		path: '/login',
		redirect: '/',
	},
	{
		path: '/reset-password/:token',
		redirect: to => {
			return {
				name: 'home',
				query: {
					//token: to.params.token,
					email: to.query.email
				},
			}
		},
	},
	{
		path: '/address/:wallet',
		name: 'address',
		meta: { auth: true },
		component: () => import('../views/Address.vue'),
	},
	{
		path: '/:pathMatch(.*)*',
		redirect: '/',
	}
]

export const createRouter = (app) => {
	const router = createVueRouter({
		history: createWebHistory(),
		routes: routes,
	})

	router.beforeEach((to, from, next) => {
		//console.log(to)
		const doc = new DOMParser().parseFromString(app._component.template, 'text/html')
		const user = JSON.parse(doc.querySelector('root').getAttribute(':user'))

		//next()
		if (to.meta.auth && !user) {
			next('/')
		} else {
			next()
		}
	})

	return router
}
