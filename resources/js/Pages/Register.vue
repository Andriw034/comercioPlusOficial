<template>
  <div class="min-h-screen bg-gray-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-bold text-orange-500">Crear cuenta ComercioPlus</h2>
        <p class="mt-2 text-center text-sm text-gray-300">Regístrate para comenzar</p>
      </div>
      <form @submit.prevent="submit" class="mt-8 space-y-6 bg-gray-800 rounded-lg p-6" enctype="multipart/form-data">
        <!-- Error Message -->
        <div v-if="generalError" class="p-3 bg-red-100 border border-red-400 text-red-700 rounded text-sm">
          {{ generalError }}
        </div>

        <!-- Name -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-300">Nombre completo</label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-600 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 bg-white sm:text-sm"
            placeholder="Juan Pérez"
          />
          <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
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
            type="password"
            required
            minlength="8"
            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-600 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 bg-white sm:text-sm"
            placeholder="••••••••"
          />
          <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
        </div>

        <!-- Password Confirmation -->
        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-300">Confirmar contraseña</label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            required
            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-600 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 bg-white sm:text-sm"
            placeholder="••••••••"
          />
        </div>

        <!-- Role -->
        <div>
          <label for="role" class="block text-sm font-medium text-gray-300">Tipo de cuenta</label>
          <select
            id="role"
            v-model="form.role"
            required
            class="mt-1 block w-full px-3 py-2 bg-white border border-gray-600 rounded-md shadow-sm placeholder-gray-500 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
          >
            <option value="cliente">Cliente comprador</option>
            <option value="comerciante">Comerciante</option>
          </select>
          <p v-if="errors.role" class="mt-1 text-sm text-red-600">{{ errors.role }}</p>
        </div>

        <!-- Profile Photo -->
        <div>
          <label for="profile_photo" class="block text-sm font-medium text-gray-300">Foto de perfil (opcional)</label>
          <input
            id="profile_photo"
            ref="profilePhotoInput"
            type="file"
            accept="image/*"
            class="mt-1 block w-full text-sm text-gray-900 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100"
          />
          <p v-if="errors.profile_photo" class="mt-1 text-sm text-red-600">{{ errors.profile_photo }}</p>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-orange-500 hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50"
          >
            <span v-if="loading">Creando cuenta...</span>
            <span v-else>Crear cuenta</span>
          </button>
        </div>

        <div class="text-center">
          <p class="text-sm text-gray-300">
            ¿Ya tienes cuenta?
            <RouterLink to="/login" class="font-medium text-orange-500 hover:text-orange-400 ml-1">Inicia sesión</RouterLink>
          </p>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'

const auth = useAuthStore()
const profilePhotoInput = ref(null)

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'cliente',
  profile_photo: null
})

const errors = reactive({
  name: null,
  email: null,
  password: null,
  password_confirmation: null,
  role: null,
  profile_photo: null
})

const generalError = ref('')
const loading = ref(false)

const submit = async () => {
  clearErrors()
  generalError.value = ''
  loading.value = true

  // Handle file upload
  if (profilePhotoInput.value && profilePhotoInput.value.files.length > 0) {
    form.profile_photo = profilePhotoInput.value.files[0]
  }

  try {
    await auth.register(form.name, form.email, form.password, form.password_confirmation, form.role, form.profile_photo)
    // Redirect to login or dashboard
    window.location.href = '/login'
  } catch (error) {
    loading.value = false
    // Handle validation errors
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
      generalError.value = 'Error en el registro. Inténtalo de nuevo.'
    }
  }
}

const clearErrors = () => {
  errors.name = null
  errors.email = null
  errors.password = null
  errors.password_confirmation = null
  errors.role = null
  errors.profile_photo = null
  generalError.value = ''
}
</script>
