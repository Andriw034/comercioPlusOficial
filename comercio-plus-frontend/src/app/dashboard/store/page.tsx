import { useEffect, useRef, useState } from 'react'
import type { ChangeEvent, ReactNode } from 'react'
import API from '@/lib/api'
import { ErpBtn } from '@/components/erp'
import { uploadStoreLogo, uploadStoreCover } from '@/services/uploads'
import {
  Globe,
  Phone,
  MessageCircle,
  Mail,
  Clock,
  Banknote,
  Building2,
  CreditCard,
  Truck,
  Receipt,
} from 'lucide-react'

// ─── Types ───────────────────────────────────────────────────────────────────

type Tab = 'general' | 'contact' | 'design' | 'payments'

interface StoreForm {
  name: string
  description: string
  category: string
  city: string
  address: string
  phone: string
  whatsapp: string
  email: string
  schedule: string
  logo_url: string
  cover_url: string
  currency: string
  taxes_enabled: boolean
  payment_methods: string[]
  is_public: boolean
}

const DEFAULT_FORM: StoreForm = {
  name: '',
  description: '',
  category: '',
  city: '',
  address: '',
  phone: '',
  whatsapp: '',
  email: '',
  schedule: '',
  logo_url: '',
  cover_url: '',
  currency: 'COP',
  taxes_enabled: false,
  payment_methods: [],
  is_public: false,
}

const PAYMENT_OPTIONS = [
  { id: 'cash', label: 'Efectivo', description: 'Pago en efectivo al entregar o en tienda', Icon: Banknote, color: '#16A34A', activeCard: 'bg-green-50 border-green-400', inactiveCard: 'bg-white border-gray-200' },
  { id: 'transfer', label: 'Transferencia', description: 'Transferencia bancaria o depósito', Icon: Building2, color: '#2563EB', activeCard: 'bg-blue-50 border-blue-400', inactiveCard: 'bg-white border-gray-200' },
  { id: 'mercadopago', label: 'MercadoPago', description: 'Pagos online con tarjeta y más', Icon: CreditCard, color: '#F97316', activeCard: 'bg-orange-50 border-orange-400', inactiveCard: 'bg-white border-gray-200' },
  { id: 'cod', label: 'Contraentrega', description: 'El cliente paga al recibir el pedido', Icon: Truck, color: '#7C3AED', activeCard: 'bg-purple-50 border-purple-400', inactiveCard: 'bg-white border-gray-200' },
]

const CURRENCIES = ['COP', 'USD', 'EUR']

const TABS: { id: Tab; label: string }[] = [
  { id: 'general', label: 'General' },
  { id: 'contact', label: 'Contacto' },
  { id: 'design', label: 'Diseño' },
  { id: 'payments', label: 'Pagos' },
]

