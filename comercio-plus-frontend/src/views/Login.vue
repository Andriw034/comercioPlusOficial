<template>
  <div class="min-h-screen flex items-center justify-center bg-[#0F172A] py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 cp-card p-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-[#E7DBCB]">
          Iniciar sesión en ComercioPlus
        </h2>
        <p class="mt-2 text-center text-sm text-gray-300">
          O
          <router-link to="/register" class="font-medium text-[#EE471B] hover:text-[#d63f18]">
            crear una nueva cuenta
          </router-link>
        </p>
      </div>
      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <input type="hidden" name="remember" value="true" />
        <div class="space-y-4">
          <div>
            <label for="email" class="sr-only">Correo electrónico</label>
            <input
              id="email"
              name="email"
              type="email"
              autocomplete="email"
              required
              class="cp-input"
              placeholder="Correo electrónico"
              v-model="form.email"
            />
          </div>
          <div>
            <label for="password" class="sr-only">Contraseña</label>
            <input
              id="password"
              name="password"
              type="password"
              autocomplete="current-password"
              required
              class="cp-input"
              placeholder="Contraseña"
              v-model="form.password"
            />
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember-me"
              name="remember-me"
              type="checkbox"
              class="h-4 w-4 text-[#EE471B] focus:ring-[#EE471B] border-gray-600 rounded bg-[#1E293B]"
            />
            <label for="remember-me" class="ml-2 block text-sm text-gray-200">
              Recordarme
            </label>
          </div>

          <div class="text-sm">
            <a href="#" class="font-medium text-[#EE471B] hover:text-[#d63f18]">
              ¿Olvidaste tu contraseña?
            </a>
          </div>
        </div>

        <div>
          <button
            type="submit"
            class="btn-primary w-full relative flex justify-center text-sm"
            :disabled="loading"
          >
            <span v-if="loading" class="absolute left-1/2 transform -translate-x-1/2">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            <span v-else>Iniciar sesión</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import API from '../services/api.js'

export default {
  name: 'Login',
  setup() {
    const router = useRouter()
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
        // Login con API de Laravel
        const response = await API.post('/login', {
          email: form.email,
          password: form.password,
          remember: form.remember
        })

        if (response.data) {
          // Guardar información del usuario
          localStorage.setItem('user', JSON.stringify(response.data.user))

          // Redirigir según el rol del usuario
          if (response.data.user.role === 'comerciante') {
            router.push('/dashboard')
          } else {
            router.push('/')
          }
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
