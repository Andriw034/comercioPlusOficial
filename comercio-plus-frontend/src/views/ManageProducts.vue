<template>
  <div class="space-y-8">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm text-muted">Productos de mi tienda</p>
        <h1 class="text-2xl font-semibold text-white">Gestión de productos</h1>
      </div>
      <button class="btn-primary" @click="startCreate">Nuevo producto</button>
    </div>

    <div class="glass rounded-3xl p-6 space-y-4">
      <div class="flex flex-wrap items-center gap-3">
        <input v-model="filters.search" @input="debouncedFetch"
          class="w-full md:w-64 rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-white placeholder:text-slate-400 focus:border-brand-400 focus:ring-2 focus:ring-brand-500/60"
          placeholder="Buscar por nombre" />
        <select v-model="filters.status" @change="fetchProducts"
          class="rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-white">
          <option value="">Todos</option>
          <option value="active">Activos</option>
          <option value="draft">Borrador</option>
        </select>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-slate-200">
          <thead class="text-muted border-b border-white/10">
            <tr>
              <th class="py-3 text-left">Producto</th>
              <th class="py-3 text-left">Precio</th>
              <th class="py-3 text-left">Stock</th>
              <th class="py-3 text-left">Estado</th>
              <th class="py-3 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in products" :key="item.id" class="border-b border-white/5">
              <td class="py-3 flex items-center gap-3">
                <div class="w-12 h-12 bg-white/5 rounded-lg overflow-hidden">
                  <img v-if="item.image_url" :src="item.image_url" class="w-full h-full object-cover" />
                </div>
                <div>
                  <p class="font-semibold text-white">{{ item.name }}</p>
                  <p class="text-xs text-muted">{{ item.category?.name || 'Sin categoría' }}</p>
                </div>
              </td>
              <td class="py-3">${{ item.price }}</td>
              <td class="py-3">{{ item.stock }}</td>
              <td class="py-3 capitalize">{{ item.status || 'draft' }}</td>
              <td class="py-3 text-right space-x-2">
                <button class="btn-ghost text-sm" @click="startEdit(item)">Editar</button>
                <button class="btn-ghost text-sm text-red-200" @click="remove(item)">Eliminar</button>
              </td>
            </tr>
            <tr v-if="!products.length && !loading">
              <td colspan="5" class="py-6 text-center text-muted">Aún no tienes productos.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="loading" class="text-sm text-muted">Cargando...</div>
      <div v-if="error" class="text-sm text-red-200">{{ error }}</div>
    </div>

    <div class="glass rounded-3xl p-6 space-y-4">
      <h2 class="text-xl font-semibold text-white">{{ form.id ? 'Editar producto' : 'Nuevo producto' }}</h2>
      <form class="grid grid-cols-1 md:grid-cols-2 gap-4" @submit.prevent="save">
        <div class="space-y-2">
          <label class="text-sm text-muted">Nombre</label>
          <input v-model="form.name" required class="input-dark" />
        </div>
        <div class="space-y-2">
          <label class="text-sm text-muted">Slug (opcional)</label>
          <input v-model="form.slug" class="input-dark" />
        </div>
        <div class="space-y-2">
          <label class="text-sm text-muted">Precio</label>
          <input type="number" min="0" step="0.01" v-model="form.price" required class="input-dark" />
        </div>
        <div class="space-y-2">
          <label class="text-sm text-muted">Stock</label>
          <input type="number" min="0" step="1" v-model="form.stock" required class="input-dark" />
        </div>
        <div class="space-y-2">
          <label class="text-sm text-muted">Categoría</label>
          <select v-model="form.category_id" required class="select-dark w-full rounded-2xl border px-4 py-3 text-sm">
            <option value="">Selecciona</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>
        <div class="space-y-2">
          <label class="text-sm text-muted">Estado</label>
          <select v-model="form.status" class="select-dark w-full rounded-2xl border px-4 py-3 text-sm">
            <option value="active">Activo</option>
            <option value="draft">Borrador</option>
          </select>
        </div>
        <div class="md:col-span-2 space-y-2">
          <label class="text-sm text-muted">Descripción</label>
          <textarea v-model="form.description" rows="3" class="input-dark"></textarea>
        </div>
        <div class="space-y-2">
          <label class="text-sm text-muted">Imagen</label>
          <label class="btn-secondary cursor-pointer w-fit">
            Subir imagen
            <input type="file" accept="image/*" @change="onImage" class="hidden" />
          </label>
          <div v-if="preview" class="w-28 h-28 rounded-2xl overflow-hidden border border-white/10 mt-2">
            <img :src="preview" class="w-full h-full object-cover" />
          </div>
        </div>

        <div class="md:col-span-2 flex items-center gap-3">
          <button type="submit" class="btn-primary w-full md:w-auto" :disabled="saving">
            {{ saving ? 'Guardando...' : (form.id ? 'Actualizar' : 'Crear producto') }}
          </button>
          <span v-if="formMessage" class="text-sm text-green-200">{{ formMessage }}</span>
          <span v-if="formError" class="text-sm text-red-200">{{ formError }}</span>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import API from '../services/api.js'

