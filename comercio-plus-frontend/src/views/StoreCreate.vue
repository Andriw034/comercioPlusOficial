<template>
  <main class="max-w-xl mx-auto px-4 py-10">
    <div class="bg-white rounded-2xl shadow-xl p-6">
      <h1 class="text-2xl font-bold mb-1">Crear tu primera tienda</h1>
      <p class="text-sm text-[var(--cp-ink-2)] mb-6">Completa la información básica para comenzar</p>

      <form @submit.prevent="onSubmit" enctype="multipart/form-data" class="space-y-4">
        <div><label class="text-sm">Nombre</label><input v-model="name" class="input" required></div>
        <div><label class="text-sm">Slug (URL)</label><input v-model="slug" class="input" placeholder="mi-tienda"></div>
        <div><label class="text-sm">Descripción</label><textarea v-model="description" rows="3" class="input"></textarea></div>

        <div class="grid grid-cols-2 gap-3">
          <div><label class="text-sm">Dirección</label><input v-model="direccion" class="input"></div>
          <div><label class="text-sm">Teléfono</label><input v-model="telefono" class="input"></div>
        </div>

        <div><label class="text-sm">Categoría principal</label><input v-model="categoria_principal" class="input"></div>

        <div class="grid grid-cols-2 gap-3">
          <div><label class="text-sm">Color primario</label><input v-model="primary_color" class="input"></div>
          <div><label class="text-sm">Texto</label><input v-model="text_color" class="input"></div>
          <div><label class="text-sm">Botón</label><input v-model="button_color" class="input"></div>
          <div><label class="text-sm">Fondo</label><input v-model="background_color" class="input"></div>
        </div>

        <div>
          <label class="text-sm">Logo</label>
          <input type="file" accept="image/*" @change="onFile($event,'logo')" class="block w-full">
          <img v-if="logoPreview" :src="logoPreview" class="mt-2 h-16 w-16 object-contain rounded"/>
        </div>

        <div>
          <label class="text-sm">Portada</label>
          <input type="file" accept="image/*" @change="onFile($event,'cover')" class="block w-full">
          <img v-if="coverPreview" :src="coverPreview" class="mt-2 h-24 w-full object-cover rounded"/>
        </div>

        <button class="btn btn-primary w-full" :disabled="loading">{{ loading ? 'Creando...' : 'Crear tienda' }}</button>
        <p v-if="error" class="text-red-600 text-sm mt-2">{{ error }}</p>
      </form>
    </div>
  </main>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../lib/api'
import { fetchMe, applyThemeFromStore } from '../stores/session'

const name=ref(''), slug=ref(''), description=ref(''), direccion=ref(''), telefono=ref('')
const categoria_principal=ref('')
const primary_color=ref('#FF6A2E'), text_color=ref('#333333'), button_color=ref('#FF6A2E'), background_color=ref('#FFF7F2')
const logo=ref(null), cover=ref(null), logoPreview=ref(null), coverPreview=ref(null)
const loading=ref(false), error=ref('')
const router = useRouter()

watch(name, v => { if(!slug.value) slug.value = String(v).toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'') })

function onFile(e,type){
  const f = e.target.files?.[0]
  if(type==='logo'){ logo.value=f; logoPreview.value = f ? URL.createObjectURL(f) : null }
  else { cover.value=f; coverPreview.value = f ? URL.createObjectURL(f) : null }
}

async function onSubmit(){
  loading.value=true; error.value=''
  try{
    const fd = new FormData()
    fd.append('name',name.value); if(slug.value) fd.append('slug',slug.value)
    fd.append('description',description.value||'')
    fd.append('direccion',direccion.value||''); fd.append('telefono',telefono.value||'')
    fd.append('categoria_principal',categoria_principal.value||'')
    fd.append('primary_color',primary_color.value||''); fd.append('text_color',text_color.value||'')
    fd.append('button_color',button_color.value||''); fd.append('background_color',background_color.value||'')
    if(logo.value) fd.append('logo',logo.value); if(cover.value) fd.append('cover',cover.value)

    const { data } = await api.post('/api/v1/stores', fd, { headers:{'Content-Type':'multipart/form-data'} })
    applyThemeFromStore(data?.data)
    await fetchMe()
    router.push({ name:'products.create' })
  }catch(e){ error.value = e?.response?.data?.message || 'Error al crear la tienda' }
  finally{ loading.value=false }
}
</script>
