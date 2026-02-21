import { useEffect, useState } from 'react'
import { useOutletContext } from 'react-router-dom'
import Badge from '@/components/Badge'
import Button from '@/components/Button'
import Card from '@/components/Card'
import { Icon } from '@/components/Icon'
import Input from '@/components/Input'
import API from '@/lib/api'
import { getApiMeta, getApiPayload } from '@/lib/apiPayload'
import { resolveMediaUrl } from '@/lib/format'
import { uploadStoreCover, uploadStoreLogo } from '@/services/uploads'
import { generatePreview, validateImage } from '@/utils/imageUtils'

interface StoreData {
  id: number
  name: string
  description: string
  email: string
  phone: string
  address: string
  isVisible: boolean
  status: 'active' | 'inactive'
  logo: string | null
  cover: string | null
}

type TaxRoundingMode = 'HALF_UP' | 'DOWN' | 'UP'

interface TaxSettingsForm {
  enable_tax: boolean
  tax_name: string
  tax_rate_percent: number
  prices_include_tax: boolean
  tax_rounding_mode: TaxRoundingMode
}

interface TaxPreview {
  example_input: number
  base_sin_iva: number
  iva: number
  total: number
}

interface DashboardOutletContext {
  store?: {
    id?: number | string
  } | null
}

type ToastVariant = 'success' | 'error'

interface ToastState {
  id: number
  message: string
  variant: ToastVariant
}

const fallbackStore: StoreData = {
  id: 0,
  name: 'Accesorios Biker Colombia',
  description: 'Accesorios, cascos y repuestos para motos en toda Colombia.',
  email: 'ventas@bikercolombia.com',
  phone: '+57 300 123 4567',
  address: 'Bogota, Colombia',
  isVisible: true,
  status: 'active',
  logo: null,
  cover: null,
}

const autoDraftValues = {
  name: ['Accesorios Biker Colombia', 'Artesanias del Valle'],
  description: [
    'Accesorios, cascos y repuestos para motos en toda Colombia.',
    'Productos artesanales hechos a mano con materiales naturales.',
  ],
  email: ['ventas@bikercolombia.com', 'contacto@artesanias.com'],
  phone: ['+57 300 123 4567', '+56 9 9999 9999'],
  address: ['Bogota, Colombia', 'Santiago, Chile'],
} as const

const EXAMPLE_INPUT = 100000

const TAX_DEFAULTS: TaxSettingsForm = {
  enable_tax: false,
  tax_name: 'IVA',
  tax_rate_percent: 19,
  prices_include_tax: false,
  tax_rounding_mode: 'HALF_UP',
}

const asNumber = (value: unknown): number | null => {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : null
}

const clamp = (value: number, min: number, max: number) => Math.min(max, Math.max(min, value))

const roundTo = (value: number, decimals = 2): number => {
  const factor = 10 ** decimals
  return Math.round(value * factor) / factor
}

const applyTaxRounding = (value: number, mode: TaxRoundingMode): number => {
  const factor = 100
  if (mode === 'UP') return Math.ceil(value * factor) / factor
  if (mode === 'DOWN') return Math.floor(value * factor) / factor
  return roundTo(value, 2)
}

const calculateTaxPreview = (settings: TaxSettingsForm): TaxPreview => {
  const rate = clamp(asNumber(settings.tax_rate_percent) ?? 0, 0, 100) / 100

  if (!settings.enable_tax || rate <= 0) {
    return {
      example_input: EXAMPLE_INPUT,
      base_sin_iva: roundTo(EXAMPLE_INPUT, 2),
      iva: 0,
      total: roundTo(EXAMPLE_INPUT, 2),
    }
  }

  if (settings.prices_include_tax) {
    const total = roundTo(EXAMPLE_INPUT, 2)
    const rawTax = total - total / (1 + rate)
    const iva = applyTaxRounding(rawTax, settings.tax_rounding_mode)
    const base = roundTo(total - iva, 2)

    return {
      example_input: EXAMPLE_INPUT,
      base_sin_iva: base,
      iva: roundTo(iva, 2),
      total,
    }
  }

  const base = roundTo(EXAMPLE_INPUT, 2)
  const rawTax = base * rate
  const iva = applyTaxRounding(rawTax, settings.tax_rounding_mode)
  const total = roundTo(base + iva, 2)

  return {
    example_input: EXAMPLE_INPUT,
    base_sin_iva: base,
    iva: roundTo(iva, 2),
    total,
  }
}

