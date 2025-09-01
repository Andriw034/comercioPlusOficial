<template>
  <main class="max-w-5xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-2 gap-8">
    <!-- Imágenes -->
    <div class="bg-white rounded-xl border border-[var(--cp-border)] p-4">
      <div class="h-48 w-full bg-gray-100 rounded-lg mb-3 flex items-center justify-center text-[var(--cp-ink-2)]">
        {{ preview ? '' : 'Preview de imagen' }}
        <img v-if="preview" :src="preview" class="h-48 object-contain" />
      </div>
      <input type="file" accept="image/*" @change="onImage" class="block w-full">
      <p class="text-xs text-[var(--cp-ink-2)] mt-2">Formatos: JPG/PNG. Máx 2MB.</p>
    </div>

    <!-- Formulario -->
    <form @submit.prevent="save" enctype="multipart/form-data" class="space-y-4">
      <div><label class="text-sm">Nombre</label><input v-model="name" class="input" required></div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="text-sm">Precio ($)</label><input v-model.number="price" type="number" step="0.01" class="input" required></div>
        <div><label class="text-sm">Stock</label><input v-model.number="stock" type="number" class="input" required></div>
      </div>

      <div>
        <label class="text-sm">Categoría</label>
        <select v-model="category_id" class="input" required>
          <option value="" disabled>Selecciona una categoría</option>
          <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <p v-if="categories.length===0" class="text-xs text-[var(--cp-ink-2)] mt-1">
          No hay categorías aún. Crea categorías en tu panel o pide al admin que ejecute el seeder.
        </p>
      </div>

      <div><label class="text-sm">Descripción</label><textarea v-model="description" rows="4" class="input"></textarea></div>

      <div class="flex gap-3">
        <router-link to="/products" class="btn btn-secondary">Cancelar</router-link>
        <button class="btn btn-primary">{{ loading ? 'Guardando...' : 'Guardar producto' }}</button>
      </div>

      <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
    </form>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../lib/api'
import { session } from '../stores/session'
import { useRouter } from 'vue-router'

const name=ref(''), price=ref(null), stock=ref(0), category_id=ref(''), description=ref('')
const image=ref(null), preview=ref(null), categories=ref([]), loading=ref(false), error=ref('')
const router = useRouter()

onMounted(async()=>{
  await loadCategories()
})

async function loadCategories(){
  try{
    // IMPORTANTE: carga categorías de la tienda del usuario => el select SÍ tendrá opciones
    const { data } = await api.get('/api/v1/categories', { params:{ store_id: session.store?.id } })
    categories.value = data?.data || data || []
  }catch(e){ categories.value = [] }
}

function onImage(e){
  const f = e.target.files?.[0]
  image.value = f || null
  preview.value = f ? URL.createObjectURL(f) : null
}

async function save(){
  loading.value = true; error.value = ''
  try{
    const fd = new FormData()
    fd.append('name',name.value); fd.append('price',price.value ?? 0)
    fd.append('stock',stock.value ?? 0); fd.append('category_id',category_id.value)
    fd.append('description',description.value || '')
    if(image.value) fd.append('image',image.value)
    const { data } = await api.post('/api/v1/products', fd, { headers:{'Content-Type':'multipart/form-data'} })
    router.push({ name:'products.index' })
  }catch(e){
    error.value = e?.response?.data?.message || 'No se pudo guardar el producto'
  }finally{ loading.value=false }
}
</script>
