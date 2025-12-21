<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 m-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Error al cargar la tienda</h3>
          <div class="mt-2 text-sm text-red-700">{{ error }}</div>
        </div>
      </div>
    </div>

    <!-- Store Content -->
    <div v-else-if="store" class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <!-- Store Header -->
      <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
        <div class="relative h-64 bg-gradient-to-r from-orange-400 to-orange-600">
          <div class="absolute inset-0 bg-black bg-opacity-30"></div>
          <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
            <div class="flex items-center justify-between">
              <div>
                <h1 class="text-3xl font-bold">{{ store.name }}</h1>
                <p class="mt-2 text-orange-100">{{ store.description }}</p>
                <div class="mt-4 flex items-center space-x-4">
                  <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>{{ store.address || 'Dirección no especificada' }}</span>
                  </div>
                  <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <span>{{ store.phone || 'Teléfono no especificado' }}</span>
                  </div>
                </div>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold">{{ storeProducts.length }}</div>
                <div class="text-sm text-orange-100">productos</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Products Section -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Productos de {{ store.name }}</h3>

          <!-- Products Grid -->
          <div v-if="storeProducts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="product in storeProducts" :key="product.id" class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
              <div class="aspect-w-1 aspect-h-1 bg-gray-200">
                <img
                  v-if="product.image"
                  :src="product.image"
                  :alt="product.name"
                  class="w-full h-48 object-cover"
                />
                <div v-else class="w-full h-48 bg-gray-300 flex items-center justify-center">
                  <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
              </div>
              <div class="p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-1">{{ product.name }}</h4>
                <p class="text-sm text-gray-500 mb-2 line-clamp-2">{{ product.description }}</p>
                <div class="flex items-center justify-between">
                  <span class="text-lg font-bold text-gray-900">${{ product.price }}</span>
                  <span class="text-sm text-gray-500">Stock: {{ product.stock }}</span>
                </div>
                <div class="mt-3 flex items-center justify-between">
                  <div class="flex items-center">
                    <div class="flex items-center">
                      <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                      </svg>
                      <span class="ml-1 text-sm text-gray-600">{{ product.average_rating || '0.0' }}</span>
                    </div>
                  </div>
                  <router-link
                    :to="`/product/${product.slug}`"
                    class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-orange-600 bg-orange-100 hover:bg-orange-200"
                  >
                    Ver detalle
                  </router-link>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay productos</h3>
            <p class="mt-1 text-sm text-gray-500">Esta tienda aún no tiene productos disponibles.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Store Not Found -->
    <div v-else class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Tienda no encontrada</h3>
        <p class="mt-1 text-sm text-gray-500">La tienda que buscas no existe o no está disponible.</p>
        <div class="mt-6">
          <router-link
            to="/stores"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700"
          >
            Ver todas las tiendas
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import API from '../services/api.js'

export default {
  name: 'StoreDetail',
  setup() {
    const route = useRoute()
    const loading = ref(true)
    const error = ref('')
    const store = ref(null)
    const storeProducts = ref([])

    const fetchStoreDetail = async () => {
      try {
        loading.value = true
        error.value = ''

        const slug = route.params.slug

        // Fetch store details
        const storeResponse = await API.get(`/public-stores/${slug}`)
        store.value = storeResponse.data

        // Fetch products for this store
        const productsResponse = await API.get('/products', {
          params: { store_id: store.value.id }
        })
        storeProducts.value = productsResponse.data.data || []

      } catch (err) {
        console.error('Store detail loading error:', err)
        error.value = err.response?.data?.message || 'Error al cargar la tienda'
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchStoreDetail()
    })

    return {
      loading,
      error,
      store,
      storeProducts
    }
  }
}
</script>
