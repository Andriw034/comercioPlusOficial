import { useEffect, useState } from 'react'
import API from '@/lib/api'
import GlassCard from '@/components/ui/GlassCard'
import Button from '@/components/ui/button'

// -- Types ---------------------------------------------------------------------

interface SalePoint {
  date: string
  revenue: number
  orders: number
}

interface TopProduct {
  product_id: number
  product_name: string
  sku: string
  units_sold: number
  revenue: number
}

interface ReportData {
  total_revenue: number
  total_orders: number
  avg_ticket: number
  total_tax: number
  unique_customers: number
  prev_revenue: number
  prev_orders: number
  sales_by_day: SalePoint[]
  top_products: TopProduct[]
  revenue_by_category: { category: string; revenue: number }[]
}

type Range = '7d' | '30d' | '90d' | 'custom'

// -- Helpers -------------------------------------------------------------------

function fmt(n: number) {
  if (n >= 1_000_000) return `$${(n / 1_000_000).toFixed(1)}M`
  if (n >= 1_000) return `$${(n / 1_000).toFixed(0)}k`
  return `$${n}`
}

function fmtFull(n: number) {
  return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(n)
}

function pct(current: number, prev: number) {
  if (!prev) return null
  const diff = ((current - prev) / prev) * 100
  return { value: diff, positive: diff >= 0 }
}

function toISO(date: Date) {
  return date.toISOString().split('T')[0]
}

function rangeToDateRange(range: Range): { from: string; to: string } {
  const to = new Date()
  const from = new Date()
  if (range === '7d') from.setDate(from.getDate() - 7)
  else if (range === '30d') from.setDate(from.getDate() - 30)
  else if (range === '90d') from.setDate(from.getDate() - 90)
  return { from: toISO(from), to: toISO(to) }
}

// -- MiniBarChart --------------------------------------------------------------

function MiniBarChart({ data, color = '#f97316' }: { data: SalePoint[]; color?: string }) {
  const maxVal = Math.max(...data.map((d) => d.revenue), 1)

  return (
    <div className="flex h-32 items-end gap-1">
      {data.map((d, i) => {
        const h = Math.max(4, (d.revenue / maxVal) * 100)
        const label = new Date(d.date).toLocaleDateString('es-CO', { day: '2-digit', month: 'short' })
        return (
          <div key={i} className="group relative flex flex-1 flex-col items-center justify-end gap-1">
            <div
              className="w-full rounded-t-md transition-opacity hover:opacity-80"
              style={{ height: `${h}%`, backgroundColor: color }}
            />
            {/* Tooltip */}
            <div className="pointer-events-none absolute bottom-full left-1/2 mb-1 -translate-x-1/2 whitespace-nowrap rounded-lg bg-slate-900 px-2 py-1 text-[10px] text-white opacity-0 shadow-lg transition-opacity group-hover:opacity-100 dark:bg-white dark:text-slate-900">
              <p className="font-bold">{fmt(d.revenue)}</p>
              <p className="text-[9px] opacity-70">{label} - {d.orders} pedidos</p>
            </div>
          </div>
        )
      })}
    </div>
  )
}

// -- KPICard -------------------------------------------------------------------

function KPICard({
  label,
  value,
  sub,
  delta,
  icon,
  large = false,
}: {
  label: string
  value: string
  sub?: string
  delta?: { value: number; positive: boolean } | null
  icon: string
  large?: boolean
}) {
  return (
    <div className={`rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/5 ${large ? 'sm:col-span-2' : ''}`}>
      <div className="mb-2 flex items-center justify-between">
        <p className="text-[11px] uppercase tracking-wider text-slate-500 dark:text-white/40">{label}</p>
        <span className="text-lg">{icon}</span>
      </div>
      <p className={`font-black text-slate-900 dark:text-white ${large ? 'text-4xl' : 'text-2xl'}`}>{value}</p>
      {sub && <p className="mt-0.5 text-[11px] text-slate-400 dark:text-white/30">{sub}</p>}
      {delta !== undefined && delta !== null && (
        <div className={`mt-1.5 flex items-center gap-1 text-[11px] font-semibold ${delta.positive ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400'}`}>
          <span>{delta.positive ? '?' : '?'}</span>
          <span>{Math.abs(delta.value).toFixed(1)}% vs periodo anterior</span>
        </div>
      )}
    </div>
  )
}

