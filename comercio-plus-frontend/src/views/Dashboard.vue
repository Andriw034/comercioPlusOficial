<template>
  <div class="space-y-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <p class="text-sm text-muted">Hola, {{ user?.name || 'comerciante' }}</p>
        <h1 class="text-3xl font-semibold text-white">Panel del comerciante</h1>
      </div>
      <button @click="logout" class="btn-ghost">Cerrar sesión</button>
    </div>

    <div v-if="loading" class="flex justify-center py-10">
      <div class="h-12 w-12 rounded-full border-2 border-white/20 border-t-brand-400 animate-spin"></div>
    </div>

    <div v-else-if="error" class="rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-100">
      {{ error }}
    </div>

    <div v-else class="space-y-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="glass rounded-2xl p-5">
          <p class="text-sm text-muted">Tienda principal</p>
          <h3 class="mt-2 text-2xl font-semibold text-white">{{ primaryStore?.name || 'Sin tienda' }}</h3>
          <p class="text-sm text-muted mt-1">{{ primaryStore?.description || 'Aún no creas tu tienda' }}</p>
        </div>
        <div class="glass rounded-2xl p-5">
          <p class="text-sm text-muted">Productos publicados</p>
          <h3 class="mt-2 text-2xl font-semibold text-white">{{ productsCount }}</h3>
          <p class="text-xs text-muted mt-1">Usa filtros en Productos para editarlos</p>
        </div>
        <div class="glass rounded-2xl p-5">
          <p class="text-sm text-muted">Ventas del mes</p>
          <h3 class="mt-2 text-2xl font-semibold text-white">${{ monthlySales.toLocaleString('es-CO') }}</h3>
          <p class="text-xs text-muted mt-1">{{ ordersCount }} pedidos registrados</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 glass rounded-2xl p-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-lg font-semibold text-white">Acciones rápidas</h3>
              <p class="text-sm text-muted">Gestión diaria de tu tienda</p>
            </div>
          </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <router-link
              v-if="!primaryStore"
              to="/dashboard/store"
              class="glass rounded-xl px-4 py-3 text-center hover:border-brand-400/60"
            >
              <p class="font-semibold text-white">Crear tienda</p>
              <p class="text-xs text-muted mt-1">Publica tu catálogo</p>
            </router-link>
            <router-link
              to="/dashboard/products"
              class="glass rounded-xl px-4 py-3 text-center hover:border-brand-400/60"
            >
              <p class="font-semibold text-white">Productos</p>
              <p class="text-xs text-muted mt-1">Crea, edita y elimina</p>
            </router-link>
            <router-link
              to="/stores"
              class="glass rounded-xl px-4 py-3 text-center hover:border-brand-400/60"
            >
              <p class="font-semibold text-white">Explorar tiendas</p>
              <p class="text-xs text-muted mt-1">Inspírate en otros catálogos</p>
            </router-link>
            </div>
          </div>

        <div class="glass rounded-2xl p-6">
          <h3 class="text-lg font-semibold text-white mb-3">Actividad reciente</h3>
          <div v-if="!recentActivity.length" class="text-sm text-muted">Aún no hay pedidos registrados.</div>
          <div v-else class="space-y-3">
            <div v-for="item in recentActivity" :key="item.id" class="flex items-start justify-between border-b border-white/5 pb-3 last:border-b-0">
              <div>
                <p class="text-sm text-white font-medium">Pedido #{{ item.id }}</p>
                <p class="text-xs text-muted">{{ formatDate(item.date) }}</p>
              </div>
              <div class="text-right">
                <span class="chip capitalize">{{ item.status }}</span>
                <p class="text-sm text-brand-200 font-semibold mt-1">${{ item.amount.toLocaleString('es-CO') }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import API from '../services/api.js'

const router = useRouter()
const loading = ref(true)
const error = ref('')
const stores = ref([])
const products = ref([])
const orders = ref([])

const user = computed(() => {
  const userData = localStorage.getItem('user')
  return userData ? JSON.parse(userData) : null
})

const primaryStore = computed(() => stores.value[0] || null)
const productsCount = computed(() => products.value.length)
const ordersCount = computed(() => orders.value.length)
const monthlySales = computed(() => {
  const now = new Date()
  return orders.value.reduce((acc, order) => {
    const date = new Date(order.date || order.created_at || now)
    if (date.getMonth() === now.getMonth() && date.getFullYear() === now.getFullYear()) {
      return acc + Number(order.total_amount ?? order.total ?? 0)
    }
    return acc
  }, 0)
})

const recentActivity = computed(() =>
  [...orders.value]
    .sort((a, b) => new Date(b.date || b.created_at || 0) - new Date(a.date || a.created_at || 0))
    .slice(0, 5)
    .map((order) => ({
      id: order.id,
      status: order.status || 'creada',
      amount: Number(order.total_amount ?? order.total ?? 0),
      date: order.date || order.created_at,
    }))
)

const normalizeList = (response) => {
  if (Array.isArray(response?.data?.data)) return response.data.data
  if (Array.isArray(response?.data)) return response.data
  return []
}

const fetchDashboardData = async () => {
  try {
    loading.value = true
    error.value = ''

    const [storesRes, productsRes, ordersRes] = await Promise.all([
      API.get('/stores'),
      API.get('/products'),
      API.get('/orders'),
    ])

    stores.value = normalizeList(storesRes)
    products.value = normalizeList(productsRes)
    orders.value = normalizeList(ordersRes)
  } catch (err) {
    console.error('Dashboard loading error:', err)
    error.value = err.response?.data?.message || 'Error al cargar el dashboard'
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) =>
  new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })

const logout = async () => {
  try {
    await API.post('/logout')
  } catch (err) {
    console.error('Logout error:', err)
  } finally {
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    router.push('/login')
  }
}

onMounted(() => {
  if (!user.value) {
    router.push('/login')
    return
  }
  fetchDashboardData()
})
</script>
