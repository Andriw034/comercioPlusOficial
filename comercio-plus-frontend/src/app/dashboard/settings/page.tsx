import { useEffect, useMemo, useState, type ReactNode } from 'react'
import API from '@/lib/api'
import Input from '@/components/ui/Input'
import Switch from '@/components/ui/Switch'
import { ErpBadge, ErpBtn, ErpFilterSelect, ErpKpiCard, ErpPageHeader, ErpSearchBar } from '@/components/erp'

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

// -- Helpers -------------------------------------------------------------------

function fmtCurrency(value: number): string {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(Math.max(0, value))
}

function SectionHeader({ title, description, right }: { title: string; description: string; right?: ReactNode }) {
  return (
    <div className="mb-5 flex flex-wrap items-start justify-between gap-3">
      <div>
        <h3 className="text-[16px] font-semibold text-slate-900 dark:text-white">{title}</h3>
        <p className="text-[13px] text-slate-500 dark:text-white/50">{description}</p>
      </div>
      {right ?? null}
    </div>
  )
}

// -- SaveBar -------------------------------------------------------------------

function SaveBar({
  onSave,
  saving,
  message,
  error,
}: {
  onSave: () => void
  saving: boolean
  message: string
  error: string
}) {
  return (
    <div className="sticky bottom-0 z-10 mt-6 flex flex-wrap items-center gap-3 border-t border-slate-200 bg-white/95 px-5 py-3 backdrop-blur dark:border-white/10 dark:bg-slate-900/95">
      <ErpBtn onClick={onSave} variant="primary" size="md" disabled={saving}>
        {saving ? 'Guardando...' : 'Guardar cambios'}
      </ErpBtn>

      {message ? (
        <div className="inline-flex items-center gap-2">
          <ErpBadge status="approved" label="Guardado" />
          <span className="text-[12px] text-green-700 dark:text-green-300">{message}</span>
        </div>
      ) : null}

      {error ? (
        <div className="inline-flex items-center gap-2">
          <ErpBadge status="rejected" label="Error" />
          <span className="text-[12px] text-red-700 dark:text-red-300">{error}</span>
        </div>
      ) : null}
    </div>
  )
}

// -- TaxesTab ------------------------------------------------------------------

function TaxesTab({ taxes, onChange }: { taxes: TaxRule[]; onChange: (t: TaxRule[]) => void }) {
  const toggle = (id: string) => onChange(taxes.map((t) => (t.id === id ? { ...t, is_active: !t.is_active } : t)))
  const activeCount = taxes.filter((tax) => tax.is_active).length

  return (
    <div>
      <SectionHeader
        title="Reglas de impuestos"
        description="Configura los impuestos que aplican a tus productos. Los productos heredan la regla por defecto de su categoria."
        right={<ErpBadge status="active" label={`${activeCount} activas`} />}
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
            <div className="min-w-0 flex-1">
              <div className="flex items-center gap-2">
                <p className="text-[14px] font-semibold text-slate-900 dark:text-white">{tax.name}</p>
                <ErpBadge status={tax.is_active ? 'active' : 'inactive'} label={`${tax.rate}%`} />
              </div>
              <p className="mt-1 text-[12px] text-slate-500 dark:text-white/40">{tax.applies_to}</p>
            </div>
            <Switch checked={tax.is_active} onCheckedChange={() => toggle(tax.id)} size="sm" aria-label={`Activar ${tax.name}`} />
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
  const toggle = (id: string) => onChange(payments.map((p) => (p.id === id ? { ...p, is_active: !p.is_active } : p)))
  const activeCount = payments.filter((payment) => payment.is_active).length

  return (
    <div>
      <SectionHeader
        title="Metodos de pago"
        description="Activa los canales de cobro disponibles para tus clientes. Algunos requieren configuracion adicional en la plataforma de pagos."
        right={<ErpBadge status="active" label={`${activeCount} activos`} />}
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
            <span className="flex-shrink-0 text-2xl">{pm.icon}</span>
            <div className="min-w-0 flex-1">
              <div className="flex items-center gap-2">
                <p className="text-[14px] font-semibold text-slate-900 dark:text-white">{pm.name}</p>
                {pm.requires_config ? <ErpBadge status="pending" label="Requiere config." /> : null}
              </div>
              <p className="mt-1 text-[12px] text-slate-500 dark:text-white/40">{pm.description}</p>
            </div>
            <Switch checked={pm.is_active} onCheckedChange={() => toggle(pm.id)} size="sm" aria-label={`Activar ${pm.name}`} />
          </div>
        ))}
      </div>
    </div>
  )
}

// -- ShippingTab ---------------------------------------------------------------

