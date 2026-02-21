import { useEffect, useState } from 'react'
import API from '@/lib/api'
import GlassCard from '@/components/ui/GlassCard'
import Button from '@/components/ui/button'
import Input from '@/components/ui/Input'

// -- Types ---------------------------------------------------------------------

interface TaxRule {
  id: string
  name: string
  rate: number
  applies_to: string
  is_active: boolean
}

interface PaymentMethod {
  id: string
  name: string
  description: string
  icon: string
  is_active: boolean
  requires_config: boolean
}

interface ShippingZone {
  id: string
  name: string
  description: string
  price: number
  days_min: number
  days_max: number
  is_active: boolean
}

interface NotificationSetting {
  id: string
  label: string
  description: string
  email: boolean
  in_app: boolean
}

interface FiscalData {
  company_name: string
  nit: string
  tax_regime: string
  city: string
  address: string
  billing_email: string
  phone: string
  responsible_name: string
}

interface Settings {
  currency: string
  timezone: string
  language: string
  taxes: TaxRule[]
  payments: PaymentMethod[]
  shipping: ShippingZone[]
  notifications: NotificationSetting[]
  fiscal: FiscalData
}

type TabId = 'taxes' | 'payments' | 'shipping' | 'notifications' | 'fiscal'

// -- Config --------------------------------------------------------------------

const TABS: { id: TabId; label: string; icon: string }[] = [
  { id: 'taxes', label: 'Impuestos', icon: '🧮' },
  { id: 'payments', label: 'Metodos de pago', icon: '💳' },
  { id: 'shipping', label: 'Envios', icon: '🚚' },
  { id: 'notifications', label: 'Notificaciones', icon: '🔔' },
  { id: 'fiscal', label: 'Datos fiscales', icon: '🧾' },
]

const DEFAULT_SETTINGS: Settings = {
  currency: 'COP',
  timezone: 'America/Bogota',
  language: 'es',
  taxes: [
    { id: 'iva-19', name: 'IVA General', rate: 19, applies_to: 'Mayoria de productos', is_active: true },
    { id: 'iva-5', name: 'IVA Reducido', rate: 5, applies_to: 'Productos de la canasta basica', is_active: false },
    { id: 'exento', name: 'Exento de IVA', rate: 0, applies_to: 'Productos excluidos por ley', is_active: false },
    { id: 'ic', name: 'Impoconsumo', rate: 8, applies_to: 'Comidas y bebidas', is_active: false },
  ],
  payments: [
    { id: 'pse', name: 'PSE', description: 'Debito bancario directo desde bancos colombianos', icon: '🏦', is_active: true, requires_config: false },
    { id: 'card', name: 'Tarjeta credito / debito', description: 'Visa, Mastercard, Amex - procesado por Wompi', icon: '💳', is_active: true, requires_config: true },
    { id: 'nequi', name: 'Nequi', description: 'Billetera digital de Bancolombia', icon: '📱', is_active: false, requires_config: true },
    { id: 'daviplata', name: 'Daviplata', description: 'Billetera digital de Davivienda', icon: '📲', is_active: false, requires_config: true },
    { id: 'cash', name: 'Contra-entrega', description: 'Pago en efectivo al recibir el pedido', icon: '💵', is_active: false, requires_config: false },
  ],
  shipping: [
    { id: 'bogota', name: 'Bogota D.C.', description: 'Envio dentro de la ciudad', price: 8000, days_min: 1, days_max: 2, is_active: true },
    { id: 'main-cities', name: 'Ciudades principales', description: 'Medellin, Cali, Barranquilla, Bucaramanga', price: 12000, days_min: 2, days_max: 3, is_active: true },
    { id: 'national', name: 'Nacional', description: 'Resto del pais', price: 18000, days_min: 3, days_max: 5, is_active: true },
    { id: 'free', name: 'Envio gratis', description: 'Pedidos mayores a $200.000', price: 0, days_min: 2, days_max: 4, is_active: false },
  ],
  notifications: [
    { id: 'new-order', label: 'Nuevo pedido', description: 'Cuando llega un pedido nuevo', email: true, in_app: true },
    { id: 'paid', label: 'Pago confirmado', description: 'Cuando un pedido es pagado', email: true, in_app: true },
    { id: 'low-stock', label: 'Stock minimo', description: 'Cuando un producto alcanza el minimo', email: true, in_app: true },
    { id: 'new-review', label: 'Nueva resena', description: 'Cuando un cliente deja una resena', email: false, in_app: true },
    { id: 'weekly-report', label: 'Reporte semanal', description: 'Resumen de ventas cada lunes', email: true, in_app: false },
    { id: 'monthly-report', label: 'Reporte mensual', description: 'Informe mensual con metricas', email: false, in_app: false },
  ],
  fiscal: {
    company_name: '',
    nit: '',
    tax_regime: 'Responsable de IVA',
    city: 'Bogota D.C.',
    address: '',
    billing_email: '',
    phone: '',
    responsible_name: '',
  },
}

