<template>
  <div class="min-h-screen bg-gray-50 text-gray-900">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900">Productos</h1>
        <p class="mt-2 text-sm text-gray-600">Descubre los mejores repuestos para moto</p>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col md:flex-row gap-4">
          <!-- Search -->
          <div class="flex-1">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input
                v-model="searchQuery"
                @input="debouncedSearch"
                type="text"
                placeholder="Buscar productos..."
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
              />
            </div>
          </div>

          <!-- Category Filter -->
          <div class="w-full md:w-48">
            <select
              v-model="selectedCategory"
              @change="fetchProducts(1)"
              class="select-light block w-full py-2 px-3 border border-gray-300 bg-white text-gray-900 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
            >
              <option value="">Todas las categorías</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>

          <!-- Sort -->
          <div class="w-full md:w-48">
            <select
              v-model="sortBy"
              @change="fetchProducts(1)"
              class="select-light block w-full py-2 px-3 border border-gray-300 bg-white text-gray-900 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
            >
              <option value="recent">Más recientes</option>
              <option value="price_asc">Precio: menor a mayor</option>
              <option value="price_desc">Precio: mayor a menor</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
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
            <h3 class="text-sm font-medium text-red-800">Error al cargar productos</h3>
            <div class="mt-2 text-sm text-red-700">{{ error }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Products Grid -->
    <div v-else class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <!-- Results count -->
      <div class="mb-6">
        <p class="text-sm text-gray-700">
          Mostrando {{ products.length }} de {{ pagination.total }} productos
        </p>
      </div>

      <!-- Empty State -->
      <div v-if="products.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron productos</h3>
        <p class="mt-1 text-sm text-gray-500">Intenta cambiar los filtros de búsqueda.</p>
      </div>

      <!-- Products Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div v-for="product in products" :key="product.id" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow duration-300">
          <div class="aspect-w-1 aspect-h-1 bg-gray-200">
            <img
              :src="product.image_url || '/placeholder-product.png'"
              :alt="product.name"
              class="w-full h-48 object-center object-cover"
            />
          </div>

          <div class="p-4">
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-medium text-gray-900 truncate">{{ product.name }}</h3>
              <p class="text-sm font-medium text-gray-900">${{ product.price }}</p>
            </div>

            <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ product.description }}</p>

            <div class="mt-2">
              <p v-if="product.category" class="text-xs text-gray-600 bg-gray-100 px-2 py-1 rounded-full inline-block">
                {{ product.category.name }}
              </p>
            </div>

            <div class="mt-4 flex items-center justify-between">
              <div class="flex items-center">
                <div class="flex items-center">
                  <svg v-for="i in 5" :key="i" class="w-3 h-3" :class="i <= Math.floor(product.rating || 0) ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </div>
                <span class="ml-1 text-xs text-gray-600">{{ product.rating || '0.0' }}</span>
              </div>
            </div>

            <div class="mt-4 flex gap-2">
              <router-link
                :to="`/product/${product.id}`"
                class="flex-1 bg-gray-100 text-gray-700 px-3 py-2 rounded-md text-sm font-medium text-center hover:bg-gray-200 transition-colors"
              >
                Ver detalles
              </router-link>
              <button
                @click="addToCart(product)"
                class="flex-1 bg-orange-600 text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-orange-700 transition-colors"
              >
                Agregar
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="mt-8 flex justify-center">
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
          <!-- Previous Button -->
          <button
            @click="goToPage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>

          <!-- Page Numbers -->
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="typeof page === 'number' ? goToPage(page) : null"
            :class="[
              'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
              page === pagination.current_page
                ? 'z-10 bg-orange-50 border-orange-500 text-orange-600'
                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
              typeof page === 'string' ? 'cursor-default' : 'cursor-pointer'
            ]"
          >
            {{ page }}
          </button>

          <!-- Next Button -->
          <button
            @click="goToPage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import API from '../services/api.js'

export default {
  name: 'Products',
  setup() {
    const route = useRoute()
    const products = ref([])
    const categories = ref([])
    const loading = ref(true)
    const error = ref('')
    const searchQuery = ref('')
    const selectedCategory = ref('')
    const sortBy = ref('recent')
    const pagination = ref({
      current_page: 1,
      last_page: 1,
      per_page: 12,
      total: 0
    })

    const visiblePages = computed(() => {
      const current = pagination.value.current_page
      const last = pagination.value.last_page
      const delta = 2
      const range = []
      const rangeWithDots = []

      for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
        range.push(i)
      }

      if (current - delta > 2) {
        rangeWithDots.push(1, '...')
      } else {
        rangeWithDots.push(1)
      }

      rangeWithDots.push(...range)

      if (current + delta < last - 1) {
        rangeWithDots.push('...', last)
      } else if (last > 1) {
        rangeWithDots.push(last)
      }

      return rangeWithDots.filter(item => item !== '...' || rangeWithDots.indexOf(item) === rangeWithDots.lastIndexOf(item))
    })

    const fetchProducts = async (page = 1) => {
      try {
        loading.value = true
        error.value = ''

        const params = {
          page,
          per_page: 12,
          search: searchQuery.value,
          category: selectedCategory.value || undefined,
          sort: sortBy.value === 'recent' ? undefined : sortBy.value
        }

        const response = await API.get('/products', { params })
        products.value = response.data.data || []
        pagination.value = {
          current_page: response.data.current_page || 1,
          last_page: response.data.last_page || 1,
          per_page: response.data.per_page || 12,
          total: response.data.total || 0
        }
      } catch (err) {
        console.error('Error fetching products:', err)
        error.value = err.response?.data?.message || 'Error al cargar los productos. Inténtalo de nuevo.'
      } finally {
        loading.value = false
      }
    }

    const fetchCategories = async () => {
      try {
        const response = await API.get('/categories')
        categories.value = response.data || []
      } catch (err) {
        console.error('Error fetching categories:', err)
      }
    }

    let searchTimeout = null
    const debouncedSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => fetchProducts(1), 450)
    }

    const goToPage = (page) => {
      if (page >= 1 && page <= pagination.value.last_page) {
        fetchProducts(page)
      }
    }

    const addToCart = (product) => {
      // TODO: Implementar carrito de compras
      console.log('Agregar al carrito:', product)
      // Por ahora solo mostrar un mensaje
      alert(`Producto "${product.name}" agregado al carrito (funcionalidad pendiente)`)
    }

    onMounted(() => {
      selectedCategory.value = route.query.category || route.query.category_id || ''
      fetchCategories()
      fetchProducts()
    })

    onBeforeUnmount(() => {
      clearTimeout(searchTimeout)
    })

    return {
      products,
      categories,
      loading,
      error,
      searchQuery,
      selectedCategory,
      sortBy,
      pagination,
      visiblePages,
      fetchProducts,
      debouncedSearch,
      goToPage,
      addToCart
    }
  }
}
</script>