function ShippingTab({ zones, onChange }: { zones: ShippingZone[]; onChange: (z: ShippingZone[]) => void }) {
  const [editing, setEditing] = useState<string | null>(null)

  const toggle = (id: string) => onChange(zones.map((z) => (z.id === id ? { ...z, is_active: !z.is_active } : z)))
  const updatePrice = (id: string, price: number) => onChange(zones.map((z) => (z.id === id ? { ...z, price } : z)))

  return (
    <div>
      <SectionHeader
        title="Zonas de envio"
        description="Define el costo y tiempos de entrega por zona geografica. El envio gratuito se aplica automaticamente si esta activo."
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
            <div className="flex items-center justify-between gap-3">
              <div className="min-w-0 flex-1">
                <div className="flex items-center gap-2">
                  <p className="text-[14px] font-semibold text-slate-900 dark:text-white">{zone.name}</p>
                  <ErpBadge status={zone.is_active ? 'active' : 'inactive'} label={zone.is_active ? 'Activa' : 'Inactiva'} />
                </div>
                <p className="text-[12px] text-slate-500 dark:text-white/40">{zone.description}</p>
                <p className="mt-1 text-[11px] text-slate-400 dark:text-white/30">
                  Tiempo estimado: {zone.days_min}-{zone.days_max} dias habiles
                </p>
              </div>

              <div className="flex items-center gap-3">
                <div className="text-right">
                  {editing === zone.id ? (
                    <input
                      type="number"
                      autoFocus
                      value={zone.price}
                      onChange={(event) => updatePrice(zone.id, Number(event.target.value))}
                      onBlur={() => setEditing(null)}
                      className="w-28 rounded-lg border border-orange-400 px-2 py-1 text-right text-[13px] font-bold outline-none ring-2 ring-orange-400/20 dark:bg-white/10 dark:text-white"
                    />
                  ) : (
                    <button
                      type="button"
                      onClick={() => setEditing(zone.id)}
                      className="rounded-lg px-2 py-1 text-right text-[15px] font-bold text-slate-900 transition-colors hover:bg-slate-200 dark:text-white dark:hover:bg-white/10"
                    >
                      {zone.price === 0 ? 'Gratis' : fmtCurrency(zone.price)}
                    </button>
                  )}
                  <p className="text-[10px] text-slate-400 dark:text-white/30">Toca para editar</p>
                </div>
                <Switch checked={zone.is_active} onCheckedChange={() => toggle(zone.id)} size="sm" aria-label={`Activar ${zone.name}`} />
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}

// -- NotificationsTab ----------------------------------------------------------

function NotificationsTab({
  notifications,
  onChange,
}: {
  notifications: NotificationSetting[]
  onChange: (n: NotificationSetting[]) => void
}) {
  const update = (id: string, key: 'email' | 'in_app', value: boolean) =>
    onChange(notifications.map((n) => (n.id === id ? { ...n, [key]: value } : n)))

  return (
    <div>
      <SectionHeader
        title="Preferencias de notificaciones"
        description="Elige como y cuando quieres recibir alertas sobre la actividad de tu tienda."
      />

      <div className="overflow-hidden rounded-2xl border border-slate-200 dark:border-white/10">
        <div className="grid grid-cols-[1fr_92px_92px] border-b border-slate-100 bg-slate-50 px-5 py-2.5 dark:border-white/5 dark:bg-white/5">
          <span className="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-white/30">Evento</span>
          <span className="text-center text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-white/30">Email</span>
          <span className="text-center text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-white/30">App</span>
        </div>
        {notifications.map((notification, index) => (
          <div
            key={notification.id}
            className={`grid grid-cols-[1fr_92px_92px] items-center px-5 py-3.5 ${
              index < notifications.length - 1 ? 'border-b border-slate-100 dark:border-white/5' : ''
            }`}
          >
            <div>
              <p className="text-[13px] font-semibold text-slate-800 dark:text-white">{notification.label}</p>
              <p className="text-[11px] text-slate-400 dark:text-white/30">{notification.description}</p>
            </div>

            <div className="flex justify-center">
              <Switch
                checked={notification.email}
                onCheckedChange={(value) => update(notification.id, 'email', value)}
                size="sm"
                aria-label={`Notificacion por email para ${notification.label}`}
              />
            </div>
            <div className="flex justify-center">
              <Switch
                checked={notification.in_app}
                onCheckedChange={(value) => update(notification.id, 'in_app', value)}
                size="sm"
                aria-label={`Notificacion en app para ${notification.label}`}
              />
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
        description="Esta informacion se usa en las facturas electronicas y documentos tributarios. Debe coincidir con los datos de la DIAN."
      />

      <div className="space-y-4">
        <div className="grid gap-4 md:grid-cols-2">
          <Input
            label="Razon social"
            value={fiscal.company_name}
            onChange={(event) => set('company_name', event.target.value)}
            placeholder="Mi Empresa S.A.S."
          />
          <Input
            label="NIT"
            value={fiscal.nit}
            onChange={(event) => set('nit', event.target.value)}
            placeholder="900.123.456-7"
          />
        </div>

        <div className="grid gap-4 md:grid-cols-2">
          <div>
            <label className="mb-1.5 block text-[13px] font-medium text-slate-700 dark:text-white/70">Regimen tributario</label>
            <ErpFilterSelect
              value={fiscal.tax_regime}
              onChange={(value: string) => set('tax_regime', value)}
              options={[
                { value: 'Responsable de IVA', label: 'Responsable de IVA' },
                { value: 'No responsable de IVA', label: 'No responsable de IVA' },
                { value: 'Gran contribuyente', label: 'Gran contribuyente' },
                { value: 'Autorretenedor', label: 'Autorretenedor' },
              ]}
              placeholder="Selecciona regimen"
            />
          </div>
          <Input
            label="Ciudad"
            value={fiscal.city}
            onChange={(event) => set('city', event.target.value)}
            placeholder="Bogota D.C."
          />
        </div>

        <Input
          label="Direccion fiscal"
          value={fiscal.address}
          onChange={(event) => set('address', event.target.value)}
          placeholder="Calle 123 #45-67, Bogota"
        />

        <div className="grid gap-4 md:grid-cols-2">
          <Input
            label="Email de facturacion"
            type="email"
            value={fiscal.billing_email}
            onChange={(event) => set('billing_email', event.target.value)}
            placeholder="facturacion@empresa.com"
          />
          <Input
            label="Telefono"
            value={fiscal.phone}
            onChange={(event) => set('phone', event.target.value)}
            placeholder="+57 300 123 4567"
          />
        </div>

        <Input
          label="Nombre del responsable"
          value={fiscal.responsible_name}
          onChange={(event) => set('responsible_name', event.target.value)}
          placeholder="Nombre completo del representante legal"
        />
      </div>

      <div className="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-500/20 dark:bg-amber-500/10">
        <p className="text-[12px] text-amber-700 dark:text-amber-300">
          Para habilitar la <strong>factura electronica</strong> integrada con la DIAN, el equipo de ComercioPlus debe configurar tu habilitador. Escribenos a soporte@comercioplus.co.
        </p>
      </div>
    </div>
  )
}

// -- GlobalSection -------------------------------------------------------------

function GlobalSection({ settings, onChange }: { settings: Settings; onChange: (s: Partial<Settings>) => void }) {
  return (
    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_2px_8px_rgba(0,0,0,0.04)] dark:border-white/10 dark:bg-white/5">
      <SectionHeader
        title="Parametros globales"
        description="Configuracion general de la tienda"
        right={<ErpBadge status="active" label="General" />}
      />

      <div className="grid gap-4 sm:grid-cols-3">
        <div>
          <label className="mb-1.5 block text-[12px] font-medium text-slate-600 dark:text-white/60">Moneda</label>
          <ErpFilterSelect
            value={settings.currency}
            onChange={(value: string) => onChange({ currency: value })}
            options={[
              { value: 'COP', label: 'COP' },
              { value: 'USD', label: 'USD' },
              { value: 'EUR', label: 'EUR' },
            ]}
            placeholder="Moneda"
          />
        </div>

        <div>
          <label className="mb-1.5 block text-[12px] font-medium text-slate-600 dark:text-white/60">Idioma</label>
          <ErpFilterSelect
            value={settings.language}
            onChange={(value: string) => onChange({ language: value })}
            options={[
              { value: 'es', label: 'Espanol' },
              { value: 'en', label: 'English' },
            ]}
            placeholder="Idioma"
          />
        </div>

        <div>
          <label className="mb-1.5 block text-[12px] font-medium text-slate-600 dark:text-white/60">Zona horaria</label>
          <ErpFilterSelect
            value={settings.timezone}
            onChange={(value: string) => onChange({ timezone: value })}
            options={[
              { value: 'America/Bogota', label: 'Bogota (UTC-5)' },
              { value: 'America/New_York', label: 'New York (UTC-4/5)' },
              { value: 'UTC', label: 'UTC' },
            ]}
            placeholder="Zona horaria"
          />
        </div>
      </div>
    </div>
  )
}

// -- Page ----------------------------------------------------------------------

export default function DashboardSettingsPage() {
  const [activeTab, setActiveTab] = useState<TabId>('taxes')
  const [tabQuery, setTabQuery] = useState('')
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

  const visibleTabs = useMemo(() => {
    const query = tabQuery.trim().toLowerCase()
    if (!query) return TABS
    return TABS.filter((tab) => tab.label.toLowerCase().includes(query))
  }, [tabQuery])

  const activeTaxes = settings.taxes.filter((tax) => tax.is_active).length
  const activePayments = settings.payments.filter((payment) => payment.is_active).length
  const activeShipping = settings.shipping.filter((zone) => zone.is_active).length
  const emailNotifications = settings.notifications.filter((notification) => notification.email).length

  return (
    <div className="space-y-6 pb-6">
      <ErpPageHeader
        breadcrumb="Dashboard"
        title="Configuracion"
        subtitle="Parametros operativos y fiscales de tu tienda"
        actions={
          <ErpBtn onClick={save} variant="primary" size="md" disabled={saving}>
            {saving ? 'Guardando...' : 'Guardar cambios'}
          </ErpBtn>
        }
      />

      {loadError ? (
        <div className="flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-[13px] text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">
          <ErpBadge status="pending" label="Advertencia" />
          <span>{loadError}</span>
        </div>
      ) : null}

      <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <ErpKpiCard
          label="Impuestos activos"
          value={activeTaxes}
          hint="Reglas habilitadas"
          icon="file-text"
          iconBg="rgba(255,161,79,0.12)"
          iconColor="#FFA14F"
        />
        <ErpKpiCard
          label="Pagos activos"
          value={activePayments}
          hint="Canales de cobro"
          icon="credit-card"
          iconBg="rgba(16,185,129,0.12)"
          iconColor="#10B981"
        />
        <ErpKpiCard
          label="Envios activos"
          value={activeShipping}
          hint="Zonas habilitadas"
          icon="truck"
          iconBg="rgba(59,130,246,0.12)"
          iconColor="#3B82F6"
        />
        <ErpKpiCard
          label="Alertas por email"
          value={emailNotifications}
          hint="Eventos con correo"
          icon="mail"
          iconBg="rgba(139,92,246,0.12)"
          iconColor="#8B5CF6"
        />
      </div>

      <GlobalSection settings={settings} onChange={updateGlobal} />

      <div className="space-y-3">
        <div className="rounded-2xl border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-white/5">
          <div className="flex flex-wrap items-center gap-3">
            <div className="min-w-[220px] flex-1">
              <ErpSearchBar
                value={tabQuery}
                onChange={(value: string) => setTabQuery(value)}
                placeholder="Buscar seccion..."
              />
            </div>
            <ErpFilterSelect
              value={activeTab}
              onChange={(value: string) => setActiveTab(value as TabId)}
              options={TABS.map((tab) => ({ value: tab.id, label: tab.label }))}
              placeholder="Seccion"
            />
          </div>

          <div className="mt-3 flex gap-1 overflow-x-auto rounded-xl bg-slate-50 p-1.5 dark:bg-white/5">
            {(visibleTabs.length > 0 ? visibleTabs : TABS).map((tab) => (
              <ErpBtn
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                variant={activeTab === tab.id ? 'primary' : 'ghost'}
                size="sm"
                className={`flex-shrink-0 ${activeTab === tab.id ? '' : '!text-slate-600 dark:!text-white/70'}`}
              >
                <span>{tab.icon}</span>
                <span>{tab.label}</span>
              </ErpBtn>
            ))}
          </div>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_2px_8px_rgba(0,0,0,0.04)] dark:border-white/10 dark:bg-white/5">
          {loading ? (
            <div className="flex flex-col items-center justify-center py-16">
              <div className="mb-3 h-8 w-8 animate-spin rounded-full border-2 border-orange-500 border-t-transparent" />
              <p className="text-[13px] text-slate-400">Cargando configuracion...</p>
            </div>
          ) : (
            <>
              {activeTab === 'taxes' ? (
                <TaxesTab taxes={settings.taxes} onChange={(taxes) => setSettings((prev) => ({ ...prev, taxes }))} />
              ) : null}
              {activeTab === 'payments' ? (
                <PaymentsTab payments={settings.payments} onChange={(payments) => setSettings((prev) => ({ ...prev, payments }))} />
              ) : null}
              {activeTab === 'shipping' ? (
                <ShippingTab zones={settings.shipping} onChange={(shipping) => setSettings((prev) => ({ ...prev, shipping }))} />
              ) : null}
              {activeTab === 'notifications' ? (
                <NotificationsTab
                  notifications={settings.notifications}
                  onChange={(notifications) => setSettings((prev) => ({ ...prev, notifications }))}
                />
              ) : null}
              {activeTab === 'fiscal' ? (
                <FiscalTab fiscal={settings.fiscal} onChange={(fiscal) => setSettings((prev) => ({ ...prev, fiscal }))} />
              ) : null}
            </>
          )}
        </div>

        <SaveBar onSave={save} saving={saving} message={message} error={error} />
      </div>
    </div>
  )
}