const parseStoreId = (value: unknown): number | null => {
  const parsed = asNumber(value)
  if (!parsed || parsed <= 0) return null
  return Math.trunc(parsed)
}

const normalizeRoundingMode = (value: unknown): TaxRoundingMode => {
  const mode = String(value || '').trim().toUpperCase()
  if (mode === 'DOWN' || mode === 'UP') return mode
  return 'HALF_UP'
}

const mapTaxSettings = (payload: any): TaxSettingsForm => {
  const decimalRate = asNumber(payload?.tax_rate)
  const percentRate = asNumber(payload?.tax_rate_percent)
  const fallbackPercent = decimalRate !== null ? decimalRate * 100 : TAX_DEFAULTS.tax_rate_percent

  return {
    enable_tax: Boolean(payload?.enable_tax ?? TAX_DEFAULTS.enable_tax),
    tax_name: String(payload?.tax_name || TAX_DEFAULTS.tax_name),
    tax_rate_percent: clamp(percentRate ?? fallbackPercent, 0, 100),
    prices_include_tax: Boolean(payload?.prices_include_tax ?? TAX_DEFAULTS.prices_include_tax),
    tax_rounding_mode: normalizeRoundingMode(payload?.tax_rounding_mode),
  }
}

const parsePreview = (payload: any): TaxPreview | null => {
  if (!payload || typeof payload !== 'object') return null

  const exampleInput = asNumber(payload.example_input)
  const base = asNumber(payload.base_sin_iva)
  const iva = asNumber(payload.iva)
  const total = asNumber(payload.total)

  if (exampleInput === null || base === null || iva === null || total === null) {
    return null
  }

  return {
    example_input: roundTo(exampleInput, 2),
    base_sin_iva: roundTo(base, 2),
    iva: roundTo(iva, 2),
    total: roundTo(total, 2),
  }
}

const formatCurrency = (value: number) =>
  new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 2,
  }).format(value)

