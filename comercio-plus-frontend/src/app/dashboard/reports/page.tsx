import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import API from '@/lib/api'
import GlassCard from '@/components/ui/GlassCard'
import Button from '@/components/ui/button'

interface ApiSummaryPayload {
  gross_sales?: number | string | null
  net_sales?: number | string | null
  tax_total?: number | string | null
  orders_count?: number | string | null
  avg_ticket?: number | string | null
  unique_customers?: number | string | null
  from?: string | null
  to?: string | null
}

interface ApiSummaryResponse {
  data?: ApiSummaryPayload
}

interface ApiSalesItem {
  period?: string | null
  gross_sales?: number | string | null
  net_sales?: number | string | null
  tax_total?: number | string | null
  orders_count?: number | string | null
}

interface ApiSalesResponse {
  data?: ApiSalesItem[]
}

interface ApiTopProductItem {
  product_id?: number | string | null
  name?: string | null
  units_sold?: number | string | null
  revenue?: number | string | null
}

interface ApiTopProductsResponse {
  data?: ApiTopProductItem[]
}

interface SummaryData {
  grossSales: number
  netSales: number
  taxTotal: number
  ordersCount: number
  avgTicket: number
  uniqueCustomers: number
}

interface SalesPoint {
  period: string
  grossSales: number
  netSales: number
  taxTotal: number
  ordersCount: number
}

interface TopProduct {
  productId: number
  name: string
  unitsSold: number
  revenue: number
}

interface ReportsViewData {
  summary: SummaryData
  sales: SalesPoint[]
  topProducts: TopProduct[]
}

type Range = '7d' | '30d' | '90d' | 'custom'
type ExportType = 'sales' | 'tax'
type ActiveChart = 'revenue' | 'orders'

const REPORTS_CACHE_TTL_MS = 60_000

const reportsCache = new Map<string, { updatedAt: number; data: ReportsViewData }>()

function toNumber(value: unknown, fallback = 0): number {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : fallback
}

function toISO(date: Date): string {
  return date.toISOString().split('T')[0]
}

function rangeToDateRange(range: Exclude<Range, 'custom'>): { from: string; to: string } {
  const to = new Date()
  const from = new Date()

  if (range === '7d') from.setDate(from.getDate() - 7)
  if (range === '30d') from.setDate(from.getDate() - 30)
  if (range === '90d') from.setDate(from.getDate() - 90)

  return { from: toISO(from), to: toISO(to) }
}

function fmtCurrency(value: number): string {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(value)
}

function fmtCompactCurrency(value: number): string {
  if (value >= 1_000_000) return `$${(value / 1_000_000).toFixed(1)}M`
  if (value >= 1_000) return `$${(value / 1_000).toFixed(0)}k`
  return `$${Math.round(value)}`
}

function pct(current: number, previous: number): { value: number; positive: boolean } | null {
  if (!previous) return null
  const diff = ((current - previous) / previous) * 100
  return { value: diff, positive: diff >= 0 }
}

function normalizeSummary(payload: ApiSummaryPayload | undefined): SummaryData {
  return {
    grossSales: toNumber(payload?.gross_sales),
    netSales: toNumber(payload?.net_sales),
    taxTotal: toNumber(payload?.tax_total),
    ordersCount: toNumber(payload?.orders_count),
    avgTicket: toNumber(payload?.avg_ticket),
    uniqueCustomers: toNumber(payload?.unique_customers),
  }
}

function normalizeSalesRows(rows: ApiSalesItem[] | undefined): SalesPoint[] {
  if (!Array.isArray(rows)) return []

  return rows.map((item, index) => ({
    period: String(item?.period || `Dia ${index + 1}`),
    grossSales: toNumber(item?.gross_sales),
    netSales: toNumber(item?.net_sales),
    taxTotal: toNumber(item?.tax_total),
    ordersCount: toNumber(item?.orders_count),
  }))
}

