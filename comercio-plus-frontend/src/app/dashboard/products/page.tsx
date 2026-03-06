import { Suspense, lazy, useCallback, useEffect, useMemo, useRef, useState, type ChangeEvent, type FormEvent } from 'react'
import { Link, useLocation, useNavigate, useParams } from 'react-router-dom'
import API from '@/lib/api'
import Button, { buttonVariants } from '@/components/ui/button'
import Input from '@/components/ui/Input'
import Select from '@/components/ui/Select'
import Textarea from '@/components/ui/Textarea'
import GlassCard from '@/components/ui/GlassCard'
import { ErpBadge, ErpBtn, ErpFilterSelect, ErpKpiCard, ErpPageHeader, ErpSearchBar } from '@/components/erp'
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

const isProductTab = (value: string): value is ProductTab =>
  value === 'all' || value === 'active' || value === 'out_of_stock'

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

const buildInternalProductCode = (storeId: number | null) => {
  const storeSegment = storeId ? String(storeId).padStart(3, '0') : '000'
  const timestampSegment = Date.now().toString().slice(-6)
  const randomSegment = String(Math.floor(Math.random() * 90) + 10)
  return `CP-${storeSegment}-${timestampSegment}${randomSegment}`
}

const getInventoryEmoji = (name: string, categoryName = '') => {
  const source = `${name} ${categoryName}`.toLowerCase()
  if (source.includes('llanta') || source.includes('neumatico')) return '🏍️'
  if (source.includes('aceite') || source.includes('lubricante')) return '🛢️'
  if (source.includes('cadena') || source.includes('kit')) return '⛓️'
  if (source.includes('casco')) return '🪖'
  if (source.includes('filtro')) return '🔩'
  return '📦'
}

function ProductThumbnail({
  src,
  name,
  fallbackEmoji,
}: {
  src: string
  name: string
  fallbackEmoji: string
}) {
  const [failed, setFailed] = useState(false)
  const [hovered, setHovered] = useState(false)
  const [previewPos, setPreviewPos] = useState<{ top: number; left: number } | null>(null)
  const triggerRef = useRef<HTMLDivElement | null>(null)
  const imageSrc = String(src || '').trim()
  const showImage = imageSrc.length > 0 && !failed

  const updatePreviewPos = useCallback(() => {
    const node = triggerRef.current
    if (!node) return

    const rect = node.getBoundingClientRect()
    const previewWidth = Math.min(window.innerWidth * 0.65, 520)
    const previewHeight = Math.min(window.innerHeight * 0.72, 520)
    const margin = 12

    const maxLeft = window.innerWidth - previewWidth - margin
    const left = Math.min(Math.max(rect.left, margin), Math.max(margin, maxLeft))

    let top = rect.top - previewHeight - margin
    if (top < margin) {
      top = rect.bottom + margin
    }

    const maxTop = window.innerHeight - previewHeight - margin
    top = Math.min(Math.max(top, margin), Math.max(margin, maxTop))

    setPreviewPos({ top, left })
  }, [])

  useEffect(() => {
    if (!hovered || !showImage) return

    updatePreviewPos()

    const onViewportChange = () => updatePreviewPos()
    window.addEventListener('scroll', onViewportChange, true)
    window.addEventListener('resize', onViewportChange)

    return () => {
      window.removeEventListener('scroll', onViewportChange, true)
      window.removeEventListener('resize', onViewportChange)
    }
  }, [hovered, showImage, updatePreviewPos])

  return (
    <div
      ref={triggerRef}
      className="relative h-10 w-10 overflow-visible"
      onMouseEnter={() => {
        if (!showImage) return
        setHovered(true)
        updatePreviewPos()
      }}
      onMouseLeave={() => setHovered(false)}
    >
      <div className="h-10 w-10 overflow-hidden rounded-lg border border-[#E2E8F0] bg-white">
        {showImage ? (
          <img
            src={imageSrc}
            alt={name}
            className="h-full w-full object-cover"
            loading="lazy"
            decoding="async"
            onError={() => setFailed(true)}
          />
        ) : (
          <div className="flex h-full w-full items-center justify-center text-[16px]">{fallbackEmoji}</div>
        )}
      </div>

      {showImage && hovered && previewPos ? (
        <div
          className="pointer-events-none fixed z-[120] rounded-xl border border-[#D4E1F2] bg-white p-2 shadow-[0_18px_40px_rgba(15,23,42,0.22)]"
          style={{ top: `${previewPos.top}px`, left: `${previewPos.left}px` }}
        >
          <div className="flex max-h-[70vh] max-w-[70vw] items-center justify-center rounded-lg bg-[#F8FAFC] p-2">
            <img
              src={imageSrc}
              alt={name}
              className="h-auto max-h-[65vh] w-auto max-w-[65vw] object-contain"
              loading="lazy"
              decoding="async"
            />
          </div>
        </div>
      ) : null}
    </div>
  )
}

