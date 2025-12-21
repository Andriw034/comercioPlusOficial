<template>
  <div class="min-h-screen">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-orange-600 to-orange-700">
      <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
        <div class="text-center">
          <h1 class="text-4xl font-extrabold text-white sm:text-5xl md:text-6xl">
            Bienvenido a ComercioPlus
          </h1>
          <p class="mt-3 max-w-md mx-auto text-base text-orange-100 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
            La plataforma líder para repuestos de moto. Encuentra todo lo que necesitas para mantener tu motocicleta en perfectas condiciones.
          </p>
          <div class="mt-5 max-w-md mx-auto sm:flex sm:justify-center md:mt-8">
            <div class="rounded-md shadow">
              <router-link
                to="/products"
                class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-orange-600 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10"
              >
                Explorar Productos
              </router-link>
            </div>
            <div class="mt-3 rounded-md shadow sm:mt-0 sm:ml-3">
              <router-link
                to="/stores"
                class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-orange-500 hover:bg-orange-400 md:py-4 md:text-lg md:px-10"
              >
                Ver Tiendas
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Section -->
    <div class="bg-white">
      <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8">
        <div class="max-w-4xl mx-auto text-center">
          <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
            Confianza y calidad garantizada
          </h2>
          <p class="mt-3 text-xl text-gray-500 sm:mt-4">
            Miles de motociclistas confían en nosotros para sus repuestos
          </p>
        </div>
        <dl class="mt-10 text-center sm:max-w-3xl sm:mx-auto sm:grid sm:grid-cols-3 sm:gap-8">
          <div class="flex flex-col">
            <dt class="order-2 mt-2 text-lg leading-6 font-medium text-gray-500">Tiendas verificadas</dt>
            <dd class="order-1 text-5xl font-extrabold text-orange-600">150+</dd>
          </div>
          <div class="flex flex-col mt-10 sm:mt-0">
            <dt class="order-2 mt-2 text-lg leading-6 font-medium text-gray-500">Productos disponibles</dt>
            <dd class="order-1 text-5xl font-extrabold text-orange-600">10K+</dd>
          </div>
          <div class="flex flex-col mt-10 sm:mt-0">
            <dt class="order-2 mt-2 text-lg leading-6 font-medium text-gray-500">Clientes satisfechos</dt>
            <dd class="order-1 text-5xl font-extrabold text-orange-600">5K+</dd>
          </div>
        </dl>
      </div>
    </div>

    <!-- Featured Products Section -->
    <div class="bg-gray-50">
      <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
          <div class="sm:flex-auto">
            <h2 class="text-3xl font-extrabold text-gray-900">Productos destacados</h2>
            <p class="mt-2 text-sm text-gray-700">
              Los productos más populares y mejor valorados por nuestros clientes
            </p>
          </div>
          <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <router-link
              to="/products"
              class="inline-flex items-center justify-center rounded-md border border-transparent bg-orange-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-orange-700"
            >
              Ver todos los productos
            </router-link>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="mt-8 flex justify-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-600"></div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="mt-8">
          <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Error al cargar productos destacados</h3>
                <div class="mt-2 text-sm text-red-700">{{ error }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Products Grid -->
        <div v-else class="mt-8 grid grid-cols-1 gap-y-10 gap-x-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8">
          <div v-for="product in featuredProducts" :key="product.id" class="group">
            <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-lg bg-gray-200 xl:aspect-w-7 xl:aspect-h-8">
              <img
                :src="product.image_url || '/placeholder-product.png'"
                :alt="product.name"
                class="h-full w-full object-cover object-center group-hover:opacity-75 transition-opacity duration-300"
              />
            </div>
            <h3 class="mt-4 text-sm text-gray-700">{{ product.name }}</h3>
            <p class="mt-1 text-lg font-medium text-gray-900">${{ product.price }}</p>
            <div class="mt-2 flex items-center">
              <div class="flex items-center">
                <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= Math.floor(product.rating || 0) ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </div>
              <span class="ml-2 text-sm text-gray-600">{{ product.rating || '0.0' }}</span>
            </div>
            <div class="mt-4">
              <router-link
                :to="`/product/${product.id}`"
                class="w-full bg-orange-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-orange-700 transition-colors text-center block"
              >
                Ver producto
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Categories Section -->
    <div class="bg-white">
      <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
          <div class="sm:flex-auto">
            <h2 class="text-3xl font-extrabold text-gray-900">Categorías</h2>
            <p class="mt-2 text-sm text-gray-700">
              Explora nuestros productos organizados por categorías
            </p>
          </div>
        </div>

        <!-- Categories Grid -->
        <div class="mt-8 grid grid-cols-1 gap-y-10 gap-x-6 sm:grid-cols-2 lg:grid-cols-4 xl:gap-x-8">
          <div v-for="category in categories" :key="category.id" class="group">
            <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-lg bg-gray-200">
              <div class="flex items-center justify-center h-full bg-gradient-to-br from-orange-400 to-orange-600">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
              </div>
            </div>
            <h3 class="mt-4 text-lg font-medium text-gray-900">{{ category.name }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ category.description }}</p>
            <div class="mt-4">
              <router-link
                :to="`/products?category_id=${category.id}`"
                class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-200 transition-colors text-center block"
              >
                Ver productos
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-orange-700">
      <div class="max-w-2xl mx-auto text-center py-16 px-4 sm:py-20 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
          <span class="block">¿Eres comerciante?</span>
          <span class="block">Únete a nuestra plataforma</span>
        </h2>
        <p class="mt-4 text-lg leading-6 text-orange-200">
          Crea tu tienda virtual y llega a miles de motociclistas en toda la región.
        </p>
        <router-link
          to="/register"
          class="mt-8 w-full inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-orange-600 bg-white hover:bg-orange-50 sm:w-auto"
        >
          Crear tienda gratis
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import API from '../services/api.js'

const categories = ref([])
const featuredProducts = ref([])
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    loading.value = true
    error.value = null

    // Cargar categorías y productos destacados en paralelo
    const [categoriesResponse, productsResponse] = await Promise.all([
      API.get('/categories'),
      API.get('/products?per_page=8&sort=rating') // Productos destacados
    ])

    categories.value = categoriesResponse.data || []
    featuredProducts.value = productsResponse.data.data || []
  } catch (err) {
    error.value = 'Error al cargar el contenido'
    console.error('Home loading error:', err)
  } finally {
    loading.value = false
  }
})
</script>


