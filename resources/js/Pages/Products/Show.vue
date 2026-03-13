<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-5">
      <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
        <defs>
          <pattern id="product-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
            <circle cx="10" cy="10" r="1" fill="white"/>
            <path d="M5 5 L15 15 M15 5 L5 15" stroke="white" stroke-width="0.5"/>
          </pattern>
        </defs>
        <rect width="100%" height="100%" fill="url(#product-pattern)"/>
      </svg>
    </div>

    <!-- Floating Elements -->
    <div class="absolute top-20 left-20 w-20 h-20 bg-orange-500/10 rounded-full blur-xl animate-pulse"></div>
    <div class="absolute bottom-20 right-20 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl animate-pulse delay-1000"></div>
    <div class="absolute top-1/2 left-10 w-16 h-16 bg-yellow-500/10 rounded-full blur-lg animate-pulse delay-500"></div>

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
              class="flex items-center space-x-2 bg-gradient-to-r from-orange-500 to-red-600 text-white px-4 py-2 rounded-xl font-medium hover:shadow-2xl hover:shadow-orange-500/25 transition-all duration-500 transform hover:scale-105"
              to="/cart"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500"></div>
      </div>

      <!-- Product Not Found -->
      <div v-else-if="!product" class="text-center py-20">
        <div class="relative mb-12">
          <div class="w-32 h-32 bg-gradient-to-br from-gray-700 to-gray-800 rounded-full flex items-center justify-center mx-auto mb-8 shadow-2xl">
            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
          </div>
          <!-- Floating elements -->
          <div class="absolute top-10 left-1/4 w-8 h-8 bg-orange-500/20 rounded-full blur-sm animate-pulse"></div>
          <div class="absolute bottom-10 right-1/4 w-6 h-6 bg-blue-500/20 rounded-full blur-sm animate-pulse delay-1000"></div>
        </div>
        <h2 class="text-4xl font-black text-white mb-6">Producto no encontrado</h2>
        <p class="text-xl text-gray-400 mb-10 max-w-md mx-auto leading-relaxed">
          El producto que buscas no existe o ha sido eliminado
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

      <!-- Product Detail -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">
        <!-- Product Gallery -->
        <div class="space-y-4">
          <div class="relative aspect-square bg-gradient-to-br from-gray-700 to-gray-800 rounded-2xl overflow-hidden shadow-2xl border border-white/10">
            <img
              :src="product.image_url || '/images/placeholders/product.png'"
              :alt="product.name"
              class="w-full h-full object-cover"
              @error="onImgError($event)"
            />
            <div v-if="product.featured" class="absolute top-4 right-4">
              <div class="bg-gradient-to-r from-orange-500 to-red-600 text-white px-4 py-2 rounded-2xl text-sm font-bold shadow-xl border border-orange-400/30">
                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                Destacado
              </div>
            </div>
            <div class="absolute top-4 left-4">
              <div class="bg-black/60 backdrop-blur-xl text-white px-4 py-2 rounded-2xl text-sm font-bold border border-white/20 shadow-xl">
                {{ product.category?.name || 'Sin categoría' }}
              </div>
            </div>
            <!-- Floating decorative elements -->
            <div class="absolute -top-2 -right-2 w-8 h-8 bg-orange-500/20 rounded-full blur-sm animate-pulse"></div>
            <div class="absolute -bottom-2 -left-2 w-6 h-6 bg-blue-500/20 rounded-full blur-sm animate-pulse delay-1000"></div>
          </div>
        </div>

        <!-- Product Info -->
        <div class="space-y-8">
          <!-- Product Header -->
          <div class="relative">
            <div class="flex items-center space-x-2 mb-4">
              <div class="w-10 h-10 bg-gradient-to-br from-orange-500/20 to-red-500/20 rounded-xl flex items-center justify-center border border-orange-400/30">
                <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
              </div>
              <span class="text-sm text-gray-300 font-medium">{{ product.store?.name || 'Tienda' }}</span>
            </div>

            <h1 class="text-4xl md:text-6xl font-black text-white mb-6 leading-tight">
              {{ product.name }}
            </h1>

            <div class="flex items-center space-x-6 mb-8">
              <div class="text-5xl font-black text-transparent bg-gradient-to-r from-orange-400 to-red-500 bg-clip-text">
                {{ price(product.price) }}
              </div>
              <div class="flex items-center space-x-3">
                <div class="w-3 h-3 rounded-full shadow-lg" :class="product.stock > 0 ? 'bg-green-400 shadow-green-400/50' : 'bg-red-400 shadow-red-400/50'"></div>
                <span class="text-sm font-semibold" :class="product.stock > 0 ? 'text-green-400' : 'text-red-400'">
                  {{ product.stock > 0 ? `Stock: ${product.stock}` : 'Agotado' }}
                </span>
              </div>
            </div>

            <!-- Decorative elements -->
            <div class="absolute -top-4 -right-4 w-16 h-16 bg-orange-500/10 rounded-full blur-xl animate-pulse"></div>
            <div class="absolute -bottom-4 -left-4 w-12 h-12 bg-blue-500/10 rounded-full blur-lg animate-pulse delay-1000"></div>
          </div>

          <!-- Product Description -->
          <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur-xl rounded-2xl p-8 shadow-2xl border border-white/10">
            <div class="flex items-center space-x-3 mb-6">
              <div class="w-12 h-12 bg-gradient-to-br from-orange-500/20 to-red-500/20 rounded-xl flex items-center justify-center border border-orange-400/30">
                <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
              <h3 class="text-2xl font-black text-white">Descripción del producto</h3>
            </div>
            <p class="text-gray-300 text-lg leading-relaxed">
              {{ product.description || 'Sin descripción disponible.' }}
            </p>
            <!-- Decorative elements -->
            <div class="absolute top-4 right-4 w-20 h-20 bg-orange-500/5 rounded-full blur-xl"></div>
            <div class="absolute bottom-4 left-4 w-16 h-16 bg-blue-500/5 rounded-full blur-lg"></div>
          </div>

          <!-- Product Actions -->
          <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur-xl rounded-2xl p-8 shadow-2xl border border-white/10">
            <div class="flex items-center justify-between mb-8">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-orange-500/20 to-red-500/20 rounded-xl flex items-center justify-center border border-orange-400/30">
                  <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h16"/>
                  </svg>
                </div>
                <span class="text-lg font-bold text-white">Cantidad</span>
              </div>
              <div class="flex items-center bg-black/30 backdrop-blur-sm rounded-2xl p-2 border border-white/10">
                <button
                  @click="decreaseQuantity"
                  :disabled="quantity <= 1"
                  class="w-12 h-12 bg-gradient-to-br from-gray-700 to-gray-800 rounded-xl flex items-center justify-center text-gray-300 hover:text-orange-400 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-lg hover:shadow-orange-500/20"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                  </svg>
                </button>
                <span class="w-16 text-center font-black text-white text-xl">{{ quantity }}</span>
                <button
                  @click="increaseQuantity"
                  :disabled="quantity >= product.stock"
                  class="w-12 h-12 bg-gradient-to-br from-gray-700 to-gray-800 rounded-xl flex items-center justify-center text-gray-300 hover:text-orange-400 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-lg hover:shadow-orange-500/20"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                </button>
              </div>
            </div>

            <button
              class="w-full bg-gradient-to-r from-orange-500 to-red-600 text-white py-5 px-8 rounded-2xl font-black hover:shadow-2xl hover:shadow-orange-500/25 transition-all duration-500 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center text-lg border border-orange-400/30"
              @click="addToCart"
              :disabled="product.stock <= 0"
            >
              <svg v-if="product.stock > 0" class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13H5.4"/>
              </svg>
              <span>{{ product.stock > 0 ? 'Agregar al carrito' : 'Producto agotado' }}</span>
            </button>

            <!-- Decorative elements -->
            <div class="absolute top-4 right-4 w-16 h-16 bg-orange-500/5 rounded-full blur-xl"></div>
            <div class="absolute bottom-4 left-4 w-12 h-12 bg-blue-500/5 rounded-full blur-lg"></div>
          </div>

          <!-- Store Info -->
          <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur-xl rounded-2xl p-8 shadow-2xl border border-white/10">
            <div class="flex items-center space-x-3 mb-6">
              <div class="w-12 h-12 bg-gradient-to-br from-orange-500/20 to-red-500/20 rounded-xl flex items-center justify-center border border-orange-400/30">
                <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
              </div>
              <h3 class="text-2xl font-black text-white">Información de la tienda</h3>
            </div>
            <div class="flex items-center space-x-6">
              <div class="w-20 h-20 bg-gradient-to-br from-orange-500/20 to-red-500/20 rounded-2xl flex items-center justify-center border border-orange-400/30">
                <svg class="w-10 h-10 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
              </div>
              <div class="flex-1">
                <h4 class="text-xl font-black text-white mb-2">{{ product.store?.name || 'Tienda' }}</h4>
                <p class="text-gray-300 text-lg leading-relaxed">{{ product.store?.description || 'Tienda especializada en repuestos.' }}</p>
              </div>
              <RouterLink
                :to="`/stores/${product.store?.id}`"
                class="bg-gradient-to-r from-orange-500 to-red-600 text-white px-6 py-3 rounded-2xl font-bold hover:shadow-2xl hover:shadow-orange-500/25 transition-all duration-500 transform hover:scale-105 border border-orange-400/30"
              >
                <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Ver tienda
              </RouterLink>
            </div>

            <!-- Decorative elements -->
            <div class="absolute top-4 right-4 w-20 h-20 bg-orange-500/5 rounded-full blur-xl"></div>
            <div class="absolute bottom-4 left-4 w-16 h-16 bg-blue-500/5 rounded-full blur-lg"></div>
          </div>
        </div>
      </div>

      <!-- Related Products -->
      <div v-if="relatedProducts.length > 0" class="space-y-12">
        <div class="text-center relative">
          <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-32 h-32 bg-orange-500/5 rounded-full blur-3xl"></div>
          </div>
          <h2 class="text-5xl font-black text-white mb-6 relative z-10">
            Productos
            <span class="bg-gradient-to-r from-orange-400 to-red-500 bg-clip-text text-transparent">
              Relacionados
            </span>
          </h2>
          <p class="text-xl text-gray-300 max-w-2xl mx-auto leading-relaxed">
            Descubre otros productos que podrían interesarte
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div
            v-for="related in relatedProducts"
            :key="related.id"
            class="group bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur-xl rounded-2xl overflow-hidden shadow-2xl border border-white/10 hover:shadow-orange-500/10 hover:border-orange-400/30 transition-all duration-500 transform hover:scale-105"
          >
            <!-- Product Image -->
            <div class="relative h-56 bg-gradient-to-br from-gray-700 to-gray-800 overflow-hidden">
              <img
                :src="related.image_url || '/images/placeholders/product.png'"
                :alt="related.name"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                @error="onImgError($event)"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              <div class="absolute top-4 right-4">
                <div class="w-8 h-8 bg-gradient-to-br from-orange-500/20 to-red-500/20 rounded-xl flex items-center justify-center border border-orange-400/30">
                  <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Product Info -->
            <div class="p-6">
              <h3 class="font-black text-white mb-3 line-clamp-2 group-hover:text-orange-400 transition-colors duration-300 text-lg">
                {{ related.name }}
              </h3>
              <p class="text-gray-300 text-sm mb-4 line-clamp-2 leading-relaxed">{{ related.description }}</p>

              <div class="flex items-center justify-between mb-6">
                <div class="text-2xl font-black text-transparent bg-gradient-to-r from-orange-400 to-red-500 bg-clip-text">
                  {{ price(related.price) }}
                </div>
                <div class="flex items-center space-x-2">
                  <div class="w-2 h-2 rounded-full" :class="related.stock > 0 ? 'bg-green-400' : 'bg-red-400'"></div>
                  <span class="text-xs font-semibold" :class="related.stock > 0 ? 'text-green-400' : 'text-red-400'">
                    {{ related.stock > 0 ? `${related.stock}` : 'Agotado' }}
                  </span>
                </div>
              </div>

              <!-- Action Button -->
              <RouterLink
                :to="`/products/${related.id}`"
                class="w-full bg-gradient-to-r from-orange-500 to-red-600 text-white py-3 px-6 rounded-2xl font-bold hover:shadow-2xl hover:shadow-orange-500/25 transition-all duration-500 transform hover:scale-105 border border-orange-400/30 flex items-center justify-center text-sm"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Ver producto
              </RouterLink>
            </div>

            <!-- Decorative elements -->
            <div class="absolute top-4 left-4 w-12 h-12 bg-orange-500/5 rounded-full blur-xl animate-pulse"></div>
            <div class="absolute bottom-4 right-4 w-8 h-8 bg-blue-500/5 rounded-full blur-lg animate-pulse delay-1000"></div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const API = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000'

