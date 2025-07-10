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
              <span>Ver productos</span>
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

      <!-- Store Not Found -->
      <div v-else-if="!store" class="text-center py-20">
        <div class="relative mb-12">
          <div class="w-40 h-40 bg-gradient-to-br from-gray-700 to-gray-800 rounded-full flex items-center justify-center mx-auto mb-8 shadow-2xl">
            <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
          </div>
          <!-- Floating elements -->
          <div class="absolute top-10 left-1/4 w-8 h-8 bg-orange-500/20 rounded-full blur-sm animate-pulse"></div>
          <div class="absolute bottom-10 right-1/4 w-6 h-6 bg-blue-500/20 rounded-full blur-sm animate-pulse delay-1000"></div>
        </div>
        <h2 class="text-4xl font-black text-white mb-6">Tienda no encontrada</h2>
        <p class="text-xl text-gray-400 mb-10 max-w-lg mx-auto leading-relaxed">
          La tienda que buscas no existe o ha sido eliminada
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

      <!-- Store Content -->
      <div v-else class="space-y-10">
        <!-- Store Hero Section -->
        <div class="bg-white/5 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/10 overflow-hidden">
          <div class="relative h-80 bg-gradient-to-r from-orange-500 via-red-500 to-yellow-500">
            <div class="absolute inset-0 bg-black/30"></div>
            <div class="absolute bottom-0 left-0 right-0 p-10 text-white">
              <div class="flex items-center space-x-6 mb-6">
                <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/20">
                  <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                  </svg>
                </div>
                <div>
                  <h1 class="text-5xl font-black mb-3">{{ store.name }}</h1>
                  <div class="flex items-center space-x-6 text-sm">
                    <span class="flex items-center bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                      </svg>
                      {{ store.products?.length || 0 }} productos
                    </span>
                    <span class="flex items-center bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                      </svg>
                      Ubicación disponible
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Store Description -->
          <div class="p-10">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
              <div class="lg:col-span-2">
                <h2 class="text-3xl font-black text-white mb-6">Sobre la tienda</h2>
                <p class="text-gray-300 text-xl leading-relaxed">{{ store.description }}</p>

                <!-- Store Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-10">
                  <div class="bg-gradient-to-br from-blue-500/20 to-blue-600/20 backdrop-blur-sm p-6 rounded-2xl text-center border border-blue-500/30">
                    <div class="text-3xl font-black text-blue-400">{{ store.products?.length || 0 }}</div>
                    <div class="text-sm text-blue-300 font-medium">Productos</div>
                  </div>
                  <div class="bg-gradient-to-br from-green-500/20 to-green-600/20 backdrop-blur-sm p-6 rounded-2xl text-center border border-green-500/30">
                    <div class="text-3xl font-black text-green-400">4.8</div>
                    <div class="text-sm text-green-300 font-medium">Calificación</div>
                  </div>
                  <div class="bg-gradient-to-br from-purple-500/20 to-purple-600/20 backdrop-blur-sm p-6 rounded-2xl text-center border border-purple-500/30">
                    <div class="text-3xl font-black text-purple-400">98%</div>
                    <div class="text-sm text-purple-300 font-medium">Satisfacción</div>
                  </div>
                  <div class="bg-gradient-to-br from-orange-500/20 to-orange-600/20 backdrop-blur-sm p-6 rounded-2xl text-center border border-orange-500/30">
                    <div class="text-3xl font-black text-orange-400">24h</div>
                    <div class="text-sm text-orange-300 font-medium">Entrega</div>
                  </div>
                </div>
              </div>

              <!-- Contact Info -->
              <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-8 border border-white/10">
                <h3 class="text-xl font-bold text-white mb-6">Información de contacto</h3>
                <div class="space-y-4">
                  <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-orange-500/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-orange-500/30">
                      <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                      </svg>
                    </div>
                    <span class="text-gray-300">+57 300 123 4567</span>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-orange-500/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-orange-500/30">
                      <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                      </svg>
                    </div>
                    <span class="text-gray-300">contacto@tienda.com</span>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-orange-500/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-orange-500/30">
                      <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                      </svg>
                    </div>
                    <span class="text-gray-300">Calle 123 #45-67</span>
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
              <h2 class="text-4xl font-black text-white mb-3">Productos de {{ store.name }}</h2>
              <p class="text-gray-300 text-lg">Descubre todos nuestros repuestos y accesorios para motocicleta</p>
            </div>
            <div class="text-right">
              <div class="text-3xl font-black text-orange-400">{{ store.products?.length || 0 }}</div>
              <div class="text-sm text-gray-400 font-medium">productos disponibles</div>
            </div>
          </div>

          <!-- Products Grid -->
          <div v-if="store.products && store.products.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <div
              v-for="product in store.products"
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
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
              </div>
              <!-- Floating elements -->
              <div class="absolute top-5 left-1/4 w-6 h-6 bg-orange-500/20 rounded-full blur-sm animate-pulse"></div>
              <div class="absolute bottom-5 right-1/4 w-4 h-4 bg-blue-500/20 rounded-full blur-sm animate-pulse delay-1000"></div>
            </div>
            <h3 class="text-2xl font-bold text-white mb-4">No hay productos disponibles</h3>
            <p class="text-gray-400">Esta tienda aún no tiene productos publicados</p>
          </div>
        </div>

        <!-- Call to Action -->
        <div class="bg-gradient-to-r from-orange-500 via-red-500 to-yellow-500 rounded-3xl p-10 text-white text-center shadow-2xl">
          <h3 class="text-4xl font-black mb-6">¿Necesitas ayuda?</h3>
          <p class="text-2xl mb-8 opacity-90 leading-relaxed">Nuestro equipo está listo para ayudarte con cualquier consulta sobre nuestros productos</p>
          <div class="flex flex-col sm:flex-row gap-6 justify-center">
            <button class="bg-white/10 backdrop-blur-sm text-white px-10 py-4 rounded-2xl font-bold hover:bg-white/20 transition-all duration-300 border border-white/20 text-lg">
              <svg class="w-5 h-5 mr-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
              </svg>
              Contactar tienda
            </button>
            <RouterLink
              to="/products"
              class="bg-black/20 backdrop-blur-sm border-2 border-white text-white px-10 py-4 rounded-2xl font-bold hover:bg-black/30 transition-all duration-300 text-lg"
            >
              <svg class="w-5 h-5 mr-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              Ver más productos
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
const store = ref(null)
const loading = ref(true)

const fetchStore = async () => {
  loading.value = true
  try {
    const response = await fetch(`/api/public-stores/${route.params.id}`)
    if (!response.ok) throw new Error('Failed to fetch store')
    const data = await response.json()
    store.value = data.data || data
  } catch (error) {
    console.error('Error fetching store:', error)
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
  fetchStore()
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