// -- Toggle --------------------------------------------------------------------

function Toggle({ checked, onChange }: { checked: boolean; onChange: (v: boolean) => void }) {
  return (
    <button
      type="button"
      role="switch"
      aria-checked={checked}
      onClick={() => onChange(!checked)}
      className={`relative inline-flex h-5 w-9 flex-shrink-0 items-center rounded-full transition-colors ${
        checked ? 'bg-orange-500' : 'bg-slate-300 dark:bg-white/20'
      }`}
    >
      <span
        className={`inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform ${
          checked ? 'translate-x-4' : 'translate-x-0.5'
        }`}
      />
    </button>
  )
}

// -- Section header ------------------------------------------------------------

function SectionHeader({ title, description }: { title: string; description: string }) {
  return (
    <div className="mb-5">
      <h3 className="text-[16px] font-semibold text-slate-900 dark:text-white">{title}</h3>
      <p className="text-[13px] text-slate-500 dark:text-white/50">{description}</p>
    </div>
  )
}

// -- SaveBar -------------------------------------------------------------------

function SaveBar({ onSave, saving, message, error }: {
  onSave: () => void
  saving: boolean
  message: string
  error: string
}) {
  return (
    <div className="sticky bottom-0 z-10 flex items-center gap-3 border-t border-slate-200 bg-white/95 px-5 py-3 backdrop-blur dark:border-white/10 dark:bg-slate-900/95 -mx-6 -mb-6 mt-6 px-6">
      <Button onClick={onSave} loading={saving}>
        {saving ? 'Guardando...' : 'Guardar cambios'}
      </Button>
      {message && <span className="text-[12px] text-green-600 dark:text-green-400">{message}</span>}
      {error && <span className="text-[12px] text-red-600 dark:text-red-400">{error}</span>}
    </div>
  )
}

// -- TaxesTab ------------------------------------------------------------------

function TaxesTab({ taxes, onChange }: { taxes: TaxRule[]; onChange: (t: TaxRule[]) => void }) {
  const toggle = (id: string) => onChange(taxes.map((t) => t.id === id ? { ...t, is_active: !t.is_active } : t))

  return (
    <div>
      <SectionHeader
        title="Reglas de impuestos"
        description="Configura los impuestos que aplican a tus productos. Los productos heredan la regla por defecto de su categoria."
      />
      <div className="space-y-3">
        {taxes.map((tax) => (
          <div
            key={tax.id}
            className={`flex items-center justify-between rounded-2xl border p-4 transition-colors ${
              tax.is_active
                ? 'border-orange-200 bg-orange-50/50 dark:border-orange-500/20 dark:bg-orange-500/5'
                : 'border-slate-200 bg-slate-50 dark:border-white/10 dark:bg-white/5'
            }`}
          >
            <div className="flex-1 min-w-0">
              <div className="flex items-center gap-2">
                <p className="text-[14px] font-semibold text-slate-900 dark:text-white">{tax.name}</p>
                <span className={`rounded-full px-2 py-0.5 font-mono text-[12px] font-bold ${
                  tax.is_active ? 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300' : 'bg-slate-200 text-slate-500 dark:bg-white/10 dark:text-white/30'
                }`}>
                  {tax.rate}%
                </span>
              </div>
              <p className="mt-0.5 text-[12px] text-slate-500 dark:text-white/40">{tax.applies_to}</p>
            </div>
            <Toggle checked={tax.is_active} onChange={() => toggle(tax.id)} />
          </div>
        ))}
      </div>

      <div className="mt-4 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 dark:border-sky-500/20 dark:bg-sky-500/10">
        <p className="text-[12px] text-sky-700 dark:text-sky-300">
          <strong>Nota DIAN:</strong> La configuracion de impuestos debe estar alineada con tu regimen tributario. Consulta con tu contador para la configuracion correcta.
        </p>
      </div>
    </div>
  )
}