const product = ref(null)
const relatedProducts = ref([])
const loading = ref(true)
const cartCount = ref(0)
const quantity = ref(1)

const fetchProduct = async () => {
  loading.value = true
  try {
    const { data } = await axios.get(`${API}/api/products/${route.params.id}`)
    product.value = data.data || data
    fetchRelatedProducts()
  } catch (e) {
    console.error('Error cargando producto', e)
  } finally {
    loading.value = false
  }
}

const fetchRelatedProducts = async () => {
  if (!product.value?.category_id) return

  try {
    const { data } = await axios.get(`${API}/api/products`, {
      params: {
        category_id: product.value.category_id,
        limit: 4
      }
    })
    relatedProducts.value = (data.data || data).filter(p => p.id !== product.value.id).slice(0, 4)
  } catch (e) {
    console.error('Error cargando productos relacionados', e)
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

const increaseQuantity = () => {
  if (quantity.value < product.value.stock) {
    quantity.value++
  }
}

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const addToCart = async () => {
  try {
    await axios.post(`${API}/api/cart`, {
      product_id: product.value.id,
      quantity: quantity.value
    })
    fetchCartCount()
    // Mostrar notificación de éxito
    alert(`¡${quantity.value} ${product.value.name} agregado(s) al carrito!`)
  } catch (e) {
    console.error('Error agregando al carrito', e)
    alert('Error al agregar el producto al carrito')
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
  fetchProduct()
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
</style>