const products = ref([])
const categories = ref([])
const loading = ref(false)
const saving = ref(false)
const error = ref('')
const formError = ref('')
const formMessage = ref('')
const preview = ref('')
const imageFile = ref(null)

const filters = reactive({ search: '', status: '' })

const form = reactive({
  id: null,
  name: '',
  slug: '',
  price: '',
  stock: '',
  category_id: '',
  description: '',
  status: 'active',
})

let debounceTimer = null
const debouncedFetch = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchProducts, 400)
}

const fetchCategories = async () => {
  try {
    const { data } = await API.get('/categories')
    categories.value = data || []
  } catch (err) {
    console.error('categories', err)
  }
}

const fetchProducts = async () => {
  loading.value = true
  error.value = ''
  try {
    const { data } = await API.get('/products', {
      params: {
        search: filters.search,
        status: filters.status || undefined,
        per_page: 100,
      }
    })
    products.value = data.data || data
  } catch (err) {
    console.error('products', err)
    error.value = err.response?.data?.message || 'Error al cargar productos'
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  Object.assign(form, { id: null, name: '', slug: '', price: '', stock: '', category_id: '', description: '', status: 'active' })
  preview.value = ''
  imageFile.value = null
  formError.value = ''
  formMessage.value = ''
}

const startCreate = () => {
  resetForm()
}

const startEdit = (item) => {
  Object.assign(form, {
    id: item.id,
    name: item.name,
    slug: item.slug,
    price: item.price,
    stock: item.stock,
    category_id: item.category_id,
    description: item.description,
    status: item.status || 'active'
  })
  preview.value = item.image_url || ''
  imageFile.value = null
  formError.value = ''
  formMessage.value = ''
}

const onImage = (event) => {
  const file = event.target.files[0]
  imageFile.value = file || null
  preview.value = file ? URL.createObjectURL(file) : preview.value
}

const save = async () => {
  saving.value = true
  formError.value = ''
  formMessage.value = ''
  try {
    const payload = new FormData()
    Object.entries(form).forEach(([key, value]) => {
      if (value !== null && value !== '') payload.append(key, value)
    })
    if (imageFile.value) payload.append('image', imageFile.value)

    let response
    if (form.id) {
      payload.append('_method', 'PUT')
      response = await API.post(`/products/${form.id}`, payload, { headers: { 'Content-Type': 'multipart/form-data' } })
    } else {
      response = await API.post('/products', payload, { headers: { 'Content-Type': 'multipart/form-data' } })
    }

    formMessage.value = 'Guardado correctamente'
    fetchProducts()
    startEdit(response.data.data || response.data)
  } catch (err) {
    console.error('save', err)
    formError.value = err.response?.data?.message || 'No se pudo guardar'
  } finally {
    saving.value = false
  }
}

const remove = async (item) => {
  const confirmDelete = window.confirm(`Eliminar "${item.name}"?`)
  if (!confirmDelete) return
  try {
    await API.delete(`/products/${item.id}`)
    fetchProducts()
    if (form.id === item.id) resetForm()
  } catch (err) {
    console.error('delete', err)
    alert('No se pudo eliminar')
  }
}

onMounted(() => {
  fetchCategories()
  fetchProducts()
})
</script>
