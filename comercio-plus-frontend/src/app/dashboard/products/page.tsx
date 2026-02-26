import { Suspense, lazy, useEffect, useMemo, useRef, useState, type ChangeEvent, type FormEvent } from 'react'
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
import { lookupMerchantProductCode } from '@/services/productCodeLookup'
import ProductMethodSelector, { type InputMethod } from '@/components/products/create/ProductMethodSelector'
import ProductScannerKeyboardPanel, { type ScanState } from '@/components/products/create/ProductScannerKeyboardPanel'

const ProductScannerCameraModal = lazy(() => import('@/components/products/ProductScannerCameraModal'))

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

interface ScannerFeedbackState {
  type: 'ok' | 'error' | 'info'
  message: string
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
  code_type: 'barcode' as 'barcode' | 'qr' | 'sku',
  code_value: '',
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

const getProductCodes = (product: Product) =>
  product.codes || product.product_codes || product.productCodes || []

const getPrimaryProductCode = (product: Product) => {
  const codes = getProductCodes(product)
  if (!codes.length) return null
  return codes.find((code) => code.is_primary) || codes[0]
}

const parseScanCode = (
  rawCode: string,
  fallbackType: 'barcode' | 'qr' | 'sku',
): { type: 'barcode' | 'qr' | 'sku'; value: string } | null => {
  const normalized = rawCode.trim()
  if (!normalized) return null

  const lower = normalized.toLowerCase()
  if (lower.startsWith('sku:')) {
    const value = normalized.slice(4).trim()
    return value ? { type: 'sku', value } : null
  }

  if (lower.startsWith('barcode:')) {
    const value = normalized.slice(8).trim()
    return value ? { type: 'barcode', value } : null
  }

  if (lower.startsWith('qr:')) {
    const value = normalized.slice(3).trim()
    return value ? { type: 'qr', value } : null
  }

  return {
    type: fallbackType,
    value: normalized,
  }
}

export default function ManageProducts() {
  const navigate = useNavigate()
  const location = useLocation()
  const params = useParams<{ id: string }>()

  const formSectionRef = useRef<HTMLDivElement | null>(null)
  const listEndRef = useRef<HTMLDivElement | null>(null)
  const scannerInputRef = useRef<HTMLInputElement | null>(null)

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
  const [entryMethod, setEntryMethod] = useState<InputMethod>('manual')

  const [filters, setFilters] = useState({ search: '' })
  const [activeTab, setActiveTab] = useState<ProductTab>('all')
  const [form, setForm] = useState(INITIAL_FORM)
  const [toast, setToast] = useState<ToastState | null>(null)
  const [shouldScrollToListEnd, setShouldScrollToListEnd] = useState(false)
  const [scanCodeInput, setScanCodeInput] = useState('')
  const [scanLookupBusy, setScanLookupBusy] = useState(false)
  const [scanConsecutiveFailures, setScanConsecutiveFailures] = useState(0)
  const [scanFeedback, setScanFeedback] = useState<ScannerFeedbackState | null>(null)
  const [cameraModalOpen, setCameraModalOpen] = useState(false)

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
    setEntryMethod('manual')
    setScanCodeInput('')
    setScanLookupBusy(false)
    setScanConsecutiveFailures(0)
    setScanFeedback(null)
    setCameraModalOpen(false)
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
    setEntryMethod('keyboard')
    setShowForm(true)
    navigate('/dashboard/products/create')
    scrollToForm()
  }

