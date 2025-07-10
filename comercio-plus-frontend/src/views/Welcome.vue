<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-purple-700 text-white py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
          <h1 class="text-4xl md:text-6xl font-bold mb-6" data-testid="hero-title">
            Crea tu tienda de repuestos y accesorios de moto en minutos
          </h1>
          <p class="text-xl md:text-2xl mb-8" data-testid="hero-subtitle">
            Multi-tenant, tiendas verificadas, pagos seguros
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button
              class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors"
              data-testid="cta-explorar"
              @click="navigateToStores"
            >
              Explorar tiendas
            </button>
            <button
              class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors"
              data-testid="cta-crear"
              @click="navigateToCreateStore"
            >
              Crear mi tienda
            </button>
            <button
              class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors"
              data-testid="cta-como"
              @click="navigateToHowItWorks"
            >
              Cómo funciona
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Trust Bar -->
    <section class="bg-white py-8 border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-center items-center gap-8 text-center">
          <div class="flex items-center gap-2">
            <span class="text-2xl font-bold text-green-600">+1000</span>
            <span class="text-gray-600">productos</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-2xl font-bold text-blue-600">Tiendas</span>
            <span class="text-gray-600">verficadas</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-2xl font-bold text-purple-600">Pagos</span>
            <span class="text-gray-600">seguros</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Branding Card -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center" data-testid="branding-card">
          <h2 class="text-3xl font-bold text-gray-900 mb-4">
            Branding asistido por IA
          </h2>
          <p class="text-gray-600 mb-6">
            Crea una identidad visual profesional para tu tienda con la ayuda de inteligencia artificial
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
              Probar branding IA
            </button>
            <button class="bg-gray-200 text-gray-800 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
              Ver ejemplo
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Categories Section -->
    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">
          Explora categorías
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
          <button
            v-for="category in categories"
            :key="category.id"
            class="bg-gray-100 hover:bg-gray-200 rounded-lg p-4 text-center transition-colors"
            data-testid="category-chip"
            @click="navigateToCategory(category)"
          >
            <span class="text-sm font-medium text-gray-900">{{ category.name }}</span>
          </button>
        </div>
        <div class="text-center mt-8">
          <button class="text-blue-600 hover:text-blue-800 font-semibold">
            Ver todas las categorías →
          </button>
        </div>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-12">
          <h2 class="text-3xl font-bold text-gray-900">
            Productos destacados
          </h2>
          <button
            class="text-blue-600 hover:text-blue-800 font-semibold"
            @click="navigateToProducts"
          >
            Ver todo →
          </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="product in featuredProducts"
            :key="product.id"
            class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow"
            data-testid="product-card"
          >
            <img
              :src="product.image"
              :alt="product.name"
              class="w-full h-48 object-cover"
            />
            <div class="p-4">
              <h3 class="font-semibold text-gray-900 mb-2">{{ product.name }}</h3>
              <p class="text-blue-600 font-bold text-lg">{{ product.price }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

// Configure axios with backend API base URL
axios.defaults.baseURL = window.location.origin + '/api'
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['Accept'] = 'application/json'

// CSRF token for Laravel
const token = document.head.querySelector('meta[name="csrf-token"]')
if (token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
}

const router = useRouter()

// Reactive data
const categories = ref([])
const featuredProducts = ref([
  {
    id: 1,
    name: 'Casco integral básico',
    price: '$230,000',
    image: '/images/casco.jpg'
  },
  {
    id: 2,
    name: 'Guantes de cuero',
    price: '$45,000',
    image: '/images/guantes.jpg'
  },
  {
    id: 3,
    name: 'Aceite de motor 4T',
    price: '$35,000',
    image: '/images/aceite.jpg'
  },
  {
    id: 4,
    name: 'Batería de litio',
    price: '$180,000',
    image: '/images/bateria.jpg'
  }
])

// Methods
const navigateToStores = () => {
  router.push('/stores')
}

const navigateToCreateStore = () => {
  router.push('/stores/create')
}

const navigateToHowItWorks = () => {
  router.push('/how-it-works')
}

const navigateToProducts = () => {
  router.push('/products')
}

const navigateToCategory = (category) => {
  router.push(`/categories/${category.slug}`)
}

// Load categories on mount
onMounted(async () => {
  try {
    const response = await axios.get('/categories')
    categories.value = response.data.data || []
  } catch (error) {
    console.error('Error loading categories:', error)
    // Fallback categories
    categories.value = [
      { id: 1, name: 'Accesorios', slug: 'accesorios' },
      { id: 2, name: 'Aceites', slug: 'aceites' },
      { id: 3, name: 'Frenos', slug: 'frenos' },
      { id: 4, name: 'Motor', slug: 'motor' },
      { id: 5, name: 'Suspensión', slug: 'suspension' },
      { id: 6, name: 'Electricidad', slug: 'electricidad' }
    ]
  }
})
</script>

<style scoped>
/* Additional styles can be added here if needed */
</style>
