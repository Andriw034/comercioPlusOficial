<template>
  <div v-if="loading" class="h-8 w-24 rounded-md animate-pulse bg-muted"></div>

  <div v-else-if="!user" class="flex items-center gap-2">
    <router-link to="/login" class="px-4 py-2 text-sm font-medium text-muted-foreground hover:text-primary transition-colors">
      Entrar
    </router-link>
    <router-link to="/register" class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors">
      Crear cuenta
    </router-link>
  </div>

  <div v-else class="flex items-center gap-2">
    <div class="flex items-center gap-2">
      <span class="text-sm text-muted-foreground">Hola, {{ user.name }}</span>
      <form method="POST" action="/logout" class="inline">
        <input type="hidden" name="_token" :value="csrfToken" />
        <button type="submit" class="px-4 py-2 text-sm font-medium text-red-600 hover:text-red-800 transition-colors">
          Cerrar sesión
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

// Props para recibir datos del usuario desde Laravel
const props = defineProps({
  user: {
    type: Object,
    default: null
  },
  csrfToken: {
    type: String,
    default: ''
  }
})

const loading = ref(false)

// El estado de autenticación se maneja a través de props desde Laravel
// No necesitamos lógica adicional aquí ya que Laravel maneja la autenticación
</script>

<style scoped>
/* Puedes agregar estilos específicos para el AuthWidget aquí */
</style>
