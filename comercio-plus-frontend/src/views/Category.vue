<template>
  <div class="min-h-screen bg-cp-surface text-cp-text p-6">
    <h1 class="text-3xl font-semibold text-cp-text mb-4">{{ category?.name || slug }}</h1>

    <div v-if="loading" class="text-center">Cargando productos...</div>
    <div v-else-if="error" class="text-center text-red-500">{{ error }}</div>
    <div v-else-if="category">
      <p class="text-sm text-cp-sub mb-4">{{ category.description }}</p>

      <div v-if="category.products && category.products.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div v-for="product in category.products" :key="product.id" class="product-card rounded-2xl p-4 bg-white shadow-sm">
          <img :src="product.image" class="w-full h-40 object-cover rounded-lg mb-3" />
          <h3 class="text-lg font-medium text-cp-text">{{ product.name }}</h3>
          <p class="text-sm text-cp-sub">{{ product.short_description }}</p>
          <p class="font-bold text-comercioplus">Precio: ${{ product.price }}</p>
          <button class="mt-3 px-4 py-2 rounded-2xl bg-comercioplus text-white">Agregar</button>
        </div>
      </div>
      <div v-else>
        <p class="text-cp-sub">No hay productos en esta categoría.</p>
      </div>
    </div>
    <div v-else>
      <p class="text-cp-sub">Categoría no encontrada.</p>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

export default {
  name: 'Category',
  setup() {
    const route = useRoute()
    const category = ref(null)
    const loading = ref(true)
    const error = ref(null)

    onMounted(async () => {
      const slug = route.params.slug
      try {
        const response = await API.get(`/categories/${slug}`)
        category.value = response.data
      } catch (err) {
        error.value = 'Error al cargar la categoría'
        console.error(err)
      } finally {
        loading.value = false
      }
    })

    return {
      category,
      loading,
      error
    }
  }
}
</script>