// -- PaymentsTab ---------------------------------------------------------------

function PaymentsTab({ payments, onChange }: { payments: PaymentMethod[]; onChange: (p: PaymentMethod[]) => void }) {
  const toggle = (id: string) => onChange(payments.map((p) => p.id === id ? { ...p, is_active: !p.is_active } : p))

  return (
    <div>
      <SectionHeader
        title="Metodos de pago"
        description="Activa los canales de cobro disponibles para tus clientes. Algunos requieren configuracion adicional en la plataforma de pagos."
      />
      <div className="space-y-3">
        {payments.map((pm) => (
          <div
            key={pm.id}
            className={`flex items-center gap-4 rounded-2xl border p-4 transition-colors ${
              pm.is_active
                ? 'border-orange-200 bg-orange-50/50 dark:border-orange-500/20 dark:bg-orange-500/5'
                : 'border-slate-200 bg-slate-50 dark:border-white/10 dark:bg-white/5'
            }`}
          >
            <span className="text-2xl flex-shrink-0">{pm.icon}</span>
            <div className="flex-1 min-w-0">
              <div className="flex items-center gap-2">
                <p className="text-[14px] font-semibold text-slate-900 dark:text-white">{pm.name}</p>
                {pm.requires_config && (
                  <span className="rounded-full border border-amber-300 bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/15 dark:text-amber-300">
                    Requiere config.
                  </span>
                )}
              </div>
              <p className="mt-0.5 text-[12px] text-slate-500 dark:text-white/40">{pm.description}</p>
            </div>
            <Toggle checked={pm.is_active} onChange={() => toggle(pm.id)} />
          </div>
        ))}
      </div>
    </div>
  )
}

// -- ShippingTab ---------------------------------------------------------------

