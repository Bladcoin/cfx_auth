import { createRouter, createWebHistory } from 'vue-router'

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
		path: '/:pathMatch(.*)*',
		redirect: '/',
	}
]

const router = createRouter({
	history: createWebHistory(),
	routes: routes,
})

export default router