export default function ManageProducts() {
  const navigate = useNavigate()
  const location = useLocation()
  const params = useParams<{ id: string }>()

  const formSectionRef = useRef<HTMLDivElement | null>(null)
  const listEndRef = useRef<HTMLDivElement | null>(null)
  const scannerInputRef = useRef<HTMLInputElement | null>(null)
  const formNameInputRef = useRef<HTMLInputElement | null>(null)

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
  const [deleting, setDeleting] = useState(false)
  const [imageRefreshKey, setImageRefreshKey] = useState(0)

  const categorySeedRef = useRef(false)

  const showToast = (message: string, variant: ToastVariant) => {
    setToast({ id: Date.now(), message, variant })
  }

  const focusNameField = () => {
    window.setTimeout(() => {
      formNameInputRef.current?.focus()
      formNameInputRef.current?.select()
    }, 60)
  }

  const generateAutomaticCode = () => {
    const generatedCode = buildInternalProductCode(storeId)
    setForm((previous) => ({
      ...previous,
      code_type: previous.code_type === 'qr' ? 'qr' : 'barcode',
      code_value: generatedCode,
    }))
    showToast('Codigo interno generado automaticamente.', 'success')
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

  const scrollToForm = useCallback(() => {
    window.setTimeout(() => {
      formSectionRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }, 60)
  }, [])

  const openCreate = () => {
    clearForm()
    setEntryMethod('keyboard')
    setShowForm(true)
    navigate('/dashboard/products/create')
    scrollToForm()
  }

  const applyProductToForm = useCallback((item: Product) => {
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
  }, [])

  const openEdit = useCallback((item: Product, withNavigate = true) => {
    applyProductToForm(item)
    setShowForm(true)
    if (withNavigate) {
      navigate(`/dashboard/products/${item.id}/edit`)
    }
    scrollToForm()
  }, [applyProductToForm, navigate, scrollToForm])

  const ensureMotorcycleCategories = useCallback(async (initialCategories: Category[]) => {
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
  }, [storeId])

  const fetchCategories = useCallback(async () => {
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
  }, [ensureMotorcycleCategories])

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

  const fetchProducts = useCallback(async () => {
    if (!storeId) {
      setProducts([])
      return
    }

    setLoading(true)
    setError('')

    try {
      const fetchedProducts: Product[] = []
      const requestTimestamp = Date.now()
      const perPage = 100
      let page = 1
      let lastPage = 1

      do {
        const { data } = await API.get('/products', {
          params: {
            store_id: storeId,
            per_page: perPage,
            page,
            _t: requestTimestamp,
          },
        })

        fetchedProducts.push(...extractList<Product>(data))

        const nextLastPage = Number(data?.last_page || data?.meta?.last_page || 1)
        lastPage = Number.isFinite(nextLastPage) && nextLastPage > 0 ? nextLastPage : 1
        page += 1
      } while (page <= lastPage && page <= 30)

      const sortedProducts = [...fetchedProducts].sort((a, b) => toNumber(a.id) - toNumber(b.id))
      setProducts(sortedProducts)
    } catch (err: any) {
      console.error('products', err)
      setError(err.response?.data?.message || 'Error al cargar productos.')
    } finally {
      setLoading(false)
    }
  }, [storeId])

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
      let uploadedNewImage = false

      if (imageFile) {
        const upload = await uploadProductImage(imageFile)
        imageUrl = upload.url
        uploadedNewImage = true
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

      if (uploadedNewImage) {
        setImageRefreshKey((previous) => previous + 1)
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
    if (deleting) return

    if (!window.confirm(`Se eliminara "${item.name}". Esta accion no se puede deshacer. Deseas continuar?`)) {
      return
    }

    setDeleting(true)
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
    } finally {
      setDeleting(false)
    }
  }

  useEffect(() => {
    fetchStore()
  }, [])

  useEffect(() => {
    if (!storeId) return
    void fetchCategories()
    void fetchProducts()
  }, [fetchCategories, fetchProducts, storeId])

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
  }, [location.pathname, openEdit, params.id, products, scrollToForm])

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
  const catalogValue = products.reduce((sum, item) => sum + toNumber(item.price), 0)
  const keyboardScanState: ScanState =
    scanFeedback?.type === 'error'
      ? 'error'
      : scanFeedback?.type === 'ok'
        ? 'success'
        : scanCodeInput.trim().length > 0
          ? 'ready'
          : 'idle'
  const keyboardStatusMessage = scanFeedback?.message || (scanConsecutiveFailures > 0 ? `Intentos fallidos: ${scanConsecutiveFailures}/3` : undefined)
  const tabFilterOptions: Array<{ value: string; label: string }> = [
    { value: 'all', label: `Todos (${totalProducts})` },
    { value: 'active', label: `Activos (${activeProducts})` },
    { value: 'out_of_stock', label: `Sin stock (${outOfStockProducts})` },
  ]
  const isCreateRoute = location.pathname === '/dashboard/products/create'

  return (
    <div
      className={`flex min-h-[calc(100vh-2.5rem)] flex-col gap-4 text-[#0F172A] ${
        isCreateRoute ? 'md:-mx-4 lg:-mx-6 xl:-mx-8' : ''
      }`}
    >
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

      {isCreateRoute ? (
        <div className="rounded-2xl border border-[#E2E8F0] bg-white px-4 py-3 shadow-[0_12px_30px_rgba(15,23,42,0.08)] sm:px-5">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <div className="flex items-center gap-3">
              <button
                type="button"
                onClick={() => navigate('/dashboard/products')}
                className="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#E2E8F0] text-[#64748B] transition hover:bg-[#F8FAFC] hover:text-[#0F172A]"
                aria-label="Volver al catalogo"
              >
                <Icon name="arrow-left" size={16} />
              </button>
              <div>
                <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-[#94A3B8]">Panel de ventas / Productos</p>
                <h1 className="text-[24px] font-black leading-none text-[#0F172A]">Crear producto</h1>
                <p className="mt-1 text-[12px] text-[#64748B]">Flujo rapido con scanner + formulario por bloques + inventario.</p>
              </div>
            </div>
            <div className="flex flex-wrap gap-2">
              <Button type="button" variant="outline" onClick={closeForm} className="h-10 rounded-lg px-4 text-[12px]">
                Cancelar
              </Button>
              <Button type="submit" form="product-create-form" loading={saving} className="h-10 rounded-lg px-4 text-[12px] font-semibold">
                {saving ? 'Guardando...' : form.id ? 'Actualizar' : 'Guardar producto'}
              </Button>
            </div>
          </div>
        </div>
      ) : (
        <>
          <ErpPageHeader
            breadcrumb="Panel de ventas / Productos"
            title="Productos e inventario"
            subtitle="Diseno V2 con scanner, formulario por bloques y tabla operativa."
            actions={
              <ErpBtn
                variant="secondary"
                size="md"
                icon={<Icon name="refresh" size={14} />}
                onClick={() => void fetchProducts()}
                className="min-w-[140px] justify-center"
              >
                Recargar
              </ErpBtn>
            }
          />

          <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <ErpKpiCard
              label="Total productos"
              value={totalProducts}
              hint="Catalogo registrado"
              icon="package"
              iconBg="rgba(255,161,79,0.15)"
              iconColor="#FFA14F"
            />
            <ErpKpiCard
              label="Activos"
              value={activeProducts}
              hint="Disponibles para venta"
              icon="check-circle"
              iconBg="rgba(16,185,129,0.14)"
              iconColor="#10B981"
            />
            <ErpKpiCard
              label="Sin stock"
              value={outOfStockProducts}
              hint="Requieren reposicion"
              icon="alert"
              iconBg="rgba(239,68,68,0.14)"
              iconColor="#EF4444"
            />
            <ErpKpiCard
              label="Valor catalogo"
              value={`$${formatPrice(catalogValue)}`}
              hint="Suma de precios actuales"
              icon="dollar"
              iconBg="rgba(59,130,246,0.14)"
              iconColor="#3B82F6"
            />
          </div>
        </>
      )}

      <div ref={formSectionRef}>
        <GlassCard className="border-[#D4E1F2] bg-[linear-gradient(155deg,#FFFFFF_0%,#F8FAFC_55%,#EEF4FF_100%)] p-4 shadow-[0_24px_48px_rgba(15,23,42,0.12)] sm:p-5">
          {!showForm ? (
            <div className="flex flex-col gap-4 rounded-2xl border border-[#E2E8F0] bg-[linear-gradient(120deg,#FFF7ED_0%,#FFEDD5_55%,#FFE4D6_100%)] px-5 py-5 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <p className="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#9A3412]">Nuevo producto</p>
                <h3 className="mt-1 text-[24px] font-black leading-none tracking-[-0.02em] text-[#0F172A] sm:text-[30px]">Nuevo repuesto / accesorio</h3>
                <p className="mt-2 text-[14px] text-[#475569]">
                  Escanea codigo, completa por bloques y publica en el catalogo.
                </p>
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

              <div className="mb-4 rounded-2xl border-2 border-[#FED7AA] bg-[linear-gradient(135deg,#FFF7ED_0%,#FFEDD5_62%,#FFE4D6_100%)] p-4 shadow-[0_12px_30px_rgba(251,146,60,0.15)]">
                <div className="mb-3 flex flex-wrap items-start justify-between gap-2">
                  <div>
                    <p className="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#9A3412]">Busqueda rapida</p>
                    <h4 className="mt-1 text-[18px] font-black leading-tight text-[#7C2D12]">Scanner y consulta de codigo</h4>
                    <p className="text-[12px] text-[#9A3412]">Escanea por USB/camara o ingresa manual para crear o editar.</p>
                  </div>
                  <span className="inline-flex rounded-full border border-[#FDBA74] bg-[#FFF7ED] px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.08em] text-[#C2410C]">
                    {entryMethod === 'keyboard' ? 'Modo USB' : entryMethod === 'camera' ? 'Modo camara' : 'Modo manual'}
                  </span>
                </div>

                <ProductMethodSelector active={entryMethod} onChange={handleEntryMethodChange} disabled={saving} />

                <div className="mt-3">
                  {entryMethod === 'keyboard' ? (
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
                  ) : (
                    <div className="rounded-xl border border-dashed border-[#FDBA74] bg-white/75 px-4 py-3 text-[12px] text-[#92400E]">
                      {entryMethod === 'camera'
                        ? 'Usa el escaner de camara para detectar el codigo y completar automaticamente.'
                        : 'Modo manual activo. Puedes escribir codigo, SKU y datos del producto directamente.'}
                    </div>
                  )}
                </div>

                {scanFeedback && (
                  <div
                    className={`mt-3 flex flex-wrap items-center justify-between gap-3 rounded-xl border px-3 py-3 text-[12px] ${
                      scanFeedback.type === 'ok'
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                        : scanFeedback.type === 'error'
                          ? 'border-red-200 bg-red-50 text-red-800'
                          : 'border-orange-200 bg-orange-50 text-orange-800'
                    }`}
                  >
                    <p className="font-semibold">{scanFeedback.message}</p>
                    <div className="flex flex-wrap gap-2">
                      {scanFeedback.type === 'ok' && form.id ? (
                        <ErpBtn variant="success" size="sm" onClick={() => navigate(`/dashboard/products/${form.id}/edit`)}>
                          Editar producto
                        </ErpBtn>
                      ) : null}
                      {scanFeedback.type !== 'ok' ? (
                        <ErpBtn variant="primary" size="sm" onClick={focusNameField}>
                          Crear producto
                        </ErpBtn>
                      ) : null}
                    </div>
                  </div>
                )}

              </div>

              <form id="product-create-form" className="space-y-4" onSubmit={save}>
                <section className="rounded-2xl border border-[#E2E8F0] bg-white p-4 shadow-[0_8px_20px_rgba(15,23,42,0.05)]">
                  <div className="mb-3 flex items-center gap-2 border-b border-[#E2E8F0] pb-2">
                    <span className="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-[#FFF7ED] text-[14px]">📝</span>
                    <h4 className="text-[14px] font-black text-[#0F172A]">Informacion basica</h4>
                  </div>

                  <div className="grid gap-3 lg:grid-cols-2">
                    <Input
                      ref={formNameInputRef}
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
                    <Select
                      label="Estado"
                      value={form.status}
                      onChange={(event) => setForm((previous) => ({ ...previous, status: event.target.value }))}
                    >
                      <option value="active">Activo</option>
                      <option value="draft">Borrador</option>
                    </Select>
                  </div>

                  {!categoriesLoading && categories.length === 0 && (
                    <div className="mt-3 space-y-3 rounded-xl border border-[#D9E4F3] bg-[linear-gradient(145deg,#FFFFFF_0%,#F8FAFC_70%,#F1F5F9_100%)] p-3 shadow-[0_10px_24px_rgba(15,23,42,0.06)]">
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
                </section>

                <section className="rounded-2xl border border-[#E2E8F0] bg-white p-4 shadow-[0_8px_20px_rgba(15,23,42,0.05)]">
                  <div className="mb-3 flex items-center gap-2 border-b border-[#E2E8F0] pb-2">
                    <span className="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-[#FFF7ED] text-[14px]">📊</span>
                    <h4 className="text-[14px] font-black text-[#0F172A]">Codigo de barras</h4>
                  </div>
                  <div className="grid gap-3 lg:grid-cols-[1fr_2fr_auto] lg:items-end">
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
                      label="Codigo"
                      value={form.code_value}
                      onChange={(event) => setForm((previous) => ({ ...previous, code_value: event.target.value }))}
                      placeholder="Ej: 7701234567890 o CP-000-00000000"
                    />
                    <ErpBtn type="button" variant="secondary" size="md" onClick={generateAutomaticCode} className="h-11">
                      Generar automatico
                    </ErpBtn>
                  </div>
                </section>

                <section className="rounded-2xl border border-[#E2E8F0] bg-white p-4 shadow-[0_8px_20px_rgba(15,23,42,0.05)]">
                  <div className="mb-3 flex items-center gap-2 border-b border-[#E2E8F0] pb-2">
                    <span className="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-[#FFF7ED] text-[14px]">📦</span>
                    <h4 className="text-[14px] font-black text-[#0F172A]">Inventario y precios</h4>
                  </div>
                  <div className="grid gap-3 lg:grid-cols-2">
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
                </section>

                <section className="rounded-2xl border border-[#E2E8F0] bg-white p-4 shadow-[0_8px_20px_rgba(15,23,42,0.05)]">
                  <div className="mb-3 flex items-center gap-2 border-b border-[#E2E8F0] pb-2">
                    <span className="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-[#FFF7ED] text-[14px]">🖼️</span>
                    <h4 className="text-[14px] font-black text-[#0F172A]">Descripcion e imagen</h4>
                  </div>
                  <div className="grid gap-3 lg:grid-cols-2">
                    <Textarea
                      label="Descripcion"
                      rows={5}
                      value={form.description}
                      onChange={(event) => setForm((previous) => ({ ...previous, description: event.target.value }))}
                    />
                    <div className="space-y-2 rounded-xl border border-dashed border-[#D9E4F3] bg-[linear-gradient(150deg,#FFFFFF_0%,#F8FAFC_100%)] p-3">
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

                      <p className="text-[11px] text-[#64748B]">Arrastra una imagen o haz clic para buscar. PNG/JPG/WEBP/AVIF hasta 5MB.</p>

                      {preview && (
                        <div className="h-24 w-24 overflow-hidden rounded-xl border border-[#E2E8F0]">
                          <img src={preview} alt="Vista previa del producto" className="h-full w-full object-cover" />
                        </div>
                      )}
                    </div>
                  </div>
                </section>

                {formError && <p className="text-[12px] text-red-600">{formError}</p>}
                {categoriesError && <p className="text-[12px] text-red-600">{categoriesError}</p>}

                <div className="flex flex-wrap items-center justify-end gap-2 border-t border-[#E2E8F0] pt-3">
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

      <GlassCard className="flex flex-col border-[#D4E1F2] bg-[linear-gradient(155deg,#FFFFFF_0%,#F8FAFC_56%,#EEF4FF_100%)] p-0 shadow-[0_24px_52px_rgba(15,23,42,0.12)]">
        <div className={`border-b border-[#E2E8F0] py-4 ${isCreateRoute ? 'px-3 sm:px-4' : 'px-4 sm:px-5'}`}>
          <div className="flex flex-wrap items-end justify-between gap-3">
            <div>
              <h2 className="text-[28px] font-black leading-none tracking-[-0.025em] sm:text-[32px]">Inventario de productos</h2>
              <p className="mt-1 text-[13px] text-[#64748B]">Filtra, edita y elimina productos de tu catalogo.</p>
            </div>
            <div className="flex flex-wrap gap-2">
              <Link to="/dashboard/inventory/import">
                <ErpBtn variant="secondary" size="md" icon={<Icon name="upload" size={14} />}>
                  Importar inventario
                </ErpBtn>
              </Link>
              <ErpBtn variant="primary" size="md" icon={<Icon name="plus" size={14} />} onClick={openCreate}>
                Nuevo producto
              </ErpBtn>
            </div>
          </div>

          <div className="mt-4 grid gap-2 lg:grid-cols-[minmax(0,1fr)_220px_220px]">
            <ErpSearchBar
              value={filters.search}
              onChange={(value: string) => setFilters({ search: value })}
              placeholder="Buscar por nombre, categoria, codigo o ID"
            />
            <ErpFilterSelect
              value={activeTab}
              onChange={(value: string) => {
                if (isProductTab(value)) setActiveTab(value)
              }}
              options={tabFilterOptions}
              placeholder="Filtro de estado"
            />
            <div className="flex items-center justify-start gap-2 rounded-xl border border-[#D9E4F3] bg-[linear-gradient(145deg,#FFFFFF_0%,#F8FAFC_100%)] px-3 py-1.5 shadow-[0_8px_18px_rgba(15,23,42,0.05)]">
              <ErpBadge status="active" label={`${activeProducts} activos`} />
              <ErpBadge status="critical" label={`${outOfStockProducts} sin stock`} />
            </div>
          </div>
        </div>

        <div className={`${isCreateRoute ? 'px-3 py-3 sm:px-4' : 'px-4 py-3 sm:px-5'}`}>
          {loading && <p className="text-[13px] text-[#64748B]">Cargando productos...</p>}
          {error && <p className="mb-3 text-[13px] text-red-600">{error}</p>}

          {!loading && filteredProducts.length === 0 && (
            <div className="rounded-xl border border-dashed border-[#CBD5E1] px-4 py-8 text-center">
              <p className="text-[13px] text-[#64748B]">No hay productos para este filtro.</p>
            </div>
          )}

          {!loading && filteredProducts.length > 0 && (
            <div className="overflow-x-auto rounded-xl border border-[#D4E1F2] bg-[linear-gradient(150deg,#FFFFFF_0%,#F8FAFC_100%)] shadow-[0_14px_32px_rgba(15,23,42,0.08)]">
              <table className="w-full min-w-[820px]">
                <thead>
                  <tr className="border-b border-[#E2E8F0] bg-[#F8FAFC]">
                    <th className="px-3 py-2.5 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-[#64748B]">Producto</th>
                    <th className="px-3 py-2.5 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-[#64748B]">SKU/Codigo</th>
                    <th className="px-3 py-2.5 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-[#64748B]">Precio</th>
                    <th className="px-3 py-2.5 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-[#64748B]">Stock</th>
                    <th className="px-3 py-2.5 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-[#64748B]">Estado</th>
                    <th className="px-3 py-2.5 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-[#64748B]">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredProducts.map((item) => {
                    const imageUrlBase = resolveMediaUrl(item.image_url || item.image) || ''
                    const imageUrl = imageUrlBase
                      ? `${imageUrlBase}${imageUrlBase.includes('?') ? '&' : '?'}v=${imageRefreshKey}`
                      : ''
                    const price = toNumber(item.price)
                    const stock = toNumber(item.stock)
                    const isActive = normalizeStatus(item.status) === 'active'
                    const primaryCode = getPrimaryProductCode(item)
                    const stockStatus: 'critical' | 'low' | 'ok' = stock <= 0 ? 'critical' : stock <= 5 ? 'low' : 'ok'
                    const stockTextClass = stock <= 0 ? 'text-rose-600' : stock <= 5 ? 'text-amber-600' : 'text-emerald-600'
                    const stockStateLabel = !isActive ? 'Borrador' : stock <= 0 ? 'Agotado' : stock <= 5 ? 'Bajo stock' : 'Activo'
                    const stockStateClass =
                      !isActive
                        ? 'border-slate-200 bg-slate-100 text-slate-700'
                        : stock <= 0
                          ? 'border-rose-200 bg-rose-100 text-rose-700'
                          : stock <= 5
                            ? 'border-amber-200 bg-amber-100 text-amber-700'
                            : 'border-emerald-200 bg-emerald-100 text-emerald-700'
                    const productEmoji = getInventoryEmoji(item.name || '', item.category?.name || '')

                    return (
                      <tr key={item.id} className="border-b border-[#EEF2F7] last:border-b-0 transition-colors hover:bg-[#FAFAFA]">
                        <td className="px-3 py-2.5">
                          <div className="flex items-center gap-2.5">
                            <ProductThumbnail src={imageUrl} name={item.name} fallbackEmoji={productEmoji} />
                            <div className="min-w-0">
                              <p className="truncate text-[13px] font-semibold text-[#0F172A]">{item.name}</p>
                              <p className="truncate text-[11px] text-[#64748B]">{item.category?.name || 'Sin categoria'}</p>
                            </div>
                          </div>
                        </td>
                        <td className="px-3 py-2.5">
                          <p className="font-mono text-[11px] text-[#64748B]">
                            {primaryCode ? `${primaryCode.type.toUpperCase()}: ${primaryCode.value}` : `ID-${item.id}`}
                          </p>
                        </td>
                        <td className="px-3 py-2.5 text-[13px] font-bold text-[#0F172A]">
                          ${formatPrice(price)}
                        </td>
                        <td className="px-3 py-2.5">
                          <div className="flex items-center gap-2">
                            <span className={`text-[13px] font-black ${stockTextClass}`}>{stock} un.</span>
                            <ErpBadge status={stockStatus} />
                          </div>
                        </td>
                        <td className="px-3 py-2.5">
                          <span className={`inline-flex rounded-full border px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.06em] ${stockStateClass}`}>
                            {stockStateLabel}
                          </span>
                        </td>
                        <td className="px-3 py-2.5">
                          <div className="flex items-center gap-1.5">
                            <ErpBtn
                              variant="secondary"
                              size="sm"
                              onClick={() => openEdit(item)}
                              icon={<Icon name="edit" size={13} />}
                              className="border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100"
                            >
                              Editar
                            </ErpBtn>
                            <ErpBtn
                              variant="danger"
                              size="sm"
                              onClick={() => void remove(item)}
                              icon={<Icon name="trash" size={13} />}
                              disabled={deleting}
                            >
                              {deleting ? 'Eliminando...' : 'Eliminar'}
                            </ErpBtn>
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
