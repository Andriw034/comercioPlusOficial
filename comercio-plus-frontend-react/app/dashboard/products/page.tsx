import { useEffect, useRef, useState } from 'react'
import API from '@/lib/api'
import Button from '@/components/ui/Button'
import Input from '@/components/ui/Input'
import Select from '@/components/ui/Select'
import Textarea from '@/components/ui/Textarea'
import type { Category, Product, Store } from '@/types/api'

export default function ManageProducts() {
  const [products, setProducts] = useState<Product[]>([])
  const [categories, setCategories] = useState<Category[]>([])
  const [loading, setLoading] = useState(false)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState('')
  const [formError, setFormError] = useState('')
  const [formMessage, setFormMessage] = useState('')
  const [preview, setPreview] = useState('')
  const [imageFile, setImageFile] = useState<File | null>(null)
  const [storeId, setStoreId] = useState<number | null>(null)

  const [filters, setFilters] = useState({ search: '', status: '' })
  const [form, setForm] = useState({
    id: null as number | null,
    name: '',
    slug: '',
    price: '',
    stock: '',
    category_id: '',
    description: '',
    status: 'active',
  })

  const debounceRef = useRef<number | null>(null)

  const fetchCategories = async () => {
    try {
      const { data } = await API.get('/categories')
      setCategories(data || [])
    } catch (err) {
      console.error('categories', err)
    }
  }

  const fetchStore = async () => {
    try {
      const { data } = await API.get('/my/store')
      if (data?.id) {
        const store: Store = data
        setStoreId(store.id)
      } else {
        setStoreId(null)
        setError('Primero crea una tienda para gestionar productos.')
      }
    } catch (err: any) {
      console.error('stores', err)
      if (err.response?.status === 404) {
        setStoreId(null)
        setError('Primero crea una tienda para gestionar productos.')
      } else {
        setError(err.response?.data?.message || 'Error al cargar tu tienda')
      }
    }
  }

  const fetchProducts = async () => {
    if (!storeId) {
      setProducts([])
      return
    }
    setLoading(true)
    setError('')
    try {
      const { data } = await API.get('/products', {
        params: {
          search: filters.search,
          status: filters.status || undefined,
          store_id: storeId,
          per_page: 100,
        },
      })
      setProducts(data.data || data)
    } catch (err: any) {
      console.error('products', err)
      setError(err.response?.data?.message || 'Error al cargar productos')
    } finally {
      setLoading(false)
    }
  }

  const resetForm = () => {
    setForm({ id: null, name: '', slug: '', price: '', stock: '', category_id: '', description: '', status: 'active' })
    setPreview('')
    setImageFile(null)
    setFormError('')
    setFormMessage('')
  }

  const startCreate = () => {
    resetForm()
  }

  const startEdit = (item: Product) => {
    setForm({
      id: item.id,
      name: item.name || '',
      slug: item.slug || '',
      price: item.price?.toString() || '',
      stock: item.stock?.toString() || '',
      category_id: item.category_id?.toString() || '',
      description: item.description || '',
      status: item.status || 'active',
    })
    setPreview(item.image_url || '')
    setImageFile(null)
    setFormError('')
    setFormMessage('')
  }

  const onImage = (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0] || null
    setImageFile(file)
    if (file) {
      setPreview(URL.createObjectURL(file))
    }
  }

  const save = async (event: React.FormEvent) => {
    event.preventDefault()
    if (!storeId) {
      setFormError('Primero debes crear una tienda.')
      return
    }
    setSaving(true)
    setFormError('')
    setFormMessage('')

    try {
      const payload = new FormData()
      Object.entries(form).forEach(([key, value]) => {
        if (value !== null && value !== '') payload.append(key, String(value))
      })
      if (imageFile) payload.append('image', imageFile)

      let response
      if (form.id) {
        payload.append('_method', 'PUT')
        response = await API.post(`/products/${form.id}`, payload, { headers: { 'Content-Type': 'multipart/form-data' } })
      } else {
        response = await API.post('/products', payload, { headers: { 'Content-Type': 'multipart/form-data' } })
      }

      setFormMessage('Guardado correctamente')
      await fetchProducts()
      const newProduct = response.data?.data || response.data
      if (newProduct) {
        startEdit(newProduct)
      }
    } catch (err: any) {
      console.error('save', err)
      setFormError(err.response?.data?.message || 'No se pudo guardar')
    } finally {
      setSaving(false)
    }
  }

  const remove = async (item: Product) => {
    const confirmDelete = window.confirm(`Eliminar "${item.name}"?`)
    if (!confirmDelete) return
    try {
      await API.delete(`/products/${item.id}`)
      await fetchProducts()
      if (form.id === item.id) resetForm()
    } catch (err: any) {
      console.error('delete', err)
      alert(err.response?.data?.message || 'No se pudo eliminar')
    }
  }

  useEffect(() => {
    fetchCategories()
    fetchStore()
  }, [])

  useEffect(() => {
    if (debounceRef.current) {
      window.clearTimeout(debounceRef.current)
    }
    debounceRef.current = window.setTimeout(() => {
      fetchProducts()
    }, 400)
    return () => {
      if (debounceRef.current) {
        window.clearTimeout(debounceRef.current)
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [filters.search, filters.status, storeId])

  return (
    <div className="space-y-8">
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm text-muted">Productos de mi tienda</p>
          <h1 className="text-2xl font-semibold text-white">Gestión de productos</h1>
        </div>
        <Button onClick={startCreate}>Nuevo producto</Button>
      </div>

      <div className="glass rounded-3xl p-6 space-y-4">
        <div className="flex flex-wrap items-center gap-3">
          <input
            value={filters.search}
            onChange={(e) => setFilters((prev) => ({ ...prev, search: e.target.value }))}
            className="w-full md:w-64 rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-white placeholder:text-slate-400 focus:border-brand-400 focus:ring-2 focus:ring-brand-500/60"
            placeholder="Buscar por nombre"
          />
          <select
            value={filters.status}
            onChange={(e) => setFilters((prev) => ({ ...prev, status: e.target.value }))}
            className="select-dark rounded-2xl border px-4 py-2 text-sm"
          >
            <option value="">Todos</option>
            <option value="active">Activos</option>
            <option value="draft">Borrador</option>
          </select>
        </div>

        <div className="overflow-x-auto">
          <table className="min-w-full text-sm text-slate-200">
            <thead className="text-muted border-b border-white/10">
              <tr>
                <th className="py-3 text-left">Producto</th>
                <th className="py-3 text-left">Precio</th>
                <th className="py-3 text-left">Stock</th>
                <th className="py-3 text-left">Estado</th>
                <th className="py-3 text-right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              {products.map((item) => (
                <tr key={item.id} className="border-b border-white/5">
                  <td className="py-3 flex items-center gap-3">
                    <div className="w-12 h-12 bg-white/5 rounded-lg overflow-hidden">
                      {item.image_url && <img src={item.image_url} className="w-full h-full object-cover" />}
                    </div>
                    <div>
                      <p className="font-semibold text-white">{item.name}</p>
                      <p className="text-xs text-muted">{item.category?.name || 'Sin categoría'}</p>
                    </div>
                  </td>
                  <td className="py-3">${item.price}</td>
                  <td className="py-3">{item.stock}</td>
                  <td className="py-3 capitalize">{item.status || 'draft'}</td>
                  <td className="py-3 text-right space-x-2">
                    <button className="btn-ghost text-sm" onClick={() => startEdit(item)}>Editar</button>
                    <button className="btn-ghost text-sm text-red-200" onClick={() => remove(item)}>Eliminar</button>
                  </td>
                </tr>
              ))}
              {!products.length && !loading && (
                <tr>
                  <td colSpan={5} className="py-6 text-center text-muted">Aún no tienes productos.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        {loading && <div className="text-sm text-muted">Cargando...</div>}
        {error && <div className="text-sm text-red-200">{error}</div>}
      </div>

      <div className="glass rounded-3xl p-6 space-y-4">
        <h2 className="text-xl font-semibold text-white">{form.id ? 'Editar producto' : 'Nuevo producto'}</h2>
        <form className="grid grid-cols-1 md:grid-cols-2 gap-4" onSubmit={save}>
          <div className="space-y-2">
            <label className="text-sm text-muted">Nombre</label>
            <Input
              value={form.name}
              required
              onChange={(e) => setForm((prev) => ({ ...prev, name: e.target.value }))}
            />
          </div>
          <div className="space-y-2">
            <label className="text-sm text-muted">Slug (opcional)</label>
            <Input value={form.slug} onChange={(e) => setForm((prev) => ({ ...prev, slug: e.target.value }))} />
          </div>
          <div className="space-y-2">
            <label className="text-sm text-muted">Precio</label>
            <Input
              type="number"
              min="0"
              step="0.01"
              value={form.price}
              required
              onChange={(e) => setForm((prev) => ({ ...prev, price: e.target.value }))}
            />
          </div>
          <div className="space-y-2">
            <label className="text-sm text-muted">Stock</label>
            <Input
              type="number"
              min="0"
              step="1"
              value={form.stock}
              required
              onChange={(e) => setForm((prev) => ({ ...prev, stock: e.target.value }))}
            />
          </div>
          <div className="space-y-2">
            <label className="text-sm text-muted">Categoría</label>
            <Select
              value={form.category_id}
              required
              onChange={(e) => setForm((prev) => ({ ...prev, category_id: e.target.value }))}
              className="w-full rounded-2xl border px-4 py-3 text-sm"
            >
              <option value="">Selecciona</option>
              {categories.map((cat) => (
                <option key={cat.id} value={cat.id}>
                  {cat.name}
                </option>
              ))}
            </Select>
          </div>
          <div className="space-y-2">
            <label className="text-sm text-muted">Estado</label>
            <Select
              value={form.status}
              onChange={(e) => setForm((prev) => ({ ...prev, status: e.target.value }))}
              className="w-full rounded-2xl border px-4 py-3 text-sm"
            >
              <option value="active">Activo</option>
              <option value="draft">Borrador</option>
            </Select>
          </div>
          <div className="md:col-span-2 space-y-2">
            <label className="text-sm text-muted">Descripción</label>
            <Textarea
              rows={3}
              value={form.description}
              onChange={(e) => setForm((prev) => ({ ...prev, description: e.target.value }))}
            />
          </div>
          <div className="space-y-2">
            <label className="text-sm text-muted">Imagen</label>
            <label className="btn-secondary cursor-pointer w-fit">
              Subir imagen
              <input type="file" accept="image/*" onChange={onImage} className="hidden" />
            </label>
            {preview && (
              <div className="w-28 h-28 rounded-2xl overflow-hidden border border-white/10 mt-2">
                <img src={preview} className="w-full h-full object-cover" />
              </div>
            )}
          </div>

          <div className="md:col-span-2 flex items-center gap-3">
            <Button type="submit" className="w-full md:w-auto" loading={saving}>
              {saving ? 'Guardando...' : form.id ? 'Actualizar' : 'Crear producto'}
            </Button>
            {formMessage && <span className="text-sm text-green-200">{formMessage}</span>}
            {formError && <span className="text-sm text-red-200">{formError}</span>}
          </div>
        </form>
      </div>
    </div>
  )
}

