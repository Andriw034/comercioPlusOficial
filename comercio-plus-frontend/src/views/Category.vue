<template>
  <div class="min-h-screen bg-panel text-slate-50 p-6 rounded-3xl glass">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-semibold text-white">{{ category?.name || 'Categoría' }}</h1>
        <p class="text-sm text-muted mt-1">{{ category?.description || 'Productos agrupados por categoría' }}</p>
      </div>
      <router-link to="/products" class="btn-ghost">Ver todos los productos</router-link>
    </div>

    <div v-if="loading" class="text-center text-muted">Cargando productos...</div>
    <div v-else-if="error" class="text-center text-red-400">{{ error }}</div>
    <div v-else>
      <div v-if="products.length" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div v-for="product in products" :key="product.id" class="rounded-2xl bg-panel-soft border border-white/5 p-4 shadow-soft">
          <div class="w-full h-44 rounded-xl bg-white/5 flex items-center justify-center mb-3">
            <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-cover rounded-xl" />
            <span v-else class="text-muted text-sm">Sin imagen</span>
          </div>
          <h3 class="text-lg font-semibold text-white">{{ product.name }}</h3>
          <p class="text-sm text-muted line-clamp-2">{{ product.description || 'Sin descripción' }}</p>
          <div class="flex items-center justify-between mt-3">
            <span class="text-brand-200 font-semibold">${{ product.price }}</span>
            <router-link :to="`/product/${product.id}`" class="text-sm text-brand-200 hover:text-white">Ver detalle</router-link>
          </div>
        </div>
      </div>
      <div v-else class="text-muted">No hay productos en esta categoría.</div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import API from '../services/api.js'

export default {
  name: 'Category',
  setup() {
    const route = useRoute()
    const category = ref(null)
    const products = ref([])
    const loading = ref(true)
    const error = ref(null)

    const loadCategory = async () => {
      const id = route.params.id
      try {
        const [categoryRes, productsRes] = await Promise.all([
          API.get(`/categories/${id}`),
          API.get('/products', { params: { category: id, per_page: 20 } })
        ])
        category.value = categoryRes.data
        products.value = productsRes.data.data || []
      } catch (err) {
        error.value = 'Error al cargar la categoría'
        console.error(err)
      } finally {
        loading.value = false
      }
    }

    onMounted(loadCategory)

    return {
      category,
      products,
      loading,
      error
    }
  }
}
</script>
