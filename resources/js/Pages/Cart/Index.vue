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
              <span>Continuar comprando</span>
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13H5.4"/>
          </svg>
          Carrito de Compras
        </div>
        <h1 class="text-5xl md:text-7xl font-black text-white mb-6">
          Tu
          <span class="bg-gradient-to-r from-orange-400 via-red-500 to-yellow-500 bg-clip-text text-transparent">
            Carrito
          </span>
        </h1>
        <p class="text-2xl text-gray-300 max-w-3xl mx-auto leading-relaxed">
          {{ cartItems.length }} {{ cartItems.length === 1 ? 'producto' : 'productos' }} listo{{ cartItems.length === 1 ? '' : 's' }} para tu motocicleta
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-orange-500"></div>
      </div>

      <!-- Empty Cart State -->
      <div v-else-if="cartItems.length === 0" class="text-center py-20">
        <div class="relative mb-12">
          <div class="w-40 h-40 bg-gradient-to-br from-gray-700 to-gray-800 rounded-full flex items-center justify-center mx-auto mb-8 shadow-2xl">
            <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13H5.4"/>
            </svg>
          </div>
          <!-- Floating elements -->
          <div class="absolute top-10 left-1/4 w-8 h-8 bg-orange-500/20 rounded-full blur-sm animate-pulse"></div>
          <div class="absolute bottom-10 right-1/4 w-6 h-6 bg-blue-500/20 rounded-full blur-sm animate-pulse delay-1000"></div>
        </div>
        <h2 class="text-4xl font-black text-white mb-6">Tu carrito está vacío</h2>
        <p class="text-xl text-gray-400 mb-10 max-w-lg mx-auto leading-relaxed">
          ¡Es hora de encontrar los mejores repuestos para tu motocicleta!
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

      <!-- Cart Content -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Cart Items -->
        <div class="lg:col-span-2 space-y-8">
          <div class="bg-white/5 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/10 overflow-hidden">
            <div class="p-8 border-b border-white/10">
              <h2 class="text-3xl font-black text-white">Productos en tu carrito</h2>
            </div>

            <div class="divide-y divide-white/10">
              <div
                v-for="item in cartItems"
                :key="item.id"
                class="p-8 hover:bg-white/5 transition-all duration-300"
              >
                <div class="flex items-center space-x-8">
                  <!-- Product Image -->
                  <div class="relative w-28 h-28 bg-gradient-to-br from-gray-700 to-gray-800 rounded-2xl overflow-hidden flex-shrink-0 shadow-lg">
                    <img
                      :src="item.product.image_url || '/images/placeholders/product.png'"
                      :alt="item.product.name"
                      class="w-full h-full object-cover"
                      @error="onImgError($event)"
                    />
                    <div v-if="item.product.featured" class="absolute top-2 right-2">
                      <div class="bg-gradient-to-r from-orange-500 to-red-600 text-white px-2 py-1 rounded-full text-xs font-medium">
                        Destacado
                      </div>
                    </div>
                  </div>

                  <!-- Product Details -->
                  <div class="flex-1 min-w-0">
                    <h3 class="text-xl font-bold text-white mb-2 truncate">{{ item.product.name }}</h3>
                    <p class="text-gray-400 text-sm mb-3 line-clamp-2">{{ item.product.description }}</p>
                    <div class="flex items-center justify-between">
                      <span class="text-sm text-gray-500 bg-white/10 px-3 py-1 rounded-full">{{ item.product.store?.name || 'Tienda' }}</span>
                      <span class="text-xl font-bold text-orange-400">{{ price(item.product.price) }}</span>
                    </div>
                  </div>

                  <!-- Quantity Controls -->
                  <div class="flex flex-col items-center space-y-4">
                    <div class="flex items-center bg-white/10 backdrop-blur-sm rounded-2xl p-2 border border-white/20">
                      <button
                        @click="updateQuantity(item, item.quantity - 1)"
                        :disabled="item.quantity <= 1"
                        class="w-10 h-10 bg-white/20 hover:bg-white/30 rounded-xl flex items-center justify-center text-white hover:text-orange-400 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                        </svg>
                      </button>
                      <span class="w-14 text-center font-bold text-white text-lg">{{ item.quantity }}</span>
                      <button
                        @click="updateQuantity(item, item.quantity + 1)"
                        :disabled="item.quantity >= item.product.stock"
                        class="w-10 h-10 bg-white/20 hover:bg-white/30 rounded-xl flex items-center justify-center text-white hover:text-orange-400 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                      </button>
                    </div>
                    <span class="text-xs text-gray-500 bg-white/10 px-2 py-1 rounded-full">{{ item.product.stock }} disponibles</span>
                  </div>

                  <!-- Item Total & Actions -->
                  <div class="text-right space-y-3">
                    <div class="text-2xl font-black text-white">{{ price(item.product.price * item.quantity) }}</div>
                    <button
                      class="text-red-400 hover:text-red-300 text-sm font-medium transition-colors flex items-center justify-end"
                      @click="removeItem(item)"
                    >
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                      Eliminar
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
          <div class="sticky top-24">
            <div class="bg-white/5 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/10 p-8">
              <h3 class="text-2xl font-black text-white mb-8">Resumen del pedido</h3>

              <div class="space-y-6 mb-8">
                <div class="flex justify-between items-center">
                  <span class="text-gray-300">Subtotal ({{ totalItems }} productos)</span>
                  <span class="font-bold text-white text-lg">{{ price(subtotal) }}</span>
                </div>

                <div class="flex justify-between items-center">
                  <span class="text-gray-300">Envío</span>
                  <span class="font-bold" :class="shipping === 0 ? 'text-green-400' : 'text-white'">
                    {{ shipping === 0 ? 'Gratis' : price(shipping) }}
                  </span>
                </div>

                <div v-if="shipping === 0" class="text-sm text-green-400 bg-green-500/20 backdrop-blur-sm px-4 py-3 rounded-2xl border border-green-500/30">
                  <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                  </svg>
                  ¡Envío gratis en compras superiores a $100.000!
                </div>
              </div>

              <div class="border-t border-white/20 pt-6 mb-8">
                <div class="flex justify-between items-center">
                  <span class="text-xl font-bold text-white">Total</span>
                  <span class="text-3xl font-black text-orange-400">{{ price(total) }}</span>
                </div>
              </div>

              <div class="space-y-4">
                <RouterLink
                  class="w-full bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white py-5 px-8 rounded-2xl font-bold hover:shadow-2xl hover:shadow-orange-500/25 transition-all duration-500 transform hover:scale-105 flex items-center justify-center text-lg"
                  to="/checkout"
                >
                  <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Proceder al Pago
                </RouterLink>

                <RouterLink
                  class="w-full bg-white/10 backdrop-blur-sm text-white py-4 px-8 rounded-2xl font-semibold hover:bg-white/20 transition-all duration-300 border border-white/20 flex items-center justify-center"
                  to="/products"
                >
                  <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                  </svg>
                  Continuar Comprando
                </RouterLink>
              </div>

              <!-- Security Badges -->
              <div class="mt-8 pt-6 border-t border-white/20">
                <div class="flex items-center justify-center space-x-6 text-sm text-gray-400">
                  <div class="flex items-center bg-white/5 px-3 py-2 rounded-full">
                    <svg class="w-4 h-4 mr-2 text-green-400" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    Pago Seguro
                  </div>
                  <div class="flex items-center bg-white/5 px-3 py-2 rounded-full">
                    <svg class="w-4 h-4 mr-2 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    Envío Rápido
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const API = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000'

