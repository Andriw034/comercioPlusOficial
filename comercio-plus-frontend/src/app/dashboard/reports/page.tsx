import { useCallback, useEffect, useMemo, useState } from 'react'
import API from '@/lib/api'
import { Icon } from '@/components/Icon'
import { ErpBtn, ErpKpiCard, ErpPageHeader } from '@/components/erp'

type ReportStatus = 'all' | 'pending' | 'processing' | 'paid' | 'approved' | 'completed' | 'cancelled'
type ReportView = 'overview' | 'sales' | 'tax' | 'products' | 'inventory' | 'alerts' | 'trends' | 'decisions'

type Filters = {
  from: string
  to: string
  status: ReportStatus
}

type SummaryData = {
  gross_sales: number
  net_sales: number
  tax_total: number
  orders_count: number
  avg_ticket: number
  items_sold: number
  unique_customers: number
}

type SalesRow = {
  period: string
  gross_sales: number
  net_sales: number
  tax_total: number
  orders_count: number
}

type TaxData = {
  base: number
  iva: number
  total: number
  breakdown: SalesRow[]
}

type TopProduct = {
  product_id: number
  name: string
  units_sold: number
  revenue: number
}

type InventoryTopOut = {
  product_id: number
  name: string
  units_out: number
}

type InventoryData = {
  entries: number
  outs: number
  adjustments: number
  avg_stock: number
  rotation_approx: number | null
  top_out_products: InventoryTopOut[]
}

type LowStockItem = {
  product_id: number
  name: string
  stock: number
  reorder_point: number
}

type NoMovementItem = {
  product_id: number
  name: string
  last_sale: string | null
}

type TopGrowthItem = {
  product_id: number
  name: string
  growth_pct: number
}

type AlertsData = {
  low_stock: LowStockItem[]
  no_movement_30d: NoMovementItem[]
  top_growth: TopGrowthItem[]
}

type MonthSale = {
  month: string
  total: number
}

type CategoryTotal = {
  name: string
  total: number
}

type TrendsData = {
  sales_by_month: MonthSale[]
  top_categories: CategoryTotal[]
}

type DecisionItem = {
  id: number
  name: string
  sku: string | null
  stock: number
  reorder_point: number
  price: number
  sold_30d: number
  sold_7d: number
  daily_rotation: number
  priority: 'critical' | 'high' | 'medium' | 'low'
  suggested_qty: number
  days_of_stock: number | null
  projected_stockout: string | null
}

type DecisionSummary = {
  critical_count: number
  high_count: number
  medium_count: number
  low_count: number
  total_restock_value: number
  health_score: number
}

const DECISION_PRIORITY_CONFIG: Record<string, { label: string; textColor: string; bg: string; border: string; dotColor: string; leftBorder: string }> = {
  critical: { label: 'Crítico', textColor: '#DC2626', bg: '#FEF2F2', border: '#FECACA', dotColor: '#EF4444', leftBorder: '#EF4444' },
  high:     { label: 'Alto',    textColor: '#C2410C', bg: '#FFF7ED', border: '#FED7AA', dotColor: '#F97316', leftBorder: '#F97316' },
  medium:   { label: 'Medio',   textColor: '#92400E', bg: '#FFFBEB', border: '#FDE68A', dotColor: '#F59E0B', leftBorder: '#F59E0B' },
  low:      { label: 'Bajo',    textColor: '#64748B', bg: '#F8FAFC', border: '#E2E8F0', dotColor: '#94A3B8', leftBorder: '#94A3B8' },
}

const STATUS_OPTIONS: Array<{ value: ReportStatus; label: string }> = [
  { value: 'all', label: 'Todos' },
  { value: 'pending', label: 'Pendiente' },
  { value: 'processing', label: 'Procesando' },
  { value: 'paid', label: 'Pagado' },
  { value: 'approved', label: 'Aprobado' },
  { value: 'completed', label: 'Completado' },
  { value: 'cancelled', label: 'Cancelado' },
]

const VIEW_TABS: Array<{ key: ReportView; label: string; icon: 'chart' | 'trending' | 'file-text' | 'package' | 'users' | 'bell' | 'pie-chart' }> = [
  { key: 'overview', label: 'Resumen IA', icon: 'chart' },
  { key: 'sales', label: 'Ventas', icon: 'trending' },
  { key: 'tax', label: 'Impuestos', icon: 'file-text' },
  { key: 'products', label: 'Top productos', icon: 'package' },
  { key: 'inventory', label: 'Inventario', icon: 'users' },
  { key: 'alerts', label: 'Alertas', icon: 'bell' },
  { key: 'trends', label: 'Tendencias', icon: 'pie-chart' },
  { key: 'decisions', label: '🧠 Decisiones IA', icon: 'trending' },
]

function toDateInput(value: Date): string {
  return value.toISOString().slice(0, 10)
}

function toNumber(value: unknown, fallback = 0): number {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : fallback
}

function toText(value: unknown, fallback = ''): string {
  if (typeof value === 'string') {
    const normalized = value.trim()
    return normalized.length > 0 ? normalized : fallback
  }
  if (typeof value === 'number' && Number.isFinite(value)) return String(value)
  return fallback
}

function formatMoney(value: number): string {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(value)
}

function formatInt(value: number): string {
  return new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 }).format(value)
}

function normalizeSummary(payload: unknown): SummaryData {
  const row = (payload ?? {}) as Record<string, unknown>
  return {
    gross_sales: toNumber(row.gross_sales),
    net_sales: toNumber(row.net_sales),
    tax_total: toNumber(row.tax_total),
    orders_count: toNumber(row.orders_count),
    avg_ticket: toNumber(row.avg_ticket),
    items_sold: toNumber(row.items_sold),
    unique_customers: toNumber(row.unique_customers),
  }
}

function normalizeSalesRows(payload: unknown): SalesRow[] {
  if (!Array.isArray(payload)) return []

  return payload.map((row): SalesRow => {
    const entry = (row ?? {}) as Record<string, unknown>
    return {
      period: toText(entry.period, '-'),
      gross_sales: toNumber(entry.gross_sales),
      net_sales: toNumber(entry.net_sales),
      tax_total: toNumber(entry.tax_total),
      orders_count: toNumber(entry.orders_count),
    }
  })
}

function normalizeTax(payload: unknown): TaxData {
  const row = (payload ?? {}) as Record<string, unknown>
  const summary = (row.summary ?? {}) as Record<string, unknown>

  return {
    base: toNumber(summary.base),
    iva: toNumber(summary.iva),
    total: toNumber(summary.total),
    breakdown: normalizeSalesRows(row.breakdown),
  }
}

function normalizeTopProducts(payload: unknown): TopProduct[] {
  if (!Array.isArray(payload)) return []

  return payload.map((row): TopProduct => {
    const entry = (row ?? {}) as Record<string, unknown>
    return {
      product_id: toNumber(entry.product_id),
      name: toText(entry.name, 'Producto'),
      units_sold: toNumber(entry.units_sold),
      revenue: toNumber(entry.revenue),
    }
  })
}

function normalizeInventory(payload: unknown): InventoryData {
  const row = (payload ?? {}) as Record<string, unknown>
  const rawTopOut = Array.isArray(row.top_out_products) ? row.top_out_products : []

  return {
    entries: toNumber(row.entries),
    outs: toNumber(row.outs),
    adjustments: toNumber(row.adjustments),
    avg_stock: toNumber(row.avg_stock),
    rotation_approx: row.rotation_approx === null ? null : toNumber(row.rotation_approx),
    top_out_products: rawTopOut.map((item): InventoryTopOut => {
      const entry = (item ?? {}) as Record<string, unknown>
      return {
        product_id: toNumber(entry.product_id),
        name: toText(entry.name, 'Producto'),
        units_out: toNumber(entry.units_out),
      }
    }),
  }
}

