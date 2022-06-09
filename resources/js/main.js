import { createApp } from 'vue'
import App from './App.vue'
//import router from './router'
import i18n from './i18n'
import 'js-conflux-sdk/dist/js-conflux-sdk.umd.min'
import 'bootstrap-icons/font/bootstrap-icons.css'

//const app = createApp()
//const app = createApp(App)
const app = createApp({})

//app.use(router)
app.use(i18n)
app.component('root', App)
app.mount('#app')
