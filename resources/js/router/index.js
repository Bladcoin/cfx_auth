import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
	history: createWebHistory(),
	routes: [
        {
            path: '/',
			name: 'root',
            component: () => import('../views/Home.vue'),
			alias: ['/login'],
			children: [
				//{
					//path: 'login',
					//name: 'login',
					//component: () => import('~/views/Home'),
				//},
			]
        },
	]
})

export default router
