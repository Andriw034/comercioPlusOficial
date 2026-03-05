import { useCallback, useEffect, useMemo, useState } from 'react'
import API from '@/lib/api'
import { Icon } from '@/components/Icon'
import { ErpBtn, ErpKpiCard, ErpPageHeader } from '@/components/erp'

type ReportStatus = 'all' | 'pending' | 'processing' | 'paid' | 'approved' | 'completed' | 'cancelled'

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

const STATUS_OPTIONS: Array<{ value: ReportStatus; label: string }> = [
  { value: 'all', label: 'Todos' },
  { value: 'pending', label: 'Pendiente' },
  { value: 'processing', label: 'Procesando' },
  { value: 'paid', label: 'Pagado' },
  { value: 'approved', label: 'Aprobado' },
  { value: 'completed', label: 'Completado' },
  { value: 'cancelled', label: 'Cancelado' },
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

  useEffect(() => {
    void loadReports()
  }, [loadReports])

  const applyFilters = (): void => {
    setAppliedFilters(draftFilters)
  }

  const resetFilters = (): void => {
    const next = { from: defaultFrom, to: defaultTo, status: 'all' as const }
    setDraftFilters(next)
    setAppliedFilters(next)
  }

  const salesPreview = salesRows.slice(0, 10)
  const taxPreview = taxData?.breakdown.slice(0, 10) ?? []
  const inventoryTopOut = inventory?.top_out_products.slice(0, 10) ?? []
  const rotationLabel =
    inventory?.rotation_approx === null || inventory?.rotation_approx === undefined
      ? '-'
      : inventory.rotation_approx.toFixed(2)

  return (
    <div className="space-y-4">
      <ErpPageHeader
        breadcrumb="Dashboard / Reportes"
        title="Reportes comerciales"
        subtitle="Datos reales de ventas, impuestos, productos e inventario."
        actions={
          <>
            <ErpBtn variant="secondary" size="md" icon={<Icon name="refresh" size={14} />} onClick={() => void loadReports()}>
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

      <div className="rounded-2xl border border-slate-200 bg-white p-4">
        <div className="grid gap-3 md:grid-cols-4">
          <label className="text-[12px] font-semibold text-slate-700">
            Desde
            <input
              type="date"
              value={draftFilters.from}
              onChange={(event) => setDraftFilters((prev) => ({ ...prev, from: event.target.value }))}
              className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[13px]"
            />
          </label>
          <label className="text-[12px] font-semibold text-slate-700">
            Hasta
            <input
              type="date"
              value={draftFilters.to}
              onChange={(event) => setDraftFilters((prev) => ({ ...prev, to: event.target.value }))}
              className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[13px]"
            />
          </label>
          <label className="text-[12px] font-semibold text-slate-700">
            Estado pedido
            <select
              value={draftFilters.status}
              onChange={(event) => setDraftFilters((prev) => ({ ...prev, status: event.target.value as ReportStatus }))}
              className="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[13px]"
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
              Aplicar filtros
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
      ) : null}

      {!loading ? (
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

          <div className="grid gap-4 lg:grid-cols-2">
            <section className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
              <div className="border-b border-slate-100 px-4 py-3">
                <h2 className="text-[16px] font-black text-slate-900">Serie de ventas</h2>
                <p className="text-[12px] text-slate-500">Periodo, ventas e impuestos.</p>
              </div>
              {salesPreview.length === 0 ? (
                <p className="px-4 py-5 text-[13px] text-slate-500">No hay datos para el rango seleccionado.</p>
              ) : (
                <div className="overflow-x-auto">
                  <table className="w-full min-w-[520px]">
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

            <section className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
              <div className="border-b border-slate-100 px-4 py-3">
                <h2 className="text-[16px] font-black text-slate-900">Top productos</h2>
                <p className="text-[12px] text-slate-500">Ranking por ingreso.</p>
              </div>
              {topProducts.length === 0 ? (
                <p className="px-4 py-5 text-[13px] text-slate-500">No hay productos vendidos en el rango seleccionado.</p>
              ) : (
                <div className="overflow-x-auto">
                  <table className="w-full min-w-[520px]">
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
          </div>

          <div className="grid gap-4 lg:grid-cols-2">
            <section className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
              <div className="border-b border-slate-100 px-4 py-3">
                <h2 className="text-[16px] font-black text-slate-900">Resumen IVA</h2>
                <p className="text-[12px] text-slate-500">Base gravable, impuesto y total.</p>
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
                  <p className="mb-2 text-[11px] font-semibold uppercase tracking-[0.11em] text-slate-500">Desglose</p>
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

            <section className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
              <div className="border-b border-slate-100 px-4 py-3">
                <h2 className="text-[16px] font-black text-slate-900">Inventario</h2>
                <p className="text-[12px] text-slate-500">Entradas, salidas y rotacion.</p>
              </div>
              <div className="grid gap-3 p-4 sm:grid-cols-2">
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
          </div>
        </>
      ) : null}
    </div>
  )
}
