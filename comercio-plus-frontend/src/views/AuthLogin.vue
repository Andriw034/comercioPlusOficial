<template>
  <div class="max-w-6xl mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-2 gap-8">
    <div class="space-y-5">
      <div class="flex items-center gap-3">
        <div class="h-9 w-9 rounded-2xl" :style="hero"></div>
        <span class="text-xl font-extrabold">Comercio<span :style="{color:'var(--cp-primary)'}">Plus</span></span>
      </div>

      <form @submit.prevent="login" class="space-y-4">
        <div>
          <label class="text-sm">Correo electrónico</label>
          <input v-model="email" type="email" class="input" required autofocus />
        </div>
        <div>
          <label class="text-sm">Contraseña</label>
          <input v-model="password" type="password" class="input" required />
        </div>
        <div class="flex items-center justify-between text-sm">
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" v-model="remember"> Recuérdame
          </label>
          <router-link to="/password/forgot" class="hover:text-[var(--cp-primary)]">¿Olvidaste tu contraseña?</router-link>
        </div>
        <button class="btn btn-primary w-full" :disabled="loading">{{ loading ? 'Entrando...' : 'Iniciar sesión' }}</button>
        <p class="text-sm text-center">¿Aún no tienes cuenta?
          <router-link to="/register" class="hover:text-[var(--cp-primary)]">Crear cuenta</router-link>
        </p>
        <p v-if="error" class="text-red-600 text-sm mt-2">{{ error }}</p>
      </form>
    </div>

    <div class="rounded-3xl min-h-80 flex items-center justify-center text-white text-2xl font-bold text-center px-6" :style="hero">
      Empieza gratis y comparte tu <br/> catálogo hoy.
    </div>
  </div>
</template>

<script setup>
import api from '../lib/api'
import { fetchMe } from '../stores/session'
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const email = ref(''); const password = ref(''); const remember = ref(false)
const loading = ref(false); const error = ref(''); const router = useRouter()
const hero = { background:'linear-gradient(135deg,var(--cp-primary),var(--cp-primary-2))' }

async function login(){
  loading.value = true; error.value = ''
  try{
    // ajusta al endpoint real
    const { data } = await api.post('/api/login', { email: email.value, password: password.value })
    if (data?.token) localStorage.setItem('token', data.token)
    await fetchMe()
    router.push({ name: 'products.index' })
  }catch(e){
    error.value = e?.response?.data?.message || 'No se pudo iniciar sesión'
  }finally{ loading.value = false }
}
</script>
