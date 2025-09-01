<template>
  <main class="min-h-screen bg-cp-bg text-white">
    <div class="mx-auto max-w-3xl px-4 py-12">
      <h1 class="text-3xl font-extrabold">Crear tu tienda</h1>
      <p class="text-slate-300 mt-1">Configura nombre, slug, descripción y tu identidad visual.</p>
      
      <form class="mt-8 space-y-5" @submit.prevent="onSubmit">
        <div>
          <label class="block text-sm font-medium text-slate-200">Nombre</label>
          <input v-model="name" @input="onName" type="text" required class="mt-1 w-full rounded-xl border border-white/15 bg-white/10 text-white placeholder-white/50 px-4 py-2 focus:border-cp-brand focus:ring-cp-brand" placeholder="Ej: Mi Tienda" />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-slate-200">Slug</label>
          <input v-model="slug" type="text" required class="mt-1 w-full rounded-xl border border-white/15 bg-white/10 text-white placeholder-white/50 px-4 py-2 focus:border-cp-brand focus:ring-cp-brand" placeholder="mi-tienda" />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-slate-200">Descripción</label>
          <textarea v-model="description" rows="4" class="mt-1 w-full rounded-xl border border-white/15 bg-white/10 text-white placeholder-white/50 px-4 py-2 focus:border-cp-brand focus:ring-cp-brand" placeholder="Describe tu tienda"></textarea>
        </div>
        
        <div class="grid gap-5 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-slate-200">Logo</label>
            <input ref="logoRef" type="file" accept="image/*" class="mt-1 block w-full text-sm text-slate-200 file:mr-4 file:rounded-lg file:border-0 file:bg-cp-brand file:px-4 file:py-2 file:text-white hover:file:bg-cp-brand-2" />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-slate-200">Portada</label>
            <input ref="coverRef" type="file" accept="image/*" class="mt-1 block w-full text-sm text-slate-200 file:mr-4 file:rounded-lg file:border-0 file:bg-cp-brand file:px-4 file:py-2 file:text-white hover:file:bg-cp-brand-2" />
          </div>
        </div>
        
        <button :disabled="loading" type="submit" class="rounded-xl bg-cp-brand hover:bg-cp-brand-2 text-white font-semibold px-6 py-3">
          {{ loading ? 'Creando...' : 'Crear tienda' }}
        </button>
        
        <p v-if="error" class="text-red-300 text-sm">{{ error }}</p>
      </form>
    </div>
  </main>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiCreateStore } from '../lib/api'

const router = useRouter()
const name = ref('')
const slug = ref('')
const description = ref('')
const logoRef = ref(null)
const coverRef = ref(null)
const loading = ref(false)
const error = ref('')

function toSlug(s){
  const allow = 'abcdefghijklmnopqrstuvwxyz0123456789 -'
  return (s || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .split('')
    .filter(ch => allow.includes(ch.toLowerCase()))
    .join('')
    .trim()
    .replaceAll(' ', '-')
    .split('-')
    .filter(Boolean)
    .join('-')
    .toLowerCase()
}

function onName(){ slug.value = toSlug(name.value) }

async function onSubmit(){
  loading.value = true
  error.value = ''
  try{
    const form = new FormData()
    form.append('name', name.value)
    form.append('slug', slug.value)
    form.append('description', description.value)
    if (logoRef.value?.files?.[0]) form.append('logo', logoRef.value.files[0])
    if (coverRef.value?.files?.[0]) form.append('cover', coverRef.value.files[0])
    
    await apiCreateStore(form)
    router.push({ name: 'dashboard' })
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al crear la tienda'
  } finally { 
    loading.value = false 
  }
}
</script>
