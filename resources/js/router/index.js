import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
	history: createWebHistory(),
	routes: [
        {
            path: '/',
			name: 'root',
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
							name: 'root',
							query: {
								//token: to.params.token,
								email: to.query.email
							},
						}
					},
				},
			]
        },
	]
})

export default router