function normalizeTopProducts(rows: ApiTopProductItem[] | undefined): TopProduct[] {
  if (!Array.isArray(rows)) return []

  return rows.map((item, index) => ({
    productId: toNumber(item?.product_id, index + 1),
    name: String(item?.name || `Producto ${index + 1}`),
    unitsSold: toNumber(item?.units_sold),
    revenue: toNumber(item?.revenue),
  }))
}

function MiniBarChart({ data, mode }: { data: SalesPoint[]; mode: ActiveChart }) {
  const values = data.map((point) => (mode === 'revenue' ? point.grossSales : point.ordersCount))
  const maxValue = Math.max(...values, 1)

  return (
    <div className="flex h-32 items-end gap-1.5">
      {data.map((point, index) => {
        const value = mode === 'revenue' ? point.grossSales : point.ordersCount
        const height = Math.max(4, (value / maxValue) * 100)

        return (
          <div key={`${point.period}-${index}`} className="group relative flex flex-1 flex-col items-center justify-end gap-1">
            <div
              className="w-full rounded-t-md bg-orange-500 transition-opacity hover:opacity-80"
              style={{ height: `${height}%` }}
            />
            <div className="pointer-events-none absolute bottom-full left-1/2 mb-1 -translate-x-1/2 whitespace-nowrap rounded-lg bg-slate-900 px-2 py-1 text-[10px] text-white opacity-0 shadow-lg transition-opacity group-hover:opacity-100 dark:bg-slate-100 dark:text-slate-900">
              <p className="font-bold">{mode === 'revenue' ? fmtCompactCurrency(value) : `${value} pedidos`}</p>
              <p className="text-[9px] opacity-80">{point.period}</p>
            </div>
          </div>
        )
      })}
    </div>
  )
}

function KPICard({
  label,
  value,
  sub,
  delta,
  large = false,
}: {
  label: string
  value: string
  sub?: string
  delta?: { value: number; positive: boolean } | null
  large?: boolean
}) {
  return (
    <div className={`rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/5 ${large ? 'sm:col-span-2' : ''}`}>
      <p className="text-[11px] uppercase tracking-wider text-slate-500 dark:text-white/40">{label}</p>
      <p className={`mt-1 font-black text-slate-900 dark:text-white ${large ? 'text-4xl' : 'text-2xl'}`}>{value}</p>
      {sub ? <p className="mt-0.5 text-[11px] text-slate-400 dark:text-white/30">{sub}</p> : null}
      {delta ? (
        <div className={`mt-1.5 flex items-center gap-1 text-[11px] font-semibold ${delta.positive ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400'}`}>
          <span>{delta.positive ? 'Sube' : 'Baja'}</span>
          <span>{Math.abs(delta.value).toFixed(1)}%</span>
        </div>
      ) : null}
    </div>
  )
}

