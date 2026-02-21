import { useEffect, useMemo, useRef, useState, type ChangeEvent, type FormEvent } from 'react'
import { Link, useLocation, useNavigate, useParams } from 'react-router-dom'
import API from '@/lib/api'
import Button, { buttonVariants } from '@/components/ui/button'
import Input from '@/components/ui/Input'
import Select from '@/components/ui/Select'
import Textarea from '@/components/ui/Textarea'
import GlassCard from '@/components/ui/GlassCard'
import Badge from '@/components/ui/Badge'
import { Icon } from '@/components/Icon'
import type { Category, Product, Store } from '@/types/api'
import { resolveMediaUrl, formatPrice } from '@/lib/format'
import { extractList } from '@/lib/api-response'
import { uploadProductImage } from '@/services/uploads'

const MAX_IMAGE_SIZE_BYTES = 5 * 1024 * 1024
const ALLOWED_IMAGE_MIME_TYPES = new Set(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])

const MOTORCYCLE_CATEGORY_PRESETS = [
  {
    name: 'Cascos y proteccion',
    description: 'Cascos, guantes, chaquetas y elementos de seguridad para motociclista.',
  },
  { name: 'Accesorios para moto', description: 'Accesorios para personalizar y mejorar tu moto.' },
  { name: 'Frenos y suspension', description: 'Pastillas, discos, amortiguadores y componentes de suspension.' },
  { name: 'Llantas y rines', description: 'Llantas, rines y componentes para ruedas de moto.' },
  {
    name: 'Lubricantes y mantenimiento',
    description: 'Aceites, filtros, liquidos y cuidado general de la moto.',
  },
  {
    name: 'Repuestos generales',
    description: 'Repuestos de motor, transmision, electricidad y partes varias.',
  },
]

type ProductTab = 'all' | 'active' | 'out_of_stock'

type ToastVariant = 'success' | 'error'

interface ToastState {
  id: number
  message: string
  variant: ToastVariant
}

const normalizeCategoryLabel = (value: string) =>
  value
    .trim()
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')

const INITIAL_FORM = {
  id: null as number | null,
  name: '',
  slug: '',
  price: '',
  stock: '',
  category_id: '',
  description: '',
  status: 'active',
}

const toNumber = (value: unknown) => {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : 0
}

const normalizeStatus = (status: unknown) => String(status ?? '').trim().toLowerCase()

const extractCategoryList = (payload: unknown): Category[] => {
  if (Array.isArray(payload)) return payload as Category[]

  if (payload && typeof payload === 'object') {
    const record = payload as Record<string, unknown>
    if (Array.isArray(record.data)) return record.data as Category[]
  }

  return extractList<Category>(payload)
}

