<template>
  <main class="min-h-screen bg-white text-cp-text">
    <div class="mx-auto max-w-7xl px-4 py-10">
      <div v-if="store" class="rounded-2xl overflow-hidden border border-slate-200">
        <div class="aspect-[3/1] bg-slate-100">
          <img 
            v-if="store.cover_url" 
            :src="store.cover_url" 
            :alt="store.name" 
            class="h-full w-full object-cover" 
          />
          <div v-else class="h-full w-full bg-slate-200 flex items-center justify-center">
            <span class="text-slate-400">Sin imagen de portada</span>
          </div>
        </div>
        
        <div class="p-6 flex items-center gap-4">
          <img 
            v-if="store.logo_url" 
            :src="store.logo_url" 
            :alt="store.name" 
            class="h-16 w-16 rounded-2xl object-cover" 
          />
          <div v-else class="h-16 w-16 rounded-2xl bg-slate-200 flex items-center justify-center">
            <span class="text-slate-400 text-xs">Logo</span>
          </div>
          
          <div>
            <h1 class="text-2xl font-extrabold">{{ store.name }}</h1>
            <p class="text-slate-600">{{ store.description }}</p>
          </div>
        </div>
      </div>

      <div v-if="products.length" class="mt-8">
        <h2 class="text-xl font-bold">Productos</h2>
        <div class="mt-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <div 
            v-for="product in products" 
            :key="product.id" 
            class="rounded-2xl border border-slate-200 bg-white overflow-hidden hover:shadow-lg transition"
          >
            <div class="aspect-[4/3] bg-slate-100">
              <img 
                v-if="product.image_url" 
                :src="product.image_url" 
                :alt="product.name" 
                class="h-full w-full object-cover" 
              />
              <div v-else class="h-full w-full bg-slate-200 flex items-center justify-center">
                <span class="text-slate-400 text-sm">Sin imagen</span>
              </div>
            </div>
            
            <div class="p-4">
              <h3 class="font-semibold">{{ product.name }}</h3>
              <p class="text-sm text-slate-600">$ {{ product.price }}</p>
            </div>
          </div>
        </div>
      </div>

      <p v-else class="mt-8 text-slate-600">Tienda no visible para tu usuario o sin productos.</p>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { apiPublicStoreBySlug } from '../lib/api'

const route = useRoute()
const store = ref(null)
const products = ref([])

onMounted(async () => {
  try {
    const res = await apiPublicStoreBySlug(route.params.slug)
    store.value = (res && (res.store || res.data)) || res
    products.value = (res && res.products) || []
  } catch (e) {
    store.value = null
    products.value = []
  }
})
</script>
