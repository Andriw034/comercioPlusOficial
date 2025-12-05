<template>
  <section class="max-w-5xl mx-auto px-4 py-10" v-if="product">
    <div class="grid md:grid-cols-2 gap-8">
      <div class="rounded-xl overflow-hidden border border-neutral-200 dark:border-neutral-800">
        <img :src="product.image_path || product.image" :alt="product.name" class="w-full h-auto" />
      </div>
      <div>
        <h1 class="text-2xl font-bold">{{ product.name }}</h1>
        <p class="mt-2 text-neutral-700 dark:text-neutral-300">{{ product.description }}</p>
        <div class="mt-4 text-3xl font-bold">${{ Number(product.price).toFixed(2) }}</div>
        <div class="mt-6 flex gap-2">
          <button class="px-4 py-2 rounded-xl bg-primary text-white hover:bg-primary-600">Añadir al carrito</button>
          <RouterLink to="/cart" class="px-4 py-2 rounded-xl border border-neutral-200 dark:border-neutral-800">Ver carrito</RouterLink>
        </div>
      </div>
    </div>
  </section>
</template>
<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
const route = useRoute()
const product = ref(null)

onMounted(async () => { try { const r = await fetch(`/api/products/${route.params.slug}`); product.value = await r.json() } catch {} })
</script>

