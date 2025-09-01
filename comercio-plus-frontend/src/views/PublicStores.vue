<template>
  <main class="min-h-screen bg-white text-cp-text">
    <div class="mx-auto max-w-7xl px-4 py-10">
      <h1 class="text-3xl font-extrabold">Tiendas</h1>
      <p class="text-slate-600 mt-1">Listado accesible solo para superadmin y propietarios.</p>

      <div v-if="stores.length" class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <RouterLink 
          v-for="store in stores" 
          :key="store.id || store.slug" 
          :to="{ name: 'public-store-show', params: { slug: store.slug } }" 
          class="rounded-2xl border border-slate-200 overflow-hidden hover:shadow-lg transition"
        >
          <div class="aspect-[3/1] bg-slate-100">
            <img 
              v-if="store.cover_url" 
              :src="store.cover_url" 
              :alt="store.name" 
              class="h-full w-full object-cover" 
            />
            <div v-else class="h-full w-full bg-slate-200 flex items-center justify-center">
              <span class="text-slate-400">Sin portada</span>
            </div>
          </div>
          
          <div class="p-4 flex items-center gap-3">
            <img 
              v-if="store.logo_url" 
              :src="store.logo_url" 
              :alt="store.name" 
              class="h-12 w-12 rounded-xl object-cover" 
            />
            <div v-else class="h-12 w-12 rounded-xl bg-slate-200 flex items-center justify-center">
              <span class="text-slate-400 text-xs">Logo</span>
            </div>
            
            <div>
              <h3 class="font-semibold">{{ store.name }}</h3>
              <p class="text-sm text-slate-600">/{{ store.slug }}</p>
            </div>
          </div>
        </RouterLink>
      </div>

      <p v-else class="mt-8 text-slate-600">No hay tiendas visibles para tu usuario.</p>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { apiPublicStores } from '../lib/api'

const stores = ref([])

onMounted(async () => {
  try {
    const res = await apiPublicStores()
    stores.value = Array.isArray(res?.data) ? res.data : (Array.isArray(res) ? res : [])
  } catch (e) {
    stores.value = []
  }
})
</script>
