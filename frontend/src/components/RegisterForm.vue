si<template>
  <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Crea tu cuenta</h1>

    <div v-if="errors.length > 0" class="mb-4 text-red-600">
      <ul>
        <li v-for="error in errors" :key="error">- {{ error }}</li>
      </ul>
    </div>

    <form @submit.prevent="handleSubmit" method="POST" action="/register">
      <input type="hidden" name="_token" :value="csrfToken" />

      <div class="mb-4">
        <label for="name" class="block font-semibold mb-1">Nombre completo</label>
        <input
          type="text"
          name="name"
          id="name"
          v-model="form.name"
          required
          class="w-full border border-gray-300 rounded px-3 py-2"
          placeholder="Juan Pérez"
        />
      </div>

      <div class="mb-4">
        <label for="email" class="block font-semibold mb-1">Correo electrónico</label>
        <input
          type="email"
          name="email"
          id="email"
          v-model="form.email"
          required
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
        <label for="password_confirmation" class="block font-semibold mb-1">Confirmar contraseña</label>
        <input
          type="password"
          name="password_confirmation"
          id="password_confirmation"
          v-model="form.password_confirmation"
          required
          class="w-full border border-gray-300 rounded px-3 py-2"
        />
      </div>

      <div class="mb-4">
        <label for="role" class="block font-semibold mb-1">Quiero usar la plataforma como</label>
        <select
          name="role"
          id="role"
          v-model="form.role"
          required
          class="w-full border border-gray-300 rounded px-3 py-2"
        >
          <option value="Comerciante">Comerciante (Quiero vender)</option>
          <option value="Cliente">Cliente (Quiero comprar)</option>
        </select>
      </div>

      <button
        type="submit"
        :disabled="loading"
        class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 disabled:opacity-50"
      >
        <span v-if="loading">Creando cuenta...</span>
        <span v-else>Crear cuenta</span>
      </button>
    </form>

    <p class="mt-4 text-center text-sm text-gray-600">
      ¿Ya tienes una cuenta?
      <router-link to="/login" class="text-blue-600 hover:underline">Iniciar sesión</router-link>
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
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'Cliente'
})

const loading = ref(false)

// Handle form submission
const handleSubmit = async () => {
  loading.value = true

  try {
    // The form will be submitted normally to Laravel
    // Vue Router will handle navigation after successful registration
    const formElement = document.querySelector('form')
    formElement.submit()
  } catch (error) {
    console.error('Registration error:', error)
    loading.value = false
  }
}
</script>

<style scoped>
/* Estilos específicos para el formulario de registro */
</style>
