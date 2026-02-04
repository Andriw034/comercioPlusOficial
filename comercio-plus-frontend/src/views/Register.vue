<template>
  <div class="space-y-6">
    <div class="space-y-1">
      <p class="text-sm text-muted">Crea tu cuenta para vender o comprar</p>
      <h1 class="text-3xl font-semibold text-white">Crear cuenta</h1>
    </div>

    <form class="space-y-5" @submit.prevent="handleSubmit">
      <div class="space-y-4">
        <div class="space-y-1">
          <label for="name" class="text-sm text-muted">Nombre</label>
          <input id="name" name="name" type="text" autocomplete="name" required class="input-dark"
            placeholder="Nombre completo" v-model="form.name" />
        </div>

        <div class="space-y-1">
          <label for="email" class="text-sm text-muted">Correo electrónico</label>
          <input id="email" name="email" type="email" autocomplete="email" required class="input-dark"
            placeholder="tu@correo.com" v-model="form.email" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1">
            <label for="password" class="text-sm text-muted">Contraseña</label>
            <input
              id="password"
              name="password"
              type="password"
              autocomplete="new-password"
              required
            class="input-dark"
            placeholder="********"
            v-model="form.password"
          />
          </div>
          <div class="space-y-1">
            <label for="password_confirmation" class="text-sm text-muted">Confirmar contraseña</label>
            <input
              id="password_confirmation"
              name="password_confirmation"
              type="password"
              autocomplete="new-password"
              required
            class="input-dark"
            placeholder="Repite tu contraseña"
            v-model="form.password_confirmation"
          />
          </div>
        </div>
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
          Creando...
        </span>
        <span v-else>Crear cuenta</span>
      </button>

      <p class="text-center text-sm text-muted">
        ¿Ya tienes cuenta?
        <router-link to="/login" class="text-brand-200 hover:text-white font-medium">Inicia sesión</router-link>
      </p>
    </form>
  </div>
</template>

<script>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import API from '../services/api.js'

export default {
  name: 'Register',
  setup() {
    const router = useRouter()
    const loading = ref(false)
    const error = ref('')
    const form = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: ''
    })

    const handleSubmit = async () => {
      loading.value = true
      error.value = ''

      try {
        const { data } = await API.post('/register', {
          name: form.name,
          email: form.email,
          password: form.password,
          password_confirmation: form.password_confirmation
        })

        if (data) {
          localStorage.setItem('user', JSON.stringify(data.user))
          if (data.token) {
            localStorage.setItem('token', data.token)
            API.defaults.headers.common.Authorization = `Bearer ${data.token}`
          }
          router.push('/dashboard')
        }
      } catch (err) {
        console.error('Register error:', err)
        error.value = err.response?.data?.message || 'Error al crear la cuenta. Verifica tus datos.'
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
