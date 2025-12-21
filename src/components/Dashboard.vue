<template>
  <div class="max-w-4xl mx-auto p-4">
    <h1 class="text-3xl font-bold mb-6">Panel de Control</h1>

    <div v-if="successMessage" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
      {{ successMessage }}
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-2">Inicio</h2>
        <p class="text-gray-600">Bienvenido a tu panel de control.</p>
      </div>

      <div v-if="user && user.role === 'merchant'" class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-2">Productos</h2>
        <p class="text-gray-600">Gestiona tus productos.</p>
        <router-link to="/products" class="text-blue-600 hover:underline">Ver productos</router-link>
      </div>

      <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-2">Perfil</h2>
        <p class="text-gray-600">Actualiza tu información personal.</p>
        <router-link to="/profile/edit" class="text-blue-600 hover:underline">Editar perfil</router-link>
      </div>
    </div>

    <div class="mt-8 bg-white p-6 rounded shadow">
      <h2 class="text-xl font-semibold mb-4">Información del Usuario</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <strong>Nombre:</strong> {{ user?.name }}
        </div>
        <div>
          <strong>Email:</strong> {{ user?.email }}
        </div>
        <div>
          <strong>Rol:</strong> {{ user?.role }}
        </div>
        <div>
          <strong>Estado:</strong> {{ user?.status ? 'Activo' : 'Inactivo' }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getAuth, onAuthStateChanged } from 'firebase/auth'

const user = ref(null)
const successMessage = ref('')

const auth = getAuth()

onMounted(() => {
  onAuthStateChanged(auth, (firebaseUser) => {
    if (firebaseUser) {
      // Map Firebase user to expected user object structure
      user.value = {
        name: firebaseUser.displayName || firebaseUser.email,
        email: firebaseUser.email,
        role: 'merchant', // You may want to fetch this from your backend or Firestore
        status: true // Assume active, or fetch real status
      }
    } else {
      user.value = null
    }
  })
})
</script>

<style scoped>
/* Estilos para el dashboard */
</style>
