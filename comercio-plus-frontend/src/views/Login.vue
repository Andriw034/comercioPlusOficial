<template>
  <div class="space-y-6">
    <div class="space-y-1">
      <p class="text-sm text-muted">Accede para comprar o gestionar tu tienda</p>
      <h1 class="text-3xl font-semibold text-white">Iniciar sesión</h1>
    </div>

    <form class="space-y-5" @submit.prevent="handleSubmit">
      <div class="space-y-4">
        <div class="space-y-1">
          <label for="email" class="text-sm text-muted">Correo electrónico</label>
          <input id="email" name="email" type="email" autocomplete="email" required class="input-dark"
            placeholder="tu@correo.com" v-model="form.email" />
        </div>
        <div class="space-y-1">
          <label for="password" class="text-sm text-muted">Contraseña</label>
          <input id="password" name="password" type="password" autocomplete="current-password" required class="input-dark"
            placeholder="********" v-model="form.password" />
        </div>
      </div>

      <div class="flex items-center justify-between text-sm text-muted">
        <label class="inline-flex items-center gap-2">
          <input
            id="remember-me"
            name="remember-me"
            type="checkbox"
            class="h-4 w-4 rounded border-white/20 bg-white/5 text-brand-500 focus:ring-brand-500/60"
            v-model="form.remember"
          />
          Recordarme
        </label>
        <a href="#" class="text-brand-200 hover:text-white">¿Olvidaste tu contraseña?</a>
      </div>

      <div v-if="error" class="rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-100">
        {{ error }}
      </div>

      <button type="submit" class="btn-primary w-full md:w-auto justify-center" :disabled="loading">
        <span v-if="loading" class="flex items-center gap-2">
          <svg class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          Iniciando...
        </span>
        <span v-else>Entrar</span>
      </button>

      <p class="text-center text-sm text-muted">
        ¿No tienes cuenta?
        <router-link to="/register" class="text-brand-200 hover:text-white font-medium">Crear cuenta</router-link>
      </p>
    </form>
  </div>
</template>

<script>
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import API from '../services/api.js'

export default {
  name: 'Login',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const loading = ref(false)
    const error = ref('')
    const form = reactive({
      email: '',
      password: '',
      remember: false
    })

    const handleSubmit = async () => {
      loading.value = true
      error.value = ''

      try {
        const { data } = await API.post('/login', {
          email: form.email,
          password: form.password,
          remember: form.remember
        })

        if (data) {
          localStorage.setItem('user', JSON.stringify(data.user))
          if (data.token) {
            localStorage.setItem('token', data.token)
            API.defaults.headers.common.Authorization = `Bearer ${data.token}`
          }
          const redirect = route.query.redirect || '/dashboard'
          router.push(redirect)
        }
      } catch (err) {
        console.error('Login error:', err)
        error.value = err.response?.data?.message || 'Error al iniciar sesión. Verifica tus credenciales.'
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      error,
      handleSubmit
    }
  }
}
</script>
