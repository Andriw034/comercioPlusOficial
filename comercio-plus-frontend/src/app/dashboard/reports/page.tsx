import { useCallback, useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import API from '@/lib/api'
import { ErpBadge, ErpBtn, ErpKpiCard, ErpPageHeader } from '@/components/erp'

type TabKey = 'alertas' | 'tendencias' | 'top' | 'sugerencias'
type AlertTone = 'danger' | 'warning' | 'info' | 'success'

interface ApiTopProductItem {
  name?: string | null
  category?: string | null
  units_sold?: number | string | null
  revenue?: number | string | null
}

interface ApiSalesMonthItem {
  label?: string | null
  month?: string | null
  value?: number | string | null
  total?: number | string | null
}

interface ApiAiAlerts {
  high_rotation?: string | null
  out_of_stock?: string | null
  low_movement?: string | null
  top_growth?: string | null
}

interface ApiReportSummaryData {
  gross_sales?: number | string | null
  orders_count?: number | string | null
  top_products?: ApiTopProductItem[] | null
  sales_by_month?: ApiSalesMonthItem[] | null
  ai_alerts?: ApiAiAlerts | null
}

interface ApiReportSummaryResponse {
  data?: ApiReportSummaryData | null
}

interface TopProduct {
  name: string
  category: string
  units: number
  revenue: number
}

interface SalesMonth {
  label: string
  value: number
}

interface ReportSummary {
  grossSales: number
  ordersCount: number
  topProducts: TopProduct[]
  salesByMonth: SalesMonth[]
  aiAlerts: {
    highRotation: string
    outOfStock: string
    lowMovement: string
    topGrowth: string
  }
}

interface AlertCard {
  id: string
  icon: string
  title: string
  message: string
  tone: AlertTone
}

const FALLBACK_MONTHS: SalesMonth[] = [
  { label: 'Sep', value: 1200000 },
  { label: 'Oct', value: 1450000 },
  { label: 'Nov', value: 1320000 },
  { label: 'Dic', value: 1680000 },
  { label: 'Ene', value: 1590000 },
  { label: 'Feb', value: 1760000 },
]

const FIXED_SUGGESTIONS = [
  'Mueve los repuestos de mayor salida a una zona de picking rapido para despachar en menos tiempo.',
  'Revisa semanalmente las referencias de motos mas vendidas y sube el stock minimo antes de fin de semana.',
  'Arma combos de mantenimiento (aceite + filtro + bujia) para aumentar ticket promedio en mostrador y web.',
]

function toNumber(value: unknown, fallback = 0): number {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : fallback
}

function fmtCurrency(value: number): string {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(value)
}

function normalizeSummary(payload: ApiReportSummaryData | null | undefined): ReportSummary {
  const rawTopProducts = Array.isArray(payload?.top_products) ? payload.top_products : []
  const rawSalesByMonth = Array.isArray(payload?.sales_by_month) ? payload.sales_by_month : []
  const rawAi = payload?.ai_alerts

  return {
    grossSales: toNumber(payload?.gross_sales),
    ordersCount: toNumber(payload?.orders_count),
    topProducts: rawTopProducts.map((item) => ({
      name: String(item?.name || 'Producto sin nombre'),
      category: String(item?.category || 'Sin categoria'),
      units: toNumber(item?.units_sold),
      revenue: toNumber(item?.revenue),
    })),
    salesByMonth: rawSalesByMonth.map((item, index) => ({
      label: String(item?.label || item?.month || `Mes ${index + 1}`),
      value: toNumber(item?.value ?? item?.total),
    })),
    aiAlerts: {
      highRotation:
        String(rawAi?.high_rotation || '').trim() ||
        'Hay referencias de alta rotacion. Reponer a tiempo evita perder ventas.',
      outOfStock:
        String(rawAi?.out_of_stock || '').trim() ||
        'Algunas referencias clave estan sin stock. Prioriza esas compras primero.',
      lowMovement:
        String(rawAi?.low_movement || '').trim() ||
        'Tienes productos con poca salida. Puedes moverlos con promo o combo.',
      topGrowth:
        String(rawAi?.top_growth || '').trim() ||
        'Una categoria viene creciendo. Aprovecha para ampliar surtido y margen.',
    },
  }
}

function alertStyles(tone: AlertTone): { border: string; bg: string; badge: 'critical' | 'low' | 'new' | 'high' } {
  if (tone === 'danger') return { border: 'border-l-red-500', bg: 'bg-red-50 dark:bg-red-500/10', badge: 'critical' }
  if (tone === 'warning') return { border: 'border-l-amber-500', bg: 'bg-amber-50 dark:bg-amber-500/10', badge: 'low' }
  if (tone === 'info') return { border: 'border-l-blue-500', bg: 'bg-blue-50 dark:bg-blue-500/10', badge: 'new' }
  return { border: 'border-l-emerald-500', bg: 'bg-emerald-50 dark:bg-emerald-500/10', badge: 'high' }
}

export default function DashboardReportsPage() {
  const [summary, setSummary] = useState<ReportSummary | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [activeTab, setActiveTab] = useState<TabKey>('alertas')
  const [exporting, setExporting] = useState(false)

  const loadSummary = useCallback(async () => {
    setLoading(true)
    setError('')
    try {
      const response = await API.get<ApiReportSummaryResponse>('/reports/summary')
      setSummary(normalizeSummary(response.data?.data))
    } catch (err: any) {
      setError(err?.response?.data?.message || 'No se pudieron cargar los reportes en este momento.')
      setSummary(normalizeSummary(null))
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    void loadSummary()
  }, [loadSummary])

  const todayLabel = useMemo(
    () =>
      new Intl.DateTimeFormat('es-CO', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
      }).format(new Date()),
    [],
  )

  const safeSummary = summary ?? normalizeSummary(null)
  const topProducts = safeSummary.topProducts
  const trendData = safeSummary.salesByMonth.length > 0 ? safeSummary.salesByMonth : FALLBACK_MONTHS
  const maxTrendValue = Math.max(...trendData.map((item) => item.value), 1)
  const starProduct = topProducts[0]
  const hasData = safeSummary.grossSales > 0 || safeSummary.ordersCount > 0 || topProducts.length > 0

  const alertCards: AlertCard[] = [
    {
      id: 'high-rotation',
      icon: '🔥',
      title: 'Alta rotacion',
      message: safeSummary.aiAlerts.highRotation,
      tone: 'danger',
    },
    {
      id: 'out-of-stock',
      icon: '⚠️',
      title: 'Sin stock',
      message: safeSummary.aiAlerts.outOfStock,
      tone: 'warning',
    },
    {
      id: 'low-movement',
      icon: '❄️',
      title: 'Sin movimiento',
      message: safeSummary.aiAlerts.lowMovement,
      tone: 'info',
    },
    {
      id: 'top-growth',
      icon: '📈',
      title: 'Mayor crecimiento',
      message: safeSummary.aiAlerts.topGrowth,
      tone: 'success',
    },
  ]

  const handleExportCsv = useCallback(() => {
    if (!summary) return
    setExporting(true)
    try {
      const rows = [
        ['Producto', 'Categoria', 'Unidades', 'Ingresos'],
        ...summary.topProducts.map((item) => [item.name, item.category, String(item.units), String(item.revenue)]),
      ]
      const csv = rows.map((row) => row.map((col) => `"${col.replace(/"/g, '""')}"`).join(',')).join('\n')
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
      const url = URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `reportes-ia-${new Date().toISOString().slice(0, 10)}.csv`
      document.body.appendChild(link)
      link.click()
      link.remove()
      URL.revokeObjectURL(url)
    } finally {
      setExporting(false)
    }
  }, [summary])

  return (
    <div className="space-y-4">
      <ErpPageHeader
        breadcrumb="Dashboard / Reportes"
        title="Reportes e inteligencia comercial"
        subtitle={`Resumen del negocio · ${todayLabel}`}
        actions={
          <ErpBtn variant="primary" size="md" onClick={handleExportCsv} disabled={exporting || loading}>
            {exporting ? 'Exportando...' : 'Exportar CSV'}
          </ErpBtn>
        }
      />

      {error ? (
        <div className="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
          {error}
        </div>
      ) : null}

      {loading ? (
        <div className="grid gap-3 sm:grid-cols-3">
          {Array.from({ length: 3 }).map((_, index) => (
            <div key={index} className="h-24 animate-pulse rounded-2xl border border-slate-200 bg-slate-100 dark:border-white/10 dark:bg-white/5" />
          ))}
        </div>
      ) : (
        <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
          <ErpKpiCard
            label="Ventas del mes"
            value={fmtCurrency(safeSummary.grossSales)}
            hint="Facturacion bruta del periodo"
            icon="dollar"
            iconBg="rgba(16,185,129,0.14)"
            iconColor="#10B981"
          />
          <ErpKpiCard
            label="Total pedidos"
            value={safeSummary.ordersCount}
            hint="Pedidos registrados en el mes"
            icon="file-text"
            iconBg="rgba(59,130,246,0.14)"
            iconColor="#3B82F6"
          />
          <ErpKpiCard
            label="Producto estrella"
            value={starProduct ? starProduct.name : 'Sin datos'}
            hint={starProduct ? `${starProduct.units} unidades` : 'Aun no hay ranking disponible'}
            icon="star"
            iconBg="rgba(245,158,11,0.14)"
            iconColor="#F59E0B"
          />
        </div>
      )}

      {!loading && !hasData ? (
        <div className="rounded-2xl border border-slate-200 bg-white px-6 py-10 text-center shadow-[0_2px_8px_rgba(0,0,0,0.04)] dark:border-white/10 dark:bg-white/5">
          <p className="text-[20px] font-black text-slate-900 dark:text-white">Todavia no hay informacion suficiente</p>
          <p className="mt-1 text-[13px] text-slate-500 dark:text-white/50">
            Cuando tengas ventas y pedidos, aqui veras alertas y recomendaciones utiles para tu tienda.
          </p>
          <Link to="/dashboard/products" className="mt-4 inline-flex">
            <ErpBtn variant="primary" size="md">Ir a productos</ErpBtn>
          </Link>
        </div>
      ) : null}

      {!loading && hasData ? (
        <>
          <div className="rounded-2xl border border-slate-200 bg-slate-100/80 p-1.5 dark:border-white/10 dark:bg-white/5">
            <div className="grid gap-1 sm:grid-cols-4">
              {[
                { key: 'alertas', label: '⚠️ Alertas' },
                { key: 'tendencias', label: '📊 Tendencias' },
                { key: 'top', label: '🏆 Top productos' },
                { key: 'sugerencias', label: '💡 Sugerencias' },
              ].map((tab) => (
                <button
                  key={tab.key}
                  type="button"
                  onClick={() => setActiveTab(tab.key as TabKey)}
                  className={`rounded-xl px-3 py-2 text-[12px] font-semibold transition ${
                    activeTab === tab.key
                      ? 'border border-orange-200 bg-white text-slate-900 shadow-[0_1px_4px_rgba(0,0,0,0.08)] dark:border-orange-500/30 dark:bg-slate-900 dark:text-white'
                      : 'text-slate-500 hover:bg-white/80 hover:text-slate-700 dark:text-white/60 dark:hover:bg-white/10 dark:hover:text-white'
                  }`}
                >
                  {tab.label}
                </button>
              ))}
            </div>
          </div>

          {activeTab === 'alertas' ? (
            <div className="space-y-3">
              {alertCards.map((card) => {
                const styles = alertStyles(card.tone)
                return (
                  <div key={card.id} className={`rounded-2xl border-l-4 p-4 ${styles.border} ${styles.bg}`}>
                    <div className="flex items-start gap-3">
                      <div className="mt-0.5 text-[20px]">{card.icon}</div>
                      <div className="min-w-0 flex-1">
                        <div className="mb-1 flex items-center gap-2">
                          <p className="text-[14px] font-semibold text-slate-900 dark:text-white">{card.title}</p>
                          <ErpBadge status={styles.badge} />
                        </div>
                        <p className="text-[12px] leading-relaxed text-slate-600 dark:text-white/60">{card.message}</p>
                      </div>
                    </div>
                  </div>
                )
              })}
            </div>
          ) : null}

          {activeTab === 'tendencias' ? (
            <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_2px_8px_rgba(0,0,0,0.04)] dark:border-white/10 dark:bg-white/5">
              <p className="mb-3 text-[14px] font-semibold text-slate-900 dark:text-white">Ventas por mes</p>
              <div className="space-y-3">
                {trendData.map((item) => (
                  <div key={item.label}>
                    <div className="mb-1 flex items-center justify-between">
                      <span className="text-[12px] font-semibold text-slate-700 dark:text-white/70">{item.label}</span>
                      <span className="text-[12px] font-black text-slate-900 dark:text-white">{fmtCurrency(item.value)}</span>
                    </div>
                    <div className="h-2 rounded-full bg-slate-200 dark:bg-white/10">
                      <div className="h-2 rounded-full bg-orange-500" style={{ width: `${Math.max(4, (item.value / maxTrendValue) * 100)}%` }} />
                    </div>
                  </div>
                ))}
              </div>
            </div>
          ) : null}

          {activeTab === 'top' ? (
            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_2px_8px_rgba(0,0,0,0.04)] dark:border-white/10 dark:bg-white/5">
              <div className="overflow-x-auto">
                <table className="w-full min-w-[760px]">
                  <thead>
                    <tr className="bg-slate-50 dark:bg-white/5">
                      {['#', 'Producto', 'Categoría', 'Unidades', 'Ingresos'].map((header) => (
                        <th
                          key={header}
                          className="px-3 py-2 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-500 dark:text-white/40"
                        >
                          {header}
                        </th>
                      ))}
                    </tr>
                  </thead>
                  <tbody>
                    {topProducts.length === 0 ? (
                      <tr>
                        <td colSpan={5} className="px-4 py-8 text-center text-[12px] text-slate-500 dark:text-white/50">
                          Todavia no hay ranking de productos.
                        </td>
                      </tr>
                    ) : (
                      topProducts.map((product, index) => (
                        <tr key={`${product.name}-${index}`} className="border-b border-slate-100 dark:border-white/10">
                          <td className="px-3 py-2.5 text-[12px] font-bold text-slate-700 dark:text-white/70">{index + 1}</td>
                          <td className="px-3 py-2.5 text-[12px] font-semibold text-slate-900 dark:text-white">{product.name}</td>
                          <td className="px-3 py-2.5 text-[12px] text-slate-600 dark:text-white/60">{product.category}</td>
                          <td className="px-3 py-2.5 text-[12px] font-bold text-slate-900 dark:text-white">{product.units}</td>
                          <td className="px-3 py-2.5 text-[12px] font-black text-slate-900 dark:text-white">{fmtCurrency(product.revenue)}</td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          ) : null}

          {activeTab === 'sugerencias' ? (
            <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_2px_8px_rgba(0,0,0,0.04)] dark:border-white/10 dark:bg-white/5">
              <p className="mb-3 text-[14px] font-semibold text-slate-900 dark:text-white">Acciones sugeridas para tu tienda</p>
              <div className="space-y-2">
                {FIXED_SUGGESTIONS.map((item, index) => (
                  <div
                    key={index}
                    className="flex items-start justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 dark:border-white/10 dark:bg-white/5"
                  >
                    <p className="text-[12px] leading-relaxed text-slate-700 dark:text-white/70">{item}</p>
                    <ErpBtn variant="secondary" size="sm">Aplicar</ErpBtn>
                  </div>
                ))}
              </div>
            </div>
          ) : null}
        </>
      ) : null}
    </div>
  )
}