// -- Page ----------------------------------------------------------------------

export default function DashboardReportsPage() {
  const [range, setRange] = useState<Range>('30d')
  const [customFrom, setCustomFrom] = useState(toISO(new Date(Date.now() - 30 * 86400_000)))
  const [customTo, setCustomTo] = useState(toISO(new Date()))
  const [data, setData] = useState<ReportData | null>(null)
  const [loading, setLoading] = useState(true)
  const [loadError, setLoadError] = useState('')
  const [exporting, setExporting] = useState<'csv' | 'pdf' | null>(null)
  const [activeChart, setActiveChart] = useState<'revenue' | 'orders'>('revenue')

  const fetchData = async (from: string, to: string) => {
    setLoading(true)
    setLoadError('')
    try {
      const { data: res } = await API.get(`/reports/sales?from=${from}&to=${to}`)
      setData(res)
    } catch (err: any) {
      setLoadError(err?.response?.data?.message || 'No se pudieron cargar los reportes.')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    if (range !== 'custom') {
      const { from, to } = rangeToDateRange(range)
      fetchData(from, to)
    }
  }, [range])

  const applyCustom = () => {
    if (customFrom && customTo) fetchData(customFrom, customTo)
  }

  const doExport = async (format: 'csv' | 'pdf') => {
    setExporting(format)
    try {
      const { from, to } = range !== 'custom' ? rangeToDateRange(range) : { from: customFrom, to: customTo }
      const response = await API.get(`/reports/export?format=${format}&from=${from}&to=${to}`, {
        responseType: 'blob',
      })
      const url = URL.createObjectURL(response.data)
      const a = document.createElement('a')
      a.href = url
      a.download = `reporte-${from}-${to}.${format}`
      a.click()
      URL.revokeObjectURL(url)
    } catch {
      alert('Error al exportar. Intenta nuevamente.')
    } finally {
      setExporting(null)
    }
  }

  // -- Derived -----------------------------------------------------------------

  const revDelta = data ? pct(data.total_revenue, data.prev_revenue) : null
  const ordDelta = data ? pct(data.total_orders, data.prev_orders) : null

  const maxRevCategory = data
    ? Math.max(...data.revenue_by_category.map((c) => c.revenue), 1)
    : 1

  const chartData: SalePoint[] =
    data?.sales_by_day?.length
      ? data.sales_by_day
      : Array.from({ length: 30 }, (_, i) => ({
          date: toISO(new Date(Date.now() - (29 - i) * 86400_000)),
          revenue: 0,
          orders: 0,
        }))

  return (
    <div className="space-y-6 pb-6">
      {/* Header */}
      <div className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <p className="text-[13px] text-slate-500 dark:text-white/50">Dashboard</p>
          <h1 className="font-display text-[32px] font-bold text-slate-950 dark:text-white">Reportes</h1>
        </div>
        <div className="flex items-center gap-2">
          <button
            onClick={() => doExport('csv')}
            disabled={!!exporting || loading}
            className="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-[12px] font-semibold text-slate-600 transition-colors hover:border-slate-300 hover:text-slate-900 disabled:opacity-50 dark:border-white/10 dark:bg-white/5 dark:text-white/60 dark:hover:text-white"
          >
            {exporting === 'csv' ? '?' : '??'} CSV
          </button>
          <button
            onClick={() => doExport('pdf')}
            disabled={!!exporting || loading}
            className="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-[12px] font-semibold text-slate-600 transition-colors hover:border-slate-300 hover:text-slate-900 disabled:opacity-50 dark:border-white/10 dark:bg-white/5 dark:text-white/60 dark:hover:text-white"
          >
            {exporting === 'pdf' ? '?' : '??'} PDF
          </button>
        </div>
      </div>

      {/* Range selector */}
      <div className="flex flex-wrap items-center gap-2">
        <div className="flex gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1 dark:border-white/10 dark:bg-white/5">
          {(['7d', '30d', '90d'] as const).map((r) => (
            <button
              key={r}
              onClick={() => setRange(r)}
              className={`rounded-lg px-3 py-1.5 text-[12px] font-semibold transition-all ${
                range === r
                  ? 'bg-white text-slate-900 shadow-sm dark:bg-white/10 dark:text-white'
                  : 'text-slate-500 hover:text-slate-700 dark:text-white/40 dark:hover:text-white/70'
              }`}
            >
              {r}
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

        {range === 'custom' && (
          <div className="flex items-center gap-2">
            <input
              type="date"
              value={customFrom}
              onChange={(e) => setCustomFrom(e.target.value)}
              className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-[12px] text-slate-700 outline-none focus:border-orange-400 dark:border-white/10 dark:bg-white/5 dark:text-white"
            />
            <span className="text-slate-400 dark:text-white/30">?</span>
            <input
              type="date"
              value={customTo}
              onChange={(e) => setCustomTo(e.target.value)}
              className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-[12px] text-slate-700 outline-none focus:border-orange-400 dark:border-white/10 dark:bg-white/5 dark:text-white"
            />
            <Button onClick={applyCustom} className="text-[12px]">Aplicar</Button>
          </div>
        )}
      </div>

      {loadError && (
        <div className="flex items-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">
          <span>??</span><span>{loadError}</span>
          <button onClick={() => { const {from,to} = rangeToDateRange(range !== 'custom' ? range : '30d'); fetchData(from, to) }} className="ml-auto text-[12px] underline">Reintentar</button>
        </div>
      )}

      {/* KPIs */}
      {loading ? (
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
          {Array.from({ length: 4 }).map((_, i) => (
            <div key={i} className="h-24 animate-pulse rounded-2xl border border-slate-200 bg-slate-100 dark:border-white/10 dark:bg-white/5" />
          ))}
        </div>
      ) : data ? (
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
          <KPICard
            label="Ventas totales"
            value={fmtFull(data.total_revenue)}
            delta={revDelta}
            icon="??"
            large
          />
          <KPICard
            label="Pedidos"
            value={String(data.total_orders)}
            delta={ordDelta}
            icon="??"
          />
          <KPICard
            label="Ticket promedio"
            value={fmt(data.avg_ticket)}
            sub="por pedido"
            icon="???"
          />
          <KPICard
            label="IVA generado"
            value={fmt(data.total_tax)}
            sub="Tasa 19%"
            icon="??"
          />
          <KPICard
            label="Clientes unicos"
            value={String(data.unique_customers)}
            icon="??"
          />
        </div>
      ) : null}

      {/* Charts row */}
      <div className="grid gap-4 xl:grid-cols-[1.5fr_1fr]">
        {/* Sales chart */}
        <GlassCard className="space-y-4">
          <div className="flex items-center justify-between">
            <div>
              <h2 className="text-[16px] font-semibold text-slate-900 dark:text-white">Evolucion de ventas</h2>
              <p className="text-[12px] text-slate-500 dark:text-white/40">
                {chartData.length} dias - {range !== 'custom' ? range : `${customFrom} -> ${customTo}`}
              </p>
            </div>
            <div className="flex gap-1 rounded-lg border border-slate-200 bg-slate-50 p-1 dark:border-white/10 dark:bg-white/5">
              {(['revenue', 'orders'] as const).map((c) => (
                <button
                  key={c}
                  onClick={() => setActiveChart(c)}
                  className={`rounded-md px-2.5 py-1 text-[11px] font-semibold transition-all ${
                    activeChart === c
                      ? 'bg-white text-slate-900 shadow-sm dark:bg-white/10 dark:text-white'
                      : 'text-slate-400 hover:text-slate-600 dark:text-white/30 dark:hover:text-white/60'
                  }`}
                >
                  {c === 'revenue' ? 'Ingresos' : 'Pedidos'}
                </button>
              ))}
            </div>
          </div>

          {loading ? (
            <div className="h-32 animate-pulse rounded-xl bg-slate-100 dark:bg-white/5" />
          ) : (
            <MiniBarChart
              data={
                activeChart === 'revenue'
                  ? chartData
                  : chartData.map((d) => ({ ...d, revenue: d.orders * 1000 }))
              }
            />
          )}
        </GlassCard>

        {/* Revenue by category */}
        <GlassCard className="space-y-3">
          <h2 className="text-[16px] font-semibold text-slate-900 dark:text-white">Por categoria</h2>
          {loading ? (
            <div className="space-y-2">
              {Array.from({ length: 4 }).map((_, i) => (
                <div key={i} className="h-8 animate-pulse rounded-lg bg-slate-100 dark:bg-white/5" />
              ))}
            </div>
          ) : data?.revenue_by_category?.length ? (
            <div className="space-y-2.5">
              {data.revenue_by_category.slice(0, 6).map((cat) => (
                <div key={cat.category}>
                  <div className="mb-1 flex items-center justify-between">
                    <span className="text-[12px] font-medium text-slate-700 dark:text-white/70">{cat.category}</span>
                    <span className="text-[12px] font-bold text-slate-900 dark:text-white">{fmt(cat.revenue)}</span>
                  </div>
                  <div className="h-1.5 overflow-hidden rounded-full bg-slate-200 dark:bg-white/10">
                    <div
                      className="h-full rounded-full bg-orange-500 transition-all"
                      style={{ width: `${(cat.revenue / maxRevCategory) * 100}%` }}
                    />
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-[13px] text-slate-400 dark:text-white/30">Sin datos de categorias</p>
          )}
        </GlassCard>
      </div>

      {/* Top products */}
      <GlassCard className="overflow-hidden p-0">
        <div className="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-white/10">
          <h2 className="text-[16px] font-semibold text-slate-900 dark:text-white">Top productos vendidos</h2>
          <span className="text-[12px] text-slate-400 dark:text-white/30">Por ingresos generados</span>
        </div>
        {loading ? (
          <div className="divide-y divide-slate-100 dark:divide-white/5">
            {Array.from({ length: 5 }).map((_, i) => (
              <div key={i} className="h-12 animate-pulse bg-slate-50 dark:bg-white/5" />
            ))}
          </div>
        ) : !data?.top_products?.length ? (
          <div className="flex flex-col items-center justify-center py-12 text-slate-400 dark:text-white/30">
            <span className="mb-2 text-3xl">??</span>
            <p className="text-[13px]">Sin datos de ventas en el periodo</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-slate-100 dark:border-white/5">
                  {['#', 'Producto', 'SKU', 'Unidades', 'Ingresos', '% del total'].map((h) => (
                    <th key={h} className="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-white/30">
                      {h}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {data.top_products.map((p, i) => {
                  const share = data.total_revenue ? (p.revenue / data.total_revenue) * 100 : 0
                  return (
                    <tr key={p.product_id} className="border-b border-slate-100 transition-colors hover:bg-slate-50/80 dark:border-white/5 dark:hover:bg-white/5">
                      <td className="py-3 pl-4 pr-2">
                        <span className={`text-[14px] font-black ${i === 0 ? 'text-orange-500' : i === 1 ? 'text-slate-400' : i === 2 ? 'text-amber-600' : 'text-slate-300 dark:text-white/20'}`}>
                          {i + 1}
                        </span>
                      </td>
                      <td className="px-3 py-3">
                        <p className="text-[13px] font-semibold text-slate-900 dark:text-white">{p.product_name}</p>
                      </td>
                      <td className="px-3 py-3 font-mono text-[12px] text-slate-400 dark:text-white/30">{p.sku}</td>
                      <td className="px-3 py-3 text-[13px] font-semibold text-slate-700 dark:text-white/70">
                        {p.units_sold}
                      </td>
                      <td className="px-3 py-3">
                        <span className="text-[14px] font-bold text-slate-900 dark:text-white">{fmt(p.revenue)}</span>
                      </td>
                      <td className="px-4 py-3">
                        <div className="flex items-center gap-2">
                          <div className="h-1.5 w-20 overflow-hidden rounded-full bg-slate-200 dark:bg-white/10">
                            <div
                              className="h-full rounded-full bg-orange-500"
                              style={{ width: `${share}%` }}
                            />
                          </div>
                          <span className="text-[11px] text-slate-400 dark:text-white/30">{share.toFixed(1)}%</span>
                        </div>
                      </td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          </div>
        )}
      </GlassCard>
    </div>
  )
}