export default function ManageProducts() {
  const navigate = useNavigate()
  const location = useLocation()
  const params = useParams<{ id: string }>()

  const formSectionRef = useRef<HTMLDivElement | null>(null)
  const listEndRef = useRef<HTMLDivElement | null>(null)

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
  const [preview, setPreview] = useState('')
  const [imageFile, setImageFile] = useState<File | null>(null)
  const [storeId, setStoreId] = useState<number | null>(null)
  const [showForm, setShowForm] = useState(false)

  const [filters, setFilters] = useState({ search: '' })
  const [activeTab, setActiveTab] = useState<ProductTab>('all')
  const [form, setForm] = useState(INITIAL_FORM)
  const [toast, setToast] = useState<ToastState | null>(null)
  const [shouldScrollToListEnd, setShouldScrollToListEnd] = useState(false)

  const categorySeedRef = useRef(false)

  const showToast = (message: string, variant: ToastVariant) => {
    setToast({ id: Date.now(), message, variant })
  }

  const upsertProductInList = (product: Product) => {
    setProducts((previous) => {
      const exists = previous.some((item) => Number(item.id) === Number(product.id))
      const next = exists
        ? previous.map((item) => (Number(item.id) === Number(product.id) ? { ...item, ...product } : item))
        : [...previous, product]
      return next.sort((a, b) => toNumber(a.id) - toNumber(b.id))
    })
  }

  const clearForm = () => {
    setForm(INITIAL_FORM)
    setPreview('')
    setImageFile(null)
    setFormError('')
  }

  const closeForm = () => {
    clearForm()
    setShowForm(false)
    navigate('/dashboard/products')
  }

  const scrollToForm = () => {
    window.setTimeout(() => {
      formSectionRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }, 60)
  }

  const openCreate = () => {
    clearForm()
    setShowForm(true)
    navigate('/dashboard/products/create')
    scrollToForm()
  }

  const applyProductToForm = (item: Product) => {
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
    setPreview(resolveMediaUrl(item.image_url || item.image) || '')
    setImageFile(null)
    setFormError('')
  }

  const openEdit = (item: Product, withNavigate = true) => {
    applyProductToForm(item)
    setShowForm(true)
    if (withNavigate) {
      navigate(`/dashboard/products/${item.id}/edit`)
    }
    scrollToForm()
  }

  const ensureMotorcycleCategories = async (initialCategories: Category[]) => {
    if (!storeId || categorySeedRef.current) return initialCategories

    const existingByName = new Set(initialCategories.map((item) => normalizeCategoryLabel(item.name || '')))
    const missingPresets = MOTORCYCLE_CATEGORY_PRESETS.filter(
      (preset) => !existingByName.has(normalizeCategoryLabel(preset.name)),
    )

    if (!missingPresets.length) return initialCategories

    categorySeedRef.current = true

    try {
      await Promise.allSettled(
        missingPresets.map((preset) =>
          API.post('/categories', {
            name: preset.name,
            description: preset.description,
          }),
        ),
      )

      const { data } = await API.get('/categories')
      return extractCategoryList(data)
    } finally {
      categorySeedRef.current = false
    }
  }

  const fetchCategories = async () => {
    setCategoriesLoading(true)
    setCategoriesError('')

    try {
      const { data } = await API.get('/categories')
      const fetchedCategories = extractCategoryList(data)
      setCategories(fetchedCategories)

      // Evita bloquear la UI en la carga inicial.
      if (!fetchedCategories.length && !categorySeedRef.current) {
        void ensureMotorcycleCategories(fetchedCategories).then((seeded) => {
          if (seeded.length) {
            setCategories(seeded)
          }
        })
      }
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
        return
      }

      setStoreId(null)
      setError('Primero crea una tienda para gestionar productos.')
    } catch (err: any) {
      console.error('stores', err)
      if (err.response?.status === 404) {
        setStoreId(null)
        setError('Primero crea una tienda para gestionar productos.')
      } else {
        setError(err.response?.data?.message || 'Error al cargar tu tienda.')
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
          store_id: storeId,
          per_page: 50,
        },
      })

      const fetchedProducts = extractList<Product>(data)
      const sortedProducts = [...fetchedProducts].sort((a, b) => toNumber(a.id) - toNumber(b.id))
      setProducts(sortedProducts)
    } catch (err: any) {
      console.error('products', err)
      setError(err.response?.data?.message || 'Error al cargar productos.')
    } finally {
      setLoading(false)
    }
  }

  const onImage = (event: ChangeEvent<HTMLInputElement>) => {
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

    if (preview.startsWith('blob:')) {
      URL.revokeObjectURL(preview)
    }

    setImageFile(file)
    setPreview(file ? URL.createObjectURL(file) : '')
  }

  const save = async (event: FormEvent) => {
    event.preventDefault()

    if (!storeId) {
      setFormError('Primero debes crear una tienda.')
      return
    }

    setSaving(true)
    setFormError('')

    const isCreating = !form.id

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

      const response = form.id
        ? await API.put(`/products/${form.id}`, payload)
        : await API.post('/products', payload)

      const responseProduct = (response.data?.data || response.data) as Product | undefined
      if (responseProduct?.id) {
        upsertProductInList(responseProduct)
      } else {
        await fetchProducts()
      }

      if (isCreating) {
        clearForm()
        setShowForm(true)
        setShouldScrollToListEnd(true)
        showToast('Repuesto creado correctamente.', 'success')
      } else {
        if (responseProduct?.id) {
          applyProductToForm(responseProduct)
        }
        showToast('Repuesto actualizado correctamente.', 'success')
      }
    } catch (err: any) {
      console.error('save', err)
      const message = err.response?.data?.message || 'Error al guardar.'
      setFormError(message)
      showToast(message, 'error')
    } finally {
      setSaving(false)
    }
  }

  const createCategory = async () => {
    const trimmedName = categoryName.trim()
    const trimmedDescription = categoryDescription.trim()

    if (!storeId) {
      setCategoryCreateError('Primero debes crear tu tienda para crear categorias.')
      navigate('/dashboard/store')
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
        setForm((previous) => ({ ...previous, category_id: String(createdCategory.id) }))
      }

      setCategoryName('')
      setCategoryDescription('')
      setCategoryCreateMessage('Categoria creada correctamente.')
      showToast('Categoria creada correctamente.', 'success')
    } catch (err: any) {
      console.error('create category', err)
      const message = err.response?.data?.message || 'No se pudo crear la categoria.'
      setCategoryCreateError(message)
      showToast(message, 'error')
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

      if (form.id === item.id) {
        closeForm()
      }

      showToast('Repuesto eliminado correctamente.', 'success')
    } catch (err: any) {
      console.error('delete', err)
      const message = err.response?.data?.message || 'No se pudo eliminar.'
      showToast(message, 'error')
    }
  }

  useEffect(() => {
    fetchStore()
  }, [])

  useEffect(() => {
    if (!storeId) return
    fetchCategories()
    fetchProducts()
  }, [storeId])

  useEffect(() => {
    if (!toast) return

    const timeoutId = window.setTimeout(() => {
      setToast(null)
    }, 4000)

    return () => {
      window.clearTimeout(timeoutId)
    }
  }, [toast])

  useEffect(() => {
    if (!shouldScrollToListEnd) return

    window.setTimeout(() => {
      listEndRef.current?.scrollIntoView({ behavior: 'smooth', block: 'end' })
      setShouldScrollToListEnd(false)
    }, 120)
  }, [products, shouldScrollToListEnd])

  useEffect(() => {
    if (!preview.startsWith('blob:')) return

    return () => {
      URL.revokeObjectURL(preview)
    }
  }, [preview])

  useEffect(() => {
    if (location.pathname === '/dashboard/products/create') {
      clearForm()
      setShowForm(true)
      scrollToForm()
      return
    }

    if (location.pathname.endsWith('/edit') && params.id) {
      const productToEdit = products.find((item) => String(item.id) === String(params.id))
      if (productToEdit) {
        openEdit(productToEdit, false)
      }
      return
    }

    if (location.pathname === '/dashboard/products') {
      setShowForm(false)
    }
  }, [location.pathname, params.id, products])

  const orderedProducts = useMemo(
    () => [...products].sort((a, b) => toNumber(a.id) - toNumber(b.id)),
    [products],
  )

  const filteredProducts = useMemo(() => {
    const searchTerm = filters.search.trim().toLowerCase()

    return orderedProducts.filter((item) => {
      const stock = toNumber(item.stock)
      const status = normalizeStatus(item.status)

      if (activeTab === 'active' && status !== 'active') return false
      if (activeTab === 'out_of_stock' && stock !== 0) return false

      if (!searchTerm) return true

      const categoryName = item.category?.name || ''
      const slug = item.slug || ''
      const searchable = `${item.name || ''} ${categoryName} ${slug} ${item.id || ''}`.toLowerCase()
      return searchable.includes(searchTerm)
    })
  }, [orderedProducts, filters.search, activeTab])

  const totalProducts = products.length
  const activeProducts = products.filter((item) => normalizeStatus(item.status) === 'active').length
  const outOfStockProducts = products.filter((item) => toNumber(item.stock) === 0).length
  const catalogValue = products.reduce((sum, item) => sum + toNumber(item.price) * toNumber(item.stock), 0)

  return (
    <div className="flex min-h-[calc(100vh-2.5rem)] flex-col gap-4 text-[#0F172A]">
      {toast && (
        <div
          className={`fixed right-5 top-5 z-50 flex w-full max-w-sm items-start gap-3 rounded-xl border px-4 py-3 shadow-lg ${
            toast.variant === 'success'
              ? 'border-emerald-200 bg-emerald-50 text-emerald-900'
              : 'border-rose-200 bg-rose-50 text-rose-900'
          }`}
        >
          <Icon name={toast.variant === 'success' ? 'check-circle' : 'x-circle'} size={18} className="mt-0.5" />
          <p className="flex-1 text-[13px] font-medium">{toast.message}</p>
          <button
            type="button"
            onClick={() => setToast(null)}
            className="rounded-md p-1 text-current/70 transition hover:bg-black/5 hover:text-current"
            aria-label="Cerrar notificacion"
          >
            <Icon name="x" size={16} />
          </button>
        </div>
      )}

      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p className="text-[12px] font-medium text-[#64748B]">Productos de mi tienda</p>
          <h1 className="font-display text-[42px] leading-[1.05] tracking-[-0.03em]">Mis Productos</h1>
        </div>

        <Button onClick={openCreate} className="h-11 rounded-xl px-6 text-[14px] font-semibold">
          <Icon name="plus" size={16} />
          Nuevo producto
        </Button>
      </div>

      <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <GlassCard className="h-[108px] border-transparent bg-[linear-gradient(135deg,#FF6B35_0%,#E65A2B_100%)] p-4 text-white sm:p-4">
          <p className="text-[40px] font-black leading-none">{totalProducts}</p>
          <p className="mt-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-white/90">Total productos</p>
        </GlassCard>

        <GlassCard className="h-[108px] border-[#E2E8F0] p-4 sm:p-4">
          <p className="text-[40px] font-black leading-none">{activeProducts}</p>
          <p className="mt-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-[#64748B]">Activos</p>
        </GlassCard>

        <GlassCard className="h-[108px] border-[#E2E8F0] p-4 sm:p-4">
          <p className="text-[40px] font-black leading-none">{outOfStockProducts}</p>
          <p className="mt-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-[#64748B]">Sin stock</p>
        </GlassCard>

        <GlassCard className="h-[108px] border-[#E2E8F0] p-4 sm:p-4">
          <p className="text-[40px] font-black leading-none">${formatPrice(catalogValue)}</p>
          <p className="mt-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-[#64748B]">Valor catalogo</p>
        </GlassCard>
      </div>

      <div className="grid min-h-0 flex-1 gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(420px,480px)]">
        <GlassCard className="flex min-h-0 flex-col border-[#DDE3EF] bg-[linear-gradient(145deg,#FFFFFF_0%,#F8FAFC_100%)] p-0 shadow-[0_20px_45px_rgba(15,23,42,0.08)]">
          <div className="border-b border-[#E2E8F0] px-4 py-4 sm:px-5">
            <div className="flex flex-wrap items-end justify-between gap-3">
              <div>
                <h2 className="text-[32px] font-black leading-none tracking-[-0.025em]">Catalogo de repuestos</h2>
                <p className="mt-1 text-[13px] text-[#64748B]">Filtra y revisa tu inventario en segundos.</p>
              </div>

              <Input
                value={filters.search}
                onChange={(event) => setFilters({ search: event.target.value })}
                className="h-10 w-full text-[13px] sm:w-[260px]"
                placeholder="Buscar por nombre, categoria o ID"
              />
            </div>

            <div className="mt-3 inline-flex rounded-lg border border-[#E2E8F0] bg-white p-1">
              <button
                type="button"
                onClick={() => setActiveTab('all')}
                className={`rounded-md px-3 py-1.5 text-[12px] font-semibold transition ${
                  activeTab === 'all' ? 'bg-[#0F172A] text-white' : 'text-[#475569] hover:bg-[#F1F5F9]'
                }`}
              >
                Todos
              </button>
              <button
                type="button"
                onClick={() => setActiveTab('active')}
                className={`rounded-md px-3 py-1.5 text-[12px] font-semibold transition ${
                  activeTab === 'active' ? 'bg-[#0F172A] text-white' : 'text-[#475569] hover:bg-[#F1F5F9]'
                }`}
              >
                Activos
              </button>
              <button
                type="button"
                onClick={() => setActiveTab('out_of_stock')}
                className={`rounded-md px-3 py-1.5 text-[12px] font-semibold transition ${
                  activeTab === 'out_of_stock' ? 'bg-[#0F172A] text-white' : 'text-[#475569] hover:bg-[#F1F5F9]'
                }`}
              >
                Sin stock
              </button>
            </div>
          </div>

          <div className="min-h-0 flex-1 overflow-y-auto px-4 py-3 sm:px-5">
            {loading && <p className="text-[13px] text-[#64748B]">Cargando productos...</p>}
            {error && <p className="mb-3 text-[13px] text-red-600">{error}</p>}

            {!loading && filteredProducts.length === 0 && (
              <div className="rounded-xl border border-dashed border-[#CBD5E1] px-4 py-8 text-center">
                <p className="text-[13px] text-[#64748B]">No hay productos para este filtro.</p>
              </div>
            )}

            <div className="space-y-2">
              {filteredProducts.map((item) => {
                const imageUrl = resolveMediaUrl(item.image_url || item.image)
                const price = toNumber(item.price)
                const stock = toNumber(item.stock)
                const isActive = normalizeStatus(item.status) === 'active'

                return (
                  <div
                    key={item.id}
                    className="group flex flex-wrap items-center gap-3 rounded-xl border border-[#E2E8F0] bg-[linear-gradient(145deg,#FFFFFF_0%,#F8FAFC_100%)] px-3 py-2.5 shadow-[0_8px_20px_rgba(15,23,42,0.05)] transition hover:-translate-y-[1px] hover:border-[#CBD5E1]"
                  >
                    <div className="h-12 w-12 overflow-hidden rounded-xl border border-[#E2E8F0] bg-[#F8FAFC]">
                      {imageUrl ? (
                        <img src={imageUrl} alt={item.name} className="h-full w-full object-cover" loading="lazy" decoding="async" />
                      ) : (
                        <div className="flex h-full w-full items-center justify-center text-[#94A3B8]">
                          <Icon name="package" size={18} />
                        </div>
                      )}
                    </div>

                    <div className="min-w-0 flex-1">
                      <p className="truncate text-[14px] font-semibold text-[#0F172A]">{item.name}</p>
                      <p className="truncate text-[12px] text-[#64748B]">
                        {item.category?.name || 'Sin categoria'}
                        {' - '}
                        ID: {item.id}
                      </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-2 lg:ml-auto">
                      <Badge
                        variant={stock === 0 ? 'danger' : 'neutral'}
                        className={stock === 0 ? 'border-red-200 bg-red-50 text-red-700' : 'border-slate-200 bg-slate-100 text-slate-700'}
                      >
                        Stock: {stock}
                      </Badge>

                      <Badge
                        variant={isActive ? 'success' : 'warning'}
                        className={isActive ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700'}
                      >
                        {isActive ? 'Activo' : 'Borrador'}
                      </Badge>

                      <p className="min-w-[110px] text-right text-[20px] font-black tracking-[-0.02em] text-[#0F172A]">
                        ${formatPrice(price)}
                      </p>

                      <div className="flex items-center gap-2 opacity-100 transition md:opacity-0 md:group-hover:opacity-100">
                        <button
                          type="button"
                          onClick={() => openEdit(item)}
                          className={buttonVariants(
                            'secondary',
                            'h-8 rounded-full bg-[#0F5FA8] px-4 text-[12px] font-semibold shadow-none hover:bg-[#0B4D8A]',
                          )}
                        >
                          Editar
                        </button>
                        <button
                          type="button"
                          onClick={() => remove(item)}
                          className={buttonVariants(
                            'danger',
                            'h-8 rounded-full bg-[#EF476F] px-4 text-[12px] font-semibold shadow-none hover:bg-[#d83f65]',
                          )}
                        >
                          Eliminar
                        </button>
                      </div>
                    </div>
                  </div>
                )
              })}

              <div ref={listEndRef} />
            </div>
          </div>
        </GlassCard>

        <div ref={formSectionRef} className="min-h-0 xl:sticky xl:top-6 xl:self-start">
          <GlassCard className="flex h-full min-h-[520px] flex-col border-[#DDE3EF] bg-[linear-gradient(145deg,#FFFFFF_0%,#F8FAFC_100%)] p-4 shadow-[0_20px_40px_rgba(15,23,42,0.08)] sm:p-4">
            {!showForm ? (
              <div className="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-[#CBD5E1] bg-[#F8FAFC] px-4 py-8 text-center">
                <div className="mb-3 inline-flex h-11 w-11 items-center justify-center rounded-full bg-orange-100 text-orange-600">
                  <Icon name="package" size={18} />
                </div>
                <h3 className="text-[20px] font-bold text-[#0F172A]">Nuevo repuesto / accesorio</h3>
                <p className="mt-1 text-[13px] text-[#64748B]">Completa los datos y publica en el catalogo.</p>
                <Button onClick={openCreate} className="mt-4 h-10 rounded-xl px-5 text-[13px]">
                  Crear producto
                </Button>
              </div>
            ) : (
              <>
                <div className="mb-3 flex items-start justify-between gap-3 border-b border-[#E2E8F0] pb-3">
                  <div>
                    <h3 className="text-[22px] font-bold leading-tight text-[#0F172A]">
                      {form.id ? 'Editar producto' : 'Nuevo producto'}
                    </h3>
                    <p className="text-[12px] text-[#64748B]">Completa los datos y publica en tu catalogo.</p>
                  </div>

                  <button
                    type="button"
                    onClick={closeForm}
                    className="rounded-md border border-[#E2E8F0] p-1 text-[#64748B] transition hover:bg-[#F1F5F9]"
                    aria-label="Cerrar formulario"
                  >
                    <Icon name="x" size={16} />
                  </button>
                </div>

                <form className="flex min-h-0 flex-1 flex-col gap-2.5 pr-1" onSubmit={save}>
                  <Input
                    label="Nombre"
                    value={form.name}
                    required
                    onChange={(event) => setForm((previous) => ({ ...previous, name: event.target.value }))}
                  />

                  <Input
                    label="Slug (opcional)"
                    value={form.slug}
                    onChange={(event) => setForm((previous) => ({ ...previous, slug: event.target.value }))}
                  />

                  <div className="grid grid-cols-2 gap-3">
                    <Input
                      label="Precio"
                      type="number"
                      min="0"
                      step="0.01"
                      value={form.price}
                      required
                      onChange={(event) => setForm((previous) => ({ ...previous, price: event.target.value }))}
                    />

                    <Input
                      label="Stock"
                      type="number"
                      min="0"
                      step="1"
                      value={form.stock}
                      required
                      onChange={(event) => setForm((previous) => ({ ...previous, stock: event.target.value }))}
                    />
                  </div>

                  <Select
                    label="Categoria"
                    value={form.category_id}
                    required
                    onChange={(event) => setForm((previous) => ({ ...previous, category_id: event.target.value }))}
                  >
                    <option value="">
                      {categoriesLoading
                        ? 'Cargando categorias...'
                        : categories.length
                          ? 'Selecciona una categoria'
                          : 'Sin categorias disponibles'}
                    </option>
                    {!categoriesLoading &&
                      categories.map((category) => (
                        <option key={category.id} value={category.id}>
                          {category.name}
                        </option>
                      ))}
                  </Select>

                  {!categoriesLoading && categories.length === 0 && (
                    <div className="space-y-3 rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-3">
                      <p className="text-[12px] font-semibold text-[#334155]">No tienes categorias. Crea una para continuar.</p>

                      {!storeId && (
                        <div className="space-y-2">
                          <p className="text-[12px] text-[#64748B]">Primero debes crear tu tienda.</p>
                          <Link to="/dashboard/store" className={buttonVariants('secondary', 'inline-flex h-9 text-[12px]')}>
                            Ir a crear tienda
                          </Link>
                        </div>
                      )}

                      <Input
                        placeholder="Nombre de categoria"
                        value={categoryName}
                        onChange={(event) => setCategoryName(event.target.value)}
                      />

                      <Textarea
                        placeholder="Descripcion (opcional)"
                        rows={2}
                        value={categoryDescription}
                        onChange={(event) => setCategoryDescription(event.target.value)}
                      />

                      <Button
                        type="button"
                        onClick={createCategory}
                        loading={creatingCategory}
                        className="h-9 rounded-lg px-4 text-[12px]"
                      >
                        {creatingCategory ? 'Creando...' : 'Crear categoria'}
                      </Button>

                      {categoryCreateMessage && <p className="text-[12px] text-emerald-700">{categoryCreateMessage}</p>}
                      {categoryCreateError && <p className="text-[12px] text-red-600">{categoryCreateError}</p>}
                    </div>
                  )}

                  <Select
                    label="Estado"
                    value={form.status}
                    onChange={(event) => setForm((previous) => ({ ...previous, status: event.target.value }))}
                  >
                    <option value="active">Activo</option>
                    <option value="draft">Borrador</option>
                  </Select>

                  <Textarea
                    label="Descripcion"
                    rows={3}
                    value={form.description}
                    onChange={(event) => setForm((previous) => ({ ...previous, description: event.target.value }))}
                  />

                  <div className="space-y-2 rounded-xl border border-[#E2E8F0] p-3">
                    <label className={buttonVariants('secondary', 'h-9 cursor-pointer rounded-lg px-4 text-[12px]')}>
                      <Icon name="upload" size={14} />
                      Subir imagen
                      <input
                        type="file"
                        accept="image/jpeg,image/png,image/webp,image/avif"
                        onChange={onImage}
                        className="hidden"
                      />
                    </label>

                    {preview && (
                      <div className="h-24 w-24 overflow-hidden rounded-xl border border-[#E2E8F0]">
                        <img src={preview} alt="Vista previa del producto" className="h-full w-full object-cover" />
                      </div>
                    )}
                  </div>

                  {formError && <p className="text-[12px] text-red-600">{formError}</p>}
                  {categoriesError && <p className="text-[12px] text-red-600">{categoriesError}</p>}

                  <div className="flex items-center gap-2 pt-1">
                    <Button type="button" variant="outline" onClick={closeForm} className="h-10 rounded-lg px-4 text-[13px]">
                      Cerrar
                    </Button>
                    <Button type="submit" loading={saving} className="h-10 flex-1 rounded-lg px-4 text-[13px] font-semibold">
                      {saving ? 'Guardando...' : form.id ? 'Actualizar' : 'Guardar producto'}
                    </Button>
                  </div>
                </form>
              </>
            )}
          </GlassCard>
        </div>
      </div>
    </div>
  )
}