export default function DashboardStore() {
  const outlet = useOutletContext<DashboardOutletContext | null>()
  const [isEditing, setIsEditing] = useState(false)
  const [originalData, setOriginalData] = useState<StoreData>(fallbackStore)
  const [storeData, setStoreData] = useState<StoreData>(fallbackStore)
  const [storeId, setStoreId] = useState<number | null>(null)
  const [loading, setLoading] = useState(true)
  const [loadError, setLoadError] = useState('')
  const [storeNotFound, setStoreNotFound] = useState(false)
  const [files, setFiles] = useState<{ logo: File | null; cover: File | null }>({ logo: null, cover: null })
  const [errors, setErrors] = useState<{ logo?: string; cover?: string }>({})
  const [taxForm, setTaxForm] = useState<TaxSettingsForm>(TAX_DEFAULTS)
  const [taxPreview, setTaxPreview] = useState<TaxPreview>(calculateTaxPreview(TAX_DEFAULTS))
  const [taxLoading, setTaxLoading] = useState(false)
  const [taxSaving, setTaxSaving] = useState(false)
  const [taxError, setTaxError] = useState('')
  const [toast, setToast] = useState<ToastState | null>(null)

  const stats = {
    revenue: 24600000,
    orders: 382,
    customers: 284,
    totalProducts: 156,
    activeProducts: 142,
    avgRating: 4.8,
  }

  const showToast = (message: string, variant: ToastVariant) => {
    setToast({ id: Date.now(), message, variant })
  }

  const resolveStoreId = (): number | null => {
    const fromOutlet = parseStoreId(outlet?.store?.id)
    if (fromOutlet) return fromOutlet

    try {
      const cachedRaw = localStorage.getItem('store')
      const cached = cachedRaw ? JSON.parse(cachedRaw) : null
      const fromCache = parseStoreId(cached?.id)
      if (fromCache) return fromCache
    } catch {
      // Sin cache valido; se continua con fallback.
    }

    return null
  }

  const loadTaxSettings = async (resolvedStoreId: number) => {
    setTaxLoading(true)
    setTaxError('')

    try {
      const response = await API.get(`/stores/${resolvedStoreId}/tax-settings`)
      const payload = getApiPayload<any>(response, {})
      const mapped = mapTaxSettings(payload)
      setTaxForm(mapped)
      setTaxPreview(calculateTaxPreview(mapped))
    } catch (error: any) {
      const message = error?.response?.data?.message || error?.message || 'No se pudo cargar la configuracion de IVA.'
      setTaxForm(TAX_DEFAULTS)
      setTaxPreview(calculateTaxPreview(TAX_DEFAULTS))
      setTaxError(message)
    } finally {
      setTaxLoading(false)
    }
  }

  useEffect(() => {
    const loadStore = async () => {
      const initialStoreId = resolveStoreId()
      if (initialStoreId) {
        setStoreId(initialStoreId)
      }

      try {
        setLoadError('')
        setStoreNotFound(false)
        const response = await API.get('/my/store')
        const data = getApiPayload<any>(response, {})
        if (data) {
          const mapped: StoreData = {
            id: data.id || 0,
            name: data.name || '',
            description: data.description || '',
            email: data.support_email || '',
            phone: data.phone || '',
            address: data.address || '',
            isVisible: Boolean(data.is_visible),
            status: data.is_visible ? 'active' : 'inactive',
            logo: resolveMediaUrl(data.logo_url || data.logo_path || data.logo) || null,
            cover: resolveMediaUrl(data.cover_url || data.cover_path || data.background_url || data.cover) || null,
          }
          setOriginalData(mapped)
          setStoreData(mapped)
          setStoreId(parseStoreId(data.id))
          localStorage.setItem('store', JSON.stringify(data))
        }
      } catch (error: any) {
        if (error?.response?.status === 404) {
          setStoreNotFound(true)
          setLoadError('')
          setOriginalData(fallbackStore)
          setStoreData(fallbackStore)
          setIsEditing(true)
          setStoreId(null)
          localStorage.removeItem('store')
          return
        }

        const message =
          error?.response?.data?.message ||
          error?.message ||
          'No se pudo conectar con la API para cargar la tienda.'
        setLoadError(message)
        setStoreId(initialStoreId)
      } finally {
        setLoading(false)
      }
    }

    loadStore()
  }, [])

  useEffect(() => {
    if (!storeId) {
      setTaxForm(TAX_DEFAULTS)
      setTaxPreview(calculateTaxPreview(TAX_DEFAULTS))
      return
    }

    loadTaxSettings(storeId)
  }, [storeId])

  useEffect(() => {
    setTaxPreview(calculateTaxPreview(taxForm))
  }, [taxForm])

  useEffect(() => {
    if (!toast) return

    const timer = window.setTimeout(() => {
      setToast(null)
    }, 4000)

    return () => window.clearTimeout(timer)
  }, [toast])

  const handleEdit = () => setIsEditing(true)

  const handleCancel = () => {
    setStoreData({ ...originalData })
    setFiles({ logo: null, cover: null })
    setErrors({})
    setIsEditing(false)
  }

  const onImageChange = async (event: React.ChangeEvent<HTMLInputElement>, type: 'logo' | 'cover') => {
    const file = event.target.files?.[0] || null

    if (!file) {
      setFiles((prev) => ({ ...prev, [type]: null }))
      setErrors((prev) => ({ ...prev, [type]: '' }))
      return
    }

    const validation = await validateImage(file, type)
    if (!validation.valid) {
      setFiles((prev) => ({ ...prev, [type]: null }))
      setErrors((prev) => ({ ...prev, [type]: validation.error || 'Imagen invalida.' }))
      return
    }

    const preview = await generatePreview(file)
    setFiles((prev) => ({ ...prev, [type]: file }))
    setStoreData((prev) => ({ ...prev, [type]: preview }))
    setErrors((prev) => ({ ...prev, [type]: '' }))
  }

  const handleSave = async () => {
    try {
      let logoUrl = storeData.logo || ''
      let coverUrl = storeData.cover || ''

      if (files.logo) {
        const upload = await uploadStoreLogo(files.logo)
        logoUrl = upload.url
      }

      if (files.cover) {
        const upload = await uploadStoreCover(files.cover)
        coverUrl = upload.url
      }

      const payload: Record<string, string | boolean> = {
        name: storeData.name,
        description: storeData.description,
        support_email: storeData.email,
        phone: storeData.phone,
        address: storeData.address,
        is_visible: storeData.isVisible,
      }

      const isHttpUrl = (value: string) => /^https?:\/\//i.test(value.trim())
      if (logoUrl && isHttpUrl(logoUrl)) payload.logo_url = logoUrl
      if (coverUrl && isHttpUrl(coverUrl)) payload.cover_url = coverUrl

      let response
      if (storeData.id) {
        response = await API.put(`/stores/${storeData.id}`, payload)
      } else {
        response = await API.post('/stores', payload)
      }

      const responseData = getApiPayload<any>(response, {})
      const merged: StoreData = {
        ...storeData,
        id: responseData.id || storeData.id,
        status: storeData.isVisible ? 'active' : 'inactive',
        logo: resolveMediaUrl(responseData.logo_url || responseData.logo_path || responseData.logo) || logoUrl || storeData.logo,
        cover: resolveMediaUrl(responseData.cover_url || responseData.cover_path || responseData.background_url || responseData.cover) || coverUrl || storeData.cover,
      }

      setStoreData(merged)
      setOriginalData(merged)
      setStoreId(parseStoreId(responseData.id || merged.id))
      setStoreNotFound(false)
      setFiles({ logo: null, cover: null })
      setIsEditing(false)
      localStorage.setItem('store', JSON.stringify(responseData || merged))
      window.dispatchEvent(new CustomEvent('store:updated', { detail: responseData || merged }))
      showToast('Datos de la tienda guardados correctamente.', 'success')
    } catch (error: any) {
      const firstFieldError = error?.response?.data?.errors
        ? Object.values(error.response.data.errors).flat?.()[0]
        : ''
      showToast(
        String(firstFieldError || error?.response?.data?.message || error?.message || 'Error al guardar cambios'),
        'error',
      )
    }
  }

  const handleTaxSave = async () => {
    if (!storeId) {
      showToast('No se encontro la tienda para configurar impuestos.', 'error')
      return
    }

    setTaxSaving(true)
    setTaxError('')

    const sanitizedForm: TaxSettingsForm = {
      ...taxForm,
      tax_name: (taxForm.tax_name || 'IVA').trim() || 'IVA',
      tax_rate_percent: clamp(asNumber(taxForm.tax_rate_percent) ?? 0, 0, 100),
      tax_rounding_mode: normalizeRoundingMode(taxForm.tax_rounding_mode),
    }

    try {
      const response = await API.put(`/stores/${storeId}/tax-settings`, {
        enable_tax: sanitizedForm.enable_tax,
        tax_name: sanitizedForm.tax_name,
        tax_rate_percent: sanitizedForm.tax_rate_percent,
        prices_include_tax: sanitizedForm.prices_include_tax,
        tax_rounding_mode: sanitizedForm.tax_rounding_mode,
      })

      const responseData = response?.data || {}
      const payload = getApiPayload<any>(response, {})
      const meta = getApiMeta<{ preview?: Record<string, unknown> }>(response)
      const mapped = mapTaxSettings(payload)
      const previewFromApi = parsePreview(responseData?.preview || meta?.preview)

      setTaxForm(mapped)
      setTaxPreview(previewFromApi || calculateTaxPreview(mapped))
      showToast(responseData?.message || 'Configuracion de IVA guardada correctamente.', 'success')
    } catch (error: any) {
      const message =
        error?.response?.data?.message ||
        error?.message ||
        'No se pudo guardar la configuracion de impuestos.'
      setTaxError(message)
      showToast(message, 'error')
    } finally {
      setTaxSaving(false)
    }
  }

  const clearDraftValueOnFocus = (
    field: 'name' | 'description' | 'email' | 'phone' | 'address',
    currentValue: string,
  ) => {
    if (!isEditing) return false

    const normalizedCurrent = currentValue.trim().toLowerCase()
    if (!normalizedCurrent) return false

    const shouldClear = autoDraftValues[field].some(
      (value) => value.trim().toLowerCase() === normalizedCurrent,
    )

    if (!shouldClear) return false

    setStoreData((prev) => ({ ...prev, [field]: '' }))
    return true
  }

  return (
    <div className="relative">
      {toast && (
        <div
          className={`fixed right-5 top-5 z-[80] flex w-full max-w-sm items-start gap-3 rounded-xl border px-4 py-3 shadow-lg ${
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

      {storeData.cover && (
        <div className="pointer-events-none fixed inset-0 z-0 lg:left-[260px]">
          <div
            className="absolute inset-0 scale-[1.02] bg-cover bg-center bg-no-repeat saturate-125"
            style={{ backgroundImage: `url(${storeData.cover})` }}
          />
          <div className="absolute inset-0 bg-gradient-to-b from-slate-900/18 via-white/20 to-white/45" />
        </div>
      )}

      <div className="relative z-10 space-y-6">
        {storeNotFound && (
          <div className="rounded-xl border border-sky-300 bg-sky-50 px-4 py-3 text-[13px] text-sky-900">
            Aun no tienes una tienda creada. Completa los datos y presiona guardar para crearla.
          </div>
        )}

        {loadError && !storeNotFound && (
          <div className="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-[13px] text-amber-900">
            No se pudo cargar la tienda desde la API. {loadError}
          </div>
        )}

        <section className="relative overflow-hidden rounded-3xl border border-slate-200/70 bg-gradient-to-br from-white/88 via-slate-50/82 to-brand-50/38 p-8 shadow-premium backdrop-blur-sm lg:p-10">
          <div className="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-brand-500/10 blur-3xl" />
          <div className="relative z-10 flex flex-wrap items-end justify-between gap-4">
            <div>
              <h1 className="mb-2 text-display-sm text-slate-950">Panel de tienda</h1>
              <p className="text-body-lg text-slate-600">Controla tu marca, catalogo y rendimiento desde un solo lugar.</p>
            </div>
            <div className="flex gap-3">
              {!isEditing ? (
                <Button variant="primary" onClick={handleEdit} disabled={loading}>
                  {storeData.id ? 'Editar tienda' : 'Crear tienda'}
                </Button>
              ) : (
                <>
                  <Button variant="outline" onClick={handleCancel}>
                    Cancelar
                  </Button>
                  <Button variant="primary" onClick={handleSave}>
                    Guardar cambios
                  </Button>
                </>
              )}
            </div>
          </div>
        </section>

        <section className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
          <Card variant="premium" padding="md" className="bg-white/88 backdrop-blur-sm">
            <p className="text-caption uppercase tracking-wide text-slate-500">Ingresos</p>
            <p className="mt-2 text-h2 text-slate-950">${(stats.revenue / 1000000).toFixed(1)}M</p>
          </Card>
          <Card variant="glass" padding="md">
            <p className="text-caption uppercase tracking-wide text-slate-500">Pedidos</p>
            <p className="mt-2 text-h2 text-slate-950">{stats.orders}</p>
          </Card>
          <Card variant="glass" padding="md">
            <p className="text-caption uppercase tracking-wide text-slate-500">Clientes</p>
            <p className="mt-2 text-h2 text-slate-950">{stats.customers}</p>
          </Card>
          <Card variant="glass" padding="md">
            <p className="text-caption uppercase tracking-wide text-slate-500">Rating</p>
            <p className="mt-2 text-h2 text-slate-950">{stats.avgRating}</p>
          </Card>
        </section>

        <section className="grid grid-cols-1 gap-6 xl:grid-cols-[1fr_360px]">
          <Card variant="glass" padding="lg" className="bg-white/88 backdrop-blur-sm">
            <h2 className="mb-6 text-h2">Informacion general</h2>

            <div className="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label className="mb-2 block text-body-sm font-semibold text-slate-900">Logo de la tienda</label>
                <div className="relative">
                  <div className="flex h-32 items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50">
                    {storeData.logo ? (
                      <img src={storeData.logo} alt="Logo" className="h-full w-full object-contain p-2" style={{ maxHeight: '112px' }} />
                    ) : (
                      <p className="text-body-sm text-slate-500">Sube tu logo</p>
                    )}
                  </div>
                  {isEditing && (
                    <input
                      type="file"
                      accept="image/png,image/jpeg,image/jpg,image/webp,image/avif"
                      onChange={(event) => onImageChange(event, 'logo')}
                      className="absolute inset-0 cursor-pointer opacity-0"
                    />
                  )}
                </div>
                <p className="mt-1 text-caption text-slate-500">Recomendado: 512x512px, maximo 2MB.</p>
                {errors.logo && <p className="mt-1 text-caption text-danger">{errors.logo}</p>}
              </div>

              <div>
                <label className="mb-2 block text-body-sm font-semibold text-slate-900">Portada de la tienda</label>
                <div className="relative">
                  <div className="flex h-32 items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50">
                    {storeData.cover ? (
                      <img src={storeData.cover} alt="Portada" className="h-full w-full object-cover" />
                    ) : (
                      <p className="text-body-sm text-slate-500">Sube tu portada</p>
                    )}
                  </div>
                  {isEditing && (
                    <input
                      type="file"
                      accept="image/png,image/jpeg,image/jpg,image/webp,image/avif"
                      onChange={(event) => onImageChange(event, 'cover')}
                      className="absolute inset-0 cursor-pointer opacity-0"
                    />
                  )}
                </div>
                <p className="mt-1 text-caption text-slate-500">Recomendado: 1920x400px, maximo 5MB.</p>
                {errors.cover && <p className="mt-1 text-caption text-danger">{errors.cover}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
              <Input
                label="Nombre de la tienda"
                value={storeData.name}
                onChange={(event) => setStoreData({ ...storeData, name: event.target.value })}
                onFocus={(event) => {
                  const cleared = clearDraftValueOnFocus('name', event.target.value)
                  if (isEditing && !cleared) event.target.select()
                }}
                readOnly={!isEditing}
                spellCheck={false}
                fullWidth
              />
              <Input
                label="Email"
                value={storeData.email}
                onChange={(event) => setStoreData({ ...storeData, email: event.target.value })}
                onFocus={(event) => {
                  const cleared = clearDraftValueOnFocus('email', event.target.value)
                  if (isEditing && !cleared) event.target.select()
                }}
                readOnly={!isEditing}
                spellCheck={false}
                fullWidth
              />
              <Input
                label="Telefono"
                value={storeData.phone}
                onChange={(event) => setStoreData({ ...storeData, phone: event.target.value })}
                onFocus={(event) => {
                  const cleared = clearDraftValueOnFocus('phone', event.target.value)
                  if (isEditing && !cleared) event.target.select()
                }}
                readOnly={!isEditing}
                spellCheck={false}
                fullWidth
              />
              <Input
                label="Direccion"
                value={storeData.address}
                onChange={(event) => setStoreData({ ...storeData, address: event.target.value })}
                onFocus={(event) => {
                  const cleared = clearDraftValueOnFocus('address', event.target.value)
                  if (isEditing && !cleared) event.target.select()
                }}
                readOnly={!isEditing}
                spellCheck={false}
                fullWidth
              />
            </div>

            <div className="mt-4">
              <label className="mb-2 block text-body-sm font-semibold text-slate-900">Descripcion</label>
              <textarea
                className="textarea-dark w-full"
                rows={4}
                value={storeData.description}
                onChange={(event) => setStoreData({ ...storeData, description: event.target.value })}
                onFocus={(event) => {
                  const cleared = clearDraftValueOnFocus('description', event.target.value)
                  if (isEditing && !cleared) event.target.select()
                }}
                readOnly={!isEditing}
                spellCheck={false}
              />
            </div>
          </Card>

          <div className="space-y-6">
            <Card variant="premium" padding="lg">
              <h3 className="mb-4 text-h3">Estado de la tienda</h3>
              <div className="space-y-3">
                <div className="flex items-center justify-between">
                  <span className="text-body text-slate-700">Visibilidad</span>
                  <Badge variant={storeData.isVisible ? 'success' : 'warning'}>
                    {storeData.isVisible ? 'Visible' : 'Oculta'}
                  </Badge>
                </div>
                <div className="flex items-center justify-between">
                  <span className="text-body text-slate-700">Estado</span>
                  <Badge variant={storeData.status === 'active' ? 'success' : 'danger'}>
                    {storeData.status === 'active' ? 'Activa' : 'Inactiva'}
                  </Badge>
                </div>
              </div>
            </Card>

            <Card variant="glass" padding="lg">
              <h3 className="mb-4 text-h3">Inventario</h3>
              <div className="space-y-2 text-body text-slate-700">
                <p>Productos activos: <strong>{stats.activeProducts}</strong></p>
                <p>Total productos: <strong>{stats.totalProducts}</strong></p>
              </div>
            </Card>

            <Card variant="glass" padding="lg" className="bg-white/90 backdrop-blur-sm">
              <div className="mb-4 flex items-center justify-between gap-2">
                <h3 className="text-h3">Impuestos (IVA)</h3>
                {taxLoading && <span className="text-caption text-slate-500">Cargando...</span>}
              </div>

              {taxError && (
                <div className="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[12px] text-amber-900">
                  {taxError}
                </div>
              )}

              <div className="space-y-4">
                <label className="flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 px-3 py-2">
                  <div>
                    <p className="text-body-sm font-semibold text-slate-900">Activar IVA</p>
                    <p className="text-caption text-slate-500">Aplica impuesto a las ventas de la tienda.</p>
                  </div>
                  <span className="relative inline-flex h-6 w-11 items-center">
                    <input
                      type="checkbox"
                      className="peer sr-only"
                      checked={taxForm.enable_tax}
                      onChange={(event) => setTaxForm((prev) => ({ ...prev, enable_tax: event.target.checked }))}
                    />
                    <span className="absolute inset-0 rounded-full bg-slate-300 transition peer-checked:bg-brand-500" />
                    <span className="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition peer-checked:translate-x-5" />
                  </span>
                </label>

                <Input
                  type="number"
                  min={0}
                  max={100}
                  step="0.01"
                  label="Porcentaje IVA"
                  value={taxForm.tax_rate_percent}
                  onChange={(event) =>
                    setTaxForm((prev) => ({
                      ...prev,
                      tax_rate_percent: clamp(asNumber(event.target.value) ?? 0, 0, 100),
                    }))
                  }
                  fullWidth
                />

                <label className="flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 px-3 py-2">
                  <div>
                    <p className="text-body-sm font-semibold text-slate-900">Mis precios incluyen IVA</p>
                    <p className="text-caption text-slate-500">Si esta activo, el precio publicado ya es final.</p>
                  </div>
                  <span className="relative inline-flex h-6 w-11 items-center">
                    <input
                      type="checkbox"
                      className="peer sr-only"
                      checked={taxForm.prices_include_tax}
                      onChange={(event) => setTaxForm((prev) => ({ ...prev, prices_include_tax: event.target.checked }))}
                    />
                    <span className="absolute inset-0 rounded-full bg-slate-300 transition peer-checked:bg-brand-500" />
                    <span className="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition peer-checked:translate-x-5" />
                  </span>
                </label>

                <Input
                  label="Nombre del impuesto"
                  value={taxForm.tax_name}
                  onChange={(event) => setTaxForm((prev) => ({ ...prev, tax_name: event.target.value }))}
                  fullWidth
                />

                <div>
                  <label className="mb-2 block text-body-sm font-semibold text-slate-900">Redondeo</label>
                  <select
                    className="select-dark native-select w-full"
                    value={taxForm.tax_rounding_mode}
                    onChange={(event) =>
                      setTaxForm((prev) => ({
                        ...prev,
                        tax_rounding_mode: normalizeRoundingMode(event.target.value),
                      }))
                    }
                  >
                    <option value="HALF_UP">HALF_UP</option>
                    <option value="DOWN">DOWN</option>
                    <option value="UP">UP</option>
                  </select>
                </div>

                <div className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <p className="text-caption uppercase tracking-wide text-slate-500">
                    Preview (ejemplo: {formatCurrency(taxPreview.example_input)})
                  </p>
                  <div className="mt-3 space-y-1 text-body-sm text-slate-700">
                    <div className="flex items-center justify-between">
                      <span>Base sin IVA</span>
                      <strong>{formatCurrency(taxPreview.base_sin_iva)}</strong>
                    </div>
                    <div className="flex items-center justify-between">
                      <span>{taxForm.tax_name || 'IVA'}</span>
                      <strong>{formatCurrency(taxPreview.iva)}</strong>
                    </div>
                    <div className="flex items-center justify-between border-t border-slate-200 pt-2 text-slate-900">
                      <span>Total</span>
                      <strong>{formatCurrency(taxPreview.total)}</strong>
                    </div>
                  </div>
                </div>

                <Button
                  variant="primary"
                  onClick={handleTaxSave}
                  disabled={!storeId || taxSaving || taxLoading}
                  loading={taxSaving}
                  fullWidth
                >
                  Guardar IVA
                </Button>
              </div>
            </Card>
          </div>
        </section>
      </div>
    </div>
  )
}
