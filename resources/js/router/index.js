import { createRouter as createVueRouter, createWebHistory } from 'vue-router'

const routes = [
	{
		path: '/',
		name: 'home',
		component: () => import('../views/Home.vue'),
		//alias: ['/login'],
		children: [
			{
				path: 'login',
				redirect: '/'
			},
			{
				path: 'reset-password/:token',
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
				path: 'address/:wallet',
				name: 'wallet',
				component: () => import('../views/Wallet.vue'),
			}
		]
	},
]

export const createRouter = (app) => {
	const router = createVueRouter({
		history: createWebHistory(),
		routes: routes,
	})

	router.beforeEach((to, from, next) => {
		console.log(to.name)
		//console.log(app)
		//console.log('beforeEach')
		//console.log(app._component.template)
		const doc = new DOMParser().parseFromString(app._component.template, 'text/html')
		const user = JSON.parse(doc.querySelector('root').getAttribute(':user'))
		if (user) {

		}
		//console.log(user)
		// const token = localStorage.getItem('token')
		//
		// if (!token && to.name !== 'Auth') {
		// 	next('/auth')
		// } else if (token && to.name === 'Auth') {
		// 	next('/')
		// } else {
		// 	next()
		// }
		next()
	})

	return router
}
