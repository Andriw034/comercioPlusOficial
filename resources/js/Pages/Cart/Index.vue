<!-- resources/js/Pages/Cart/Index.vue -->
<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';

// Componentes UI
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';

const API = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000';

const cartItems = ref([]);
const loading = ref(true);

// --- Data Fetching ---
const fetchCart = async () => {
  loading.value = true;
  try {
    // Usar el endpoint de la API para obtener el carrito
    const response = await axios.get(`${API}/api/cart`);
    cartItems.value = response.data.data || response.data; 
  } catch (e) {
    console.error('Error cargando el carrito:', e);
    cartItems.value = [];
  } finally {
    loading.value = false;
  }
};

// --- Cart Actions ---
const updateQuantity = async (item, newQuantity) => {
  if (newQuantity < 1 || newQuantity > item.product.stock) return;
  try {
    await axios.put(`${API}/api/cart/${item.id}`, { quantity: newQuantity });
    item.quantity = newQuantity;
  } catch (e) {
    console.error('Error actualizando la cantidad:', e);
  }
};

const removeItem = async (item) => {
  if (!confirm(`¿Estás seguro de que quieres eliminar "${item.product.name}" del carrito?`)) return;
  try {
    await axios.delete(`${API}/api/cart/${item.id}`);
    cartItems.value = cartItems.value.filter(i => i.id !== item.id);
  } catch (e) {
    console.error('Error eliminando el producto:', e);
  }
};

// --- Computed Properties ---
const subtotal = computed(() => cartItems.value.reduce((sum, item) => sum + (item.product.price * item.quantity), 0));
const totalItems = computed(() => cartItems.value.reduce((sum, item) => sum + item.quantity, 0));
const shipping = computed(() => subtotal.value >= 100000 ? 0 : 15000); // Envío gratis sobre $100.000
const total = computed(() => subtotal.value + shipping.value);

// --- Helpers ---
const price = (val) => {
  if (val == null) return '—';
  return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(val);
};

const onImgError = (e) => {
  e.target.src = '/images/product_placeholder.jpg';
};

// --- Lifecycle ---
onMounted(() => {
  fetchCart();
});

</script>

<template>
  <div class="min-h-screen bg-cp-bg text-cp-text">
    <Header />

    <main class="container mx-auto px-6 py-12">
      <!-- Titulo -->
      <div class="mb-10">
        <h1 class="text-4xl font-semibold text-cp-text">Tu Carrito de Compras</h1>
        <p v-if="!loading" class="text-cp-sub mt-2">
          Tienes {{ totalItems }} {{ totalItems === 1 ? 'producto' : 'productos' }} en tu carrito.
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-comercioplus mx-auto"></div>
        <p class="mt-4 text-cp-sub">Cargando tu carrito...</p>
      </div>

      <!-- Empty Cart State -->
      <div v-else-if="cartItems.length === 0" class="text-center bg-cp-surface rounded-xl-20 shadow-cp-card p-12">
        <img src="/assets/icons/ic_cart.svg" class="h-16 w-16 mx-auto text-cp-sub opacity-50" alt="Carrito vacío"/>
        <h2 class="text-2xl font-semibold text-cp-text mt-6">Tu carrito está vacío</h2>
        <p class="text-cp-sub mt-2 mb-8">Parece que aún no has añadido productos. ¡Explora nuestras tiendas!</p>
        <Link
          href="/products"
          class="inline-block bg-comercioplus text-white px-8 py-3 rounded-lg-16 font-medium hover:bg-comercioplus-600 transition-transform transform hover:scale-105"
        >
          Explorar Productos
        </Link>
      </div>

      <!-- Cart Content -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- Cart Items -->
        <div class="lg:col-span-2 bg-cp-surface rounded-xl-20 shadow-cp-card overflow-hidden">
            <div class="divide-y divide-gray-200">
              <div
                v-for="item in cartItems"
                :key="item.id"
                class="p-6 hover:bg-gray-50/50 transition-colors duration-300"
              >
                <div class="flex items-center space-x-6">
                  <!-- Imagen -->
                  <Link :href="`/products/${item.product.id}`" class="flex-shrink-0">
                    <img
                      :src="item.product.image_url || '/images/product_placeholder.jpg'"
                      :alt="item.product.name"
                      class="w-24 h-24 object-cover rounded-lg-16 border border-gray-200"
                      @error="onImgError"
                    />
                  </Link>

                  <!-- Detalles -->
                  <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-cp-text truncate">{{ item.product.name }}</h3>
                    <p class="text-sm text-cp-sub mt-1">Vendido por: {{ item.product.store?.name || 'Tienda' }}</p>
                    <p class="text-lg font-bold text-comercioplus mt-2">{{ price(item.product.price) }}</p>
                  </div>

                  <!-- Controles -->
                  <div class="flex flex-col items-center space-y-2">
                    <div class="flex items-center border border-gray-200 rounded-lg p-1">
                      <button @click="updateQuantity(item, item.quantity - 1)" :disabled="item.quantity <= 1" class="w-8 h-8 rounded-md hover:bg-gray-100 disabled:opacity-50">-</button>
                      <span class="w-10 text-center font-medium">{{ item.quantity }}</span>
                      <button @click="updateQuantity(item, item.quantity + 1)" :disabled="item.quantity >= item.product.stock" class="w-8 h-8 rounded-md hover:bg-gray-100 disabled:opacity-50">+</button>
                    </div>
                    <button @click="removeItem(item)" class="text-xs text-red-500 hover:text-red-700">Eliminar</button>
                  </div>

                  <!-- Total Item -->
                  <div class="text-right">
                    <p class="text-lg font-semibold text-cp-text">{{ price(item.product.price * item.quantity) }}</p>
                  </div>
                </div>
              </div>
            </div>
        </div>

        <!-- Resumen del Pedido -->
        <div class="lg:col-span-1">
          <div class="sticky top-24">
            <div class="bg-cp-surface rounded-xl-20 shadow-cp-card p-6">
              <h3 class="text-xl font-semibold mb-6">Resumen del pedido</h3>
              <div class="space-y-4 mb-6 text-cp-sub">
                <div class="flex justify-between"><span>Subtotal</span> <span class="font-medium text-cp-text">{{ price(subtotal) }}</span></div>
                <div class="flex justify-between"><span>Envío</span> <span class="font-medium text-cp-text">{{ shipping === 0 ? 'Gratis' : price(shipping) }}</span></div>
              </div>
              <div v-if="shipping === 0" class="text-sm text-green-700 bg-green-100 p-3 rounded-lg mb-6">
                  ¡Felicidades! Tu envío es gratis.
              </div>
              <div class="border-t border-gray-200 pt-6 mb-6">
                <div class="flex justify-between items-center">
                  <span class="text-lg font-semibold">Total</span>
                  <span class="text-2xl font-bold text-comercioplus">{{ price(total) }}</span>
                </div>
              </div>
              <Link
                href="/checkout"
                class="w-full block text-center bg-comercioplus text-white py-3 rounded-lg-16 font-semibold hover:bg-comercioplus-600 transition-all transform hover:scale-105"
              >
                Proceder al Pago
              </Link>
              <div class="mt-6 text-center text-xs text-cp-sub">
                <p>🔒 Pago seguro garantizado</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <Footer />
  </div>
</template>
