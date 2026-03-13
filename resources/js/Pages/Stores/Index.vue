<script setup>
import { ref, onMounted } from 'vue'

const stores = ref([])
const loading = ref(true)

onMounted(async () => {
  // TODO: Fetch stores from API
  // For now, using placeholder data
  stores.value = [
    {
      id: 1,
      name: 'MotoParts Pro',
      description: 'Especialistas en repuestos para motos de alta cilindrada',
      logo: '/images/store1.jpg',
      rating: 4.5,
      totalProducts: 150
    },
    {
      id: 2,
      name: 'Repuestos Veloz',
      description: 'Repuestos originales y compatibles para todas las marcas',
      logo: '/images/store2.jpg',
      rating: 4.2,
      totalProducts: 89
    }
  ]
  loading.value = false
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900">Tiendas</h1>
          <p class="mt-2 text-gray-600">Descubre tiendas especializadas en repuestos de motos</p>
        </div>

        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="n in 6" :key="n" class="bg-white rounded-lg shadow-md p-6 animate-pulse">
            <div class="h-12 bg-gray-200 rounded mb-4"></div>
            <div class="h-4 bg-gray-200 rounded mb-2"></div>
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
          </div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="store in stores"
            :key="store.id"
            class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden"
          >
            <div class="p-6">
              <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">{{ store.name }}</h3>
                  <div class="flex items-center mt-1">
                    <div class="flex items-center">
                      <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= Math.floor(store.rating) ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                      </svg>
                    </div>
                    <span class="ml-2 text-sm text-gray-600">{{ store.rating }}</span>
                  </div>
                </div>
              </div>

              <p class="text-gray-600 mb-4">{{ store.description }}</p>

              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">{{ store.totalProducts }} productos</span>
                <router-link
                  :to="`/stores/${store.id}`"
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                >
                  Ver Tienda
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
