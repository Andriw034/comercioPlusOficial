<template>
  <div class="min-h-screen bg-gray-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-bold text-orange-500">Bienvenido ComercioPlus</h2>
        <p class="mt-2 text-center text-sm text-gray-300">Ingresa a tu cuenta</p>
      </div>
      <form @submit.prevent="submit" class="mt-8 space-y-6 bg-gray-800 rounded-lg p-6">
        <!-- Error Message -->
        <div v-if="generalError" class="p-3 bg-red-100 border border-red-400 text-red-700 rounded text-sm">
          {{ generalError }}
        </div>

        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-300">Correo electrónico</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-600 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 bg-white sm:text-sm"
            placeholder="tu@email.com"
          />
          <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-300">Contraseña</label>
          <input
            id="password"
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            required
            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-600 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 bg-white sm:text-sm"
            placeholder="••••••••"
          />
          <button
            type="button"
            @click="togglePasswordVisibility"
            class="absolute inset-y-0 right-0 pr-3 flex items-center mt-6"
          >
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="!showPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
            </svg>
          </button>
          <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember"
              v-model="form.remember"
              type="checkbox"
              class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
            />
            <label for="remember" class="ml-2 block text-sm text-gray-300">Recuérdame</label>
          </div>
          <div class="text-sm">
            <a href="#" class="font-medium text-orange-500 hover:text-orange-400">¿Olvidaste tu contraseña?</a>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-orange-500 hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50"
          >
            <span v-if="loading">Iniciando sesión...</span>
            <span v-else>Iniciar sesión</span>
          </button>
        </div>

        <div class="text-center">
          <p class="text-sm text-gray-300">
            ¿No tienes cuenta?
            <RouterLink to="/register" class="font-medium text-orange-500 hover:text-orange-400 ml-1">Crea una cuenta</RouterLink>
          </p>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { RouterLink } from 'vue-router'

const auth = useAuthStore()

const form = reactive({
  email: '',
  password: '',
  remember: false
})

const errors = reactive({
  email: null,
  password: null
})

const generalError = ref('')
const loading = ref(false)
const showPassword = ref(false)

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}

const submit = async () => {
  clearErrors()
  generalError.value = ''
  loading.value = true

  try {
    await auth.login(form.email, form.password, form.remember)
    // Redirigir basado en rol o store
    if (auth.user && auth.user.role === 'comerciante') {
      if (auth.user.store_id) {
        window.location.href = '/dashboard'
      } else {
        window.location.href = '/stores/create'
      }
    } else {
      window.location.href = '/'
    }
  } catch (error) {
    loading.value = false
    // Manejar errores de validación
    if (error.response && error.response.data && error.response.data.errors) {
      const serverErrors = error.response.data.errors
      for (const key in serverErrors) {
        if (errors.hasOwnProperty(key)) {
          errors[key] = serverErrors[key][0]
        }
      }
    } else if (error.response && error.response.data && error.response.data.message) {
      generalError.value = error.response.data.message
    } else {
      generalError.value = 'Error al iniciar sesión. Inténtalo de nuevo.'
    }
  }
}

const clearErrors = () => {
  errors.email = null
  errors.password = null
  generalError.value = ''
}
</script>
