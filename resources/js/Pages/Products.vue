<template>
  <div
    class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black"
    :style="branding.cover_url ? {
      backgroundImage: `url('${branding.cover_url}')`,
      backgroundSize: 'cover',
      backgroundPosition: 'center'
    } : {}"
  >
    <!-- Header moderno -->
    <header class="sticky top-0 z-50 bg-black/80 backdrop-blur-xl border-b border-white/10 shadow-2xl">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <RouterLink class="flex items-center space-x-3" to="/">
            <div v-if="branding.logo_url" class="w-10 h-10 rounded-xl overflow-hidden ring-1 ring-white/10 bg-white/10">
              <img :src="branding.logo_url" alt="Logo" class="w-full h-full object-cover" />
            </div>
            <div v-else class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-600 rounded-xl flex items-center justify-center">
              <span class="text-white font-bold text-lg">C</span>
            </div>
            <div class="flex flex-col">
              <span class="text-xl font-bold text-white">{{ branding.store_name || 'ComercioPlus' }}</span>
              <span class="text-xs text-gray-400">Repuestos & Accesorios</span>
            </div>
          </RouterLink>
        </div>
      </div>
    </header>

    <!-- Resto de tu contenido -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Aquí ya está toda la lógica de productos -->
      <!-- ... (mantén igual tu grid de productos) ... -->
    </main>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'

const API = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000'

const branding = ref({
  cover_url: null,
  logo_url: null,
  store_name: null,
})

onMounted(() => {
  if (window.__STORE_BRANDING__) {
    branding.value = window.__STORE_BRANDING__
  }
})
</script>
