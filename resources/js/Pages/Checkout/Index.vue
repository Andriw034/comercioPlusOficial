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
              to="/cart"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
              <span>Volver al carrito</span>
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Finalizar Compra
        </div>
        <h1 class="text-5xl md:text-7xl font-black text-white mb-6">
          Completa tu
          <span class="bg-gradient-to-r from-orange-400 via-red-500 to-yellow-500 bg-clip-text text-transparent">
            Pedido
          </span>
        </h1>
        <p class="text-2xl text-gray-300 max-w-3xl mx-auto leading-relaxed">
          Solo un paso más para recibir tus repuestos de motocicleta
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-orange-500"></div>
      </div>

      <!-- Empty Checkout State -->
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
        <h2 class="text-4xl font-black text-white mb-6">No hay productos en tu carrito</h2>
        <p class="text-xl text-gray-400 mb-10 max-w-lg mx-auto leading-relaxed">
          Agrega algunos productos antes de proceder al pago
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

      <!-- Checkout Form -->
      <form v-else @submit.prevent="submitOrder" class="space-y-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
          <!-- Shipping Information -->
          <div class="lg:col-span-2 space-y-10">
            <!-- Shipping Info -->
            <div class="bg-white/5 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/10 p-10">
              <div class="flex items-center space-x-4 mb-8">
                <div class="w-12 h-12 bg-orange-500/20 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-orange-500/30">
                  <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                </div>
                <h2 class="text-3xl font-black text-white">Información de envío</h2>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="md:col-span-2">
                  <label class="block text-sm font-semibold text-gray-300 mb-3">Nombre completo *</label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full px-6 py-4 bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-300"
                    placeholder="Tu nombre completo"
                  />
                </div>

                <div>
                  <label class="block text-sm font-semibold text-gray-300 mb-3">Correo electrónico *</label>
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    class="w-full px-6 py-4 bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-300"
                    placeholder="tu@email.com"
                  />
                </div>

                <div>
                  <label class="block text-sm font-semibold text-gray-300 mb-3">Teléfono *</label>
                  <input
                    v-model="form.phone"
                    type="tel"
                    required
                    class="w-full px-6 py-4 bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-300"
                    placeholder="+57 300 123 4567"
                  />
                </div>

                <div class="md:col-span-2">
                  <label class="block text-sm font-semibold text-gray-300 mb-3">Dirección de envío *</label>
                  <textarea
                    v-model="form.address"
                    required
                    rows="4"
                    class="w-full px-6 py-4 bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-300 resize-none"
                    placeholder="Dirección completa de envío"
                  ></textarea>
                </div>

                <div>
                  <label class="block text-sm font-semibold text-gray-300 mb-3">Ciudad *</label>
                  <input
                    v-model="form.city"
                    type="text"
                    required
                    class="w-full px-6 py-4 bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-300"
                    placeholder="Ciudad"
                  />
                </div>

                <div>
                  <label class="block text-sm font-semibold text-gray-300 mb-3">Notas adicionales (opcional)</label>
                  <textarea
                    v-model="form.notes"
                    rows="3"
                    class="w-full px-6 py-4 bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-300 resize-none"
                    placeholder="Instrucciones especiales de entrega"
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- Payment Methods -->
            <div class="bg-white/5 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/10 p-10">
              <div class="flex items-center space-x-4 mb-8">
                <div class="w-12 h-12 bg-orange-500/20 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-orange-500/30">
                  <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                  </svg>
                </div>
                <h2 class="text-3xl font-black text-white">Método de pago</h2>
              </div>

              <div class="space-y-6">
                <div
                  v-for="method in paymentMethods"
                  :key="method.id"
                  class="relative p-6 border-2 border-white/20 rounded-2xl cursor-pointer transition-all duration-300 hover:border-orange-400/50 hover:bg-white/5 backdrop-blur-sm"
                  :class="{ 'border-orange-500 bg-orange-500/10 backdrop-blur-sm': form.payment_method === method.id }"
                  @click="form.payment_method = method.id"
                >
                  <div class="flex items-center space-x-6">
                    <div class="text-4xl">{{ method.icon }}</div>
                    <div class="flex-1">
                      <h3 class="font-bold text-white text-lg">{{ method.name }}</h3>
                      <p class="text-gray-400 text-sm">{{ method.description }}</p>
                    </div>
                    <div class="w-6 h-6 border-2 border-white/30 rounded-full flex items-center justify-center">
                      <div
                        v-if="form.payment_method === method.id"
                        class="w-4 h-4 bg-orange-500 rounded-full"
                      ></div>
                    </div>
                  </div>
                  <input
                    type="radio"
                    :value="method.id"
                    v-model="form.payment_method"
                    required
                    class="sr-only"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Order Summary -->
          <div class="lg:col-span-1">
            <div class="sticky top-24">
              <div class="bg-white/5 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/10 p-8">
                <h3 class="text-2xl font-black text-white mb-8">Resumen del pedido</h3>

                <!-- Order Items -->
                <div class="space-y-6 mb-8">
                  <div
                    v-for="item in cartItems"
                    :key="item.id"
                    class="flex items-center space-x-4 pb-6 border-b border-white/10 last:border-b-0 last:pb-0"
                  >
                    <div class="w-16 h-16 bg-gradient-to-br from-gray-700 to-gray-800 rounded-2xl overflow-hidden flex-shrink-0 shadow-lg">
                      <img
                        :src="item.product.image_url || '/images/placeholders/product.png'"
                        :alt="item.product.name"
                        class="w-full h-full object-cover"
                        @error="onImgError($event)"
                      />
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-bold text-white truncate">{{ item.product.name }}</h4>
                      <p class="text-xs text-gray-400">{{ item.product.store?.name || 'Tienda' }}</p>
                      <p class="text-xs text-gray-400">Cant: {{ item.quantity }}</p>
                    </div>
                    <div class="text-sm font-bold text-orange-400">
                      {{ price(item.product.price * item.quantity) }}
                    </div>
                  </div>
                </div>

                <!-- Order Totals -->
                <div class="border-t border-white/20 pt-6 space-y-4">
                  <div class="flex justify-between items-center">
                    <span class="text-gray-300">Subtotal</span>
                    <span class="font-semibold text-white">{{ price(subtotal) }}</span>
                  </div>

                  <div class="flex justify-between items-center">
                    <span class="text-gray-300">Envío</span>
                    <span class="font-semibold" :class="shipping === 0 ? 'text-green-400' : 'text-white'">
                      {{ shipping === 0 ? 'Gratis' : price(shipping) }}
                    </span>
                  </div>

                  <div v-if="shipping === 0" class="text-sm text-green-400 bg-green-500/20 backdrop-blur-sm px-4 py-3 rounded-2xl border border-green-500/30">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                    ¡Envío gratis en compras superiores a $100.000!
                  </div>

                  <div class="border-t border-white/20 pt-4">
                    <div class="flex justify-between items-center">
                      <span class="text-xl font-bold text-white">Total</span>
                      <span class="text-3xl font-black text-orange-400">{{ price(total) }}</span>
                    </div>
                  </div>
                </div>

                <!-- Place Order Button -->
                <button
                  type="submit"
                  class="w-full mt-8 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white py-5 px-8 rounded-2xl font-bold hover:shadow-2xl hover:shadow-orange-500/25 transition-all duration-500 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center text-lg"
                  :disabled="submitting"
                >
                  <svg v-if="submitting" class="animate-spin -ml-1 mr-3 h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>{{ submitting ? 'Procesando pedido...' : 'Realizar pedido' }}</span>
                </button>

                <!-- Terms -->
                <p class="text-sm text-gray-400 text-center mt-6">
                  Al realizar el pedido aceptas nuestros
                  <a href="/terms" target="_blank" class="text-orange-400 hover:text-orange-300 underline transition-colors">
                    términos y condiciones
                  </a>
                </p>

                <!-- Security Badges -->
                <div class="mt-8 pt-6 border-t border-white/20">
                  <div class="flex items-center justify-center space-x-8 text-sm text-gray-400">
                    <div class="flex items-center bg-white/5 px-4 py-2 rounded-full">
                      <svg class="w-4 h-4 mr-2 text-green-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                      </svg>
                      Pago Seguro
                    </div>
                    <div class="flex items-center bg-white/5 px-4 py-2 rounded-full">
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
      </form>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const API = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000'

