<template>
  <div class="space-y-8">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm text-muted">Configura tu tienda</p>
        <h1 class="text-2xl font-semibold text-white">{{ store.id ? 'Editar tienda' : 'Crear tienda' }}</h1>
      </div>
      <span class="chip" :class="store.is_visible ? 'border-green-400/60 text-green-200' : ''">
        {{ store.is_visible ? 'Visible' : 'Oculta' }}
      </span>
    </div>

    <form class="glass rounded-3xl p-6 space-y-6" @submit.prevent="submit">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="text-sm text-muted">Nombre</label>
          <input v-model="store.name" required class="input-dark" />
        </div>
        <div class="space-y-2">
          <label class="text-sm text-muted">Slug (opcional)</label>
          <input v-model="store.slug" class="input-dark" />
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-sm text-muted">Descripción</label>
        <textarea v-model="store.description" rows="3" class="input-dark"></textarea>
      </div>

      <div class="flex items-center gap-3">
        <label class="text-sm text-muted">Visible al público</label>
        <input type="checkbox" v-model="store.is_visible"
          class="h-4 w-4 rounded border-white/20 bg-white/5 text-brand-500 focus:ring-brand-500/60" />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-3">
          <p class="text-sm text-muted">Logo</p>
          <div class="rounded-2xl border border-dashed border-white/15 bg-white/5 p-4 flex flex-col items-center gap-3">
            <label class="btn-secondary cursor-pointer w-full text-center">
              Subir logo
              <input type="file" accept="image/*" @change="onFileSelect($event, 'logo')" class="hidden" />
            </label>
            <div v-if="previews.logo" class="w-24 h-24 rounded-2xl overflow-hidden border border-white/10">
              <img :src="previews.logo" class="w-full h-full object-cover" />
            </div>
          </div>
        </div>
        <div class="space-y-3">
          <p class="text-sm text-muted">Portada</p>
          <div class="rounded-2xl border border-dashed border-white/15 bg-white/5 p-4 flex flex-col items-center gap-3">
            <label class="btn-secondary cursor-pointer w-full text-center">
              Subir portada
              <input type="file" accept="image/*" @change="onFileSelect($event, 'cover')" class="hidden" />
            </label>
            <div v-if="previews.cover" class="w-full h-28 rounded-2xl overflow-hidden border border-white/10">
              <img :src="previews.cover" class="w-full h-full object-cover" />
            </div>
          </div>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="btn-primary w-full md:w-auto" :disabled="submitting">
          {{ submitting ? 'Guardando...' : (store.id ? 'Actualizar tienda' : 'Crear tienda') }}
        </button>
        <span v-if="message" class="text-sm text-green-200">{{ message }}</span>
        <span v-if="error" class="text-sm text-red-200">{{ error }}</span>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import API from '../services/api.js'

const store = reactive({
  id: null,
  name: '',
  slug: '',
  description: '',
  is_visible: true,
})

const files = reactive({ logo: null, cover: null })
const previews = reactive({ logo: '', cover: '' })
const submitting = ref(false)
const message = ref('')
const error = ref('')

const loadStore = async () => {
  try {
    const { data } = await API.get('/stores')
    if (Array.isArray(data) && data.length > 0) {
      Object.assign(store, data[0])
      previews.logo = data[0].logo_url || ''
      previews.cover = data[0].cover_url || ''
    }
  } catch (err) {
    console.error('Load store', err)
  }
}

const onFileSelect = (event, type) => {
  const file = event.target.files[0]
  files[type] = file || null
  previews[type] = file ? URL.createObjectURL(file) : previews[type]
}

const submit = async () => {
  submitting.value = true
  message.value = ''
  error.value = ''

  try {
    const form = new FormData()
    form.append('name', store.name)
    if (store.slug) form.append('slug', store.slug)
    form.append('description', store.description || '')
    form.append('is_visible', store.is_visible ? '1' : '0')
    if (files.logo) form.append('logo', files.logo)
    if (files.cover) form.append('cover', files.cover)

    let response
    if (store.id) {
      form.append('_method', 'PUT')
      response = await API.post(`/stores/${store.id}`, form, { headers: { 'Content-Type': 'multipart/form-data' } })
    } else {
      response = await API.post('/stores', form, { headers: { 'Content-Type': 'multipart/form-data' } })
    }

    Object.assign(store, response.data)
    previews.logo = response.data.logo_url || previews.logo
    previews.cover = response.data.cover_url || previews.cover
    message.value = 'Guardado correctamente'
  } catch (err) {
    console.error('Store save', err)
    error.value = err.response?.data?.message || 'Error al guardar la tienda'
  } finally {
    submitting.value = false
  }
}

onMounted(loadStore)
</script>