  const applyProductToForm = (item: Product) => {
    const primaryCode = getPrimaryProductCode(item)
    setForm({
      id: item.id,
      name: item.name || '',
      slug: item.slug || '',
      code_type: (primaryCode?.type as 'barcode' | 'qr' | 'sku') || 'barcode',
      code_value: primaryCode?.value || '',
      price: item.price?.toString() || '',
      stock: item.stock?.toString() || '',
      category_id: item.category_id?.toString() || '',
      description: item.description || '',
      status: item.status || 'active',
    })
    setPreview(resolveMediaUrl(item.image_url || item.image) || '')
    setImageFile(null)
    setFormError('')
    setScanCodeInput('')
    setScanConsecutiveFailures(0)
    setScanFeedback(null)
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

  const registerScannerFailure = (message: string) => {
    setScanConsecutiveFailures((previous) => {
      const next = previous + 1

      if (next >= 3) {
        setEntryMethod('manual')
        setScanFeedback({
          type: 'error',
          message: 'No pude validar el codigo en 3 intentos. Continua en modo manual.',
        })
      } else {
        setScanFeedback({
          type: 'error',
          message,
        })
      }

      return next
    })
  }

  const handleScannerLookup = async (rawCode?: string) => {
    if (scanLookupBusy) return

    const parsed = parseScanCode(rawCode ?? scanCodeInput, form.code_type)
    if (!parsed) {
      registerScannerFailure('Escanea un codigo valido o escribelo manualmente.')
      return
    }

    setScanLookupBusy(true)
    setScanFeedback(null)
    setForm((previous) => ({
      ...previous,
      code_type: parsed.type,
      code_value: parsed.value,
    }))

    try {
      const response = await lookupMerchantProductCode({
        code: parsed.value,
        code_type: parsed.type,
      })

      const foundProduct = response?.data?.product
      if (response?.data?.found && foundProduct) {
        applyProductToForm(foundProduct)
        setForm((previous) => ({
          ...previous,
          code_type: parsed.type,
          code_value: parsed.value,
        }))
        setEntryMethod('manual')
        setScanConsecutiveFailures(0)
        setScanFeedback({
          type: 'ok',
          message: `Codigo encontrado. Cargue "${foundProduct.name}" para editarlo.`,
        })
        return
      }

      setScanConsecutiveFailures(0)
      setScanFeedback({
        type: 'info',
        message: 'Codigo disponible. Completa el formulario para crear el producto.',
      })
      setEntryMethod('manual')
    } catch (err: any) {
      const apiError = err?.response?.data
      const isNotFound = apiError?.error_code === 'PRODUCT_NOT_FOUND'

      if (isNotFound) {
        registerScannerFailure('No encuentro ese codigo. Puedes continuar creando el producto manualmente.')
      } else {
        registerScannerFailure(apiError?.message || 'No pude consultar el codigo en este momento.')
      }
    } finally {
      setScanLookupBusy(false)
      setScanCodeInput('')
      window.setTimeout(() => {
        scannerInputRef.current?.focus()
        scannerInputRef.current?.select()
      }, 30)
    }
  }

  const openCameraScanner = () => {
    setEntryMethod('camera')
    setCameraModalOpen(true)
  }

  const handleEntryMethodChange = (method: InputMethod) => {
    if (method === 'camera') {
      openCameraScanner()
      return
    }

    setEntryMethod(method)
    setCameraModalOpen(false)
  }

  const handleCameraDetected = (rawCode: string) => {
    setEntryMethod('manual')
    setScanCodeInput(rawCode)
    void handleScannerLookup(rawCode)
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

      const payload: Record<string, unknown> = {}
      Object.entries(form).forEach(([key, value]) => {
        if (key === 'id') return
        if (key === 'code_type' || key === 'code_value') return
        if (value !== null && value !== '') payload[key] = String(value)
      })

      const codeValue = form.code_value.trim()
      if (codeValue !== '') {
        payload.codes = [
          {
            type: form.code_type,
            value: codeValue,
            is_primary: true,
          },
        ]
      }

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
        setEntryMethod('keyboard')
        setShouldScrollToListEnd(true)
        showToast('Repuesto creado correctamente.', 'success')
      } else {
        if (responseProduct?.id) {
          applyProductToForm(responseProduct)
        }
        setScanConsecutiveFailures(0)
        setScanFeedback(null)
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
      setEntryMethod('keyboard')
      setShowForm(true)
      scrollToForm()
      return
    }

    if (location.pathname.endsWith('/edit') && params.id) {
      const productToEdit = products.find((item) => String(item.id) === String(params.id))
      if (productToEdit) {
        openEdit(productToEdit, false)
      }
      setEntryMethod('manual')
      return
    }

    if (location.pathname === '/dashboard/products') {
      setShowForm(false)
    }
  }, [location.pathname, params.id, products])

  useEffect(() => {
    if (!showForm || entryMethod !== 'keyboard') return

    const timeoutId = window.setTimeout(() => {
      scannerInputRef.current?.focus()
      scannerInputRef.current?.select()
    }, 70)

    return () => {
      window.clearTimeout(timeoutId)
    }
  }, [entryMethod, showForm])

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
      const codes = getProductCodes(item).map((code) => code.value).join(' ')
      const searchable = `${item.name || ''} ${categoryName} ${slug} ${item.id || ''} ${codes}`.toLowerCase()
      return searchable.includes(searchTerm)
    })
  }, [orderedProducts, filters.search, activeTab])

  const totalProducts = products.length
  const activeProducts = products.filter((item) => normalizeStatus(item.status) === 'active').length
  const outOfStockProducts = products.filter((item) => toNumber(item.stock) === 0).length
  const catalogValue = products.reduce((sum, item) => sum + toNumber(item.price) * toNumber(item.stock), 0)
  const keyboardScanState: ScanState =
    scanFeedback?.type === 'error'
      ? 'error'
      : scanFeedback?.type === 'ok'
        ? 'success'
        : scanCodeInput.trim().length > 0
          ? 'ready'
          : 'idle'
  const keyboardStatusMessage = scanFeedback?.message || (scanConsecutiveFailures > 0 ? `Intentos fallidos: ${scanConsecutiveFailures}/3` : undefined)

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
        <GlassCard className="h-[108px] border-transparent !bg-[linear-gradient(135deg,#FF6B35_0%,#E65A2B_100%)] p-4 !text-white sm:p-4">
          <p className="truncate text-[32px] font-black leading-none sm:text-[40px]">{totalProducts}</p>
          <p className="mt-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-white/90">Total productos</p>
        </GlassCard>

        <GlassCard className="h-[108px] border-[#E2E8F0] p-4 sm:p-4">
          <p className="truncate text-[32px] font-black leading-none sm:text-[40px]">{activeProducts}</p>
          <p className="mt-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-[#64748B]">Activos</p>
        </GlassCard>

        <GlassCard className="h-[108px] border-[#E2E8F0] p-4 sm:p-4">
          <p className="truncate text-[32px] font-black leading-none sm:text-[40px]">{outOfStockProducts}</p>
          <p className="mt-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-[#64748B]">Sin stock</p>
        </GlassCard>

        <GlassCard className="h-[108px] border-[#E2E8F0] p-4 sm:p-4">
          <p className="truncate text-[26px] font-black leading-none tracking-[-0.02em] sm:text-[34px]">${formatPrice(catalogValue)}</p>
          <p className="mt-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-[#64748B]">Valor catalogo</p>
        </GlassCard>
      </div>

      <div ref={formSectionRef}>
        <GlassCard className="border-[#DDE3EF] bg-[linear-gradient(145deg,#FFFFFF_0%,#F8FAFC_100%)] p-4 shadow-[0_20px_40px_rgba(15,23,42,0.08)] sm:p-5">
          {!showForm ? (
            <div className="flex flex-col gap-4 rounded-2xl border border-[#E2E8F0] bg-[linear-gradient(120deg,#FFF7ED_0%,#FFEDD5_55%,#FFE4D6_100%)] px-5 py-5 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <p className="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#9A3412]">Nuevo producto</p>
                <h3 className="mt-1 text-[24px] font-black leading-none tracking-[-0.02em] text-[#0F172A] sm:text-[30px]">Nuevo repuesto / accesorio</h3>
                <p className="mt-2 text-[14px] text-[#475569]">Completa los datos y publica en el catalogo.</p>
              </div>

              <Button onClick={openCreate} className="h-11 rounded-xl px-6 text-[14px] font-semibold sm:self-end">
                <Icon name="plus" size={16} />
                Crear producto
              </Button>
            </div>
          ) : (
            <>
              <div className="mb-4 flex items-start justify-between gap-3 border-b border-[#E2E8F0] pb-3">
                <div>
                  <h3 className="text-[24px] font-bold leading-tight text-[#0F172A]">{form.id ? 'Editar producto' : 'Nuevo producto'}</h3>
                  <p className="text-[13px] text-[#64748B]">Completa los datos y publica en tu catalogo.</p>
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

              <div className="mb-4">
                <ProductMethodSelector active={entryMethod} onChange={handleEntryMethodChange} disabled={saving} />
              </div>

              {entryMethod === 'keyboard' && (
                <ProductScannerKeyboardPanel
                  inputRef={scannerInputRef}
                  code={scanCodeInput}
                  scanState={keyboardScanState}
                  statusMessage={keyboardStatusMessage}
                  disabled={saving}
                  busy={scanLookupBusy}
                  onCodeChange={setScanCodeInput}
                  onSubmit={() => void handleScannerLookup()}
                />
              )}

              <form className="space-y-3" onSubmit={save}>
                <div className="grid gap-3 lg:grid-cols-2">
                  <div className="space-y-3">
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
                      <Select
                        label="Tipo de codigo"
                        value={form.code_type}
                        onChange={(event) =>
                          setForm((previous) => ({
                            ...previous,
                            code_type: event.target.value as 'barcode' | 'qr' | 'sku',
                          }))
                        }
                      >
                        <option value="barcode">Codigo de barras</option>
                        <option value="qr">QR</option>
                        <option value="sku">SKU</option>
                      </Select>

                      <Input
                        label="Codigo (opcional)"
                        value={form.code_value}
                        onChange={(event) => setForm((previous) => ({ ...previous, code_value: event.target.value }))}
                        placeholder="Ej: 7701234567890"
                      />
                    </div>

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
                  </div>

                  <div className="space-y-3">
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
                      rows={5}
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
                  </div>
                </div>

                {formError && <p className="text-[12px] text-red-600">{formError}</p>}
                {categoriesError && <p className="text-[12px] text-red-600">{categoriesError}</p>}

                <div className="flex flex-wrap items-center justify-end gap-2 pt-1">
                  <Button type="button" variant="outline" onClick={closeForm} className="h-10 rounded-lg px-5 text-[13px]">
                    Cerrar
                  </Button>
                  <Button type="submit" loading={saving} className="h-10 rounded-lg px-5 text-[13px] font-semibold">
                    {saving ? 'Guardando...' : form.id ? 'Actualizar' : 'Guardar producto'}
                  </Button>
                </div>
              </form>
            </>
          )}
        </GlassCard>
      </div>

      <GlassCard className="flex flex-col border-[#DDE3EF] bg-[linear-gradient(145deg,#FFFFFF_0%,#F8FAFC_100%)] p-0 shadow-[0_20px_45px_rgba(15,23,42,0.08)]">
        <div className="border-b border-[#E2E8F0] px-4 py-4 sm:px-5">
          <div className="flex flex-wrap items-end justify-between gap-3">
            <div>
              <h2 className="text-[28px] font-black leading-none tracking-[-0.025em] sm:text-[32px]">Productos</h2>
              <p className="mt-1 text-[13px] text-[#64748B]">Gestiona tu catalogo</p>
            </div>
            <div className="flex items-center gap-2">
              <button
                type="button"
                className="inline-flex h-9 items-center rounded-lg border border-[#D1D5DB] bg-white px-3 text-[12px] font-semibold text-[#334155] transition hover:bg-[#F8FAFC]"
              >
                📥 Importar
              </button>
              <Button onClick={openCreate} className="h-9 rounded-lg px-3.5 text-[12px] font-semibold">
                + Nuevo
              </Button>
            </div>
          </div>

          <div className="mt-3 flex flex-wrap items-center gap-2">
            <button
              type="button"
              onClick={() => setActiveTab('all')}
              className={`rounded-lg border px-4 py-2 text-[12px] font-semibold transition ${
                activeTab === 'all'
                  ? 'border-[#0F172A] bg-[#0F172A] text-white'
                  : 'border-[#CBD5E1] bg-white text-[#334155] hover:bg-[#F8FAFC]'
              }`}
            >
              Todos ({totalProducts})
            </button>
            <button
              type="button"
              onClick={() => setActiveTab('active')}
              className={`rounded-lg border px-4 py-2 text-[12px] font-semibold transition ${
                activeTab === 'active'
                  ? 'border-[#0F172A] bg-[#0F172A] text-white'
                  : 'border-[#CBD5E1] bg-white text-[#334155] hover:bg-[#F8FAFC]'
              }`}
            >
              Activos ({activeProducts})
            </button>
            <button
              type="button"
              onClick={() => setActiveTab('out_of_stock')}
              className={`rounded-lg border px-4 py-2 text-[12px] font-semibold transition ${
                activeTab === 'out_of_stock'
                  ? 'border-[#0F172A] bg-[#0F172A] text-white'
                  : 'border-[#CBD5E1] bg-white text-[#334155] hover:bg-[#F8FAFC]'
              }`}
            >
              Sin stock ({outOfStockProducts})
            </button>
          </div>
        </div>

        <div className="px-4 py-3 sm:px-5">
          <div className="mb-3 max-w-[380px]">
            <input
              value={filters.search}
              onChange={(event) => setFilters({ search: event.target.value })}
              className="h-10 w-full rounded-xl border border-[#D1D5DB] bg-white px-3 text-[13px] text-[#0F172A] outline-none transition focus:border-[#FF6A00] focus:ring-2 focus:ring-[#FF6A00]/20"
              placeholder="🔍 Buscar por nombre, categoria, codigo o ID"
            />
          </div>

          {loading && <p className="text-[13px] text-[#64748B]">Cargando productos...</p>}
          {error && <p className="mb-3 text-[13px] text-red-600">{error}</p>}

          {!loading && filteredProducts.length === 0 && (
            <div className="rounded-xl border border-dashed border-[#CBD5E1] px-4 py-8 text-center">
              <p className="text-[13px] text-[#64748B]">No hay productos para este filtro.</p>
            </div>
          )}

          {!loading && filteredProducts.length > 0 && (
            <div className="overflow-x-auto rounded-xl border border-[#E2E8F0] bg-white">
              <table className="w-full min-w-[920px]">
                <thead>
                  <tr className="border-b border-[#E2E8F0] bg-[#F8FAFC]">
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-[#64748B]">Producto</th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-[#64748B]">SKU/Codigo</th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-[#64748B]">Precio</th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-[#64748B]">Stock</th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-[#64748B]">Estado</th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-[#64748B]">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredProducts.map((item) => {
                    const imageUrl = resolveMediaUrl(item.image_url || item.image)
                    const price = toNumber(item.price)
                    const stock = toNumber(item.stock)
                    const isActive = normalizeStatus(item.status) === 'active'
                    const primaryCode = getPrimaryProductCode(item)

                    return (
                      <tr key={item.id} className="border-b border-[#EEF2F7] last:border-b-0 transition-colors hover:bg-[#FAFAFA]">
                        <td className="px-4 py-3">
                          <div className="flex items-center gap-2.5">
                            <div className="h-10 w-10 overflow-hidden rounded-lg border border-[#E2E8F0] bg-[#F8FAFC]">
                              {imageUrl ? (
                                <img src={imageUrl} alt={item.name} className="h-full w-full object-cover" loading="lazy" decoding="async" />
                              ) : (
                                <div className="flex h-full w-full items-center justify-center text-[#94A3B8]">
                                  <Icon name="package" size={16} />
                                </div>
                              )}
                            </div>
                            <div className="min-w-0">
                              <p className="truncate text-[13px] font-semibold text-[#0F172A]">{item.name}</p>
                              <p className="truncate text-[11px] text-[#64748B]">{item.category?.name || 'Sin categoria'}</p>
                            </div>
                          </div>
                        </td>
                        <td className="px-4 py-3">
                          <p className="font-mono text-[11px] text-[#64748B]">
                            {primaryCode ? `${primaryCode.type.toUpperCase()}: ${primaryCode.value}` : `ID-${item.id}`}
                          </p>
                        </td>
                        <td className="px-4 py-3 text-[13px] font-bold text-[#0F172A]">
                          ${formatPrice(price)}
                        </td>
                        <td className="px-4 py-3 text-[13px] font-semibold text-[#334155]">{stock}</td>
                        <td className="px-4 py-3">
                          <Badge
                            variant={isActive ? 'success' : 'warning'}
                            className={
                              isActive
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                : 'border-amber-200 bg-amber-50 text-amber-700'
                            }
                          >
                            {isActive ? 'Activo' : 'Borrador'}
                          </Badge>
                        </td>
                        <td className="px-4 py-3">
                          <div className="flex items-center gap-2">
                            <button
                              type="button"
                              onClick={() => openEdit(item)}
                              className={buttonVariants(
                                'secondary',
                                'h-8 rounded-lg border border-sky-200 bg-sky-50 px-3 text-[11px] font-semibold text-sky-700 shadow-none hover:bg-sky-100',
                              )}
                            >
                              ✏️
                            </button>
                            <button
                              type="button"
                              onClick={() => remove(item)}
                              className={buttonVariants(
                                'danger',
                                'h-8 rounded-lg border border-rose-200 bg-rose-50 px-3 text-[11px] font-semibold text-rose-700 shadow-none hover:bg-rose-100',
                              )}
                            >
                              🗑️
                            </button>
                          </div>
                        </td>
                      </tr>
                    )
                  })}
                </tbody>
              </table>
              <div ref={listEndRef} />
            </div>
          )}
        </div>
      </GlassCard>

      <Suspense fallback={null}>
        <ProductScannerCameraModal
          open={cameraModalOpen}
          onClose={() => {
            setCameraModalOpen(false)
            if (entryMethod === 'camera') {
              setEntryMethod('manual')
            }
          }}
          onDetected={handleCameraDetected}
        />
      </Suspense>
    </div>
  )
}
