<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error al cargar el producto</h3>
            <div class="mt-2 text-sm text-red-700">{{ error }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Content -->
    <div v-else-if="product" class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <!-- Breadcrumb -->
      <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
          <li class="inline-flex items-center">
            <router-link to="/" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-orange-600">
              <svg class="w-3 h-3 mr-2.5" fill="currentColor" viewBox="0 0 20 20">
                <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2A1 1 0 0 0 1 10h2v8a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1v-8h2a1 1 0 0 0 .707-1.707Z"/>
              </svg>
              Inicio
            </router-link>
          </li>
          <li>
            <div class="flex items-center">
              <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/>
              </svg>
              <router-link :to="`/products?category_id=${product.category?.id}`" class="ml-1 text-sm font-medium text-gray-700 hover:text-orange-600 md:ml-2">
                {{ product.category?.name }}
              </router-link>
            </div>
          </li>
          <li aria-current="page">
            <div class="flex items-center">
              <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/>
              </svg>
              <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ product.name }}</span>
            </div>
          </li>
        </ol>
      </nav>

      <div class="lg:grid lg:grid-cols-2 lg:gap-x-8 lg:items-start">
        <!-- Image gallery -->
        <div class="w-full max-w-2xl mx-auto sm:block lg:max-w-none">
          <div class="aspect-w-1 aspect-h-1 bg-gray-100 rounded-lg overflow-hidden">
            <img
              :src="product.image_url || '/placeholder-product.png'"
              :alt="product.name"
              class="w-full h-full object-center object-cover"
            />
          </div>
        </div>

        <!-- Product info -->
        <div class="mt-10 px-4 sm:px-0 sm:mt-16 lg:mt-0">
          <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ product.name }}</h1>

          <div class="mt-3">
            <h2 class="sr-only">Información del producto</h2>
            <p class="text-3xl text-gray-900">${{ formatPrice(product.price) }}</p>
          </div>

          <div class="mt-6">
            <h3 class="sr-only">Descripción</h3>
            <div class="text-base text-gray-700 space-y-6">
              <p>{{ product.description }}</p>
            </div>
          </div>

          <div class="mt-8">
            <div class="flex items-center">
              <h3 class="text-sm font-medium text-gray-900">Rating</h3>
              <div class="ml-3 flex items-center">
                <div class="flex items-center">
                  <svg v-for="i in 5" :key="i" class="w-5 h-5" :class="i <= Math.floor(product.rating || 0) ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </div>
                <p class="ml-2 text-sm text-gray-600">{{ product.rating || '0.0' }} ({{ product.reviews_count || 0 }} reseñas)</p>
              </div>
            </div>
          </div>

          <div class="mt-8">
            <div class="flex items-center space-x-4">
              <div class="flex items-center text-sm text-gray-600">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Stock: {{ product.stock }}
              </div>
            </div>
          </div>

          <div class="mt-8">
            <div class="flex items-center space-x-4">
              <button
                @click="addToCart"
                class="flex-1 bg-orange-600 border border-transparent rounded-md py-3 px-8 flex items-center justify-center text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
              >
                Agregar al carrito
              </button>
              <button
                @click="toggleWishlist"
                class="ml-4 p-3 border border-gray-300 rounded-md flex items-center justify-center text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
              >
                <svg class="w-6 h-6" :class="isInWishlist ? 'fill-current text-red-500' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Store Info -->
          <div class="mt-8 border-t border-gray-200 pt-8">
            <h3 class="text-sm font-medium text-gray-900">Información de la tienda</h3>
            <div class="mt-4">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <router-link
                    :to="`/store/${product.store?.slug}`"
                    class="text-sm font-medium text-gray-900 hover:text-orange-600"
                  >
                    {{ product.store?.name }}
                  </router-link>
                  <p class="text-sm text-gray-500">{{ product.store?.description }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Not Found -->
    <div v-else class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Producto no encontrado</h3>
        <p class="mt-1 text-sm text-gray-500">El producto que buscas no existe o no está disponible.</p>
        <div class="mt-6">
          <router-link
            to="/products"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700"
          >
            Ver todos los productos
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import API from '../services/api.js'

const route = useRoute()
const product = ref(null)
const loading = ref(true)
const error = ref('')
const isInWishlist = ref(false)

const formatPrice = (value) => {
  return new Intl.NumberFormat('es-CO').format(value)
}

const loadProduct = async () => {
  try {
    loading.value = true
    error.value = ''

    const productId = route.params.id
    const response = await API.get(`/products/${productId}`)
    product.value = response.data?.data || response.data
  } catch (err) {
    console.error('Error loading product:', err)
    error.value = err.response?.data?.message || 'Error al cargar el producto. Inténtalo de nuevo.'
  } finally {
    loading.value = false
  }
}

const addToCart = () => {
  // TODO: Implementar sistema de carrito de compras
  console.log('Agregar al carrito:', product.value)
  // Por ahora solo mostrar un mensaje
  alert(`Producto "${product.value.name}" agregado al carrito (funcionalidad pendiente)`)
}

const toggleWishlist = () => {
  // TODO: Implementar sistema de lista de deseos
  isInWishlist.value = !isInWishlist.value
  console.log('Toggle wishlist:', product.value, isInWishlist.value)
  // Por ahora solo cambiar el estado visual
}

onMounted(() => {
  loadProduct()
})
</script>

<style scoped>
</style>
