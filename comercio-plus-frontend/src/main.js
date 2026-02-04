// src/main.js
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import API from './services/api.js'
import './assets/main.css'

// Crear app
const app = createApp(App)

// Usar router
app.use(router)

// Configurar API global
app.config.globalProperties.$api = API

app.mount('#app')
