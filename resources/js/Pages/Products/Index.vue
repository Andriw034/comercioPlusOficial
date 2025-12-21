<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black">
    <!-- Header moderno con diseño JBL -->
    <header class="sticky top-0 z-50 bg-black/80 backdrop-blur-xl border-b border-white/10 shadow-2xl">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <RouterLink class="flex items-center space-x-3" to="/">
            <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-600 rounded-xl flex items-center justify-center">
              <span class="text-white font-bold text-lg">C</span>
            </div>
            <div class="flex flex-col">
              <span class="text-xl font-bold text-white">ComercioPlus</span>
              <span class="text-xs text-gray-400">Repuestos & Accesorios</span>
            </div>
          </RouterLink>

          <div class="flex items-center space-x-4">
            <RouterLink
              class="text-gray-300 hover:text-orange-400 px-4 py-2 rounded-lg transition-colors border border-white/10 hover:border-orange-400/30"
              to="/dashboard"
            >
              Dashboard
            </RouterLink>
            <RouterLink
              class="bg-gradient-to-r from-orange-500 to-red-600 text-white px-6 py-2 rounded-xl font-medium hover:shadow-2xl hover:shadow-orange-500/25 transition-all duration-500 transform hover:scale-105 flex items-center space-x-2"
              to="/cart"
              role="button"
              aria-label="Ver carrito"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13H5.4"/>
              </svg>
              <span>Carrito ({{ cartCount }})</span>
            </RouterLink>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Hero Section -->
      <div class="text-center mb-12">
        <div class="inline-flex items-center px-6 py-3 bg-orange-500/20 backdrop-blur-sm text-orange-300 rounded-full text-sm font-semibold mb-6 border border-orange-500/30">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
          </svg>
          Catálogo Premium de Repuestos
        </div>
        <h1 class="text-5xl md:text-7xl font-black text-white mb-6">
          Productos
          <span class="bg-gradient-to-r from-orange-400 via-red-500 to-yellow-500 bg-clip-text text-transparent">
            Disponibles
          </span>
        </h1>
        <p class="text-2xl text-gray-300 max-w-3xl mx-auto leading-relaxed">
          Descubre la mejor selección de repuestos y accesorios para tu motocicleta
        </p>
      </div>

      <!-- Search and Filters -->
      <div class="bg-white/5 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/10 p-8 mb-12">
        <div class="flex flex-col lg:flex-row gap-8">
          <!-- Search Bar -->
          <div class="flex-1">
            <div class="relative">
              <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <input
                v-model="filters.search"
                type="search"
                placeholder="Buscar productos por nombre, marca, modelo..."
                class="w-full pl-14 pr-6 py-4 bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-300"
                @input="debouncedSearch"
              />
            </div>
          </div>

          <!-- Filters -->
          <div class="flex flex-wrap gap-6">
            <div class="relative">
              <select
                v-model="filters.category_id"
                @change="fetchProducts"
                class="appearance-none bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 pr-12 text-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-300"
              >
                <option value="" class="bg-gray-800">Todas las categorías</option>
                <option v-for="category in categories" :key="category.id" :value="category.id" class="bg-gray-800">
                  {{ category.name }}
                </option>
              </select>
              <svg class="absolute right-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </div>

            <div class="relative">
              <select
                v-model="filters.store_id"
                @change="fetchProducts"
                class="appearance-none bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 pr-12 text-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-300"
              >
                <option value="" class="bg-gray-800">Todas las tiendas</option>
                <option v-for="store in stores" :key="store.id" :value="store.id" class="bg-gray-800">
                  {{ store.name }}
                </option>
              </select>
              <svg class="absolute right-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </div>

            <label class="flex items-center space-x-4 bg-white/10 backdrop-blur-sm px-6 py-4 rounded-2xl cursor-pointer hover:bg-white/15 transition-all duration-300 border border-white/20">
              <input
                type="checkbox"
                v-model="filters.featured"
                @change="fetchProducts"
                class="w-5 h-5 bg-white/10 border-white/20 rounded focus:ring-orange-500 focus:ring-2"
              />
              <span class="text-white font-medium">Solo destacados</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Products Grid -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-orange-500"></div>
      </div>

      <div v-else-if="products.data.length === 0" class="text-center py-20">
        <div class="relative mb-12">
          <div class="w-40 h-40 bg-gradient-to-br from-gray-700 to-gray-800 rounded-full flex items-center justify-center mx-auto mb-8 shadow-2xl">
            <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-5.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
          </div>
          <!-- Floating elements -->
          <div class="absolute top-10 left-1/4 w-8 h-8 bg-orange-500/20 rounded-full blur-sm animate-pulse"></div>
          <div class="absolute bottom-10 right-1/4 w-6 h-6 bg-blue-500/20 rounded-full blur-sm animate-pulse delay-1000"></div>
        </div>
        <h3 class="text-4xl font-black text-white mb-6">No hay productos que coincidan</h3>
        <p class="text-xl text-gray-400 mb-10 max-w-lg mx-auto leading-relaxed">Intenta con otros filtros o términos de búsqueda.</p>
        <button
          class="bg-gradient-to-r from-orange-500 to-red-600 text-white px-10 py-5 rounded-2xl font-bold hover:shadow-2xl hover:shadow-orange-500/25 transition-all duration-500 transform hover:scale-110 text-xl"
          @click="clearFilters"
        >
          <svg class="w-6 h-6 mr-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          Limpiar filtros
        </button>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 mb-16">
        <article
          v-for="product in products.data"
          :key="product.id"
          class="group bg-white/5 backdrop-blur-xl rounded-3xl shadow-xl border border-white/10 overflow-hidden hover:shadow-2xl hover:shadow-orange-500/10 transition-all duration-500 transform hover:-translate-y-2"
          role="listitem"
        >
          <!-- Product Image -->
          <div class="relative aspect-square overflow-hidden bg-gradient-to-br from-gray-700 to-gray-800">
            <img
              :src="product.image_url || '/images/placeholders/product.png'"
              :alt="product.name"
              class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
              @error="onImgError($event)"
            />
            <div v-if="product.featured" class="absolute top-4 left-4 bg-gradient-to-r from-orange-500 to-red-600 text-white px-4 py-2 rounded-2xl text-sm font-bold shadow-lg">
              <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
              Destacado
            </div>
            <div v-if="product.stock <= 5 && product.stock > 0" class="absolute top-4 right-4 bg-yellow-500 text-white px-4 py-2 rounded-2xl text-sm font-bold shadow-lg">
              <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
              </svg>
              ¡Últimas!
            </div>
            <div v-if="product.stock <= 0" class="absolute inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center">
              <span class="bg-red-600 text-white px-6 py-3 rounded-2xl font-bold text-lg shadow-lg">Agotado</span>
            </div>

            <!-- Hover overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          </div>

          <!-- Product Info -->
          <div class="p-8">
            <div class="flex items-center justify-between mb-4">
              <span class="inline-flex items-center px-4 py-2 rounded-2xl text-sm font-semibold bg-orange-500/20 text-orange-300 border border-orange-500/30">
                {{ product.category?.name || 'Sin categoría' }}
              </span>
              <span class="text-sm text-gray-400 bg-white/10 px-3 py-1 rounded-full">{{ product.store?.name || 'Tienda' }}</span>
            </div>

            <h3 class="text-xl font-bold text-white mb-3 line-clamp-2 group-hover:text-orange-400 transition-colors duration-300">
              {{ product.name }}
            </h3>

            <p v-if="product.description" class="text-gray-400 text-sm mb-6 line-clamp-2">
              {{ product.description }}
            </p>

            <div class="flex items-center justify-between mb-6">
              <span class="text-3xl font-black text-white">{{ price(product.price) }}</span>
              <span
                class="text-sm font-semibold px-3 py-1 rounded-full"
                :class="{
                  'text-green-400 bg-green-500/20 border border-green-500/30': product.stock > 10,
                  'text-yellow-400 bg-yellow-500/20 border border-yellow-500/30': product.stock <= 10 && product.stock > 0,
                  'text-red-400 bg-red-500/20 border border-red-500/30': product.stock <= 0
                }"
              >
                {{ product.stock > 0 ? `${product.stock} en stock` : 'Agotado' }}
              </span>
            </div>

            <div class="flex gap-4">
              <button
                class="flex-1 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white py-4 px-6 rounded-2xl font-bold hover:shadow-2xl hover:shadow-orange-500/25 transition-all duration-500 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center"
                @click="addToCart(product)"
                :disabled="product.stock <= 0"
              >
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13H5.4"/>
                </svg>
                <span>{{ product.stock > 0 ? 'Agregar al carrito' : 'Agotado' }}</span>
              </button>
              <RouterLink
                class="bg-white/10 backdrop-blur-sm text-white py-4 px-6 rounded-2xl font-semibold hover:bg-white/20 transition-all duration-300 border border-white/20 flex items-center justify-center"
                :to="`/products/${product.id}`"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
              </RouterLink>
            </div>
          </div>
        </article>
      </div>

      <!-- Pagination -->
      <div v-if="products.last_page > 1" class="flex justify-center items-center space-x-3">
        <button
          v-for="page in products.last_page"
          :key="page"
          class="px-6 py-3 rounded-2xl font-bold transition-all duration-300"
          :class="{
            'bg-gradient-to-r from-orange-500 to-red-600 text-white shadow-2xl shadow-orange-500/25': page === products.current_page,
            'bg-white/10 backdrop-blur-sm text-white border border-white/20 hover:bg-white/20': page !== products.current_page
          }"
          @click="goToPage(page)"
        >
          {{ page }}
        </button>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const API = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000'

