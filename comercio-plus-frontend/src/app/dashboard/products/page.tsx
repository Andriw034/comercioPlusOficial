import { useEffect, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import API from '@/lib/api'
import Button, { buttonVariants } from '@/components/ui/button'
import Input from '@/components/ui/Input'
import Select from '@/components/ui/Select'
import Textarea from '@/components/ui/Textarea'
import GlassCard from '@/components/ui/GlassCard'
import Badge from '@/components/ui/Badge'
import type { Category, Product, Store } from '@/types/api'
import { resolveMediaUrl, formatPrice } from '@/lib/format'
import { extractList } from '@/lib/api-response'
import { uploadProductImage } from '@/services/uploads'

const MAX_IMAGE_SIZE_BYTES = 5 * 1024 * 1024
const ALLOWED_IMAGE_MIME_TYPES = new Set([
  'image/jpeg',
  'image/png',
  'image/webp',
  'image/avif',
])

export default function ManageProducts() {
  const [products, setProducts] = useState<Product[]>([])
  const [categories, setCategories] = useState<Category[]>([])
  const [categoriesLoading, setCategoriesLoading] = useState(false)
  const [categoriesError, setCategoriesError] = useState('')
  const [creatingCategory, setCreatingCategory] = useState(false)
  const [categoryName, setCategoryName] = useState('')
  const [categoryDescription, setCategoryDescription] = useState('')
  const [categoryCreateError, setCategoryCreateError] = useState('')
  const [categoryCreateMessage, setCategoryCreateMessage] = useState('')
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
    setCategoriesLoading(true)
    setCategoriesError('')
    try {
      const { data } = await API.get('/categories')
      setCategories(extractList<Category>(data))
    } catch (err) {
      console.error('categories', err)
      setCategories([])
      setCategoriesError('No se pudieron cargar las categorias.')
    } finally {
      setCategoriesLoading(false)
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
      setProducts(extractList<Product>(data))
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
    setPreview(resolveMediaUrl(item.image_url || (item as any).image) || '')
    setImageFile(null)
    setFormError('')
    setFormMessage('')
  }

  const onImage = (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0] || null
    setFormError('')

    if (file && !ALLOWED_IMAGE_MIME_TYPES.has(file.type)) {
      setImageFile(null)
      setFormError('Formato no permitido. Usa JPG, PNG, WEBP o AVIF.')
      return
    }

    if (file && file.size > MAX_IMAGE_SIZE_BYTES) {
      setImageFile(null)
      setFormError('La imagen supera 5MB. Selecciona un archivo mas liviano.')
      return
    }

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
      let imageUrl = ''
      if (imageFile) {
        const upload = await uploadProductImage(imageFile)
        imageUrl = upload.url
      }

      const payload: Record<string, string> = {}
      Object.entries(form).forEach(([key, value]) => {
        if (key === 'id') return
        if (value !== null && value !== '') payload[key] = String(value)
      })
      if (imageUrl) payload.image_url = imageUrl

      let response
      if (form.id) {
        response = await API.put(`/products/${form.id}`, payload)
      } else {
        response = await API.post('/products', payload)
      }

      setFormMessage('Guardado correctamente')
      await fetchProducts()
      const newProduct = response.data?.data || response.data
      if (newProduct) startEdit(newProduct)
    } catch (err: any) {
      console.error('save', err)
      setFormError(err.response?.data?.message || 'No se pudo guardar')
    } finally {
      setSaving(false)
    }
  }

  const createCategory = async () => {
    const trimmedName = categoryName.trim()
    const trimmedDescription = categoryDescription.trim()

    if (!storeId) {
      setCategoryCreateError('Primero debes crear tu tienda para poder crear categorias.')
      return
    }

    if (!trimmedName) {
      setCategoryCreateError('Escribe un nombre para la categoria.')
      return
    }

    setCreatingCategory(true)
    setCategoryCreateError('')
    setCategoryCreateMessage('')

    try {
      const { data } = await API.post('/categories', {
        name: trimmedName,
        description: trimmedDescription || undefined,
      })

      const createdCategory = (data?.data || data) as Category
      await fetchCategories()

      if (createdCategory?.id) {
        setForm((prev) => ({ ...prev, category_id: String(createdCategory.id) }))
      }

      setCategoryName('')
      setCategoryDescription('')
      setCategoryCreateMessage('Categoria creada correctamente.')
    } catch (err: any) {
      console.error('create category', err)
      setCategoryCreateError(err.response?.data?.message || 'No se pudo crear la categoria.')
    } finally {
      setCreatingCategory(false)
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
    if (debounceRef.current) window.clearTimeout(debounceRef.current)
    debounceRef.current = window.setTimeout(() => {
      fetchProducts()
    }, 400)

    return () => {
      if (debounceRef.current) window.clearTimeout(debounceRef.current)
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [filters.search, filters.status, storeId])

  return (
    <div className="space-y-5 sm:space-y-6">
      <div className="flex items-center justify-between gap-4">
        <div>
          <p className="text-[13px] text-slate-600 dark:text-white/60">Productos de mi tienda</p>
          <h1 className="text-[22px] font-semibold text-slate-900 dark:text-white sm:text-[26px]">Gestion de productos</h1>
        </div>
        <Button onClick={startCreate}>Nuevo producto</Button>
      </div>

      <GlassCard className="dashboard-section">
        <div>
          <h2 className="dashboard-section-title">Catalogo actual</h2>
          <p className="dashboard-section-subtitle">Filtra y revisa rapidamente el inventario publicado de tu tienda.</p>
        </div>

        <div className="flex flex-wrap items-center gap-3">
          <Input
            value={filters.search}
            onChange={(e) => setFilters((prev) => ({ ...prev, search: e.target.value }))}
            className="w-full md:w-64"
            placeholder="Buscar por nombre"
          />
          <Select
            value={filters.status}
            onChange={(e) => setFilters((prev) => ({ ...prev, status: e.target.value }))}
            className="w-full md:w-48"
          >
            <option value="">Todos</option>
            <option value="active">Activos</option>
            <option value="draft">Borrador</option>
          </Select>
        </div>

        <div className="divide-y divide-slate-200 dark:divide-white/10">
          {products.map((item) => {
            const img = resolveMediaUrl(item.image_url || (item as any).image)
            const price = typeof item.price === 'number' ? item.price : Number(item.price ?? 0)
            const stock = typeof item.stock === 'number' ? item.stock : Number(item.stock ?? 0)

            return (
              <div key={item.id} className="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex items-center gap-3">
                  <div className="h-14 w-14 overflow-hidden rounded-2xl border border-slate-200 bg-white/70 dark:border-white/10">
                    {img ? (
                      <img
                        src={img}
                        alt={item.name}
                        className="h-full w-full object-cover"
                        loading="lazy"
                        decoding="async"
                      />
                    ) : null}
                  </div>

                  <div className="min-w-0">
                    <p className="truncate text-[13px] font-semibold text-slate-900 dark:text-white">{item.name}</p>
                    <p className="text-[12px] text-slate-500 dark:text-white/60">
                      {item.category?.name || 'Sin categoria'}
                      <span className="mx-2 opacity-50">•</span>
                      Stock: <span className="font-semibold text-slate-900 dark:text-white">{stock}</span>
                    </p>
                  </div>
                </div>

                <div className="flex flex-wrap items-center gap-3 sm:justify-end">
                  <p className="text-[16px] font-extrabold text-slate-900 dark:text-white">
                    ${formatPrice(price)}
                  </p>

                  <Badge variant={item.status === 'active' ? 'success' : 'neutral'} className="capitalize">
                    {item.status || 'draft'}
                  </Badge>

                  <button className={buttonVariants('ghost', 'h-10 text-[13px] font-medium')} onClick={() => startEdit(item)}>
                    Editar
                  </button>
                  <button className={buttonVariants('ghost', 'h-10 text-[13px] font-medium text-red-600 dark:text-red-300')} onClick={() => remove(item)}>
                    Eliminar
                  </button>
                </div>
              </div>
            )
          })}

          {!products.length && !loading && (
            <div className="py-10 text-center text-[13px] text-slate-600 dark:text-white/60">
              Aun no tienes productos.
            </div>
          )}
        </div>

        {loading && <div className="text-[13px] text-slate-600 dark:text-white/60">Cargando...</div>}
        {error && <div className="text-[13px] text-red-600 dark:text-red-200">{error}</div>}
      </GlassCard>

      <GlassCard className="dashboard-section">
        <div>
          <h2 className="dashboard-section-title">{form.id ? 'Editar producto' : 'Nuevo producto'}</h2>
          <p className="dashboard-section-subtitle">Completa los campos del producto y manten visible el estado del catalogo.</p>
        </div>

        <form className="grid gap-4 md:grid-cols-2" onSubmit={save}>
          <Input
            label="Nombre"
            value={form.name}
            required
            onChange={(e) => setForm((prev) => ({ ...prev, name: e.target.value }))}
          />
          <Input
            label="Slug (opcional)"
            value={form.slug}
            onChange={(e) => setForm((prev) => ({ ...prev, slug: e.target.value }))}
          />
          <Input
            label="Precio"
            type="number"
            min="0"
            step="0.01"
            value={form.price}
            required
            onChange={(e) => setForm((prev) => ({ ...prev, price: e.target.value }))}
          />
          <Input
            label="Stock"
            type="number"
            min="0"
            step="1"
            value={form.stock}
            required
            onChange={(e) => setForm((prev) => ({ ...prev, stock: e.target.value }))}
          />

          <Select
            label="Categoria"
            value={form.category_id}
            required
            onChange={(e) => setForm((prev) => ({ ...prev, category_id: e.target.value }))}
          >
            <option value="">
              {categoriesLoading ? 'Cargando categorias...' : categories.length ? 'Selecciona' : 'Sin categorias disponibles'}
            </option>
            {!categoriesLoading && categories.map((cat) => (
              <option key={cat.id} value={cat.id}>
                {cat.name}
              </option>
            ))}
          </Select>

          {!categoriesLoading && categories.length === 0 && (
            <div className="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/5">
              <p className="text-[13px] font-medium text-slate-700 dark:text-white/80">
                No tienes categorias. Crea una para continuar.
              </p>

              {!storeId && (
                <div className="space-y-2">
                  <p className="text-[12px] text-slate-600 dark:text-white/60">
                    Necesitas crear tu tienda antes de crear categorias.
                  </p>
                  <Link to="/dashboard/store" className={buttonVariants('secondary', 'inline-flex h-10 text-[13px]')}>
                    Ir a crear tienda
                  </Link>
                </div>
              )}

              <div className="grid gap-3 md:grid-cols-[1fr_auto]">
                <Input
                  placeholder="Nombre de categoria"
                  value={categoryName}
                  onChange={(e) => setCategoryName(e.target.value)}
                  required
                />
                <Button type="button" onClick={createCategory} loading={creatingCategory} className="w-full md:w-auto" disabled={!storeId}>
                  {creatingCategory ? 'Creando...' : 'Crear categoria'}
                </Button>

                <Textarea
                  placeholder="Descripcion (opcional)"
                  rows={2}
                  value={categoryDescription}
                  onChange={(e) => setCategoryDescription(e.target.value)}
                  containerClassName="md:col-span-2"
                />
              </div>

              {categoryCreateMessage && <span className="text-[12px] text-green-600 dark:text-green-300">{categoryCreateMessage}</span>}
              {categoryCreateError && <span className="text-[12px] text-red-600 dark:text-red-300">{categoryCreateError}</span>}
            </div>
          )}

          <Select
            label="Estado"
            value={form.status}
            onChange={(e) => setForm((prev) => ({ ...prev, status: e.target.value }))}
          >
            <option value="active">Activo</option>
            <option value="draft">Borrador</option>
          </Select>

          <Textarea
            label="Descripcion"
            rows={3}
            value={form.description}
            onChange={(e) => setForm((prev) => ({ ...prev, description: e.target.value }))}
            containerClassName="md:col-span-2"
          />

          <div className="space-y-2">
            <label className="text-[13px] font-medium text-slate-600 dark:text-white/70">Imagen</label>
            <label className={buttonVariants('secondary', 'cursor-pointer w-fit')}>
              Subir imagen
              <input type="file" accept="image/jpeg,image/png,image/webp,image/avif" onChange={onImage} className="hidden" />
            </label>
            {preview && (
              <div className="mt-2 h-28 w-28 overflow-hidden rounded-2xl border border-slate-200 dark:border-white/10">
                <img src={preview} alt="Preview del producto" className="h-full w-full object-cover" />
              </div>
            )}
          </div>

          <div className="md:col-span-2 flex flex-wrap items-center gap-3">
            <Button type="submit" className="w-full md:w-auto" loading={saving}>
              {saving ? 'Guardando...' : form.id ? 'Actualizar' : 'Crear producto'}
            </Button>
            {formMessage && <span className="text-[12px] text-green-600 dark:text-green-300">{formMessage}</span>}
            {formError && <span className="text-[12px] text-red-600 dark:text-red-300">{formError}</span>}
            {categoriesError && <span className="text-[12px] text-red-600 dark:text-red-300">{categoriesError}</span>}
          </div>
        </form>
      </GlassCard>
    </div>
  )
}