const cartItems = ref([])
const loading = ref(true)

const fetchCart = async () => {
  loading.value = true
  try {
    const { data } = await axios.get(`${API}/api/cart`)
    cartItems.value = data.data || data
  } catch (e) {
    console.error('Error cargando carrito', e)
    cartItems.value = []
  } finally {
    loading.value = false
  }
}

const updateQuantity = async (item, newQuantity) => {
  if (newQuantity < 1 || newQuantity > item.product.stock) return

  try {
    await axios.put(`${API}/api/cart/${item.id}`, {
      quantity: newQuantity
    })
    item.quantity = newQuantity
  } catch (e) {
    console.error('Error actualizando cantidad', e)
  }
}

const removeItem = async (item) => {
  if (!confirm(`¿Eliminar "${item.product.name}" del carrito?`)) return

  try {
    await axios.delete(`${API}/api/cart/${item.id}`)
    cartItems.value = cartItems.value.filter(i => i.id !== item.id)
  } catch (e) {
    console.error('Error eliminando item', e)
  }
}

const subtotal = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + (item.product.price * item.quantity), 0)
})

const totalItems = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + item.quantity, 0)
})

const shipping = computed(() => {
  return subtotal.value >= 100000 ? 0 : 10000 // Envío gratis sobre $100.000
})

const total = computed(() => {
  return subtotal.value + shipping.value
})

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
  fetchCart()
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
