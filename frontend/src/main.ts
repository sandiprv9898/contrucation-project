import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { useAuthStore } from './modules/auth'

const app = createApp(App)

app.use(createPinia())
app.use(router)

// Initialize auth state
const authStore = useAuthStore()
authStore.initAuth()

app.mount('#app')