const cartItems = ref([])
const loading = ref(true)
const submitting = ref(false)

const form = ref({
  name: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  notes: '',
  payment_method: ''
})

const paymentMethods = ref([
  {
    id: 'card',
    name: 'Tarjeta de crédito/débito',
    description: 'Pago seguro con tarjeta',
    icon: '💳'
  },
  {
    id: 'transfer',
    name: 'Transferencia bancaria',
    description: 'Pago por transferencia',
    icon: '🏦'
  },
  {
    id: 'cash',
    name: 'Pago contra entrega',
    description: 'Paga cuando recibas tu pedido',
    icon: '💵'
  }
])

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

const submitOrder = async () => {
  if (submitting.value) return

  submitting.value = true
  try {
    const orderData = {
      ...form.value,
      items: cartItems.value.map(item => ({
        product_id: item.product.id,
        quantity: item.quantity,
        price: item.product.price
      })),
      total: total.value,
      shipping: shipping.value
    }

    const { data } = await axios.post(`${API}/api/orders`, orderData)

    // Limpiar carrito y redirigir
    await axios.delete(`${API}/api/cart/clear`)

    router.push(`/orders/${data.order.id}?success=true`)
  } catch (e) {
    console.error('Error creando pedido', e)
    alert('Error al procesar el pedido. Por favor intenta nuevamente.')
  } finally {
    submitting.value = false
  }
}

const subtotal = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + (item.product.price * item.quantity), 0)
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
.delay-1000 {
  animation-delay: 1s;
}
</style>
