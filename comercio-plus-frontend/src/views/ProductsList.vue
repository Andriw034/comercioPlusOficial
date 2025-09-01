<template>
  <main class="max-w-6xl mx-auto px-4 py-10">
    <div class="flex items-center justify-between mb-5">
      <h1 class="text-2xl font-bold">Productos</h1>
      <router-link to="/products/create" class="btn btn-primary">+ Nuevo producto</router-link>
    </div>

    <div v-if="loading" class="text-[var(--cp-ink-2)]">Cargando…</div>

    <div v-else-if="items.length === 0" class="rounded-xl border border-[var(--cp-border)] bg-white p-6 text-center">
      <p class="mb-3">Aún no tienes productos.</p>
      <router-link to="/products/create" class="btn btn-primary">Crea tu primer producto</router-link>
    </div>

    <div v-else class="grid md:grid-cols-3 lg:grid-cols-4 gap-4">
      <div v-for="p in items" :key="p.id" class="bg-white rounded-xl border border-[var(--cp-border)] overflow-hidden">
        <div class="h-36 w-full bg-gray-100"></div>
        <div class="p-3">
          <div class="font-semibold">{{ p.name }}</div>
          <div class="text-sm text-[var(--cp-ink-2)]">${{ new Intl.NumberFormat().format(p.price || 0) }}</div>
          <div class="mt-2 flex gap-2">
            <router-link :to="`/products/${p.id}/edit`" class="btn btn-secondary">Editar</router-link>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../lib/api'
import { session } from '../stores/session'

const items = ref([]); const loading = ref(true)
onMounted(load)
async function load(){
  try{
    const { data } = await api.get('/api/v1/products', { params:{ store_id: session.store?.id } })
    items.value = data?.data || data || []
  }finally{ loading.value=false }
}
</script>