function normalizeAlerts(payload: unknown): AlertsData {
  const row = (payload ?? {}) as Record<string, unknown>

  const lowStock = Array.isArray(row.low_stock)
    ? row.low_stock.map((item): LowStockItem => {
        const e = (item ?? {}) as Record<string, unknown>
        return {
          product_id: toNumber(e.product_id),
          name: toText(e.name, 'Producto'),
          stock: toNumber(e.stock),
          reorder_point: toNumber(e.reorder_point),
        }
      })
    : []

  const noMovement = Array.isArray(row.no_movement_30d)
    ? row.no_movement_30d.map((item): NoMovementItem => {
        const e = (item ?? {}) as Record<string, unknown>
        return {
          product_id: toNumber(e.product_id),
          name: toText(e.name, 'Producto'),
          last_sale: typeof e.last_sale === 'string' ? e.last_sale : null,
        }
      })
    : []

  const topGrowth = Array.isArray(row.top_growth)
    ? row.top_growth.map((item): TopGrowthItem => {
        const e = (item ?? {}) as Record<string, unknown>
        return {
          product_id: toNumber(e.product_id),
          name: toText(e.name, 'Producto'),
          growth_pct: toNumber(e.growth_pct),
        }
      })
    : []

  return { low_stock: lowStock, no_movement_30d: noMovement, top_growth: topGrowth }
}

function normalizeTrends(payload: unknown): TrendsData {
  const row = (payload ?? {}) as Record<string, unknown>

  const salesByMonth = Array.isArray(row.sales_by_month)
    ? row.sales_by_month.map((item): MonthSale => {
        const e = (item ?? {}) as Record<string, unknown>
        return { month: toText(e.month, '-'), total: toNumber(e.total) }
      })
    : []

  const topCategories = Array.isArray(row.top_categories)
    ? row.top_categories.map((item): CategoryTotal => {
        const e = (item ?? {}) as Record<string, unknown>
        return { name: toText(e.name, 'Sin categoría'), total: toNumber(e.total) }
      })
    : []

  return { sales_by_month: salesByMonth, top_categories: topCategories }
}

async function downloadCsv(endpoint: string, params: Record<string, string>, filenamePrefix: string): Promise<void> {
  const response = await API.get(endpoint, { params, responseType: 'blob' })
  const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const date = new Date().toISOString().slice(0, 10)
  const link = document.createElement('a')

  link.href = url
  link.download = `${filenamePrefix}-${date}.csv`
  document.body.appendChild(link)
  link.click()
  link.remove()
  URL.revokeObjectURL(url)
}

