<template>
  <div class="min-h-screen bg-[#0F172A] text-[#E7DBCB]">
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-[#0F172A] via-[#1E293B] to-[#2A2A2A] text-[#E7DBCB] py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
          <h1 class="text-4xl md:text-6xl font-bold mb-6" data-testid="hero-title">
            Crea tu tienda de repuestos y accesorios de moto en minutos
          </h1>
          <p class="text-xl md:text-2xl mb-8 text-gray-200" data-testid="hero-subtitle">
            Multi-tenant, tiendas verificadas, pagos seguros
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button
              class="btn-primary w-full md:w-auto px-8 py-3"
              data-testid="cta-explorar"
              @click="navigateToStores"
            >
              Explorar tiendas
            </button>
            <button
              class="btn-secondary w-full md:w-auto px-8 py-3"
              data-testid="cta-crear"
              @click="navigateToCreateStore"
            >
              Crear mi tienda
            </button>
            <button
              class="btn-secondary w-full md:w-auto px-8 py-3"
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
    <section class="bg-[#111827] py-8 border-b border-[#1F2937]">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-center items-center gap-8 text-center">
          <div class="flex items-center gap-2">
            <span class="text-2xl font-bold text-[#EE471B]">+1000</span>
            <span class="text-gray-300">productos</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-2xl font-bold text-[#EE471B]">Tiendas</span>
            <span class="text-gray-300">verficadas</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-2xl font-bold text-[#EE471B]">Pagos</span>
            <span class="text-gray-300">seguros</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Branding Card -->
    <section class="py-16 bg-[#0F172A]">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="cp-card p-8 text-center" data-testid="branding-card">
          <h2 class="text-3xl font-bold text-[#E7DBCB] mb-4">
            Branding asistido por IA
          </h2>
          <p class="text-gray-300 mb-6">
            Crea una identidad visual profesional para tu tienda con la ayuda de inteligencia artificial
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button class="btn-primary w-full md:w-auto px-6 py-3">
              Probar branding IA
            </button>
            <button class="btn-secondary w-full md:w-auto px-6 py-3">
              Ver ejemplo
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Categories Section -->
    <section class="py-16 bg-[#111827]">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center text-[#E7DBCB] mb-12">
          Explora categorías
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
          <button
            v-for="category in categories"
            :key="category.id"
            class="cp-chip"
            data-testid="category-chip"
            @click="navigateToCategory(category)"
          >
            <span class="text-sm font-medium text-[#E7DBCB]">{{ category.name }}</span>
          </button>
        </div>
        <div class="text-center mt-8">
          <button class="text-[#EE471B] hover:text-[#d63f18] font-semibold">
            Ver todas las categorías →
          </button>
        </div>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="py-16 bg-[#0F172A]">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-12">
          <h2 class="text-3xl font-bold text-[#E7DBCB]">
            Productos destacados
          </h2>
          <button
            class="text-[#EE471B] hover:text-[#d63f18] font-semibold"
            @click="navigateToProducts"
          >
            Ver todo →
          </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="product in featuredProducts"
            :key="product.id"
            class="cp-card overflow-hidden hover:border-[#EE471B] transition-colors"
            data-testid="product-card"
          >
            <img
              :src="product.image"
              :alt="product.name"
              class="w-full h-48 object-cover"
            />
            <div class="p-4">
              <h3 class="font-semibold text-[#E7DBCB] mb-2">{{ product.name }}</h3>
              <p class="text-[#EE471B] font-bold text-lg">{{ product.price }}</p>
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