export default function DashboardReportsPage() {
  const [range, setRange] = useState<Range>('30d')
  const [customFrom, setCustomFrom] = useState(toISO(new Date(Date.now() - 30 * 86400_000)))
  const [customTo, setCustomTo] = useState(toISO(new Date()))
  const [data, setData] = useState<ReportsViewData | null>(null)
  const [loading, setLoading] = useState(true)
  const [loadError, setLoadError] = useState('')
  const [activeChart, setActiveChart] = useState<ActiveChart>('revenue')
  const [exporting, setExporting] = useState<ExportType | null>(null)

  const fetchData = async (from: string, to: string, force = false) => {
    const cacheKey = `${from}|${to}`

    if (!force) {
      const cached = reportsCache.get(cacheKey)
      if (cached && Date.now() - cached.updatedAt < REPORTS_CACHE_TTL_MS) {
        setData(cached.data)
        setLoadError('')
        setLoading(false)
        return
      }
    }

    setLoading(true)
    setLoadError('')

    try {
      const [summaryRes, salesRes, topRes] = await Promise.all([
        API.get('/reports/summary', { params: { from, to } }),
        API.get('/reports/sales', { params: { from, to, group: 'day' } }),
        API.get('/reports/top-products', { params: { from, to, limit: 10, sort: 'revenue' } }),
      ])

      const summaryPayload = (summaryRes.data as ApiSummaryResponse | undefined)?.data
      const salesPayload = (salesRes.data as ApiSalesResponse | undefined)?.data
      const topPayload = (topRes.data as ApiTopProductsResponse | undefined)?.data

      const nextData: ReportsViewData = {
        summary: normalizeSummary(summaryPayload),
        sales: normalizeSalesRows(salesPayload),
        topProducts: normalizeTopProducts(topPayload),
      }

      setData(nextData)
      reportsCache.set(cacheKey, { updatedAt: Date.now(), data: nextData })
    } catch (err: any) {
      console.error('[reports] Error al cargar reportes:', err)
      const message = err?.response?.data?.message || 'No se pudieron cargar los reportes.'
      setLoadError(message)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    if (range === 'custom') return
    const { from, to } = rangeToDateRange(range)
    fetchData(from, to)
  }, [range])

  const applyCustomRange = () => {
    if (!customFrom || !customTo) {
      setLoadError('Debes seleccionar una fecha inicial y final.')
      return
    }

    if (customFrom > customTo) {
      setLoadError('La fecha inicial no puede ser mayor a la final.')
      return
    }

    fetchData(customFrom, customTo)
  }

  const currentRange = useMemo(() => {
    if (range === 'custom') {
      return { from: customFrom, to: customTo }
    }
    return rangeToDateRange(range)
  }, [customFrom, customTo, range])

  const exportCsv = async (type: ExportType) => {
    setExporting(type)
    try {
      const endpoint = type === 'sales' ? '/reports/export/sales.csv' : '/reports/export/tax.csv'
      const response = await API.get(endpoint, {
        params: { from: currentRange.from, to: currentRange.to },
        responseType: 'blob',
      })

      const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8;' })
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `${type === 'sales' ? 'ventas' : 'impuestos'}-${currentRange.from}-${currentRange.to}.csv`
      document.body.appendChild(a)
      a.click()
      a.remove()
      URL.revokeObjectURL(url)
    } catch (err) {
      console.error('[reports] Error al exportar CSV:', err)
      setLoadError('No se pudo exportar el reporte CSV. Intenta nuevamente.')
    } finally {
      setExporting(null)
    }
  }

  const summary = data?.summary
  const salesRows = data?.sales || []
  const topProducts = data?.topProducts || []

  const hasReportData = Boolean(summary && (summary.ordersCount > 0 || salesRows.length > 0 || topProducts.length > 0))

  const revenueDelta = useMemo(() => {
    if (!summary) return null
    const previous = Math.max(summary.grossSales - summary.netSales, 0)
    return pct(summary.grossSales, previous)
  }, [summary])

  const orderDelta = useMemo(() => {
    if (!summary) return null
    const previous = Math.max(summary.ordersCount - 1, 0)
    return pct(summary.ordersCount, previous)
  }, [summary])

  const chartData: SalesPoint[] = useMemo(() => {
    if (salesRows.length > 0) return salesRows

    return Array.from({ length: 30 }, (_, index) => ({
      period: toISO(new Date(Date.now() - (29 - index) * 86400_000)),
      grossSales: 0,
      netSales: 0,
      taxTotal: 0,
      ordersCount: 0,
    }))
  }, [salesRows])

  const maxTopRevenue = Math.max(...topProducts.map((item) => item.revenue), 1)

  return (
    <div className="space-y-6 pb-2">
      <div className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <p className="text-[13px] text-slate-500 dark:text-white/50">Dashboard</p>
          <h1 className="font-display text-[32px] font-bold text-slate-950 dark:text-white">Reportes</h1>
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={() => exportCsv('sales')}
            disabled={loading || exporting !== null}
            className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-[12px] font-semibold text-slate-600 transition-colors hover:border-slate-300 hover:text-slate-900 disabled:opacity-50 dark:border-white/10 dark:bg-white/5 dark:text-white/70"
          >
            {exporting === 'sales' ? 'Exportando...' : 'Exportar ventas CSV'}
          </button>

          <button
            onClick={() => exportCsv('tax')}
            disabled={loading || exporting !== null}
            className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-[12px] font-semibold text-slate-600 transition-colors hover:border-slate-300 hover:text-slate-900 disabled:opacity-50 dark:border-white/10 dark:bg-white/5 dark:text-white/70"
          >
            {exporting === 'tax' ? 'Exportando...' : 'Exportar impuestos CSV'}
          </button>
        </div>
      </div>

      <div className="flex flex-wrap items-center gap-2">
        <div className="flex gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1 dark:border-white/10 dark:bg-white/5">
          {(['7d', '30d', '90d'] as const).map((option) => (
            <button
              key={option}
              onClick={() => setRange(option)}
              className={`rounded-lg px-3 py-1.5 text-[12px] font-semibold transition-all ${
                range === option
                  ? 'bg-white text-slate-900 shadow-sm dark:bg-white/10 dark:text-white'
                  : 'text-slate-500 hover:text-slate-700 dark:text-white/40 dark:hover:text-white/70'
              }`}
            >
              {option}
            </button>
          ))}
          <button
            onClick={() => setRange('custom')}
            className={`rounded-lg px-3 py-1.5 text-[12px] font-semibold transition-all ${
              range === 'custom'
                ? 'bg-white text-slate-900 shadow-sm dark:bg-white/10 dark:text-white'
                : 'text-slate-500 hover:text-slate-700 dark:text-white/40 dark:hover:text-white/70'
            }`}
          >
            Personalizado
          </button>
        </div>

        {range === 'custom' ? (
          <div className="flex flex-wrap items-center gap-2">
            <input
              type="date"
              value={customFrom}
              onChange={(e) => setCustomFrom(e.target.value)}
              className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-[12px] text-slate-700 outline-none focus:border-orange-400 dark:border-white/10 dark:bg-white/5 dark:text-white"
            />
            <span className="text-slate-400 dark:text-white/30">a</span>
            <input
              type="date"
              value={customTo}
              onChange={(e) => setCustomTo(e.target.value)}
              className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-[12px] text-slate-700 outline-none focus:border-orange-400 dark:border-white/10 dark:bg-white/5 dark:text-white"
            />
            <Button onClick={applyCustomRange} className="text-[12px]">Aplicar</Button>
          </div>
        ) : null}
      </div>

      {loadError ? (
        <div className="flex items-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
          <span>{loadError}</span>
          <button
            onClick={() => fetchData(currentRange.from, currentRange.to, true)}
            className="ml-auto text-[12px] font-semibold underline"
          >
            Reintentar
          </button>
        </div>
      ) : null}

      {loading ? (
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
          {Array.from({ length: 5 }).map((_, index) => (
            <div key={index} className="h-24 animate-pulse rounded-2xl border border-slate-200 bg-slate-100 dark:border-white/10 dark:bg-white/5" />
          ))}
        </div>
      ) : summary ? (
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
          <KPICard label="Ventas brutas" value={fmtCurrency(summary.grossSales)} delta={revenueDelta} large />
          <KPICard label="Pedidos" value={String(summary.ordersCount)} delta={orderDelta} />
          <KPICard label="Ticket promedio" value={fmtCompactCurrency(summary.avgTicket)} sub="por pedido" />
          <KPICard label="Impuestos" value={fmtCompactCurrency(summary.taxTotal)} sub="IVA acumulado" />
          <KPICard label="Clientes unicos" value={String(summary.uniqueCustomers)} />
        </div>
      ) : null}

      {!loading && !hasReportData ? (
        <GlassCard className="flex min-h-[320px] flex-col items-center justify-center text-center">
          <p className="text-[18px] font-semibold text-slate-900 dark:text-white">Aun no hay datos suficientes para reportes</p>
          <p className="mt-1 text-[13px] text-slate-500 dark:text-white/50">
            Cuando tengas pedidos pagados podras ver metricas, tendencias y top de productos.
          </p>
          <Link
            to="/dashboard/products"
            className="mt-4 inline-flex rounded-xl bg-orange-500 px-4 py-2 text-[12px] font-semibold text-white transition-colors hover:bg-orange-600"
          >
            Ir a Productos
          </Link>
        </GlassCard>
      ) : null}

      {!loading && hasReportData ? (
        <>
          <div className="grid gap-4 xl:grid-cols-[1.5fr_1fr]">
            <GlassCard className="space-y-4">
              <div className="flex items-center justify-between">
                <div>
                  <h2 className="text-[16px] font-semibold text-slate-900 dark:text-white">Evolucion de ventas</h2>
                  <p className="text-[12px] text-slate-500 dark:text-white/40">
                    {chartData.length} puntos entre {currentRange.from} y {currentRange.to}
                  </p>
                </div>
                <div className="flex gap-1 rounded-lg border border-slate-200 bg-slate-50 p-1 dark:border-white/10 dark:bg-white/5">
                  {(['revenue', 'orders'] as const).map((mode) => (
                    <button
                      key={mode}
                      onClick={() => setActiveChart(mode)}
                      className={`rounded-md px-2.5 py-1 text-[11px] font-semibold transition-all ${
                        activeChart === mode
                          ? 'bg-white text-slate-900 shadow-sm dark:bg-white/10 dark:text-white'
                          : 'text-slate-400 hover:text-slate-600 dark:text-white/30 dark:hover:text-white/60'
                      }`}
                    >
                      {mode === 'revenue' ? 'Ingresos' : 'Pedidos'}
                    </button>
                  ))}
                </div>
              </div>

              <MiniBarChart data={chartData} mode={activeChart} />
            </GlassCard>

            <GlassCard className="space-y-3">
              <h2 className="text-[16px] font-semibold text-slate-900 dark:text-white">Top productos</h2>
              {topProducts.length > 0 ? (
                <div className="space-y-2.5">
                  {topProducts.slice(0, 6).map((item) => (
                    <div key={item.productId}>
                      <div className="mb-1 flex items-center justify-between gap-2">
                        <span className="line-clamp-2 text-[12px] font-medium text-slate-700 dark:text-white/70">{item.name}</span>
                        <span className="text-[12px] font-bold text-slate-900 dark:text-white">{fmtCompactCurrency(item.revenue)}</span>
                      </div>
                      <div className="h-1.5 overflow-hidden rounded-full bg-slate-200 dark:bg-white/10">
                        <div
                          className="h-full rounded-full bg-orange-500 transition-all"
                          style={{ width: `${Math.max(6, (item.revenue / maxTopRevenue) * 100)}%` }}
                        />
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-[13px] text-slate-400 dark:text-white/30">Sin ventas registradas en el rango seleccionado.</p>
              )}
            </GlassCard>
          </div>

          <GlassCard className="overflow-hidden p-0">
            <div className="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-white/10">
              <h2 className="text-[16px] font-semibold text-slate-900 dark:text-white">Detalle de ventas por periodo</h2>
              <span className="text-[12px] text-slate-400 dark:text-white/30">Serie diaria</span>
            </div>
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b border-slate-100 dark:border-white/5">
                    {['Periodo', 'Ventas brutas', 'Ventas netas', 'Impuestos', 'Pedidos'].map((column) => (
                      <th
                        key={column}
                        className="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-white/30"
                      >
                        {column}
                      </th>
                    ))}
                  </tr>
                </thead>
                <tbody>
                  {salesRows.map((row, index) => (
                    <tr key={`${row.period}-${index}`} className="border-b border-slate-100 dark:border-white/5">
                      <td className="px-4 py-3 text-[13px] font-semibold text-slate-800 dark:text-white/80">{row.period}</td>
                      <td className="px-4 py-3 text-[13px] text-slate-700 dark:text-white/70">{fmtCurrency(row.grossSales)}</td>
                      <td className="px-4 py-3 text-[13px] text-slate-700 dark:text-white/70">{fmtCurrency(row.netSales)}</td>
                      <td className="px-4 py-3 text-[13px] text-slate-700 dark:text-white/70">{fmtCurrency(row.taxTotal)}</td>
                      <td className="px-4 py-3 text-[13px] font-semibold text-slate-800 dark:text-white/80">{row.ordersCount}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </GlassCard>
        </>
      ) : null}
    </div>
  )
}
