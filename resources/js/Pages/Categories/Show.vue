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
              class="flex items-center space-x-2 text-gray-300 hover:text-orange-400 px-4 py-2 rounded-lg transition-colors border border-white/10 hover:border-orange-400/30"
              to="/products"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
              <span>Volver a productos</span>
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
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-orange-500"></div>
      </div>

      <!-- Category Not Found -->
      <div v-else-if="!category" class="text-center py-20">
        <div class="relative mb-12">
          <div class="w-40 h-40 bg-gradient-to-br from-gray-700 to-gray-800 rounded-full flex items-center justify-center mx-auto mb-8 shadow-2xl">
            <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
          </div>
          <!-- Floating elements -->
          <div class="absolute top-10 left-1/4 w-8 h-8 bg-orange-500/20 rounded-full blur-sm animate-pulse"></div>
          <div class="absolute bottom-10 right-1/4 w-6 h-6 bg-blue-500/20 rounded-full blur-sm animate-pulse delay-1000"></div>
        </div>
        <h2 class="text-4xl font-black text-white mb-6">Categoría no encontrada</h2>
        <p class="text-xl text-gray-400 mb-10 max-w-lg mx-auto leading-relaxed">
          La categoría que buscas no existe o ha sido eliminada
        </p>
        <RouterLink
          class="inline-block bg-gradient-to-r from-orange-500 to-red-600 text-white px-10 py-5 rounded-2xl font-bold hover:shadow-2xl hover:shadow-orange-500/25 transition-all duration-500 transform hover:scale-110 text-xl"
          to="/products"
        >
          <svg class="w-6 h-6 mr-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          Explorar Productos
        </RouterLink>
      </div>

      <!-- Category Content -->
      <div v-else class="space-y-10">
        <!-- Category Hero Section -->
        <div class="bg-white/5 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/10 overflow-hidden">
          <div class="relative h-64 bg-gradient-to-r from-orange-500 via-red-500 to-yellow-500">
            <div class="absolute inset-0 bg-black/40"></div>
            <div class="absolute bottom-0 left-0 right-0 p-10 text-white">
              <div class="flex items-center space-x-6 mb-6">
                <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/20">
                  <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                  </svg>
                </div>
                <div>
                  <h1 class="text-5xl font-black mb-3">{{ category.name }}</h1>
                  <div class="flex items-center space-x-6 text-sm">
                    <span class="flex items-center bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                      </svg>
                      {{ products.length }} productos
                    </span>
                    <span class="flex items-center bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                      </svg>
                      {{ category.store?.name || 'Tienda' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Category Description -->
          <div class="p-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
              <div>
                <h2 class="text-3xl font-black text-white mb-6">Sobre esta categoría</h2>
                <p class="text-gray-300 text-xl leading-relaxed">
                  {{ category.short_description || 'Descubre todos los productos disponibles en esta categoría.' }}
                </p>
              </div>

              <!-- Category Stats -->
              <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-8 border border-white/10">
                <h3 class="text-xl font-bold text-white mb-6">Estadísticas</h3>
                <div class="grid grid-cols-2 gap-6">
                  <div class="bg-gradient-to-br from-blue-500/20 to-blue-600/20 backdrop-blur-sm p-6 rounded-2xl text-center border border-blue-500/30">
                    <div class="text-3xl font-black text-blue-400">{{ products.length }}</div>
                    <div class="text-sm text-blue-300 font-medium">Productos</div>
                  </div>
                  <div class="bg-gradient-to-br from-green-500/20 to-green-600/20 backdrop-blur-sm p-6 rounded-2xl text-center border border-green-500/30">
                    <div class="text-3xl font-black text-green-400">{{ category.store?.name ? 1 : 0 }}</div>
                    <div class="text-sm text-green-300 font-medium">Tienda</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Products Section -->
        <div class="bg-white/5 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/10 p-10">
          <div class="flex items-center justify-between mb-10">
            <div>
              <h2 class="text-4xl font-black text-white mb-3">Productos en {{ category.name }}</h2>
              <p class="text-gray-300 text-lg">Explora todos los productos disponibles en esta categoría</p>
            </div>
            <div class="text-right">
              <div class="text-3xl font-black text-orange-400">{{ products.length }}</div>
              <div class="text-sm text-gray-400 font-medium">productos disponibles</div>
            </div>
          </div>

          <!-- Products Grid -->
          <div v-if="products.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <div
              v-for="product in products"
              :key="product.id"
              class="group bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl overflow-hidden hover:shadow-2xl hover:shadow-orange-500/10 transition-all duration-500 transform hover:-translate-y-2"
            >
              <!-- Product Image -->
              <div class="relative h-56 bg-gradient-to-br from-gray-700 to-gray-800 overflow-hidden">
                <img
                  :src="product.image_url || '/images/placeholders/product.png'"
                  :alt="product.name"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                  @error="onImgError($event)"
                />
                <div class="absolute top-4 right-4">
                  <div class="w-10 h-10 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center border border-white/20">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Product Info -->
              <div class="p-6">
                <h3 class="font-bold text-white mb-3 line-clamp-2 group-hover:text-orange-400 transition-colors duration-300">
                  {{ product.name }}
                </h3>
                <p class="text-gray-400 text-sm mb-4 line-clamp-2">{{ product.description }}</p>

                <div class="flex items-center justify-between">
                  <div class="text-xl font-black text-orange-400">
                    {{ price(product.price) }}
                  </div>
                  <div class="text-xs text-gray-400">
                    Stock: {{ product.stock || 0 }}
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-3 mt-6">
                  <RouterLink
                    :to="`/products/${product.id}`"
                    class="flex-1 bg-white/10 backdrop-blur-sm text-white py-3 px-4 rounded-xl text-sm font-semibold hover:bg-white/20 transition-all duration-300 border border-white/20 text-center"
                  >
                    Ver detalle
                  </RouterLink>
                  <button
                    class="flex-1 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white py-3 px-4 rounded-xl text-sm font-bold hover:shadow-2xl hover:shadow-orange-500/25 transition-all duration-500 transform hover:scale-105"
                  >
                    Agregar
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty Products State -->
          <div v-else class="text-center py-16">
            <div class="relative mb-8">
              <div class="w-32 h-32 bg-gradient-to-br from-gray-700 to-gray-800 rounded-full flex items-center justify-center mx-auto mb-6 shadow-2xl">
                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-5.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
              </div>
              <!-- Floating elements -->
              <div class="absolute top-5 left-1/4 w-6 h-6 bg-orange-500/20 rounded-full blur-sm animate-pulse"></div>
              <div class="absolute bottom-5 right-1/4 w-4 h-4 bg-blue-500/20 rounded-full blur-sm animate-pulse delay-1000"></div>
            </div>
            <h3 class="text-2xl font-bold text-white mb-4">No hay productos disponibles</h3>
            <p class="text-gray-400">Esta categoría aún no tiene productos publicados</p>
          </div>
        </div>

        <!-- Call to Action -->
        <div class="bg-gradient-to-r from-orange-500 via-red-500 to-yellow-500 rounded-3xl p-10 text-white text-center shadow-2xl">
          <h3 class="text-4xl font-black mb-6">¿Buscas algo específico?</h3>
          <p class="text-2xl mb-8 opacity-90 leading-relaxed">Explora nuestro catálogo completo de productos para encontrar exactamente lo que necesitas</p>
          <div class="flex flex-col sm:flex-row gap-6 justify-center">
            <RouterLink
              class="inline-block bg-black/20 backdrop-blur-sm text-white px-10 py-4 rounded-2xl font-bold hover:bg-black/30 transition-all duration-300 border border-white/20 text-lg"
              to="/products"
            >
              <svg class="w-5 h-5 mr-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              Ver todos los productos
            </RouterLink>
            <RouterLink
              class="inline-block bg-white/10 backdrop-blur-sm text-white px-10 py-4 rounded-2xl font-bold hover:bg-white/20 transition-all duration-300 border border-white/20 text-lg"
              to="/stores"
            >
              <svg class="w-5 h-5 mr-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
              </svg>
              Explorar tiendas
            </RouterLink>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const category = ref(null)
const products = ref([])
const loading = ref(true)
const cartCount = ref(0)

const fetchCategory = async () => {
  loading.value = true
  try {
    const response = await fetch(`/api/public-categories/${route.params.category}`)
    if (!response.ok) throw new Error('Failed to fetch category')
    const data = await response.json()
    category.value = data.data || data
    products.value = data.products || []
  } catch (error) {
    console.error('Error fetching category:', error)
  } finally {
    loading.value = false
  }
}

const price = (val) => {
  if (val == null) return '—'
  try {
    return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(val)
  } catch {
    return `$${val}`
  }
}

const onImgError = (e) => {
  e.target.src = '/images/placeholders/product.png'
}

onMounted(() => {
  fetchCategory()
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
