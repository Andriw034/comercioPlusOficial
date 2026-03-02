import { useEffect, useRef, useState } from 'react'
import type { ChangeEvent, ReactNode } from 'react'
import API from '@/lib/api'
import { ErpBtn, ErpBadge, ErpPageHeader } from '@/components/erp'
import { uploadStoreLogo, uploadStoreCover } from '@/services/uploads'

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
  { id: 'cash', label: 'Efectivo' },
  { id: 'transfer', label: 'Transferencia' },
  { id: 'wompi', label: 'Wompi' },
  { id: 'cod', label: 'Contraentrega' },
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
  return <div className={`animate-pulse rounded-xl bg-slate-200 dark:bg-white/10 ${className}`} />
}

function Toggle({ checked, onChange }: { checked: boolean; onChange: (v: boolean) => void }) {
  return (
    <button
      type="button"
      role="switch"
      aria-checked={checked}
      onClick={() => onChange(!checked)}
      className={`relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full transition-colors ${
        checked ? 'bg-orange-500' : 'bg-slate-300 dark:bg-slate-600'
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
      <label className="block text-[12px] font-semibold text-slate-700 dark:text-white/80">{label}</label>
      {hint ? <p className="text-[11px] text-slate-400 dark:text-white/40">{hint}</p> : null}
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
      className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-900 placeholder-slate-400 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder-white/30 dark:focus:border-orange-400"
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
      className="w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-900 placeholder-slate-400 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder-white/30 dark:focus:border-orange-400"
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
      className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
    >
      {options.map((o) => (
        <option key={o.value} value={o.value}>
          {o.label}
        </option>
      ))}
    </select>
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

      await API.put('/merchant/store', { ...form, logo_url: logoUrl, cover_url: coverUrl })

      setForm((prev) => ({ ...prev, logo_url: logoUrl, cover_url: coverUrl }))
      setPreviews({ logo: logoUrl, cover: coverUrl })
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

  // ── Save footer (inlined per tab) ─────────────────────────────────────────

  const saveFooter = (
    <div className="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5 dark:border-white/10">
      <ErpBtn variant="primary" onClick={save} disabled={saving}>
        {saving ? 'Guardando...' : 'Guardar cambios'}
      </ErpBtn>
      {saved && (
        <span className="text-[12px] font-medium text-green-600 dark:text-green-400">
          Cambios guardados correctamente
        </span>
      )}
      {saveError && (
        <span className="text-[12px] font-medium text-red-600 dark:text-red-400">{saveError}</span>
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
        <div className="flex gap-1">
          {[1, 2, 3, 4].map((i) => (
            <Skeleton key={i} className="h-9 w-24" />
          ))}
        </div>
        <div className="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-white/5">
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
        <div className="max-w-sm rounded-2xl border border-red-200 bg-red-50 px-6 py-5 dark:border-red-900/30 dark:bg-red-950/30">
          <p className="text-[14px] font-semibold text-red-700 dark:text-red-400">{loadError}</p>
        </div>
        <ErpBtn variant="primary" onClick={load}>
          Reintentar
        </ErpBtn>
      </div>
    )
  }

  // ── Render ────────────────────────────────────────────────────────────────

  return (
    <div className="space-y-6 pb-24">
      {fileInputs}

      <ErpPageHeader
        breadcrumb="Configuracion"
        title="Mi Tienda"
        subtitle="Edita los datos, diseño y configuracion de tu tienda publica"
        actions={
          <ErpBadge
            status={form.is_public ? 'active' : 'inactive'}
            label={form.is_public ? 'Publica' : 'Privada'}
          />
        }
      />

      {/* Tab bar */}
      <div className="flex gap-1 overflow-x-auto rounded-2xl border border-slate-200 bg-slate-50 p-1 dark:border-white/10 dark:bg-white/5">
        {TABS.map((t) => (
          <button
            key={t.id}
            type="button"
            onClick={() => { setTab(t.id); setSaveError(null); setSaved(false) }}
            className={`whitespace-nowrap rounded-xl px-4 py-2 text-[13px] font-semibold transition-all ${
              tab === t.id
                ? 'bg-white text-slate-900 shadow-sm dark:bg-white/10 dark:text-white'
                : 'text-slate-500 hover:text-slate-800 dark:text-white/50 dark:hover:text-white/80'
            }`}
          >
            {t.label}
          </button>
        ))}
      </div>

      {/* ── Tab: General ──────────────────────────────────────────────────── */}
      {tab === 'general' && (
        <div className="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-white/5">
          <div>
            <h2 className="text-[16px] font-bold text-slate-900 dark:text-white">Informacion general</h2>
            <p className="text-[12px] text-slate-500 dark:text-white/50">Datos principales de tu tienda.</p>
          </div>

          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Nombre de la tienda *">
              <TextInput
                value={form.name}
                onChange={(v) => setField('name', v)}
                placeholder="Ej: Panaderia La Esperanza"
              />
            </Field>
            <Field label="Categoria">
              <TextInput
                value={form.category}
                onChange={(v) => setField('category', v)}
                placeholder="Ej: Alimentos, Ropa, Tecnologia…"
              />
            </Field>
          </div>

          <Field label="Descripcion">
            <TextArea
              value={form.description}
              onChange={(v) => setField('description', v)}
              placeholder="Cuéntale a tus clientes qué vendes y por qué elegiirte…"
              rows={3}
            />
          </Field>

          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Ciudad">
              <TextInput
                value={form.city}
                onChange={(v) => setField('city', v)}
                placeholder="Ej: Bogotá, Medellín, Cali…"
              />
            </Field>
            <Field label="Direccion">
              <TextInput
                value={form.address}
                onChange={(v) => setField('address', v)}
                placeholder="Calle 123 #45-67"
              />
            </Field>
          </div>

          <Field label="Moneda" hint="Se usara en precios y reportes.">
            <StoreSelect
              value={form.currency}
              onChange={(v) => setField('currency', v)}
              options={CURRENCIES.map((c) => ({ value: c, label: c }))}
            />
          </Field>

          <div className="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
            <div>
              <p className="text-[13px] font-semibold text-slate-900 dark:text-white">Tienda publica</p>
              <p className="text-[12px] text-slate-500 dark:text-white/50">
                Permite que tus clientes visiten tu tienda en linea.
              </p>
            </div>
            <Toggle checked={form.is_public} onChange={(v) => setField('is_public', v)} />
          </div>

          {saveFooter}
        </div>
      )}

      {/* ── Tab: Contacto ─────────────────────────────────────────────────── */}
      {tab === 'contact' && (
        <div className="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-white/5">
          <div>
            <h2 className="text-[16px] font-bold text-slate-900 dark:text-white">Datos de contacto</h2>
            <p className="text-[12px] text-slate-500 dark:text-white/50">Como pueden comunicarse contigo tus clientes.</p>
          </div>

          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Telefono" hint="Numero de contacto principal">
              <TextInput
                value={form.phone}
                onChange={(v) => setField('phone', v)}
                placeholder="Ej: 3001234567"
              />
            </Field>
            <Field label="WhatsApp" hint="Numero para chatear directo">
              <TextInput
                value={form.whatsapp}
                onChange={(v) => setField('whatsapp', v)}
                placeholder="Ej: 3001234567"
              />
            </Field>
          </div>

          <Field label="Correo electronico">
            <TextInput
              type="email"
              value={form.email}
              onChange={(v) => setField('email', v)}
              placeholder="contacto@tienda.com"
            />
          </Field>

          <Field label="Horario de atencion" hint="Ej: Lun–Vie 8am–6pm / Sáb 9am–1pm">
            <TextArea
              value={form.schedule}
              onChange={(v) => setField('schedule', v)}
              placeholder={'Lun a Vie: 8am - 6pm\nSáb: 9am - 1pm\nDom: cerrado'}
              rows={4}
            />
          </Field>

          {saveFooter}
        </div>
      )}

      {/* ── Tab: Diseño ───────────────────────────────────────────────────── */}
      {tab === 'design' && (
        <div className="space-y-6 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-white/5">
          <div>
            <h2 className="text-[16px] font-bold text-slate-900 dark:text-white">Diseño de la tienda</h2>
            <p className="text-[12px] text-slate-500 dark:text-white/50">
              Logo y portada que veran tus clientes al visitar tu tienda.
            </p>
          </div>

          {/* Logo */}
          <div className="flex flex-col gap-4 sm:flex-row sm:items-start">
            {/* Circular preview */}
            {previews.logo ? (
              <img
                src={previews.logo}
                alt="Logo"
                className="h-24 w-24 flex-shrink-0 rounded-full border-2 border-slate-200 bg-white object-contain p-1.5 shadow dark:border-white/10"
              />
            ) : (
              <div className="flex h-24 w-24 flex-shrink-0 items-center justify-center rounded-full border-2 border-dashed border-slate-300 bg-slate-50 text-[11px] text-slate-400 dark:border-white/20 dark:bg-white/5 dark:text-white/30">
                Sin logo
              </div>
            )}

            <div className="space-y-2">
              <p className="text-[13px] font-semibold text-slate-900 dark:text-white">Logo de la tienda</p>
              <p className="text-[12px] text-slate-500 dark:text-white/40">
                Recomendado: 512×512 px, fondo transparente. Maximo 5 MB.
              </p>
              <div className="flex flex-wrap gap-2">
                <ErpBtn variant="secondary" size="sm" onClick={() => logoRef.current?.click()}>
                  {previews.logo ? 'Cambiar logo' : 'Subir logo'}
                </ErpBtn>
                {previews.logo && (
                  <ErpBtn variant="danger" size="sm" onClick={() => removeImage('logo')}>
                    Eliminar logo
                  </ErpBtn>
                )}
              </div>
            </div>
          </div>

          <hr className="border-slate-100 dark:border-white/10" />

          {/* Cover */}
          <div className="space-y-3">
            <div>
              <p className="text-[13px] font-semibold text-slate-900 dark:text-white">Portada</p>
              <p className="text-[12px] text-slate-500 dark:text-white/40">
                Recomendado: 1600×600 px. Se muestra como hero en tu tienda publica. Maximo 5 MB.
              </p>
            </div>

            {/* Preview area */}
            <div className="relative h-48 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 dark:border-white/10 dark:bg-white/5">
              {previews.cover ? (
                <>
                  <img
                    src={previews.cover}
                    alt="Portada"
                    className="absolute inset-0 h-full w-full object-cover"
                  />
                  {/* Dark overlay — always on, ensures legibility */}
                  <div className="absolute inset-0 bg-black/40" />

                  {/* Floating delete button — top right */}
                  <button
                    type="button"
                    onClick={() => removeImage('cover')}
                    className="absolute right-3 top-3 rounded-lg bg-black/50 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur-sm transition hover:bg-red-600/80"
                  >
                    Eliminar
                  </button>

                  {/* Floating change button — bottom right */}
                  <button
                    type="button"
                    onClick={() => coverRef.current?.click()}
                    className="absolute bottom-3 right-3 inline-flex items-center gap-1.5 rounded-xl bg-black/50 px-3 py-1.5 text-[12px] font-semibold text-white backdrop-blur-sm transition hover:bg-black/70"
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
                    className="h-8 w-8 text-slate-300 dark:text-white/20"
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
                  <p className="text-[12px] text-slate-400 dark:text-white/30">Sin portada</p>
                  <ErpBtn variant="secondary" size="sm" onClick={() => coverRef.current?.click()}>
                    Subir portada
                  </ErpBtn>
                </div>
              )}
            </div>
          </div>

          {saveFooter}
        </div>
      )}

      {/* ── Tab: Pagos ────────────────────────────────────────────────────── */}
      {tab === 'payments' && (
        <div className="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-white/5">
          <div>
            <h2 className="text-[16px] font-bold text-slate-900 dark:text-white">Metodos de pago</h2>
            <p className="text-[12px] text-slate-500 dark:text-white/50">
              Selecciona como recibiras pagos de tus clientes.
            </p>
          </div>

          <div className="space-y-2">
            {PAYMENT_OPTIONS.map((opt) => {
              const checked = form.payment_methods.includes(opt.id)
              return (
                <label
                  key={opt.id}
                  className="flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:border-orange-300 dark:border-white/10 dark:bg-white/5 dark:hover:border-orange-500/40"
                >
                  <span className="text-[13px] font-medium text-slate-900 dark:text-white">{opt.label}</span>
                  <input
                    type="checkbox"
                    checked={checked}
                    onChange={() =>
                      setField(
                        'payment_methods',
                        checked
                          ? form.payment_methods.filter((m) => m !== opt.id)
                          : [...form.payment_methods, opt.id],
                      )
                    }
                    className="h-4 w-4 rounded border-slate-300 accent-orange-500 dark:border-white/20"
                  />
                </label>
              )
            })}
          </div>

          <hr className="border-slate-100 dark:border-white/10" />

          <div>
            <h3 className="text-[14px] font-bold text-slate-900 dark:text-white">Impuestos</h3>
            <p className="mt-0.5 text-[12px] text-slate-500 dark:text-white/50">
              Activa para incluir IVA u otros impuestos en pedidos y facturas.
            </p>
          </div>

          <div className="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
            <div>
              <p className="text-[13px] font-semibold text-slate-900 dark:text-white">Activar impuestos</p>
              <p className="text-[12px] text-slate-500 dark:text-white/50">
                Se mostrara el desglose de impuestos en facturas y pedidos.
              </p>
            </div>
            <Toggle checked={form.taxes_enabled} onChange={(v) => setField('taxes_enabled', v)} />
          </div>

          {saveFooter}
        </div>
      )}
    </div>
  )
}