const products = ref({ data: [], current_page: 1, last_page: 1 })
const categories = ref([])
const stores = ref([])
const loading = ref(true)
const cartCount = ref(0)

const filters = reactive({
  search: '',
  category_id: '',
  store_id: '',
  featured: false,
  page: 1
})

const fetchProducts = async () => {
  loading.value = true
  try {
    const params = { ...filters }
    if (!params.search) delete params.search
    if (!params.category_id) delete params.category_id
    if (!params.store_id) delete params.store_id
    if (!params.featured) delete params.featured

    const { data } = await axios.get(`${API}/api/products`, { params })
    products.value = data
  } catch (e) {
    console.error('Error cargando productos', e)
    products.value = { data: [], current_page: 1, last_page: 1 }
  } finally {
    loading.value = false
  }
}

const fetchCategories = async () => {
  try {
    const { data } = await axios.get(`${API}/api/categories`)
    categories.value = data.data || data
  } catch (e) {
    console.error('Error cargando categorías', e)
  }
}

const fetchStores = async () => {
  try {
    const { data } = await axios.get(`${API}/api/stores`)
    stores.value = data.data || data
  } catch (e) {
    console.error('Error cargando tiendas', e)
  }
}

const fetchCartCount = async () => {
  try {
    const { data } = await axios.get(`${API}/api/cart/count`)
    cartCount.value = data.count || 0
  } catch (e) {
    console.error('Error cargando contador del carrito', e)
  }
}

let timer
const debouncedSearch = () => {
  clearTimeout(timer)
  timer = setTimeout(() => {
    filters.page = 1
    fetchProducts()
  }, 250)
}

const goToPage = (page) => {
  filters.page = page
  fetchProducts()
}

const clearFilters = () => {
  filters.search = ''
  filters.category_id = ''
  filters.store_id = ''
  filters.featured = false
  filters.page = 1
  fetchProducts()
}

const price = (val) => {
  if (val == null) return '—'
  try {
    return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(val)
  } catch {
    return `$${val}`
  }
}

const addToCart = async (product) => {
  try {
    await axios.post(`${API}/api/cart`, {
      product_id: product.id,
      quantity: 1
    })
    fetchCartCount()
    // Mostrar notificación de éxito
  } catch (e) {
    console.error('Error agregando al carrito', e)
  }
}

const onImgError = (e) => {
  e.target.src = '/images/placeholders/product.png'
}

onMounted(() => {
  fetchProducts()
  fetchCategories()
  fetchStores()
  fetchCartCount()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.delay-1000 {
  animation-delay: 1s;
}
</style>
