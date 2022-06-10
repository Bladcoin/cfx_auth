import { createApp } from 'vue'
import App from './App.vue'
import axios from 'axios'
import VueAxios from 'vue-axios'
//import router from './router'
import i18n from './i18n'
import 'js-conflux-sdk/dist/js-conflux-sdk.umd.min'
import 'bootstrap-icons/font/bootstrap-icons.css'

//const app = createApp()
//const app = createApp(App)
const app = createApp({})

const api = axios.create({
	baseURL: '/'
})

app.config.globalProperties.$api = api

//app.use(router)
app.use(i18n)
app.use(VueAxios, axios)
app.component('root', App)
app.mount('#app')
