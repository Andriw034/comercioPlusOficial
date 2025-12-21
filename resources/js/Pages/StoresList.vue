<script setup>
import { Link } from '@inertiajs/vue3'
import StoreCard from '@/components/StoreCard.vue'

defineProps({
  stores: Object,
})
</script>

<template>
  <section class="max-w-7xl mx-auto px-4 py-10">
    <header class="flex items-center justify-between">
      <h1 class="text-2xl font-bold">Tiendas</h1>
      <Link href="/stores/create" class="px-4 py-2 rounded-xl bg-primary text-white hover:bg-primary-600 transition">Crear tienda</Link>
    </header>
    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <StoreCard v-for="s in stores.data" :key="s.id" :name="s.name || s.nombre_tienda" :logo="s.logo" :rating="s.calificacion_promedio" :category="s.categoria_principal" />
    </div>
  </section>
</template>