function ShippingTab({ zones, onChange }: { zones: ShippingZone[]; onChange: (z: ShippingZone[]) => void }) {
  const [editing, setEditing] = useState<string | null>(null)

  const toggle = (id: string) => onChange(zones.map((z) => z.id === id ? { ...z, is_active: !z.is_active } : z))
  const updatePrice = (id: string, price: number) => onChange(zones.map((z) => z.id === id ? { ...z, price } : z))

  const fmt = (n: number) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(n)

  return (
    <div>
      <SectionHeader
        title="Zonas de envio"
        description="Define el costo y tiempos de entrega por zona geografica. El envio gratuito se aplica automaticamente si esta activo y se cumple la condicion."
      />
      <div className="space-y-3">
        {zones.map((zone) => (
          <div
            key={zone.id}
            className={`rounded-2xl border p-4 transition-colors ${
              zone.is_active
                ? 'border-orange-200 bg-orange-50/50 dark:border-orange-500/20 dark:bg-orange-500/5'
                : 'border-slate-200 bg-slate-50 dark:border-white/10 dark:bg-white/5'
            }`}
          >
            <div className="flex items-center justify-between">
              <div className="flex-1 min-w-0">
                <p className="text-[14px] font-semibold text-slate-900 dark:text-white">{zone.name}</p>
                <p className="text-[12px] text-slate-500 dark:text-white/40">{zone.description}</p>
                <p className="mt-0.5 text-[11px] text-slate-400 dark:text-white/30">
                  ~ {zone.days_min}-{zone.days_max} dias habiles
                </p>
              </div>
              <div className="flex items-center gap-3">
                <div className="text-right">
                  {editing === zone.id ? (
                    <input
                      type="number"
                      autoFocus
                      value={zone.price}
                      onChange={(e) => updatePrice(zone.id, Number(e.target.value))}
                      onBlur={() => setEditing(null)}
                      className="w-24 rounded-lg border border-orange-400 px-2 py-1 text-right text-[13px] font-bold outline-none ring-2 ring-orange-400/20 dark:bg-white/10 dark:text-white"
                    />
                  ) : (
                    <button
                      onClick={() => setEditing(zone.id)}
                      className="rounded-lg px-2 py-1 text-right text-[15px] font-bold text-slate-900 transition-colors hover:bg-slate-200 dark:text-white dark:hover:bg-white/10"
                    >
                      {zone.price === 0 ? 'Gratis' : fmt(zone.price)}
                    </button>
                  )}
                  <p className="text-[10px] text-slate-400 dark:text-white/30">Toca para editar</p>
                </div>
                <Toggle checked={zone.is_active} onChange={() => toggle(zone.id)} />
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}

// -- NotificationsTab ----------------------------------------------------------

function NotificationsTab({ notifications, onChange }: { notifications: NotificationSetting[]; onChange: (n: NotificationSetting[]) => void }) {
  const update = (id: string, key: 'email' | 'in_app', value: boolean) =>
    onChange(notifications.map((n) => n.id === id ? { ...n, [key]: value } : n))

  return (
    <div>
      <SectionHeader
        title="Preferencias de notificaciones"
        description="Elige como y cuando quieres recibir alertas sobre la actividad de tu tienda."
      />
      <div className="overflow-hidden rounded-2xl border border-slate-200 dark:border-white/10">
        <div className="grid grid-cols-[1fr_80px_80px] border-b border-slate-100 bg-slate-50 px-5 py-2.5 dark:border-white/5 dark:bg-white/5">
          <span className="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-white/30">Evento</span>
          <span className="text-center text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-white/30">Email</span>
          <span className="text-center text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-white/30">App</span>
        </div>
        {notifications.map((n, i) => (
          <div
            key={n.id}
            className={`grid grid-cols-[1fr_80px_80px] items-center px-5 py-3.5 ${
              i < notifications.length - 1 ? 'border-b border-slate-100 dark:border-white/5' : ''
            }`}
          >
            <div>
              <p className="text-[13px] font-semibold text-slate-800 dark:text-white">{n.label}</p>
              <p className="text-[11px] text-slate-400 dark:text-white/30">{n.description}</p>
            </div>
            <div className="flex justify-center">
              <Toggle checked={n.email} onChange={(v) => update(n.id, 'email', v)} />
            </div>
            <div className="flex justify-center">
              <Toggle checked={n.in_app} onChange={(v) => update(n.id, 'in_app', v)} />
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}

// -- FiscalTab -----------------------------------------------------------------

function FiscalTab({ fiscal, onChange }: { fiscal: FiscalData; onChange: (f: FiscalData) => void }) {
  const set = (key: keyof FiscalData, value: string) => onChange({ ...fiscal, [key]: value })

  return (
    <div>
      <SectionHeader
        title="Datos fiscales y facturacion"
        description="Esta informacion se usa en las facturas electronicas y documentos tributarios. Debe coincidir exactamente con los datos registrados en la DIAN."
      />
      <div className="space-y-4">
        <div className="grid gap-4 md:grid-cols-2">
          <Input
            label="Razon social"
            value={fiscal.company_name}
            onChange={(e) => set('company_name', e.target.value)}
            placeholder="Mi Empresa S.A.S."
          />
          <Input
            label="NIT"
            value={fiscal.nit}
            onChange={(e) => set('nit', e.target.value)}
            placeholder="900.123.456-7"
          />
        </div>
        <div className="grid gap-4 md:grid-cols-2">
          <div>
            <label className="mb-1.5 block text-[13px] font-medium text-slate-700 dark:text-white/70">
              Regimen tributario
            </label>
            <select
              value={fiscal.tax_regime}
              onChange={(e) => set('tax_regime', e.target.value)}
              className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-[13px] text-slate-900 outline-none transition-colors focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
            >
              <option>Responsable de IVA</option>
              <option>No responsable de IVA</option>
              <option>Gran contribuyente</option>
              <option>Autorretenedor</option>
            </select>
          </div>
          <Input
            label="Ciudad"
            value={fiscal.city}
            onChange={(e) => set('city', e.target.value)}
            placeholder="Bogota D.C."
          />
        </div>
        <Input
          label="Direccion fiscal"
          value={fiscal.address}
          onChange={(e) => set('address', e.target.value)}
          placeholder="Calle 123 #45-67, Bogota"
        />
        <div className="grid gap-4 md:grid-cols-2">
          <Input
            label="Email de facturacion"
            type="email"
            value={fiscal.billing_email}
            onChange={(e) => set('billing_email', e.target.value)}
            placeholder="facturacion@empresa.com"
          />
          <Input
            label="Telefono"
            value={fiscal.phone}
            onChange={(e) => set('phone', e.target.value)}
            placeholder="+57 300 123 4567"
          />
        </div>
        <Input
          label="Nombre del responsable"
          value={fiscal.responsible_name}
          onChange={(e) => set('responsible_name', e.target.value)}
          placeholder="Nombre completo del representante legal"
        />
      </div>

      <div className="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-500/20 dark:bg-amber-500/10">
        <p className="text-[12px] text-amber-700 dark:text-amber-300">
          ℹ️ Para habilitar la <strong>factura electronica</strong> integrada con la DIAN, el equipo de ComercioPlus debe configurar tu habilitador. Escribenos a soporte@comercioplus.co.
        </p>
      </div>
    </div>
  )
}

// -- GlobalSection -------------------------------------------------------------

function GlobalSection({ settings, onChange }: { settings: Settings; onChange: (s: Partial<Settings>) => void }) {
  return (
    <GlassCard className="space-y-4">
      <div>
        <h3 className="text-[15px] font-semibold text-slate-900 dark:text-white">Parametros globales</h3>
        <p className="text-[12px] text-slate-500 dark:text-white/40">Configuracion general de la tienda</p>
      </div>
      <div className="grid gap-4 sm:grid-cols-3">
        {[
          {
            label: 'Moneda',
            key: 'currency' as keyof Settings,
            options: ['COP', 'USD', 'EUR'],
          },
          {
            label: 'Idioma',
            key: 'language' as keyof Settings,
            options: ['es', 'en'],
            labels: ['Espanol', 'English'],
          },
          {
            label: 'Zona horaria',
            key: 'timezone' as keyof Settings,
            options: ['America/Bogota', 'America/New_York', 'UTC'],
            labels: ['Bogota (UTC-5)', 'New York (UTC-4/5)', 'UTC'],
          },
        ].map((field) => (
          <div key={field.key}>
            <label className="mb-1.5 block text-[12px] font-medium text-slate-600 dark:text-white/60">{field.label}</label>
            <select
              value={String(settings[field.key])}
              onChange={(e) => onChange({ [field.key]: e.target.value })}
              className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-900 outline-none transition-colors focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
            >
              {field.options.map((opt, i) => (
                <option key={opt} value={opt}>
                  {field.labels ? field.labels[i] : opt}
                </option>
              ))}
            </select>
          </div>
        ))}
      </div>
    </GlassCard>
  )
}

// -- Page ----------------------------------------------------------------------

export default function DashboardSettingsPage() {
  const [activeTab, setActiveTab] = useState<TabId>('taxes')
  const [settings, setSettings] = useState<Settings>(DEFAULT_SETTINGS)
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')
  const [loadError, setLoadError] = useState('')

  // -- Load ---------------------------------------------------------------------

  useEffect(() => {
    const load = async () => {
      setLoading(true)
      setLoadError('')
      try {
        const { data } = await API.get('/settings')
        if (data) setSettings({ ...DEFAULT_SETTINGS, ...data })
      } catch (err: any) {
        if (err?.response?.status !== 404) {
          setLoadError(err?.response?.data?.message || 'No se pudieron cargar los ajustes. Mostrando valores predeterminados.')
        }
      } finally {
        setLoading(false)
      }
    }
    load()
  }, [])

  // -- Save ----------------------------------------------------------------------

  const save = async () => {
    setSaving(true)
    setMessage('')
    setError('')
    try {
      await API.put('/settings', {
        currency: settings.currency,
        timezone: settings.timezone,
        language: settings.language,
        taxes: settings.taxes,
        payments: settings.payments,
        shipping: settings.shipping,
        notifications: settings.notifications,
        fiscal: settings.fiscal,
      })
      setMessage('Cambios guardados correctamente.')
      setTimeout(() => setMessage(''), 4000)
    } catch (err: any) {
      setError(err?.response?.data?.message || 'Error al guardar la configuracion.')
    } finally {
      setSaving(false)
    }
  }

  // -- Updaters ------------------------------------------------------------------

  const updateGlobal = (partial: Partial<Settings>) => setSettings((prev) => ({ ...prev, ...partial }))

  return (
    <div className="space-y-6 pb-6">
      {/* Header */}
      <div>
        <p className="text-[13px] text-slate-500 dark:text-white/50">Dashboard</p>
        <h1 className="font-display text-[32px] font-bold text-slate-950 dark:text-white">Configuracion</h1>
      </div>

      {loadError && (
        <div className="flex items-center gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-[13px] text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">
          <span>⚠️</span><span>{loadError}</span>
        </div>
      )}

      {/* Global params */}
      <GlobalSection settings={settings} onChange={updateGlobal} />

      {/* Tabs */}
      <div>
        {/* Tab bar */}
        <div className="flex gap-1 overflow-x-auto rounded-2xl border border-slate-200 bg-slate-50 p-1.5 dark:border-white/10 dark:bg-white/5">
          {TABS.map((tab) => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id)}
              className={`flex flex-shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-[13px] font-semibold transition-all ${
                activeTab === tab.id
                  ? 'bg-white text-slate-900 shadow-sm dark:bg-white/10 dark:text-white'
                  : 'text-slate-500 hover:text-slate-700 dark:text-white/40 dark:hover:text-white/70'
              }`}
            >
              <span>{tab.icon}</span>
              <span>{tab.label}</span>
            </button>
          ))}
        </div>

        {/* Tab content */}
        <GlassCard className="relative mt-3 pb-20">
          {loading ? (
            <div className="flex flex-col items-center justify-center py-16">
              <div className="mb-3 h-8 w-8 animate-spin rounded-full border-2 border-orange-500 border-t-transparent" />
              <p className="text-[13px] text-slate-400">Cargando configuracion...</p>
            </div>
          ) : (
            <>
              {activeTab === 'taxes' && (
                <TaxesTab taxes={settings.taxes} onChange={(taxes) => setSettings((p) => ({ ...p, taxes }))} />
              )}
              {activeTab === 'payments' && (
                <PaymentsTab payments={settings.payments} onChange={(payments) => setSettings((p) => ({ ...p, payments }))} />
              )}
              {activeTab === 'shipping' && (
                <ShippingTab zones={settings.shipping} onChange={(shipping) => setSettings((p) => ({ ...p, shipping }))} />
              )}
              {activeTab === 'notifications' && (
                <NotificationsTab notifications={settings.notifications} onChange={(notifications) => setSettings((p) => ({ ...p, notifications }))} />
              )}
              {activeTab === 'fiscal' && (
                <FiscalTab fiscal={settings.fiscal} onChange={(fiscal) => setSettings((p) => ({ ...p, fiscal }))} />
              )}
            </>
          )}

          <SaveBar onSave={save} saving={saving} message={message} error={error} />
        </GlassCard>
      </div>
    </div>
  )
}


