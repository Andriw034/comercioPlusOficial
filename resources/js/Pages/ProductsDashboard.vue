<template>
  <section class="max-w-7xl mx-auto px-4 py-10">
    <header class="flex items-center justify-between">
      <h1 class="text-2xl font-bold">Productos</h1>
      <div class="flex gap-2">
        <button class="px-4 py-2 rounded-xl bg-primary text-white hover:bg-primary-600">Nuevo</button>
        <RouterLink to="/stores/create" class="px-4 py-2 rounded-xl border border-neutral-200 dark:border-neutral-800">Crear tienda</RouterLink>
      </div>
    </header>
    <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
      <ProductCard v-for="p in products" :key="p.id" :name="p.name" :price="p.price" :image="p.image_path || p.image" :rating="p.average_rating" />
    </div>
  </section>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import ProductCard from '@/components/ProductCard.vue'
const products = ref([])
onMounted(async () => { try{ const r = await fetch('/api/products'); products.value = await r.json() } catch {} })
</script>