export default function DashboardReportsPage() {
  const defaultTo = toDateInput(new Date())
  const defaultFrom = toDateInput(new Date(Date.now() - 29 * 24 * 60 * 60 * 1000))

  const [activeView, setActiveView] = useState<ReportView>('overview')
  const [draftFilters, setDraftFilters] = useState<Filters>({
    from: defaultFrom,
    to: defaultTo,
    status: 'all',
  })
  const [appliedFilters, setAppliedFilters] = useState<Filters>({
    from: defaultFrom,
    to: defaultTo,
    status: 'all',
  })

  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [summary, setSummary] = useState<SummaryData | null>(null)
  const [salesRows, setSalesRows] = useState<SalesRow[]>([])
  const [taxData, setTaxData] = useState<TaxData | null>(null)
  const [topProducts, setTopProducts] = useState<TopProduct[]>([])
  const [inventory, setInventory] = useState<InventoryData | null>(null)

  const [alertsData, setAlertsData] = useState<AlertsData | null>(null)
  const [alertsLoading, setAlertsLoading] = useState(true)
  const [trendsData, setTrendsData] = useState<TrendsData | null>(null)
  const [trendsLoading, setTrendsLoading] = useState(true)

  const [decisionsData, setDecisionsData] = useState<DecisionItem[] | null>(null)
  const [decisionsSummary, setDecisionsSummary] = useState<DecisionSummary | null>(null)
  const [decisionsLoading, setDecisionsLoading] = useState(true)
  const [decisionsDismissed, setDecisionsDismissed] = useState<number[]>([])
  const [decisionsFilter, setDecisionsFilter] = useState<'all' | 'critical' | 'high' | 'medium' | 'low'>('all')
  const [decisionsSearch, setDecisionsSearch] = useState('')
  const [decisionsModal, setDecisionsModal] = useState<DecisionItem | null>(null)
  const [modalQty, setModalQty] = useState(0)
  const [modalWhatsapp, setModalWhatsapp] = useState('')

  const params = useMemo(() => {
    const next: Record<string, string> = {}
    if (appliedFilters.from) next.from = appliedFilters.from
    if (appliedFilters.to) next.to = appliedFilters.to
    if (appliedFilters.status !== 'all') next.status = appliedFilters.status
    return next
  }, [appliedFilters])

  const loadReports = useCallback(async () => {
    setLoading(true)
    setError('')

    try {
      const [summaryResp, salesResp, taxResp, topResp, inventoryResp] = await Promise.all([
        API.get('/reports/summary', { params }),
        API.get('/reports/sales', { params: { ...params, group: 'day' } }),
        API.get('/reports/tax', { params: { ...params, group: 'day' } }),
        API.get('/reports/top-products', { params: { ...params, limit: '10', sort: 'revenue' } }),
        API.get('/reports/inventory', { params }),
      ])

      setSummary(normalizeSummary(summaryResp.data?.data))
      setSalesRows(normalizeSalesRows(salesResp.data?.data))
      setTaxData(normalizeTax(taxResp.data?.data))
      setTopProducts(normalizeTopProducts(topResp.data?.data))
      setInventory(normalizeInventory(inventoryResp.data?.data))
    } catch (loadError: unknown) {
      const apiError = loadError as { response?: { data?: { message?: string } } }
      setError(apiError?.response?.data?.message || 'No fue posible cargar reportes.')
      setSummary(null)
      setSalesRows([])
      setTaxData(null)
      setTopProducts([])
      setInventory(null)
    } finally {
      setLoading(false)
    }
  }, [params])

  const loadAlertsAndTrends = useCallback(async () => {
    setAlertsLoading(true)
    setTrendsLoading(true)
    setDecisionsLoading(true)

    const [alertsResult, trendsResult, decisionsResult] = await Promise.allSettled([
      API.get('/reports/alerts'),
      API.get('/reports/trends'),
      API.get('/reports/inventory-decisions'),
    ])

    if (alertsResult.status === 'fulfilled') {
      setAlertsData(normalizeAlerts(alertsResult.value.data?.data))
    } else {
      setAlertsData({ low_stock: [], no_movement_30d: [], top_growth: [] })
    }
    setAlertsLoading(false)

    if (trendsResult.status === 'fulfilled') {
      setTrendsData(normalizeTrends(trendsResult.value.data?.data))
    } else {
      setTrendsData({ sales_by_month: [], top_categories: [] })
    }
    setTrendsLoading(false)

    if (decisionsResult.status === 'fulfilled') {
      const payload = decisionsResult.value.data
      setDecisionsData(Array.isArray(payload?.data) ? (payload.data as DecisionItem[]) : [])
      setDecisionsSummary(payload?.summary ?? null)
    } else {
      setDecisionsData([])
      setDecisionsSummary(null)
    }
    setDecisionsLoading(false)
  }, [])

  useEffect(() => {
    void loadReports()
  }, [loadReports])

  useEffect(() => {
    void loadAlertsAndTrends()
  }, [loadAlertsAndTrends])

  const applyFilters = (): void => {
    setAppliedFilters(draftFilters)
  }

  const resetFilters = (): void => {
    const next = { from: defaultFrom, to: defaultTo, status: 'all' as const }
    setDraftFilters(next)
    setAppliedFilters(next)
  }

  const salesPreview = salesRows.slice(0, 12)
  const taxPreview = taxData?.breakdown.slice(0, 12) ?? []
  const inventoryTopOut = inventory?.top_out_products.slice(0, 10) ?? []
  const rotationLabel =
    inventory?.rotation_approx === null || inventory?.rotation_approx === undefined
      ? '-'
      : inventory.rotation_approx.toFixed(2)

  const maxMonthTotal = Math.max(...(trendsData?.sales_by_month.map((r) => r.total) ?? [1]), 1)
  const totalCategoryRevenue = (trendsData?.top_categories ?? []).reduce((sum, c) => sum + c.total, 0) || 1

  const filteredDecisions = (decisionsData ?? []).filter((item) => {
    if (decisionsDismissed.includes(item.id)) return false
    if (decisionsFilter !== 'all' && item.priority !== decisionsFilter) return false
    if (decisionsSearch.trim() && !item.name.toLowerCase().includes(decisionsSearch.toLowerCase())) return false
    return true
  })

  return (
    <div className="space-y-4 text-[#0F172A]">
      <ErpPageHeader
        breadcrumb="Panel de ventas / IA comercial"
        title="IA comercial y reportes"
        subtitle="Analitica real conectada al backend para ventas, IVA, productos e inventario."
        actions={
          <>
            <ErpBtn variant="secondary" size="md" icon={<Icon name="refresh" size={14} />} onClick={() => { void loadReports(); void loadAlertsAndTrends() }}>
              Recargar
            </ErpBtn>
            <ErpBtn
              variant="secondary"
              size="md"
              icon={<Icon name="download" size={14} />}
              onClick={() => void downloadCsv('/reports/export/sales.csv', params, 'reporte-ventas')}
              disabled={loading}
            >
              Exportar ventas CSV
            </ErpBtn>
            <ErpBtn
              variant="primary"
              size="md"
              icon={<Icon name="download" size={14} />}
              onClick={() => void downloadCsv('/reports/export/tax.csv', params, 'reporte-iva')}
              disabled={loading}
            >
              Exportar IVA CSV
            </ErpBtn>
          </>
        }
      />

      <div className="rounded-2xl border-2 border-[#FED7AA] bg-[linear-gradient(135deg,#FFF7ED_0%,#FFEDD5_62%,#FFE4D6_100%)] p-4 shadow-[0_12px_30px_rgba(251,146,60,0.15)]">
        <div className="flex flex-wrap items-start justify-between gap-3">
          <div>
            <p className="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#9A3412]">IA comercial</p>
            <h2 className="mt-1 text-[20px] font-black leading-tight text-[#7C2D12]">Centro inteligente de decisiones</h2>
            <p className="text-[12px] text-[#9A3412]">Visualiza el rendimiento y exporta reportes para cierre contable.</p>
          </div>
          <span className="inline-flex rounded-full border border-[#FDBA74] bg-[#FFF7ED] px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.08em] text-[#C2410C]">
            API en vivo
          </span>
        </div>
      </div>

      <div className="rounded-2xl border border-[#E2E8F0] bg-white p-4 shadow-[0_8px_20px_rgba(15,23,42,0.05)]">
        <div className="mb-3 flex items-center gap-2 border-b border-[#E2E8F0] pb-2">
          <span className="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-[#FFF7ED] text-[14px]">🔎</span>
          <h3 className="text-[14px] font-black text-[#0F172A]">Filtros de analisis</h3>
        </div>

        <div className="grid gap-3 md:grid-cols-4">
          <label className="text-[11px] font-semibold uppercase tracking-[0.08em] text-[#64748B]">
            Desde
            <input
              type="date"
              value={draftFilters.from}
              onChange={(event) => setDraftFilters((prev) => ({ ...prev, from: event.target.value }))}
              className="mt-1 w-full rounded-lg border border-[#E2E8F0] px-3 py-2 text-[13px] text-[#0F172A] outline-none transition focus:border-[#FF6A00] focus:shadow-[0_0_0_3px_rgba(255,106,0,0.12)]"
            />
          </label>

          <label className="text-[11px] font-semibold uppercase tracking-[0.08em] text-[#64748B]">
            Hasta
            <input
              type="date"
              value={draftFilters.to}
              onChange={(event) => setDraftFilters((prev) => ({ ...prev, to: event.target.value }))}
              className="mt-1 w-full rounded-lg border border-[#E2E8F0] px-3 py-2 text-[13px] text-[#0F172A] outline-none transition focus:border-[#FF6A00] focus:shadow-[0_0_0_3px_rgba(255,106,0,0.12)]"
            />
          </label>

          <label className="text-[11px] font-semibold uppercase tracking-[0.08em] text-[#64748B]">
            Estado de pedido
            <select
              value={draftFilters.status}
              onChange={(event) => setDraftFilters((prev) => ({ ...prev, status: event.target.value as ReportStatus }))}
              className="mt-1 w-full rounded-lg border border-[#E2E8F0] bg-white px-3 py-2 text-[13px] text-[#0F172A] outline-none transition focus:border-[#FF6A00] focus:shadow-[0_0_0_3px_rgba(255,106,0,0.12)]"
            >
              {STATUS_OPTIONS.map((option) => (
                <option key={option.value} value={option.value}>
                  {option.label}
                </option>
              ))}
            </select>
          </label>

          <div className="flex items-end gap-2">
            <ErpBtn variant="primary" size="md" className="flex-1 justify-center" onClick={applyFilters} disabled={loading}>
              Aplicar
            </ErpBtn>
            <ErpBtn variant="secondary" size="md" onClick={resetFilters} disabled={loading}>
              Limpiar
            </ErpBtn>
          </div>
        </div>
      </div>

      {error ? (
        <div className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-[13px] text-rose-700">
          {error}
        </div>
      ) : null}

      {loading ? (
        <div className="rounded-2xl border border-slate-200 bg-white px-4 py-14 text-center">
          <div className="mx-auto mb-3 h-8 w-8 animate-spin rounded-full border-2 border-orange-500 border-t-transparent" />
          <p className="text-[13px] text-slate-500">Cargando reportes...</p>
        </div>
      ) : (
        <>
          <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <ErpKpiCard
              label="Ventas brutas"
              value={formatMoney(summary?.gross_sales ?? 0)}
              hint="Total facturado"
              icon="dollar"
              iconBg="rgba(16,185,129,0.14)"
              iconColor="#10B981"
            />
            <ErpKpiCard
              label="Ventas netas"
              value={formatMoney(summary?.net_sales ?? 0)}
              hint="Sin impuestos"
              icon="chart"
              iconBg="rgba(59,130,246,0.14)"
              iconColor="#3B82F6"
            />
            <ErpKpiCard
              label="IVA"
              value={formatMoney(summary?.tax_total ?? 0)}
              hint="Impuesto acumulado"
              icon="file-text"
              iconBg="rgba(245,158,11,0.16)"
              iconColor="#F59E0B"
            />
            <ErpKpiCard
              label="Pedidos"
              value={formatInt(summary?.orders_count ?? 0)}
              hint={`Ticket prom: ${formatMoney(summary?.avg_ticket ?? 0)}`}
              icon="package"
              iconBg="rgba(255,161,79,0.16)"
              iconColor="#FFA14F"
            />
          </div>

          <div className="rounded-2xl border border-[#E2E8F0] bg-white p-1.5">
            <div className="flex flex-wrap gap-1">
              {VIEW_TABS.map((tab) => (
                <button
                  key={tab.key}
                  type="button"
                  onClick={() => setActiveView(tab.key)}
                  className={`inline-flex items-center gap-2 rounded-xl px-3 py-2.5 text-[12px] font-semibold transition ${
                    activeView === tab.key
                      ? 'border border-orange-200 bg-orange-50 text-orange-700'
                      : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'
                  }`}
                >
                  <Icon name={tab.icon} size={14} />
                  {tab.label}
                </button>
              ))}
            </div>
          </div>

          {activeView === 'overview' ? (
            <div className="grid gap-4 lg:grid-cols-2">
              <section className="rounded-2xl border border-[#E2E8F0] bg-white p-4 shadow-[0_8px_20px_rgba(15,23,42,0.05)]">
                <div className="mb-3 flex items-center gap-2 border-b border-[#E2E8F0] pb-2">
                  <span className="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-[#FFF7ED] text-[14px]">📈</span>
                  <h3 className="text-[14px] font-black text-[#0F172A]">Resumen comercial</h3>
                </div>
                <div className="grid gap-3 sm:grid-cols-2">
                  <div className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p className="text-[11px] text-slate-500">Items vendidos</p>
                    <p className="mt-1 text-[15px] font-black text-slate-900">{formatInt(summary?.items_sold ?? 0)}</p>
                  </div>
                  <div className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p className="text-[11px] text-slate-500">Clientes unicos</p>
                    <p className="mt-1 text-[15px] font-black text-slate-900">{formatInt(summary?.unique_customers ?? 0)}</p>
                  </div>
                  <div className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p className="text-[11px] text-slate-500">Base gravable</p>
                    <p className="mt-1 text-[15px] font-black text-slate-900">{formatMoney(taxData?.base ?? 0)}</p>
                  </div>
                  <div className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p className="text-[11px] text-slate-500">Rotacion aprox.</p>
                    <p className="mt-1 text-[15px] font-black text-orange-600">{rotationLabel}</p>
                  </div>
                </div>
              </section>

              <section className="rounded-2xl border border-[#E2E8F0] bg-white p-4 shadow-[0_8px_20px_rgba(15,23,42,0.05)]">
                <div className="mb-3 flex items-center gap-2 border-b border-[#E2E8F0] pb-2">
                  <span className="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-[#FFF7ED] text-[14px]">🏆</span>
                  <h3 className="text-[14px] font-black text-[#0F172A]">Top productos (preview)</h3>
                </div>
                {topProducts.length === 0 ? (
                  <p className="text-[13px] text-slate-500">No hay ventas de productos en el rango seleccionado.</p>
                ) : (
                  <div className="space-y-2">
                    {topProducts.slice(0, 6).map((row) => (
                      <div key={row.product_id} className="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                        <span className="truncate text-[12px] font-semibold text-slate-800">{row.name}</span>
                        <span className="text-[12px] font-bold text-orange-600">{formatMoney(row.revenue)}</span>
                      </div>
                    ))}
                  </div>
                )}
              </section>
            </div>
          ) : null}

          {activeView === 'sales' ? (
            <section className="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-[0_8px_20px_rgba(15,23,42,0.05)]">
              <div className="border-b border-slate-100 px-4 py-3">
                <h3 className="text-[14px] font-black text-[#0F172A]">Serie de ventas</h3>
                <p className="text-[12px] text-slate-500">Periodo, pedidos, neto, IVA y bruto.</p>
              </div>
              {salesPreview.length === 0 ? (
                <p className="px-4 py-5 text-[13px] text-slate-500">No hay datos para el rango seleccionado.</p>
              ) : (
                <div className="overflow-x-auto">
                  <table className="w-full min-w-[620px]">
                    <thead>
                      <tr className="bg-slate-50">
                        <th className="px-4 py-2 text-left text-[10px] uppercase tracking-[0.11em] text-slate-500">Periodo</th>
                        <th className="px-4 py-2 text-left text-[10px] uppercase tracking-[0.11em] text-slate-500">Pedidos</th>
                        <th className="px-4 py-2 text-left text-[10px] uppercase tracking-[0.11em] text-slate-500">Neto</th>
                        <th className="px-4 py-2 text-left text-[10px] uppercase tracking-[0.11em] text-slate-500">IVA</th>
                        <th className="px-4 py-2 text-left text-[10px] uppercase tracking-[0.11em] text-slate-500">Bruto</th>
                      </tr>
                    </thead>
                    <tbody>
                      {salesPreview.map((row) => (
                        <tr key={row.period} className="border-t border-slate-100">
                          <td className="px-4 py-2.5 text-[12px] font-semibold text-slate-900">{row.period}</td>
                          <td className="px-4 py-2.5 text-[12px] text-slate-700">{formatInt(row.orders_count)}</td>
                          <td className="px-4 py-2.5 text-[12px] text-slate-700">{formatMoney(row.net_sales)}</td>
                          <td className="px-4 py-2.5 text-[12px] text-slate-700">{formatMoney(row.tax_total)}</td>
                          <td className="px-4 py-2.5 text-[12px] font-bold text-orange-600">{formatMoney(row.gross_sales)}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </section>
          ) : null}

          {activeView === 'tax' ? (
            <section className="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-[0_8px_20px_rgba(15,23,42,0.05)]">
              <div className="border-b border-slate-100 px-4 py-3">
                <h3 className="text-[14px] font-black text-[#0F172A]">Reporte de impuestos</h3>
                <p className="text-[12px] text-slate-500">Base gravable, IVA y desglose por periodo.</p>
              </div>
              <div className="grid gap-3 p-4 sm:grid-cols-3">
                <div className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <p className="text-[11px] text-slate-500">Base</p>
                  <p className="mt-1 text-[15px] font-black text-slate-900">{formatMoney(taxData?.base ?? 0)}</p>
                </div>
                <div className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <p className="text-[11px] text-slate-500">IVA</p>
                  <p className="mt-1 text-[15px] font-black text-slate-900">{formatMoney(taxData?.iva ?? 0)}</p>
                </div>
                <div className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <p className="text-[11px] text-slate-500">Total</p>
                  <p className="mt-1 text-[15px] font-black text-orange-600">{formatMoney(taxData?.total ?? 0)}</p>
                </div>
              </div>
              {taxPreview.length > 0 ? (
                <div className="border-t border-slate-100 px-4 py-3">
                  <div className="space-y-1">
                    {taxPreview.map((row) => (
                      <div key={`tax-${row.period}`} className="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                        <span className="text-[12px] text-slate-700">{row.period}</span>
                        <span className="text-[12px] font-semibold text-slate-900">{formatMoney(row.tax_total)}</span>
                      </div>
                    ))}
                  </div>
                </div>
              ) : null}
            </section>
          ) : null}

          {activeView === 'products' ? (
            <section className="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-[0_8px_20px_rgba(15,23,42,0.05)]">
              <div className="border-b border-slate-100 px-4 py-3">
                <h3 className="text-[14px] font-black text-[#0F172A]">Top productos por ingresos</h3>
                <p className="text-[12px] text-slate-500">Ranking comercial de los productos mas vendidos.</p>
              </div>
              {topProducts.length === 0 ? (
                <p className="px-4 py-5 text-[13px] text-slate-500">No hay productos vendidos en el rango seleccionado.</p>
              ) : (
                <div className="overflow-x-auto">
                  <table className="w-full min-w-[560px]">
                    <thead>
                      <tr className="bg-slate-50">
                        <th className="px-4 py-2 text-left text-[10px] uppercase tracking-[0.11em] text-slate-500">Producto</th>
                        <th className="px-4 py-2 text-left text-[10px] uppercase tracking-[0.11em] text-slate-500">Unidades</th>
                        <th className="px-4 py-2 text-left text-[10px] uppercase tracking-[0.11em] text-slate-500">Ingresos</th>
                      </tr>
                    </thead>
                    <tbody>
                      {topProducts.map((row) => (
                        <tr key={row.product_id} className="border-t border-slate-100">
                          <td className="px-4 py-2.5 text-[12px] font-semibold text-slate-900">{row.name}</td>
                          <td className="px-4 py-2.5 text-[12px] text-slate-700">{formatInt(row.units_sold)}</td>
                          <td className="px-4 py-2.5 text-[12px] font-bold text-orange-600">{formatMoney(row.revenue)}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </section>
          ) : null}

          {activeView === 'inventory' ? (
            <section className="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-[0_8px_20px_rgba(15,23,42,0.05)]">
              <div className="border-b border-slate-100 px-4 py-3">
                <h3 className="text-[14px] font-black text-[#0F172A]">Analitica de inventario</h3>
                <p className="text-[12px] text-slate-500">Entradas, salidas, ajustes y top de productos de salida.</p>
              </div>
              <div className="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
                <div className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <p className="text-[11px] text-slate-500">Entradas</p>
                  <p className="mt-1 text-[15px] font-black text-slate-900">{formatInt(inventory?.entries ?? 0)}</p>
                </div>
                <div className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <p className="text-[11px] text-slate-500">Salidas</p>
                  <p className="mt-1 text-[15px] font-black text-slate-900">{formatInt(inventory?.outs ?? 0)}</p>
                </div>
                <div className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <p className="text-[11px] text-slate-500">Ajustes</p>
                  <p className="mt-1 text-[15px] font-black text-slate-900">{formatInt(inventory?.adjustments ?? 0)}</p>
                </div>
                <div className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <p className="text-[11px] text-slate-500">Rotacion aprox.</p>
                  <p className="mt-1 text-[15px] font-black text-orange-600">{rotationLabel}</p>
                </div>
              </div>
              {inventoryTopOut.length > 0 ? (
                <div className="border-t border-slate-100 px-4 py-3">
                  <p className="mb-2 text-[11px] font-semibold uppercase tracking-[0.11em] text-slate-500">Top salidas</p>
                  <div className="space-y-1">
                    {inventoryTopOut.map((row) => (
                      <div key={`inv-${row.product_id}`} className="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                        <span className="truncate text-[12px] text-slate-700">{row.name}</span>
                        <span className="text-[12px] font-semibold text-slate-900">{formatInt(row.units_out)}</span>
                      </div>
                    ))}
                  </div>
                </div>
              ) : null}
            </section>
          ) : null}

          {activeView === 'alerts' ? (
            alertsLoading ? (
              <div className="rounded-2xl border border-slate-200 bg-white px-4 py-14 text-center">
                <div className="mx-auto mb-3 h-8 w-8 animate-spin rounded-full border-2 border-orange-500 border-t-transparent" />
                <p className="text-[13px] text-slate-500">Cargando alertas...</p>
              </div>
            ) : (
              <div className="space-y-4">
                {/* Stock crítico */}
                <section className="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-[0_8px_20px_rgba(15,23,42,0.05)]">
                  <div className="border-b border-rose-100 bg-rose-50 px-4 py-3">
                    <h3 className="text-[14px] font-black text-rose-800">Stock crítico</h3>
                    <p className="text-[12px] text-rose-600">Productos por debajo del punto de reorden.</p>
                  </div>
                  {alertsData?.low_stock.length === 0 ? (
                    <div className="px-4 py-8 text-center">
                      <p className="text-[13px] text-slate-400">Sin productos en stock crítico. ¡Todo en orden!</p>
                    </div>
                  ) : (
                    <div className="divide-y divide-slate-100">
                      {alertsData?.low_stock.map((item) => (
                        <div key={`low-${item.product_id}`} className="flex items-center justify-between border-l-4 border-rose-400 px-4 py-3">
                          <span className="truncate text-[12px] font-semibold text-slate-800">{item.name}</span>
                          <div className="ml-3 flex shrink-0 items-center gap-3">
                            <span className="rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-bold text-rose-700">
                              Stock: {item.stock}
                            </span>
                            <span className="text-[11px] text-slate-400">
                              Mín: {item.reorder_point}
                            </span>
                          </div>
                        </div>
                      ))}
                    </div>
                  )}
                </section>

                {/* Sin movimiento 30 días */}
                <section className="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-[0_8px_20px_rgba(15,23,42,0.05)]">
                  <div className="border-b border-amber-100 bg-amber-50 px-4 py-3">
                    <h3 className="text-[14px] font-black text-amber-800">Sin movimiento 30 días</h3>
                    <p className="text-[12px] text-amber-600">Productos sin ventas en el último mes.</p>
                  </div>
                  {alertsData?.no_movement_30d.length === 0 ? (
                    <div className="px-4 py-8 text-center">
                      <p className="text-[13px] text-slate-400">Todos los productos tuvieron movimiento reciente.</p>
                    </div>
                  ) : (
                    <div className="divide-y divide-slate-100">
                      {alertsData?.no_movement_30d.map((item) => (
                        <div key={`nm-${item.product_id}`} className="flex items-center justify-between border-l-4 border-amber-400 px-4 py-3">
                          <span className="truncate text-[12px] font-semibold text-slate-800">{item.name}</span>
                          <span className="ml-3 shrink-0 text-[11px] text-slate-400">
                            {item.last_sale
                              ? `Última venta: ${item.last_sale.slice(0, 10)}`
                              : 'Nunca vendido'}
                          </span>
                        </div>
                      ))}
                    </div>
                  )}
                </section>

                {/* Mayor crecimiento */}
                <section className="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-[0_8px_20px_rgba(15,23,42,0.05)]">
                  <div className="border-b border-emerald-100 bg-emerald-50 px-4 py-3">
                    <h3 className="text-[14px] font-black text-emerald-800">Mayor crecimiento</h3>
                    <p className="text-[12px] text-emerald-600">Productos con mayor alza este mes vs. el anterior.</p>
                  </div>
                  {alertsData?.top_growth.length === 0 ? (
                    <div className="px-4 py-8 text-center">
                      <p className="text-[13px] text-slate-400">No hay datos de crecimiento para este mes aún.</p>
                    </div>
                  ) : (
                    <div className="divide-y divide-slate-100">
                      {alertsData?.top_growth.map((item) => (
                        <div key={`gr-${item.product_id}`} className="flex items-center justify-between border-l-4 border-emerald-400 px-4 py-3">
                          <span className="truncate text-[12px] font-semibold text-slate-800">{item.name}</span>
                          <span className="ml-3 shrink-0 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-700">
                            +{item.growth_pct}%
                          </span>
                        </div>
                      ))}
                    </div>
                  )}
                </section>
              </div>
            )
          ) : null}

          {activeView === 'trends' ? (
            trendsLoading ? (
              <div className="rounded-2xl border border-slate-200 bg-white px-4 py-14 text-center">
                <div className="mx-auto mb-3 h-8 w-8 animate-spin rounded-full border-2 border-orange-500 border-t-transparent" />
                <p className="text-[13px] text-slate-500">Cargando tendencias...</p>
              </div>
            ) : (
              <div className="grid gap-4 lg:grid-cols-2">
                {/* Ventas por mes */}
                <section className="rounded-2xl border border-[#E2E8F0] bg-white p-4 shadow-[0_8px_20px_rgba(15,23,42,0.05)]">
                  <div className="mb-4 flex items-center gap-2 border-b border-[#E2E8F0] pb-2">
                    <span className="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-orange-50 text-[14px]">📊</span>
                    <div>
                      <h3 className="text-[14px] font-black text-[#0F172A]">Ventas por mes</h3>
                      <p className="text-[11px] text-slate-400">Últimos 12 meses</p>
                    </div>
                  </div>
                  {trendsData?.sales_by_month.length === 0 ? (
                    <div className="py-8 text-center">
                      <p className="text-[13px] text-slate-400">Sin datos de ventas para los últimos 12 meses.</p>
                    </div>
                  ) : (
                    <div className="space-y-2">
                      {trendsData?.sales_by_month.map((row) => {
                        const widthPct = Math.max(2, (row.total / maxMonthTotal) * 100)
                        return (
                          <div key={row.month} className="flex items-center gap-3">
                            <span className="w-16 shrink-0 text-right text-[11px] font-semibold text-slate-500">{row.month}</span>
                            <div className="flex-1 overflow-hidden rounded-full bg-slate-100">
                              <div
                                className="h-5 rounded-full bg-gradient-to-r from-orange-400 to-orange-500 transition-all duration-500"
                                style={{ width: `${widthPct}%` }}
                              />
                            </div>
                            <span className="w-24 shrink-0 text-right text-[11px] font-bold text-orange-600">
                              {formatMoney(row.total)}
                            </span>
                          </div>
                        )
                      })}
                    </div>
                  )}
                </section>

                {/* Top categorías */}
                <section className="rounded-2xl border border-[#E2E8F0] bg-white p-4 shadow-[0_8px_20px_rgba(15,23,42,0.05)]">
                  <div className="mb-4 flex items-center gap-2 border-b border-[#E2E8F0] pb-2">
                    <span className="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-[14px]">🗂️</span>
                    <div>
                      <h3 className="text-[14px] font-black text-[#0F172A]">Top categorías</h3>
                      <p className="text-[11px] text-slate-400">Últimos 12 meses</p>
                    </div>
                  </div>
                  {trendsData?.top_categories.length === 0 ? (
                    <div className="py-8 text-center">
                      <p className="text-[13px] text-slate-400">Sin datos de categorías.</p>
                    </div>
                  ) : (
                    <div className="space-y-3">
                      {trendsData?.top_categories.map((cat) => {
                        const pct = Math.round((cat.total / totalCategoryRevenue) * 100)
                        return (
                          <div key={cat.name}>
                            <div className="mb-1 flex items-center justify-between">
                              <span className="truncate text-[12px] font-semibold text-slate-700">{cat.name}</span>
                              <span className="ml-2 shrink-0 text-[11px] font-bold text-slate-500">{pct}%</span>
                            </div>
                            <div className="overflow-hidden rounded-full bg-slate-100">
                              <div
                                className="h-2 rounded-full bg-gradient-to-r from-slate-400 to-slate-600 transition-all duration-500"
                                style={{ width: `${Math.max(2, pct)}%` }}
                              />
                            </div>
                            <p className="mt-0.5 text-right text-[11px] text-orange-500">{formatMoney(cat.total)}</p>
                          </div>
                        )
                      })}
                    </div>
                  )}
                </section>
              </div>
            )
          ) : null}

          {activeView === 'decisions' ? (
            decisionsLoading ? (
              <div className="rounded-2xl border border-[#E2E8F0] bg-white px-4 py-14 text-center shadow-[0_1px_4px_rgba(15,23,42,0.06)]">
                <div className="mx-auto mb-3 h-8 w-8 animate-spin rounded-full border-2 border-orange-500 border-t-transparent" />
                <p className="text-[13px] text-slate-500">Analizando inventario...</p>
              </div>
            ) : !decisionsData || filteredDecisions.length === 0 && decisionsFilter === 'all' && !decisionsSearch ? (
              <div className="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-emerald-200 bg-emerald-50 py-16 text-center">
                <span className="text-5xl">✅</span>
                <h3 className="mt-4 text-[18px] font-black text-slate-800">Tu inventario está saludable</h3>
                <p className="mt-1 text-[13px] text-slate-500">No hay productos en zona de alerta.</p>
              </div>
            ) : (
              <div className="space-y-4">
                {/* Health ring + KPI panel */}
                {decisionsSummary && (() => {
                  const score = decisionsSummary.health_score
                  const r = 38
                  const circ = 2 * Math.PI * r
                  const offset = circ - (score / 100) * circ
                  const ringColor = score < 40 ? '#DC2626' : score < 70 ? '#C2410C' : '#16A34A'
                  const scoreLabel = score < 40 ? 'Inventario crítico' : score < 70 ? 'Requiere atención' : 'Inventario saludable'
                  const scoreSub = score < 40 ? 'Acción inmediata' : score < 70 ? 'Revisar productos' : 'Todo en orden'
                  const copFmt = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 })
                  return (
                    <div style={{ background: '#FFFFFF', border: '1px solid #E2E8F0', borderRadius: 18, padding: '22px 26px', boxShadow: '0 1px 4px rgba(15,23,42,0.06), 0 8px 24px rgba(15,23,42,0.04)', display: 'grid', gridTemplateColumns: 'auto 1fr', gap: 24, alignItems: 'center' }}>
                      {/* Ring */}
                      <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
                        <div style={{ position: 'relative', width: 96, height: 96 }}>
                          <svg width={96} height={96} style={{ transform: 'rotate(-90deg)' }}>
                            <circle cx={48} cy={48} r={r} fill="none" stroke="#F1F5F9" strokeWidth={8} />
                            <circle cx={48} cy={48} r={r} fill="none" stroke={ringColor} strokeWidth={8}
                              strokeDasharray={circ} strokeDashoffset={offset} strokeLinecap="round"
                              style={{ transition: 'stroke-dashoffset 1.2s ease' }} />
                          </svg>
                          <div style={{ position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                            <span style={{ fontSize: 22, fontWeight: 900, color: ringColor, lineHeight: 1 }}>{score}</span>
                          </div>
                        </div>
                        <div>
                          <p style={{ fontSize: 10, color: '#94A3B8', letterSpacing: 2, textTransform: 'uppercase', marginBottom: 5, fontWeight: 600 }}>Salud del inventario</p>
                          <p style={{ fontSize: 17, fontWeight: 800, color: ringColor, marginBottom: 3 }}>{scoreLabel}</p>
                          <p style={{ fontSize: 12, color: '#94A3B8' }}>{scoreSub}</p>
                        </div>
                      </div>
                      {/* KPI cards */}
                      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: 12 }}>
                        {([
                          { label: 'Crítico', value: decisionsSummary.critical_count, icon: '🔴', color: '#DC2626', bg: '#FEF2F2', border: '#FECACA' },
                          { label: 'Alto',    value: decisionsSummary.high_count,     icon: '🟠', color: '#C2410C', bg: '#FFF7ED', border: '#FED7AA' },
                          { label: 'Medio',   value: decisionsSummary.medium_count,   icon: '🟡', color: '#92400E', bg: '#FFFBEB', border: '#FDE68A' },
                          { label: 'Inversión estimada', value: copFmt.format(decisionsSummary.total_restock_value), icon: '💰', color: '#166534', bg: '#F0FDF4', border: '#BBF7D0' },
                        ] as const).map((kpi) => (
                          <div key={kpi.label} style={{ background: kpi.bg, border: `1px solid ${kpi.border}`, borderRadius: 14, padding: '14px 16px', textAlign: 'center' }}>
                            <div style={{ fontSize: 20, marginBottom: 6 }}>{kpi.icon}</div>
                            <div style={{ fontSize: typeof kpi.value === 'string' ? 13 : 28, fontWeight: 900, color: kpi.color, fontFamily: 'monospace', lineHeight: 1.1, marginBottom: 4 }}>{kpi.value}</div>
                            <div style={{ fontSize: 10, color: '#94A3B8', textTransform: 'uppercase', letterSpacing: 1, fontWeight: 600 }}>{kpi.label}</div>
                          </div>
                        ))}
                      </div>
                    </div>
                  )
                })()}

                {/* Filter bar */}
                <div style={{ display: 'flex', gap: 10, flexWrap: 'wrap', alignItems: 'center' }}>
                  <div style={{ position: 'relative', flex: 1, minWidth: 200 }}>
                    <span style={{ position: 'absolute', left: 12, top: '50%', transform: 'translateY(-50%)', color: '#CBD5E1', fontSize: 14, pointerEvents: 'none' }}>🔍</span>
                    <input
                      type="text"
                      value={decisionsSearch}
                      onChange={(e) => setDecisionsSearch(e.target.value)}
                      placeholder="Buscar producto..."
                      style={{ width: '100%', background: '#FFFFFF', border: '1px solid #E2E8F0', borderRadius: 10, padding: '9px 14px 9px 36px', color: '#0F172A', fontSize: 13, outline: 'none', boxSizing: 'border-box', boxShadow: '0 1px 3px rgba(15,23,42,0.05)' }}
                    />
                  </div>
                  <div style={{ display: 'flex', gap: 5, background: '#FFFFFF', border: '1px solid #E2E8F0', borderRadius: 11, padding: 4, boxShadow: '0 1px 3px rgba(15,23,42,0.05)' }}>
                    {([
                      { key: 'all' as const,      label: 'Todos',   count: (decisionsData ?? []).filter(d => !decisionsDismissed.includes(d.id)).length },
                      { key: 'critical' as const, label: 'Crítico', count: decisionsSummary?.critical_count ?? 0 },
                      { key: 'high' as const,     label: 'Alto',    count: decisionsSummary?.high_count ?? 0 },
                      { key: 'medium' as const,   label: 'Medio',   count: decisionsSummary?.medium_count ?? 0 },
                      { key: 'low' as const,      label: 'Bajo',    count: decisionsSummary?.low_count ?? 0 },
                    ]).map((f) => (
                      <button key={f.key} type="button" onClick={() => setDecisionsFilter(f.key)} style={{
                        padding: '6px 14px', borderRadius: 8, border: 'none', cursor: 'pointer',
                        fontSize: 12, fontWeight: 700, transition: 'all 0.15s',
                        background: decisionsFilter === f.key ? '#F97316' : 'transparent',
                        color: decisionsFilter === f.key ? '#fff' : '#94A3B8',
                        boxShadow: decisionsFilter === f.key ? '0 2px 8px rgba(249,115,22,0.35)' : 'none',
                      }}>
                        {f.label} <span style={{ fontSize: 10, opacity: 0.75 }}>{f.count}</span>
                      </button>
                    ))}
                  </div>
                </div>

                {/* Table */}
                <div style={{ background: '#FFFFFF', border: '1px solid #E2E8F0', borderRadius: 18, overflow: 'hidden', boxShadow: '0 1px 4px rgba(15,23,42,0.06), 0 8px 24px rgba(15,23,42,0.04)' }}>
                  <div style={{ display: 'grid', gridTemplateColumns: '2fr 70px 80px 80px 90px 78px 110px 110px 120px', padding: '11px 22px', background: '#F8FAFC', borderBottom: '1px solid #F1F5F9', fontSize: 10, color: '#94A3B8', letterSpacing: 1.5, textTransform: 'uppercase', fontWeight: 700 }}>
                    {['Producto', 'Stock', 'Reorden', 'Vend. 30d', 'Rot. diaria', 'Días stock', 'Quiebre est.', 'Sugerencia 🧠', 'Acciones'].map((h, i) => (
                      <div key={h} style={{ textAlign: i === 0 ? 'left' : 'center' }}>{h}</div>
                    ))}
                  </div>

                  {filteredDecisions.length === 0 ? (
                    <div style={{ textAlign: 'center', padding: '52px 24px' }}>
                      <div style={{ fontSize: 44, marginBottom: 14 }}>✅</div>
                      <div style={{ fontSize: 16, fontWeight: 800, color: '#334155', marginBottom: 6 }}>Sin resultados</div>
                      <div style={{ fontSize: 13, color: '#94A3B8' }}>Ajusta los filtros para ver más productos.</div>
                    </div>
                  ) : filteredDecisions.map((item, idx) => {
                    const pc = DECISION_PRIORITY_CONFIG[item.priority] ?? DECISION_PRIORITY_CONFIG.low
                    const days = item.days_of_stock
                    const daysBg = days === null ? null : days < 7 ? ['#DC2626', '#FEF2F2'] : days < 14 ? ['#C2410C', '#FFF7ED'] : ['#16A34A', '#F0FDF4']
                    return (
                      <div key={item.id}
                        style={{
                          display: 'grid', gridTemplateColumns: '2fr 70px 80px 80px 90px 78px 110px 110px 120px',
                          padding: '14px 22px', borderBottom: idx < filteredDecisions.length - 1 ? '1px solid #F8FAFC' : 'none',
                          alignItems: 'center', borderLeft: `3px solid ${pc.leftBorder}`, cursor: 'default',
                        }}
                        onMouseEnter={(e) => (e.currentTarget.style.background = '#F8FAFC')}
                        onMouseLeave={(e) => (e.currentTarget.style.background = 'transparent')}
                      >
                        <div>
                          <div style={{ display: 'flex', alignItems: 'center', gap: 7, marginBottom: 4 }}>
                            <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5, padding: '2px 9px', borderRadius: 20, fontSize: 11, fontWeight: 700, color: pc.textColor, background: pc.bg, border: `1px solid ${pc.border}` }}>
                              <span style={{ width: 5, height: 5, borderRadius: '50%', background: pc.dotColor, flexShrink: 0 }} />
                              {pc.label}
                            </span>
                            <span style={{ fontSize: 13, fontWeight: 700, color: '#0F172A' }}>{item.name}</span>
                          </div>
                          {item.sku && <div style={{ fontSize: 11, color: '#CBD5E1', paddingLeft: 2 }}>SKU {item.sku}</div>}
                        </div>
                        <div style={{ textAlign: 'center', fontSize: 17, fontWeight: 900, fontFamily: 'monospace', color: item.stock === 0 ? '#DC2626' : item.stock <= item.reorder_point ? '#C2410C' : '#0F172A' }}>{item.stock}</div>
                        <div style={{ textAlign: 'center', fontSize: 13, color: '#64748B' }}>{item.reorder_point}</div>
                        <div style={{ textAlign: 'center', fontSize: 13, color: '#64748B' }}>{item.sold_30d}</div>
                        <div style={{ textAlign: 'center', fontSize: 13, color: '#64748B' }}>{item.daily_rotation}</div>
                        <div style={{ textAlign: 'center' }}>
                          {daysBg === null
                            ? <span style={{ color: '#CBD5E1', fontSize: 13 }}>—</span>
                            : <span style={{ color: daysBg[0], background: daysBg[1], padding: '3px 10px', borderRadius: 20, fontSize: 12, fontWeight: 700 }}>{days}d</span>
                          }
                        </div>
                        <div style={{ textAlign: 'center', fontSize: 11, color: '#94A3B8' }}>{item.projected_stockout ?? '—'}</div>
                        <div style={{ textAlign: 'center' }}>
                          <span style={{ fontSize: 14, fontWeight: 900, color: '#C2410C', fontFamily: 'monospace', background: '#FFF7ED', padding: '4px 12px', borderRadius: 8, border: '1px solid #FED7AA' }}>+{item.suggested_qty}</span>
                        </div>
                        <div style={{ textAlign: 'center', display: 'flex', justifyContent: 'center', gap: 6 }}>
                          <button type="button"
                            onClick={() => { setDecisionsModal(item); setModalQty(item.suggested_qty); setModalWhatsapp('') }}
                            style={{ padding: '6px 12px', background: '#FFF7ED', border: '1px solid #FED7AA', borderRadius: 8, color: '#C2410C', fontSize: 11, fontWeight: 800, cursor: 'pointer', whiteSpace: 'nowrap' }}
                          >📦 Pedir</button>
                          <button type="button"
                            onClick={() => setDecisionsDismissed((prev) => [...prev, item.id])}
                            style={{ width: 28, height: 28, background: '#F8FAFC', border: '1px solid #E2E8F0', borderRadius: 8, color: '#CBD5E1', fontSize: 12, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                          >✕</button>
                        </div>
                      </div>
                    )
                  })}

                  {filteredDecisions.length > 0 && (
                    <div style={{ padding: '12px 22px', borderTop: '1px solid #F1F5F9', display: 'flex', justifyContent: 'space-between', alignItems: 'center', background: '#FAFBFC' }}>
                      <span style={{ fontSize: 12, color: '#94A3B8' }}>{filteredDecisions.length} productos en zona de alerta</span>
                      {decisionsSummary && (
                        <span style={{ fontSize: 12, color: '#94A3B8' }}>
                          Inversión total: <span style={{ color: '#C2410C', fontWeight: 800 }}>
                            {new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(decisionsSummary.total_restock_value)}
                          </span>
                        </span>
                      )}
                    </div>
                  )}
                </div>
              </div>
            )
          ) : null}
        </>
      )}

      {/* Modal solicitar reposición */}
      {decisionsModal && (
        <div
          style={{ position: 'fixed', inset: 0, background: 'rgba(15,23,42,0.45)', zIndex: 50, display: 'flex', alignItems: 'center', justifyContent: 'center', backdropFilter: 'blur(6px)', padding: 16 }}
          onClick={() => setDecisionsModal(null)}
        >
          <div
            style={{ background: '#FFFFFF', borderRadius: 20, padding: 28, width: 440, maxWidth: '92vw', boxShadow: '0 20px 60px rgba(15,23,42,0.18), 0 0 0 1px rgba(0,0,0,0.06)' }}
            onClick={(e) => e.stopPropagation()}
          >
            {/* Header */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 20 }}>
              <div>
                <div style={{ fontSize: 10, color: '#94A3B8', letterSpacing: 2, textTransform: 'uppercase', marginBottom: 5, fontWeight: 600 }}>Solicitar reposición</div>
                <div style={{ fontSize: 16, fontWeight: 800, color: '#0F172A' }}>{decisionsModal.name}</div>
                <div style={{ fontSize: 12, color: '#94A3B8', marginTop: 3 }}>
                  {decisionsModal.sku ? `SKU ${decisionsModal.sku} · ` : ''}Stock actual:{' '}
                  <span style={{ color: '#DC2626', fontWeight: 700 }}>{decisionsModal.stock}</span>
                </div>
              </div>
              <button type="button" onClick={() => setDecisionsModal(null)} style={{ width: 32, height: 32, borderRadius: 8, border: '1px solid #E2E8F0', background: '#F8FAFC', color: '#94A3B8', cursor: 'pointer', fontSize: 14, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>✕</button>
            </div>

            {/* Stats */}
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10, marginBottom: 18 }}>
              <div style={{ background: '#FFF7ED', border: '1px solid #FED7AA', borderRadius: 12, padding: 14 }}>
                <div style={{ fontSize: 10, color: '#94A3B8', letterSpacing: 1, textTransform: 'uppercase', marginBottom: 5, fontWeight: 600 }}>Sugerencia IA 🧠</div>
                <div style={{ fontSize: 26, fontWeight: 900, color: '#C2410C', fontFamily: 'monospace' }}>{decisionsModal.suggested_qty}</div>
                <div style={{ fontSize: 11, color: '#94A3B8' }}>unidades</div>
              </div>
              <div style={{ background: '#F8FAFC', border: '1px solid #E2E8F0', borderRadius: 12, padding: 14 }}>
                <div style={{ fontSize: 10, color: '#94A3B8', letterSpacing: 1, textTransform: 'uppercase', marginBottom: 5, fontWeight: 600 }}>Valor estimado</div>
                <div style={{ fontSize: 16, fontWeight: 800, color: '#0F172A', fontFamily: 'monospace' }}>
                  {new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 }).format(decisionsModal.price * decisionsModal.suggested_qty)}
                </div>
                <div style={{ fontSize: 11, color: '#94A3B8' }}>COP</div>
              </div>
            </div>

            <div style={{ marginBottom: 12 }}>
              <label style={{ fontSize: 11, color: '#64748B', display: 'block', marginBottom: 6, letterSpacing: 1, textTransform: 'uppercase', fontWeight: 600 }}>Cantidad a pedir</label>
              <input
                type="number"
                min={1}
                value={modalQty}
                onChange={(e) => setModalQty(Math.max(1, Number(e.target.value)))}
                style={{ width: '100%', background: '#F8FAFC', border: '1px solid #E2E8F0', borderRadius: 10, padding: '10px 14px', color: '#0F172A', fontSize: 15, outline: 'none', boxSizing: 'border-box', fontWeight: 700 }}
              />
            </div>

            <div style={{ marginBottom: 22 }}>
              <label style={{ fontSize: 11, color: '#64748B', display: 'block', marginBottom: 6, letterSpacing: 1, textTransform: 'uppercase', fontWeight: 600 }}>WhatsApp proveedor</label>
              <input
                type="tel"
                value={modalWhatsapp}
                onChange={(e) => setModalWhatsapp(e.target.value)}
                placeholder="+57 300 000 0000"
                style={{ width: '100%', background: '#F8FAFC', border: '1px solid #E2E8F0', borderRadius: 10, padding: '10px 14px', color: '#0F172A', fontSize: 14, outline: 'none', boxSizing: 'border-box' }}
              />
            </div>

            <div style={{ display: 'flex', gap: 10 }}>
              <button type="button"
                onClick={() => {
                  const phone = modalWhatsapp.replace(/\D/g, '')
                  const msg = encodeURIComponent(`Hola, necesito reabastecer *${decisionsModal!.name}* x ${modalQty} unidades. Gracias.`)
                  window.open(`https://wa.me/${phone}?text=${msg}`, '_blank', 'noopener,noreferrer')
                }}
                style={{ flex: 1, padding: '12px 0', background: '#25D366', border: 'none', borderRadius: 11, color: '#fff', fontSize: 13, fontWeight: 800, cursor: 'pointer', boxShadow: '0 4px 14px rgba(37,211,102,0.35)', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8 }}
              >
                <svg width={15} height={15} viewBox="0 0 24 24" fill="white">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                  <path d="M12 0C5.373 0 0 5.373 0 12c0 2.124.555 4.118 1.528 5.855L0 24l6.335-1.505A11.95 11.95 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.885 0-3.65-.49-5.19-1.348l-.372-.22-3.762.894.954-3.668-.243-.388A9.975 9.975 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z" />
                </svg>
                Enviar WhatsApp
              </button>
              <button type="button"
                onClick={() => setDecisionsModal(null)}
                style={{ padding: '12px 18px', background: '#F8FAFC', border: '1px solid #E2E8F0', borderRadius: 11, color: '#64748B', fontSize: 13, fontWeight: 700, cursor: 'pointer' }}
              >Cerrar</button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
