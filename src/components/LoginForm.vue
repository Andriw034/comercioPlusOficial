<template>
  <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Iniciar sesión</h1>

    <div v-if="errors.length > 0" class="mb-4 text-red-600">
      <ul>
        <li v-for="error in errors" :key="error">- {{ error }}</li>
      </ul>
    </div>

    <form @submit.prevent="handleSubmit" method="POST" action="/login">
      <input type="hidden" name="_token" :value="csrfToken" />

      <div class="mb-4">
        <label for="email" class="block font-semibold mb-1">Correo electrónico</label>
        <input
          type="email"
          name="email"
          id="email"
          v-model="form.email"
          required
          autofocus
          class="w-full border border-gray-300 rounded px-3 py-2"
          placeholder="m@ejemplo.com"
        />
      </div>

      <div class="mb-4">
        <label for="password" class="block font-semibold mb-1">Contraseña</label>
        <input
          type="password"
          name="password"
          id="password"
          v-model="form.password"
          required
          class="w-full border border-gray-300 rounded px-3 py-2"
        />
      </div>

      <div class="mb-4">
        <label class="flex items-center">
          <input
            type="checkbox"
            name="remember"
            v-model="form.remember"
            class="mr-2"
          />
          Recordarme
        </label>
      </div>

      <button
        type="submit"
        :disabled="loading"
        class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 disabled:opacity-50"
      >
        <span v-if="loading">Cargando...</span>
        <span v-else>Iniciar sesión</span>
      </button>
    </form>

    <p class="mt-4 text-center text-sm text-gray-600">
      ¿Aún no tienes cuenta?
      <router-link to="/register" class="text-blue-600 hover:underline">Crear cuenta</router-link>
    </p>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Props
const props = defineProps({
  csrfToken: {
    type: String,
    required: true
  },
  errors: {
    type: Array,
    default: () => []
  }
})

// Reactive form data
const form = reactive({
  email: '',
  password: '',
  remember: false
})

const loading = ref(false)

// Handle form submission
const handleSubmit = async () => {
  loading.value = true

  try {
    // The form will be submitted normally to Laravel
    // Vue Router will handle navigation after successful login
    const formElement = document.querySelector('form')
    formElement.submit()
  } catch (error) {
    console.error('Login error:', error)
    loading.value = false
  }
}
</script>

<style scoped>
/* Estilos específicos para el formulario de login */
</style>
