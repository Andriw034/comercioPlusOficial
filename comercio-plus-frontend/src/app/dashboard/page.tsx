import { useCallback, useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import API from '@/lib/api'
import { ErpBadge, ErpBtn, ErpKpiCard, ErpPageHeader } from '@/components/erp'

type DashboardBadgeStatus = 'approved' | 'pending' | 'active' | 'inactive'
type OrderBadgeStatus = 'paid' | 'processing' | 'completed' | 'cancelled' | 'pending'
type Range = '7d' | '30d' | '90d'

type SalesPoint = {
  label: string
  value: number
}

type DashboardOrder = {
  id: string
  customer: string
  channel: 'Web' | 'WhatsApp' | 'Presencial'
  total: number
  statusLabel: string
  statusBadge: OrderBadgeStatus
}

type AiCard = {
  icon: string
  title: string
  message: string
  tone: 'danger' | 'warning' | 'info' | 'success'
}

type DashboardState = {
  merchantName: string
  storeName: string
  storeBadgeStatus: DashboardBadgeStatus
  storeBadgeLabel: string
  todaySales: number
  todayOrders: number
  criticalStock: number
  activeQuotes: number
  totalProducts: number
  totalCustomers: number
  activeCredit: number
  inventoryValue: number
  salesHistory: SalesPoint[]
  recentOrders: DashboardOrder[]
  channelWebPct: number
  channelWhatsAppPct: number
  channelLocalPct: number
  setupCompletionPct: number
  setupHint: string
  aiCards: AiCard[]
}

const FALLBACK_SALES_HISTORY: SalesPoint[] = [
  { label: 'Sep', value: 1_800_000 },
  { label: 'Oct', value: 2_300_000 },
  { label: 'Nov', value: 2_100_000 },
  { label: 'Dic', value: 3_400_000 },
  { label: 'Ene', value: 2_700_000 },
  { label: 'Feb', value: 3_100_000 },
]

const FALLBACK_RECENT_ORDERS: DashboardOrder[] = [
  {
    id: '2451',
    customer: 'Carlos Mendoza',
    channel: 'WhatsApp',
    total: 187000,
    statusLabel: 'Pagado',
    statusBadge: 'paid',
  },
  {
    id: '2450',
    customer: 'Diana Torres',
    channel: 'Web',
    total: 45000,
    statusLabel: 'Procesando',
    statusBadge: 'processing',
  },
  {
    id: '2449',
    customer: 'Luis Herrera',
    channel: 'Presencial',
    total: 312000,
    statusLabel: 'Pendiente',
    statusBadge: 'pending',
  },
  {
    id: '2448',
    customer: 'Ana Gomez',
    channel: 'Web',
    total: 96500,
    statusLabel: 'Completado',
    statusBadge: 'completed',
  },
]

const FALLBACK_AI: AiCard[] = [
  {
    icon: '\uD83D\uDD25',
    title: 'Productos de alta rotacion',
    message: '13 productos en alta rotacion - reponer esta semana',
    tone: 'danger',
  },
  {
    icon: '\u26A0\uFE0F',
    title: 'Sin stock con alta demanda',
    message: 'Disco de freno Honda CB190R sin stock - alta demanda',
    tone: 'warning',
  },
  {
    icon: '\u2744\uFE0F',
    title: 'Sin movimiento +45 dias',
    message: '7 productos sin movimiento +45 dias - reubicar',
    tone: 'info',
  },
  {
    icon: '📈',
    title: 'Categoria con mayor crecimiento',
    message: 'Filtros de aceite: +38% ventas este mes',
    tone: 'success',
  },
]

const QUICK_ACTIONS: Array<{ emoji: string; title: string; desc: string; to: string }> = [
  { emoji: '📦', title: 'Mis productos', desc: 'Gestiona tu catalogo', to: '/dashboard/products' },
  { emoji: '🧾', title: 'Mis pedidos', desc: 'Revisa y alista', to: '/dashboard/orders' },
  { emoji: '👥', title: 'Mis clientes', desc: 'Base de compradores', to: '/dashboard/customers' },
  { emoji: '💳', title: 'Fiado digital', desc: 'Credito informal', to: '/dashboard/credit' },
  { emoji: '🗂️', title: 'Categorias', desc: 'Organiza el catalogo', to: '/dashboard/categories' },
  { emoji: '🏷️', title: 'Inventario', desc: 'Control de stock', to: '/dashboard/inventory' },
  { emoji: '📈', title: 'Reportes', desc: 'Analiza tus ventas', to: '/dashboard/reports' },
  { emoji: '⚙️', title: 'Configuracion', desc: 'Ajustes de tienda', to: '/dashboard/settings' },
]

const EMPTY_STATE: DashboardState = {
  merchantName: 'Andres Arenas',
  storeName: 'Mi tienda',
  storeBadgeStatus: 'active',
  storeBadgeLabel: 'Tienda activa',
  todaySales: 1_240_000,
  todayOrders: 47,
  criticalStock: 12,
  activeQuotes: 8,
  totalProducts: 1248,
  totalCustomers: 384,
  activeCredit: 4_700_000,
  inventoryValue: 67_000_000,
  salesHistory: FALLBACK_SALES_HISTORY,
  recentOrders: FALLBACK_RECENT_ORDERS,
  channelWebPct: 52,
  channelWhatsAppPct: 31,
  channelLocalPct: 17,
  setupCompletionPct: 78,
  setupHint: 'Agrega logo y horarios',
  aiCards: FALLBACK_AI,
}

function toRecord(value: unknown): Record<string, unknown> {
  return value && typeof value === 'object' && !Array.isArray(value) ? (value as Record<string, unknown>) : {}
}

function toArray<T>(value: unknown): T[] {
  return Array.isArray(value) ? (value as T[]) : []
}

function toOptionalNumber(value: unknown): number | null {
  if (value === null || value === undefined || value === '') return null
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : null
}

function toNumber(value: unknown, fallback = 0): number {
  const parsed = toOptionalNumber(value)
  return parsed ?? fallback
}

function toText(value: unknown): string {
  return typeof value === 'string' ? value.trim() : ''
}

function formatCOP(value: number): string {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(Math.max(0, value))
}

function formatMillions(value: number): string {
  const million = value / 1_000_000
  return `$${million.toFixed(1)}M`
}

function formatDateEs(date: Date): string {
  return new Intl.DateTimeFormat('es-CO', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(date)
}

function getGreeting(date: Date): string {
  const hour = date.getHours()
  if (hour < 12) return 'Buenos dias'
  if (hour < 18) return 'Buenas tardes'
  return 'Buenas noches'
}

function normalizeOrderStatus(raw: string): { label: string; badge: OrderBadgeStatus } {
  const key = raw.toLowerCase()
  if (key === 'paid') return { label: 'Pagado', badge: 'paid' }
  if (['processing', 'picking', 'packed', 'ready', 'shipped'].includes(key)) return { label: 'Procesando', badge: 'processing' }
  if (['completed', 'delivered'].includes(key)) return { label: 'Completado', badge: 'completed' }
  if (['cancelled', 'canceled'].includes(key)) return { label: 'Cancelado', badge: 'cancelled' }
  return { label: 'Pendiente', badge: 'pending' }
}

function normalizeChannel(raw: string): 'Web' | 'WhatsApp' | 'Presencial' {
  const key = raw.toLowerCase()
  if (key === 'whatsapp') return 'WhatsApp'
  if (key === 'local') return 'Presencial'
  return 'Web'
}

function normalizeStoreStatus(store: Record<string, unknown>): { status: DashboardBadgeStatus; label: string } {
  const rawStatus = toText(store.status).toLowerCase()
  if (rawStatus === 'approved' || rawStatus === 'verified') return { status: 'approved', label: 'Verificada' }
  if (rawStatus === 'pending') return { status: 'pending', label: 'En revision' }
  if (rawStatus === 'inactive') return { status: 'inactive', label: 'Inactiva' }
  return { status: 'active', label: 'Tienda activa' }
}

function resolveAiMessage(source: unknown): string {
  if (typeof source === 'string') return source.trim()
  if (typeof source === 'number') return String(source)
  if (Array.isArray(source)) {
    return source
      .map((item) => (typeof item === 'string' ? item.trim() : ''))
      .filter(Boolean)
      .slice(0, 2)
      .join(', ')
  }
  const obj = toRecord(source)
  return toText(obj.message) || toText(obj.text) || toText(obj.title) || toText(obj.name)
}

function normalizeSalesHistory(data: Record<string, unknown>, kpis: Record<string, unknown>): SalesPoint[] {
  const candidates = [
    data.sales_history,
    data.last_6_months,
    data.monthly_sales,
    data.sales_last_6_months,
    kpis.sales_history,
    kpis.monthly_sales,
  ]

  for (const candidate of candidates) {
    const rows = toArray<Record<string, unknown>>(candidate)
    if (rows.length === 0) continue
    const parsed = rows
      .map((row) => ({
        label: toText(row.label) || toText(row.month) || toText(row.period),
        value: toNumber(row.total) || toNumber(row.amount) || toNumber(row.sales) || toNumber(row.value),
      }))
      .filter((row) => row.label || row.value > 0)
      .slice(-6)

    if (parsed.length > 0) {
      return parsed.map((row, index) => ({
        label: row.label || FALLBACK_SALES_HISTORY[index]?.label || '',
        value: row.value,
      }))
    }
  }

  return FALLBACK_SALES_HISTORY
}

function normalizeDashboard(raw: unknown): DashboardState {
  const wrapped = toRecord(raw)
  const candidate = toRecord(wrapped.data)
  const data = Object.keys(candidate).length > 0 && ('store' in candidate || 'kpis' in candidate) ? candidate : wrapped

  const store = toRecord(data.store)
  const kpis = toRecord(data.kpis)
  const status = normalizeStoreStatus(store)

  const merchantName =
    toText(store.owner_name) ||
    toText(store.merchant_name) ||
    toText(store.user_name) ||
    EMPTY_STATE.merchantName

  const storeName = toText(store.name) || EMPTY_STATE.storeName

  const recentOrders = toArray<Record<string, unknown>>(data.recent_orders)
    .slice(0, 5)
    .map((order) => {
      const rawStatus = toText(order.status) || toText(order.fulfillment_status) || 'pending'
      const statusInfo = normalizeOrderStatus(rawStatus)
      const idValue = toText(order.id) || String(toNumber(order.id))
      return {
        id: idValue || '-',
        customer: toText(order.customer_name) || toText(toRecord(order.customer).name) || 'Cliente',
        channel: normalizeChannel(toText(order.channel) || 'web'),
        total: toNumber(order.total) || toNumber(order.total_amount),
        statusLabel: statusInfo.label,
        statusBadge: statusInfo.badge,
      } as DashboardOrder
    })

  const channelObj = toRecord(data.channel_breakdown)
  const rawWeb = toOptionalNumber(channelObj.web)
  const rawWhatsApp = toOptionalNumber(channelObj.whatsapp)
  const rawLocal = toOptionalNumber(channelObj.local)
  const hasChannelData = rawWeb !== null || rawWhatsApp !== null || rawLocal !== null
  const web = rawWeb ?? 0
  const whatsapp = rawWhatsApp ?? 0
  const local = rawLocal ?? 0
  const channelTotal = web + whatsapp + local
  const channelPercents =
    hasChannelData && channelTotal > 0
      ? channelTotal <= 100
        ? { web, whatsapp, local }
        : {
            web: Math.round((web / channelTotal) * 100),
            whatsapp: Math.round((whatsapp / channelTotal) * 100),
            local: Math.round((local / channelTotal) * 100),
          }
      : {
          web: EMPTY_STATE.channelWebPct,
          whatsapp: EMPTY_STATE.channelWhatsAppPct,
          local: EMPTY_STATE.channelLocalPct,
        }

  const aiObj = toRecord(data.ai_alerts)
  const aiCards: AiCard[] = FALLBACK_AI.map((card, index) => {
    const keyByIndex = ['high_rotation', 'stockout_high_demand', 'slow_movers_45d', 'top_growth_category'][index]
    const apiMessage = resolveAiMessage(aiObj[keyByIndex])
    return {
      ...card,
      message: apiMessage || card.message,
    }
  })

  const setup =
    toOptionalNumber(channelObj.setup_completion) ??
    toOptionalNumber(store.setup_completion) ??
    toOptionalNumber(store.profile_completion) ??
    EMPTY_STATE.setupCompletionPct

  return {
    merchantName,
    storeName,
    storeBadgeStatus: status.status,
    storeBadgeLabel: status.label,
    todaySales: toOptionalNumber(kpis.sales_today) ?? toOptionalNumber(kpis.today_sales) ?? EMPTY_STATE.todaySales,
    todayOrders: toOptionalNumber(kpis.orders_today) ?? toOptionalNumber(kpis.today_orders) ?? EMPTY_STATE.todayOrders,
    criticalStock: toOptionalNumber(kpis.critical_stock) ?? toOptionalNumber(kpis.low_stock_products) ?? EMPTY_STATE.criticalStock,
    activeQuotes: toOptionalNumber(kpis.active_quotes) ?? toOptionalNumber(kpis.active_quotations) ?? EMPTY_STATE.activeQuotes,
    totalProducts: toOptionalNumber(kpis.total_products) ?? EMPTY_STATE.totalProducts,
    totalCustomers: toOptionalNumber(kpis.total_customers) ?? EMPTY_STATE.totalCustomers,
    activeCredit: toOptionalNumber(kpis.active_credit) ?? toOptionalNumber(kpis.credit_balance) ?? EMPTY_STATE.activeCredit,
    inventoryValue: toOptionalNumber(kpis.inventory_value) ?? EMPTY_STATE.inventoryValue,
    salesHistory: normalizeSalesHistory(data, kpis),
    recentOrders: recentOrders.length > 0 ? recentOrders : EMPTY_STATE.recentOrders,
    channelWebPct: Math.max(0, Math.min(100, channelPercents.web)),
    channelWhatsAppPct: Math.max(0, Math.min(100, channelPercents.whatsapp)),
    channelLocalPct: Math.max(0, Math.min(100, channelPercents.local)),
    setupCompletionPct: Math.max(0, Math.min(100, Math.round(setup))),
    setupHint: toText(store.setup_hint) || EMPTY_STATE.setupHint,
    aiCards,
  }
}

function getAlertToneClasses(tone: AiCard['tone']): string {
  if (tone === 'danger') return 'border-red-200 bg-red-50 dark:border-red-500/20 dark:bg-red-500/10'
  if (tone === 'warning') return 'border-amber-200 bg-amber-50 dark:border-amber-500/20 dark:bg-amber-500/10'
  if (tone === 'success') return 'border-emerald-200 bg-emerald-50 dark:border-emerald-500/20 dark:bg-emerald-500/10'
  return 'border-blue-200 bg-blue-50 dark:border-blue-500/20 dark:bg-blue-500/10'
}

export default function DashboardPage() {
  const [range, setRange] = useState<Range>('30d')
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [dashboard, setDashboard] = useState<DashboardState>(EMPTY_STATE)

  const loadDashboard = useCallback(async () => {
    setLoading(true)
    setError('')
    try {
      let response
      try {
        response = await API.get('/merchant/dashboard')
      } catch (primaryError: any) {
        if (primaryError?.response?.status === 404) {
          response = await API.get('/merchant/stats')
        } else {
          throw primaryError
        }
      }
      setDashboard(normalizeDashboard(response.data))
    } catch (err: any) {
      setDashboard(EMPTY_STATE)
      setError(err?.response?.data?.message || 'No se pudo cargar el resumen del panel.')
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    loadDashboard()
  }, [loadDashboard])

  const today = useMemo(() => new Date(), [])
  const greeting = useMemo(() => `${getGreeting(today)}, ${dashboard.merchantName}`, [dashboard.merchantName, today])
  const subtitle = useMemo(() => `${formatDateEs(today)} · Resumen del dia`, [today])
  const maxSales = useMemo(() => Math.max(...dashboard.salesHistory.map((point) => point.value), 1), [dashboard.salesHistory])

  return (
    <div className="space-y-6">
      <ErpPageHeader
        breadcrumb={greeting}
        title="Panel del comerciante"
        subtitle={subtitle}
        actions={
          <div className="flex items-center gap-2">
            <ErpBadge status={dashboard.storeBadgeStatus} label={dashboard.storeBadgeLabel} />
            <Link to="/dashboard/orders">
              <ErpBtn variant="primary" size="md">
                + Nuevo pedido
              </ErpBtn>
            </Link>
          </div>
        }
      />

      {error ? (
        <div className="flex items-center gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-[13px] text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">
          <ErpBadge status="pending" label="Aviso" />
          <span>{error}</span>
        </div>
      ) : null}

      {loading ? (
        <div className="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-[12px] text-slate-500 shadow-[0_2px_8px_rgba(0,0,0,0.04)] dark:border-white/10 dark:bg-white/5 dark:text-white/60">
          Cargando resumen del dia...
        </div>
      ) : null}

      <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <ErpKpiCard
          label="Ventas hoy"
          value={formatCOP(dashboard.todaySales)}
          hint="↑ 14% vs ayer"
          icon="dollar"
          iconBg="rgba(255,161,79,0.12)"
          iconColor="#FFA14F"
          trend={{ value: '14%', up: true }}
        />
        <ErpKpiCard
          label="Pedidos hoy"
          value={dashboard.todayOrders}
          hint="8 pendientes por alistar"
          icon="file-text"
          iconBg="rgba(59,130,246,0.12)"
          iconColor="#3B82F6"
          trend={{ value: '6%', up: true }}
        />
        <ErpKpiCard
          label="Stock critico"
          value={dashboard.criticalStock}
          hint="Bajo punto de reorden"
          icon="alert"
          iconBg="rgba(239,68,68,0.1)"
          iconColor="#EF4444"
        />
        <ErpKpiCard
          label="Cotizaciones"
          value={dashboard.activeQuotes}
          hint="3 sin responder"
          icon="chart"
          iconBg="rgba(139,92,246,0.12)"
          iconColor="#8B5CF6"
        />
      </div>

      <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <ErpKpiCard
          label="Mis productos"
          value={dashboard.totalProducts.toLocaleString('es-CO')}
          hint="76 activos hoy"
          icon="package"
          iconBg="rgba(16,185,129,0.12)"
          iconColor="#10B981"
        />
        <ErpKpiCard
          label="Mis clientes"
          value={dashboard.totalCustomers.toLocaleString('es-CO')}
          hint="12 nuevos este mes"
          icon="users"
          iconBg="rgba(255,161,79,0.12)"
          iconColor="#FFA14F"
        />
        <ErpKpiCard
          label="Fiado activo"
          value={formatCOP(dashboard.activeCredit)}
          hint="23 cuentas abiertas"
          icon="credit-card"
          iconBg="rgba(245,158,11,0.12)"
          iconColor="#F59E0B"
        />
        <ErpKpiCard
          label="Valor inventario"
          value={formatCOP(dashboard.inventoryValue)}
          hint="1,248 referencias"
          icon="bank"
          iconBg="rgba(59,130,246,0.12)"
          iconColor="#3B82F6"
        />
      </div>

      <div className="grid grid-cols-1 gap-4 xl:grid-cols-[1.4fr_1fr]">
        <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_2px_8px_rgba(0,0,0,0.04)] dark:border-white/10 dark:bg-white/5">
          <div className="mb-5 flex items-center justify-between gap-3">
            <div>
              <p className="text-[14px] font-semibold text-slate-900 dark:text-white">Ventas por mes</p>
              <p className="text-[11px] text-slate-500 dark:text-white/50">Ultimos 6 meses</p>
            </div>
            <div className="flex items-center gap-1">
              {(['7d', '30d', '90d'] as Range[]).map((value) => (
                <ErpBtn key={value} variant={range === value ? 'primary' : 'secondary'} size="sm" onClick={() => setRange(value)}>
                  {value}
                </ErpBtn>
              ))}
            </div>
          </div>

          <div className="flex h-28 items-end gap-3">
            {dashboard.salesHistory.map((point) => (
              <div key={point.label} className="flex min-w-0 flex-1 flex-col items-center gap-1">
                <div
                  className="w-full rounded-t-md bg-orange-200 transition-all duration-300"
                  style={{
                    height: `${Math.max(6, Math.round((point.value / maxSales) * 90))}%`,
                    backgroundColor: point.label === dashboard.salesHistory[dashboard.salesHistory.length - 1]?.label ? '#FFA14F' : 'rgba(255,161,79,0.25)',
                  }}
                />
                <span className="text-[9px] font-semibold text-slate-500 dark:text-white/60">{point.label}</span>
                <span className="text-[9px] font-bold text-slate-700 dark:text-white/70">{formatMillions(point.value)}</span>
              </div>
            ))}
          </div>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_2px_8px_rgba(0,0,0,0.04)] dark:border-white/10 dark:bg-white/5">
          <div className="mb-3 flex items-center justify-between">
            <p className="text-[13px] font-semibold text-slate-900 dark:text-white">🤖 Sugerencias inteligentes</p>
            <ErpBadge status="active" label="IA activa" />
          </div>
          <div className="space-y-2">
            {dashboard.aiCards.map((alert) => (
              <div key={alert.title} className={`rounded-xl border px-3 py-2 ${getAlertToneClasses(alert.tone)}`}>
                <p className="text-[11px] font-semibold text-slate-900 dark:text-white">
                  {alert.icon} {alert.message}
                </p>
              </div>
            ))}
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-4 xl:grid-cols-[1.4fr_1fr]">
        <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_2px_8px_rgba(0,0,0,0.04)] dark:border-white/10 dark:bg-white/5">
          <div className="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-white/10">
            <p className="text-[13px] font-semibold text-slate-900 dark:text-white">Pedidos recientes</p>
            <Link to="/dashboard/orders" className="text-[11px] font-semibold text-orange-600 hover:text-orange-700">
              Ver todos →
            </Link>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full min-w-[560px]">
              <thead>
                <tr className="bg-slate-50 dark:bg-white/5">
                  {['#', 'Cliente', 'Canal', 'Total', 'Estado'].map((header) => (
                    <th
                      key={header}
                      className="px-3 py-2 text-left text-[9px] font-semibold uppercase tracking-wider text-slate-400 dark:text-white/40"
                    >
                      {header}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {dashboard.recentOrders.map((order) => (
                  <tr key={order.id} className="border-b border-slate-100 dark:border-white/10">
                    <td className="px-3 py-2.5 text-[12px] font-bold text-slate-900 dark:text-white">#{order.id}</td>
                    <td className="px-3 py-2.5 text-[12px] font-medium text-slate-900 dark:text-white">{order.customer}</td>
                    <td className="px-3 py-2.5 text-[11px] text-slate-600 dark:text-white/60">{order.channel}</td>
                    <td className="px-3 py-2.5 text-[12px] font-bold text-slate-900 dark:text-white">{formatCOP(order.total)}</td>
                    <td className="px-3 py-2.5">
                      <ErpBadge status={order.statusBadge} label={order.statusLabel} />
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_2px_8px_rgba(0,0,0,0.04)] dark:border-white/10 dark:bg-white/5">
          <p className="mb-3 text-[13px] font-semibold text-slate-900 dark:text-white">📊 Ventas por canal</p>
          <div className="space-y-3">
            {[
              { label: 'Web', pct: dashboard.channelWebPct, color: 'bg-blue-500' },
              { label: 'WhatsApp', pct: dashboard.channelWhatsAppPct, color: 'bg-emerald-500' },
              { label: 'Presencial', pct: dashboard.channelLocalPct, color: 'bg-orange-500' },
            ].map((channel) => (
              <div key={channel.label}>
                <div className="mb-1 flex items-center justify-between">
                  <span className="text-[12px] font-semibold text-slate-900 dark:text-white">{channel.label}</span>
                  <span className="text-[12px] font-black text-slate-900 dark:text-white">{channel.pct}%</span>
                </div>
                <div className="h-2 rounded-full bg-slate-200 dark:bg-white/10">
                  <div className={`h-2 rounded-full ${channel.color}`} style={{ width: `${channel.pct}%` }} />
                </div>
              </div>
            ))}
          </div>

          <div className="mt-4 rounded-xl border border-orange-200 bg-orange-50 p-3 dark:border-orange-500/20 dark:bg-orange-500/10">
            <p className="text-[10px] font-semibold text-slate-500 dark:text-white/50">Completar perfil de tienda</p>
            <div className="my-2 h-1.5 rounded-full bg-slate-200 dark:bg-white/10">
              <div className="h-1.5 rounded-full bg-orange-500" style={{ width: `${dashboard.setupCompletionPct}%` }} />
            </div>
            <p className="text-[11px] font-bold text-orange-600 dark:text-orange-300">
              {dashboard.setupCompletionPct}% completado · {dashboard.setupHint}
            </p>
          </div>
        </div>
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_2px_8px_rgba(0,0,0,0.04)] dark:border-white/10 dark:bg-white/5">
        <p className="mb-4 text-[14px] font-semibold text-slate-900 dark:text-white">⚡ Acceso rapido</p>
        <div className="grid grid-cols-1 gap-2.5 sm:grid-cols-2 xl:grid-cols-4">
          {QUICK_ACTIONS.map((action) => (
            <Link
              key={action.title}
              to={action.to}
              className="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 transition-colors hover:border-orange-300 hover:bg-orange-50 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10"
            >
              <p className="text-[20px]">{action.emoji}</p>
              <p className="mt-1 text-[12px] font-bold text-slate-900 dark:text-white">{action.title}</p>
              <p className="text-[10px] text-slate-500 dark:text-white/50">{action.desc}</p>
            </Link>
          ))}
        </div>
      </div>
    </div>
  )
}