const ALLOWED_TYPES = new Set(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
const MAX_BYTES = 5 * 1024 * 1024

// ─── Primitives ───────────────────────────────────────────────────────────────

function Skeleton({ className = '' }: { className?: string }) {
  return <div className={`animate-pulse rounded-xl bg-gray-200 ${className}`} />
}

function Toggle({ checked, onChange }: { checked: boolean; onChange: (v: boolean) => void }) {
  return (
    <button
      type="button"
      role="switch"
      aria-checked={checked}
      onClick={() => onChange(!checked)}
      className={`relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full transition-colors ${
        checked ? 'bg-orange-500' : 'bg-gray-300'
      }`}
    >
      <span
        className={`inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform ${
          checked ? 'translate-x-6' : 'translate-x-1'
        }`}
      />
    </button>
  )
}

function Field({ label, hint, children }: { label: string; hint?: string; children: ReactNode }) {
  return (
    <div className="space-y-1">
      <label className="block text-sm font-medium text-gray-700">{label}</label>
      {hint ? <p className="text-xs text-gray-400">{hint}</p> : null}
      {children}
    </div>
  )
}

function TextInput({
  value,
  onChange,
  placeholder = '',
  type = 'text',
}: {
  value: string
  onChange: (v: string) => void
  placeholder?: string
  type?: string
}) {
  return (
    <input
      type={type}
      value={value}
      placeholder={placeholder}
      onChange={(e) => onChange(e.target.value)}
      className="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 outline-none transition focus:border-transparent focus:ring-2 focus:ring-orange-400"
    />
  )
}

function TextArea({
  value,
  onChange,
  placeholder = '',
  rows = 3,
}: {
  value: string
  onChange: (v: string) => void
  placeholder?: string
  rows?: number
}) {
  return (
    <textarea
      value={value}
      placeholder={placeholder}
      rows={rows}
      onChange={(e) => onChange(e.target.value)}
      className="w-full resize-none rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 outline-none transition focus:border-transparent focus:ring-2 focus:ring-orange-400"
    />
  )
}

function StoreSelect({
  value,
  onChange,
  options,
}: {
  value: string
  onChange: (v: string) => void
  options: { value: string; label: string }[]
}) {
  return (
    <select
      value={value}
      onChange={(e) => onChange(e.target.value)}
      className="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-transparent focus:ring-2 focus:ring-orange-400"
    >
      {options.map((o) => (
        <option key={o.value} value={o.value}>
          {o.label}
        </option>
      ))}
    </select>
  )
}

function SectionCard({ children, className = '' }: { children: ReactNode; className?: string }) {
  return (
    <div
      className={`relative rounded-2xl bg-white mb-4 p-6 ${className}`}
      style={{
        boxShadow: '0 2px 12px 0 rgba(0,0,0,0.07), 0 1px 3px 0 rgba(0,0,0,0.05)',
        border: '1px solid transparent',
        backgroundClip: 'padding-box',
        backgroundImage: 'linear-gradient(white, white), linear-gradient(135deg, #e5e7eb, #f3f4f6, #d1d5db)',
        backgroundOrigin: 'border-box',
      }}
    >
      {children}
    </div>
  )
}

// ─── Page ─────────────────────────────────────────────────────────────────────

export default function StorePage() {
  const [tab, setTab] = useState<Tab>('general')
  const [form, setForm] = useState<StoreForm>(DEFAULT_FORM)
  const [previews, setPreviews] = useState({ logo: '', cover: '' })
  const [files, setFiles] = useState<{ logo: File | null; cover: File | null }>({ logo: null, cover: null })
  const [loading, setLoading] = useState(true)
  const [loadError, setLoadError] = useState<string | null>(null)
  const [saving, setSaving] = useState(false)
  const [saveError, setSaveError] = useState<string | null>(null)
  const [saved, setSaved] = useState(false)

  const logoRef = useRef<HTMLInputElement>(null)
  const coverRef = useRef<HTMLInputElement>(null)

  const setField = <K extends keyof StoreForm>(key: K, value: StoreForm[K]) =>
    setForm((prev) => ({ ...prev, [key]: value }))

  // ── Load ──────────────────────────────────────────────────────────────────

  const load = async () => {
    setLoading(true)
    setLoadError(null)
    try {
      const res = await API.get('/merchant/store')
      const d = res.data?.data ?? res.data
      setForm({
        name: d.name ?? '',
        description: d.description ?? '',
        category: d.category ?? '',
        city: d.city ?? '',
        address: d.address ?? '',
        phone: d.phone ?? '',
        whatsapp: d.whatsapp ?? '',
        email: d.email ?? '',
        schedule: d.schedule ?? '',
        logo_url: d.logo_url ?? '',
        cover_url: d.cover_url ?? '',
        currency: d.currency ?? 'COP',
        taxes_enabled: !!d.taxes_enabled,
        payment_methods: Array.isArray(d.payment_methods) ? d.payment_methods : [],
        is_public: !!d.is_public,
      })
      setPreviews({ logo: d.logo_url ?? '', cover: d.cover_url ?? '' })
    } catch (err: unknown) {
      const msg = (err as { response?: { data?: { message?: string } } })?.response?.data?.message
      setLoadError(msg ?? 'No se pudo cargar la tienda.')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    load()
  }, [])

  // ── File pick ─────────────────────────────────────────────────────────────

  const pickFile = (e: ChangeEvent<HTMLInputElement>, type: 'logo' | 'cover') => {
    setSaveError(null)
    const file = e.target.files?.[0]
    if (!file) return
    if (!ALLOWED_TYPES.has(file.type)) {
      setSaveError('Formato no permitido. Usa JPG, PNG, WEBP o AVIF.')
      return
    }
    if (file.size > MAX_BYTES) {
      setSaveError('La imagen supera 5 MB. Elige un archivo mas liviano.')
      return
    }
    setFiles((prev) => ({ ...prev, [type]: file }))
    setPreviews((prev) => ({ ...prev, [type]: URL.createObjectURL(file) }))
  }

  const removeImage = (type: 'logo' | 'cover') => {
    setFiles((prev) => ({ ...prev, [type]: null }))
    setPreviews((prev) => ({ ...prev, [type]: '' }))
    setField(`${type}_url`, '')
    if (type === 'logo' && logoRef.current) logoRef.current.value = ''
    if (type === 'cover' && coverRef.current) coverRef.current.value = ''
  }

  // ── Save ──────────────────────────────────────────────────────────────────

  const save = async () => {
    if (!form.name.trim()) { setSaveError('El nombre de la tienda es obligatorio.'); return }
    setSaving(true)
    setSaveError(null)
    setSaved(false)
    try {
      let logoUrl = form.logo_url
      let coverUrl = form.cover_url

      if (files.logo) {
        const up = await uploadStoreLogo(files.logo)
        logoUrl = up.url
        setFiles((prev) => ({ ...prev, logo: null }))
        if (logoRef.current) logoRef.current.value = ''
      }
      if (files.cover) {
        const up = await uploadStoreCover(files.cover)
        coverUrl = up.url
        setFiles((prev) => ({ ...prev, cover: null }))
        if (coverRef.current) coverRef.current.value = ''
      }

      const response = await API.put('/merchant/store', { ...form, logo_url: logoUrl, cover_url: coverUrl })
      const updated = (response.data?.data ?? response.data ?? {}) as Partial<StoreForm> & {
        id?: string | number
        logo_url?: string
        cover_url?: string
      }

      const mergedStore = {
        ...updated,
        name: String(updated.name ?? form.name),
        logo_url: String(updated.logo_url ?? logoUrl ?? ''),
        cover_url: String(updated.cover_url ?? coverUrl ?? ''),
      }

      setForm((prev) => ({
        ...prev,
        name: String(updated.name ?? prev.name),
        description: String(updated.description ?? prev.description),
        category: String(updated.category ?? prev.category),
        city: String(updated.city ?? prev.city),
        address: String(updated.address ?? prev.address),
        phone: String(updated.phone ?? prev.phone),
        whatsapp: String(updated.whatsapp ?? prev.whatsapp),
        email: String(updated.email ?? prev.email),
        schedule: String(updated.schedule ?? prev.schedule),
        logo_url: String(updated.logo_url ?? logoUrl ?? ''),
        cover_url: String(updated.cover_url ?? coverUrl ?? ''),
        currency: String(updated.currency ?? prev.currency),
        taxes_enabled: typeof updated.taxes_enabled === 'boolean' ? updated.taxes_enabled : prev.taxes_enabled,
        payment_methods: Array.isArray(updated.payment_methods)
          ? updated.payment_methods.map((item) => String(item))
          : prev.payment_methods,
        is_public: typeof updated.is_public === 'boolean' ? updated.is_public : prev.is_public,
      }))

      setPreviews({
        logo: String(updated.logo_url ?? logoUrl ?? ''),
        cover: String(updated.cover_url ?? coverUrl ?? ''),
      })

      if (typeof window !== 'undefined') {
        localStorage.setItem('store', JSON.stringify(mergedStore))
        window.dispatchEvent(new CustomEvent('store:updated', { detail: mergedStore }))
      }

      setSaved(true)
      setTimeout(() => setSaved(false), 3000)
    } catch (err: unknown) {
      const msg = (err as { response?: { data?: { message?: string } } })?.response?.data?.message
      setSaveError(msg ?? 'Error al guardar. Intenta de nuevo.')
    } finally {
      setSaving(false)
    }
  }

  // ── Hidden file inputs (shared, outside tab content) ──────────────────────

  const fileInputs = (
    <>
      <input
        ref={logoRef}
        type="file"
        accept="image/jpeg,image/png,image/webp,image/avif"
        className="hidden"
        onChange={(e) => pickFile(e, 'logo')}
      />
      <input
        ref={coverRef}
        type="file"
        accept="image/jpeg,image/png,image/webp,image/avif"
        className="hidden"
        onChange={(e) => pickFile(e, 'cover')}
      />
    </>
  )

  // ── Save footer ───────────────────────────────────────────────────────────

  const saveFooter = (
    <div className="flex flex-wrap items-center gap-3 border-t border-gray-100 pt-5">
      <ErpBtn variant="primary" onClick={save} disabled={saving}>
        {saving ? 'Guardando...' : 'Guardar cambios'}
      </ErpBtn>
      {saved && (
        <span className="text-sm font-medium text-green-600">
          Cambios guardados correctamente
        </span>
      )}
      {saveError && (
        <span className="text-sm font-medium text-red-600">{saveError}</span>
      )}
    </div>
  )

  // ── Loading skeleton ───────────────────────────────────────────────────────

  if (loading) {
    return (
      <div className="space-y-6">
        <div className="space-y-2">
          <Skeleton className="h-3 w-28" />
          <Skeleton className="h-8 w-48" />
        </div>
        <div className="flex gap-1 border-b border-gray-200">
          {[1, 2, 3, 4].map((i) => (
            <Skeleton key={i} className="h-9 w-24 rounded-none" />
          ))}
        </div>
        <div className="space-y-4 rounded-xl border border-gray-200 bg-white p-6">
          <Skeleton className="h-5 w-40" />
          <div className="grid gap-4 sm:grid-cols-2">
            <Skeleton className="h-10 w-full" />
            <Skeleton className="h-10 w-full" />
          </div>
          <Skeleton className="h-24 w-full" />
          <div className="grid gap-4 sm:grid-cols-2">
            <Skeleton className="h-10 w-full" />
            <Skeleton className="h-10 w-full" />
          </div>
        </div>
      </div>
    )
  }

  // ── Load error ────────────────────────────────────────────────────────────

  if (loadError) {
    return (
      <div className="flex flex-col items-center justify-center gap-4 py-24 text-center">
        <div className="max-w-sm rounded-xl border border-red-200 bg-red-50 px-6 py-5">
          <p className="text-sm font-semibold text-red-700">{loadError}</p>
        </div>
        <ErpBtn variant="primary" onClick={load}>
          Reintentar
        </ErpBtn>
      </div>
    )
  }

  // ── Render ────────────────────────────────────────────────────────────────

  return (
    <div className="space-y-0 pb-24 bg-gray-100 min-h-screen -mx-4 px-4 pt-2">
      {fileInputs}

      {/* Header with improved badge */}
      <div className="mb-6">
        <p className="text-sm text-gray-400 mb-1">Configuración</p>
        <div className="flex items-center gap-3">
          <h1 className="text-2xl font-bold text-gray-900">Mi Tienda</h1>
          {form.is_public ? (
            <span className="inline-flex items-center gap-1.5 rounded-full border border-green-200 bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
              <span className="h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse" />
              Pública
            </span>
          ) : (
            <span className="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500">
              Privada
            </span>
          )}
        </div>
        <p className="text-sm text-gray-500 mt-1">Edita los datos, diseño y configuración de tu tienda pública</p>
      </div>

      {/* Tab bar — underline style */}
      <div className="bg-white border-b border-gray-200 flex overflow-x-auto mb-6">
        {TABS.map((t) => (
          <button
            key={t.id}
            type="button"
            onClick={() => { setTab(t.id); setSaveError(null); setSaved(false) }}
            className={`whitespace-nowrap pb-3 px-4 text-sm font-medium transition-all border-b-2 -mb-px ${
              tab === t.id
                ? 'text-orange-500 border-orange-500'
                : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            {t.label}
          </button>
        ))}
      </div>

      {/* ── Tab: General ──────────────────────────────────────────────────── */}
      {tab === 'general' && (
        <div className="space-y-0">
          <SectionCard>
            <h2 className="text-base font-semibold text-gray-900 mb-1">Información general</h2>
            <p className="text-sm text-gray-500 mb-5">Datos principales de tu tienda.</p>

            <div className="grid gap-4 sm:grid-cols-2">
              <Field label="Nombre de la tienda *">
                <TextInput
                  value={form.name}
                  onChange={(v) => setField('name', v)}
                  placeholder="Ej: Panaderia La Esperanza"
                />
              </Field>
              <Field label="Categoría">
                <TextInput
                  value={form.category}
                  onChange={(v) => setField('category', v)}
                  placeholder="Ej: Alimentos, Ropa, Tecnología…"
                />
              </Field>
            </div>

            <div className="mt-4">
              <Field label="Descripción">
                <TextArea
                  value={form.description}
                  onChange={(v) => setField('description', v)}
                  placeholder="Cuéntale a tus clientes qué vendes y por qué elegirte…"
                  rows={3}
                />
              </Field>
            </div>

            <div className="mt-4 grid gap-4 sm:grid-cols-2">
              <Field label="Ciudad">
                <TextInput
                  value={form.city}
                  onChange={(v) => setField('city', v)}
                  placeholder="Ej: Bogotá, Medellín, Cali…"
                />
              </Field>
              <Field label="Dirección">
                <TextInput
                  value={form.address}
                  onChange={(v) => setField('address', v)}
                  placeholder="Calle 123 #45-67"
                />
              </Field>
            </div>

            <div className="mt-4">
              <Field label="Moneda" hint="Se usará en precios y reportes.">
                <StoreSelect
                  value={form.currency}
                  onChange={(v) => setField('currency', v)}
                  options={CURRENCIES.map((c) => ({ value: c, label: c }))}
                />
              </Field>
            </div>
          </SectionCard>

          {/* Tienda pública — card separada */}
          <div
            className={`rounded-xl border px-5 py-4 mb-4 flex items-center justify-between gap-4 transition-colors ${
              form.is_public
                ? 'bg-orange-50 border-orange-200'
                : 'bg-gray-50 border-gray-200'
            }`}
          >
            <div className="flex items-center gap-3">
              <div className={`flex h-9 w-9 items-center justify-center rounded-lg ${form.is_public ? 'bg-orange-100' : 'bg-gray-200'}`}>
                <Globe className={`h-4 w-4 ${form.is_public ? 'text-orange-500' : 'text-gray-500'}`} />
              </div>
              <div>
                <p className="text-sm font-semibold text-gray-900">Tienda pública</p>
                <p className="text-xs text-gray-500">
                  Permite que tus clientes visiten tu tienda en línea.
                </p>
              </div>
            </div>
            <Toggle checked={form.is_public} onChange={(v) => setField('is_public', v)} />
          </div>

          <SectionCard>
            {saveFooter}
          </SectionCard>
        </div>
      )}

      {/* ── Tab: Contacto ─────────────────────────────────────────────────── */}
      {tab === 'contact' && (
        <div className="space-y-0">
          <SectionCard>
            <h2 className="text-base font-semibold text-gray-900 mb-1">Datos de contacto</h2>
            <p className="text-sm text-gray-500 mb-5">Cómo pueden comunicarse contigo tus clientes.</p>

            <div className="grid gap-4 sm:grid-cols-2">
              <Field label="Teléfono" hint="Número de contacto principal">
                <div className="relative">
                  <Phone className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                  <input
                    type="text"
                    value={form.phone}
                    placeholder="Ej: 3001234567"
                    onChange={(e) => setField('phone', e.target.value)}
                    className="w-full rounded-lg border border-gray-300 pl-9 pr-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 outline-none transition focus:border-transparent focus:ring-2 focus:ring-orange-400"
                  />
                </div>
              </Field>
              <Field label="WhatsApp" hint="Número para chatear directo">
                <div className="relative">
                  <MessageCircle className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                  <input
                    type="text"
                    value={form.whatsapp}
                    placeholder="Ej: 3001234567"
                    onChange={(e) => setField('whatsapp', e.target.value)}
                    className="w-full rounded-lg border border-gray-300 pl-9 pr-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 outline-none transition focus:border-transparent focus:ring-2 focus:ring-orange-400"
                  />
                </div>
              </Field>
            </div>

            <div className="mt-4">
              <Field label="Correo electrónico">
                <div className="relative">
                  <Mail className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                  <input
                    type="email"
                    value={form.email}
                    placeholder="contacto@tienda.com"
                    onChange={(e) => setField('email', e.target.value)}
                    className="w-full rounded-lg border border-gray-300 pl-9 pr-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 outline-none transition focus:border-transparent focus:ring-2 focus:ring-orange-400"
                  />
                </div>
              </Field>
            </div>

            <div className="mt-4">
              <Field label="Horario de atención" hint="Ej: Lun–Vie 8am–6pm / Sáb 9am–1pm">
                <div className="relative">
                  <Clock className="absolute left-3 top-3 h-4 w-4 text-gray-400 pointer-events-none" />
                  <textarea
                    value={form.schedule}
                    placeholder={'Lun a Vie: 8am - 6pm\nSáb: 9am - 1pm\nDom: cerrado'}
                    rows={4}
                    onChange={(e) => setField('schedule', e.target.value)}
                    className="w-full resize-none rounded-lg border border-gray-300 pl-9 pr-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 outline-none transition focus:border-transparent focus:ring-2 focus:ring-orange-400"
                  />
                </div>
              </Field>
            </div>

            {saveFooter}
          </SectionCard>
        </div>
      )}

      {/* ── Tab: Diseño ───────────────────────────────────────────────────── */}
      {tab === 'design' && (
        <div className="space-y-0">
          {/* Logo */}
          <SectionCard>
            <h2 className="text-base font-semibold text-gray-900 mb-1">Logo de la tienda</h2>
            <p className="text-sm text-gray-500 mb-5">Recomendado: 512×512 px, fondo transparente. Máximo 5 MB.</p>

            <div className="flex flex-col gap-4 sm:flex-row sm:items-start">
              {previews.logo ? (
                <img
                  src={previews.logo}
                  alt="Logo"
                  className="h-24 w-24 flex-shrink-0 rounded-full border-2 border-gray-200 bg-white object-contain p-1.5 shadow-sm"
                />
              ) : (
                <div className="flex h-24 w-24 flex-shrink-0 items-center justify-center rounded-full border-2 border-dashed border-gray-300 bg-gray-50 text-xs text-gray-400">
                  Sin logo
                </div>
              )}

              <div className="space-y-2">
                <div className="flex flex-wrap gap-2">
                  <button
                    type="button"
                    onClick={() => logoRef.current?.click()}
                    className="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                  >
                    {previews.logo ? 'Cambiar logo' : 'Subir logo'}
                  </button>
                  {previews.logo && (
                    <button
                      type="button"
                      onClick={() => removeImage('logo')}
                      className="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-medium text-red-500 transition hover:bg-red-50"
                    >
                      Eliminar logo
                    </button>
                  )}
                </div>
              </div>
            </div>
          </SectionCard>

          {/* Portada */}
          <SectionCard>
            <h2 className="text-base font-semibold text-gray-900 mb-1">Portada</h2>
            <p className="text-sm text-gray-500 mb-5">
              Recomendado: 1600×600 px. Se muestra como hero en tu tienda pública. Máximo 5 MB.
            </p>

            <div className="relative h-48 overflow-hidden rounded-xl border border-gray-200 bg-gray-100">
              {previews.cover ? (
                <>
                  <img
                    src={previews.cover}
                    alt="Portada"
                    className="absolute inset-0 h-full w-full object-cover"
                  />
                  <div className="absolute inset-0 bg-black/40" />

                  <button
                    type="button"
                    onClick={() => removeImage('cover')}
                    className="absolute right-3 top-3 rounded-lg bg-black/50 px-2.5 py-1 text-xs font-semibold text-white backdrop-blur-sm transition hover:bg-red-600/80"
                  >
                    Eliminar
                  </button>

                  <button
                    type="button"
                    onClick={() => coverRef.current?.click()}
                    className="absolute bottom-3 right-3 inline-flex items-center gap-1.5 rounded-xl bg-black/50 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur-sm transition hover:bg-black/70"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      className="h-3.5 w-3.5"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Cambiar portada
                  </button>
                </>
              ) : (
                <div className="flex h-full flex-col items-center justify-center gap-3">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    className="h-8 w-8 text-gray-300"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={1.5}
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 16M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                  <p className="text-xs text-gray-400">Sin portada</p>
                  <button
                    type="button"
                    onClick={() => coverRef.current?.click()}
                    className="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                  >
                    Subir portada
                  </button>
                </div>
              )}
            </div>

            <div className="mt-5">
              {saveFooter}
            </div>
          </SectionCard>
        </div>
      )}

      {/* ── Tab: Pagos ────────────────────────────────────────────────────── */}
      {tab === 'payments' && (
        <div className="space-y-0">
          <SectionCard>
            <h2 className="text-base font-semibold text-gray-900 mb-1">Métodos de pago</h2>
            <p className="text-sm text-gray-500 mb-5">
              Selecciona cómo recibirás pagos de tus clientes.
            </p>

            <div className="space-y-3">
              {PAYMENT_OPTIONS.map((opt) => {
                const checked = form.payment_methods.includes(opt.id)
                const { Icon } = opt
                return (
                  <div
                    key={opt.id}
                    className={`flex items-center justify-between rounded-xl border px-4 py-3.5 transition-all shadow-sm ${
                      checked ? opt.activeCard : opt.inactiveCard
                    }`}
                    style={{ boxShadow: '0 2px 8px 0 rgba(0,0,0,0.06)' }}
                  >
                    <div className="flex items-center gap-3">
                      <div
                        className="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg"
                        style={{ backgroundColor: `${opt.color}18` }}
                      >
                        <Icon className="h-4 w-4" style={{ color: opt.color }} />
                      </div>
                      <div>
                        <p className="text-sm font-semibold text-gray-900">{opt.label}</p>
                        <p className="text-xs text-gray-500">{opt.description}</p>
                      </div>
                    </div>
                    <Toggle
                      checked={checked}
                      onChange={() =>
                        setField(
                          'payment_methods',
                          checked
                            ? form.payment_methods.filter((m) => m !== opt.id)
                            : [...form.payment_methods, opt.id],
                        )
                      }
                    />
                  </div>
                )
              })}
            </div>
          </SectionCard>

          {/* Impuestos */}
          <SectionCard>
            <div className="flex items-center gap-3 mb-4">
              <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100">
                <Receipt className="h-4 w-4 text-gray-500" />
              </div>
              <div>
                <h3 className="text-base font-semibold text-gray-900">Impuestos</h3>
                <p className="text-sm text-gray-500">
                  Activa para incluir IVA u otros impuestos en pedidos y facturas.
                </p>
              </div>
            </div>

            <div
              className={`flex items-center justify-between gap-4 rounded-xl border px-4 py-3.5 transition-colors ${
                form.taxes_enabled ? 'bg-orange-50 border-orange-200' : 'bg-gray-50 border-gray-200'
              }`}
            >
              <div>
                <p className="text-sm font-semibold text-gray-900">Activar impuestos</p>
                <p className="text-xs text-gray-500">
                  Se mostrará el desglose de impuestos en facturas y pedidos.
                </p>
              </div>
              <Toggle checked={form.taxes_enabled} onChange={(v) => setField('taxes_enabled', v)} />
            </div>

            <div className="mt-5">
              {saveFooter}
            </div>
          </SectionCard>
        </div>
      )}
    </div>
  )
}
