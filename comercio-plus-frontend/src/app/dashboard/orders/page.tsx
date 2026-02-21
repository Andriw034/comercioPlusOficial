import { useEffect, useState } from 'react'
import API from '@/lib/api'
import GlassCard from '@/components/ui/GlassCard'
import Badge from '@/components/ui/Badge'
import Button from '@/components/ui/button'

// -- Types ---------------------------------------------------------------------

type OrderStatus = 'pending' | 'paid' | 'shipped' | 'delivered' | 'cancelled'

interface OrderItem {
  product_id: number
  product_name: string
  sku: string
  quantity: number
  unit_price: number
  subtotal: number
}

interface Order {
  id: number
  reference: string
  customer_name: string
  customer_email: string
  customer_phone: string
  status: OrderStatus
  payment_method: string
  subtotal: number
  tax: number
  shipping: number
  total: number
  items: OrderItem[]
  address: string
  created_at: string
  updated_at: string
  notes: string | null
}

// -- Config --------------------------------------------------------------------

const STATUS_CONFIG: Record<
  OrderStatus,
  { label: string; variant: 'warning' | 'neutral' | 'brand' | 'success' | 'danger'; icon: string; next?: OrderStatus }
> = {
  pending: { label: 'Pendiente', variant: 'warning', icon: '🕒', next: 'paid' },
  paid: { label: 'Pagado', variant: 'brand', icon: '💳', next: 'shipped' },
  shipped: { label: 'Enviado', variant: 'neutral', icon: '🚚', next: 'delivered' },
  delivered: { label: 'Entregado', variant: 'success', icon: '✅' },
  cancelled: { label: 'Cancelado', variant: 'danger', icon: '❌' },
}

const PIPELINE: OrderStatus[] = ['pending', 'paid', 'shipped', 'delivered']

// -- Helpers -------------------------------------------------------------------

function fmt(n: number) {
  return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(n)
}

function fmtDate(iso: string) {
  return new Date(iso).toLocaleString('es-CO', {
    day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
  })
}

function relative(iso: string) {
  const diff = (Date.now() - new Date(iso).getTime()) / 1000
  if (diff < 60) return 'ahora'
  if (diff < 3600) return `hace ${Math.floor(diff / 60)} min`
  if (diff < 86400) return `hace ${Math.floor(diff / 3600)} h`
  return `hace ${Math.floor(diff / 86400)} d`
}

// -- OrderDetail ---------------------------------------------------------------

function OrderDetail({
  order,
  onClose,
  onStatusUpdate,
}: {
  order: Order
  onClose: () => void
  onStatusUpdate: (id: number, status: OrderStatus) => Promise<void>
}) {
  const [updating, setUpdating] = useState(false)
  const cfg = STATUS_CONFIG[order.status]

  const advanceStatus = async () => {
    if (!cfg.next) return
    setUpdating(true)
    try {
      await onStatusUpdate(order.id, cfg.next)
    } finally {
      setUpdating(false)
    }
  }

  const cancelOrder = async () => {
    if (!window.confirm('Cancelar este pedido?')) return
    setUpdating(true)
    try {
      await onStatusUpdate(order.id, 'cancelled')
    } finally {
      setUpdating(false)
    }
  }

  return (
    <div className="fixed inset-0 z-50 flex justify-end">
      <div className="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" onClick={onClose} />
      <div className="relative z-10 flex h-full w-full max-w-lg flex-col overflow-y-auto bg-white shadow-2xl dark:bg-slate-900">
        {/* Header */}
        <div className="sticky top-0 z-10 flex items-center justify-between border-b border-slate-100 bg-white/95 px-6 py-4 backdrop-blur dark:border-white/10 dark:bg-slate-900/95">
          <div>
            <div className="flex items-center gap-2">
              <h2 className="text-[16px] font-semibold text-slate-900 dark:text-white">
                Pedido {order.reference}
              </h2>
              <Badge variant={cfg.variant}>{cfg.icon} {cfg.label}</Badge>
            </div>
            <p className="text-[12px] text-slate-400 dark:text-white/30">{fmtDate(order.created_at)}</p>
          </div>
          <button onClick={onClose} className="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10">✕</button>
        </div>

        <div className="flex-1 space-y-5 p-6">
          {/* Pipeline progress */}
          <div className="flex items-center gap-1">
            {PIPELINE.map((s, i) => {
              const idx = PIPELINE.indexOf(order.status)
              const done = i < idx
              const current = i === idx
              return (
                <div key={s} className="flex flex-1 items-center gap-1">
                  <div className={`flex-1 h-1.5 rounded-full transition-colors ${done || current ? 'bg-orange-500' : 'bg-slate-200 dark:bg-white/10'}`} />
                  <div className={`flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full text-[10px] font-bold transition-all ${
                    done ? 'bg-orange-500 text-white' : current ? 'bg-orange-500 text-white ring-2 ring-orange-300 dark:ring-orange-500/40' : 'bg-slate-200 text-slate-400 dark:bg-white/10 dark:text-white/30'
                  }`}>
                    {done ? '✓' : i + 1}
                  </div>
                  {i < PIPELINE.length - 1 && (
                    <div className={`flex-1 h-1.5 rounded-full ${done ? 'bg-orange-500' : 'bg-slate-200 dark:bg-white/10'}`} />
                  )}
                </div>
              )
            })}
          </div>
          <div className="flex justify-between">
            {PIPELINE.map((s) => (
              <span key={s} className="text-[10px] text-slate-400 dark:text-white/30">{STATUS_CONFIG[s].label}</span>
            ))}
          </div>

          {/* Customer */}
          <div className="rounded-2xl border border-slate-100 p-4 dark:border-white/10">
            <p className="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-white/30">Cliente</p>
            <p className="text-[14px] font-semibold text-slate-900 dark:text-white">{order.customer_name}</p>
            <p className="text-[13px] text-slate-500 dark:text-white/50">{order.customer_email}</p>
            {order.customer_phone && <p className="text-[13px] text-slate-500 dark:text-white/50">{order.customer_phone}</p>}
            {order.address && (
              <p className="mt-1 text-[12px] text-slate-400 dark:text-white/30">📍 {order.address}</p>
            )}
          </div>

          {/* Items */}
          <div className="rounded-2xl border border-slate-100 dark:border-white/10">
            <p className="border-b border-slate-100 px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:border-white/10 dark:text-white/30">
              Productos ({order.items?.length ?? 0})
            </p>
            {(order.items ?? []).map((item) => (
              <div key={item.product_id} className="flex items-center justify-between border-b border-slate-50 px-4 py-3 last:border-0 dark:border-white/5">
                <div className="flex-1 min-w-0">
                  <p className="text-[13px] font-medium text-slate-800 dark:text-white truncate">{item.product_name}</p>
                  <p className="text-[11px] text-slate-400 dark:text-white/30">{item.sku} x {item.quantity}</p>
                </div>
                <p className="text-[13px] font-semibold text-slate-900 dark:text-white">{fmt(item.subtotal)}</p>
              </div>
            ))}
            {/* Totals */}
            <div className="space-y-1 border-t border-slate-100 px-4 py-3 dark:border-white/10">
              {[
                { label: 'Subtotal', value: order.subtotal },
                { label: 'IVA', value: order.tax },
                { label: 'Envio', value: order.shipping },
              ].map((row) => (
                <div key={row.label} className="flex justify-between text-[12px] text-slate-500 dark:text-white/50">
                  <span>{row.label}</span>
                  <span>{fmt(row.value ?? 0)}</span>
                </div>
              ))}
              <div className="flex justify-between border-t border-slate-100 pt-2 text-[15px] font-bold text-slate-900 dark:border-white/10 dark:text-white">
                <span>Total</span>
                <span>{fmt(order.total)}</span>
              </div>
            </div>
          </div>

          {/* Payment */}
          <div className="flex items-center justify-between rounded-2xl border border-slate-100 px-4 py-3 dark:border-white/10">
            <div>
              <p className="text-[11px] text-slate-400 dark:text-white/30">Metodo de pago</p>
              <p className="text-[13px] font-semibold text-slate-800 dark:text-white">{order.payment_method}</p>
            </div>
            <span className="text-2xl">💰</span>
          </div>

          {/* Notes */}
          {order.notes && (
            <div className="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 dark:border-amber-500/20 dark:bg-amber-500/10">
              <p className="text-[11px] font-semibold text-amber-600 dark:text-amber-400">Nota del pedido</p>
              <p className="text-[13px] text-amber-800 dark:text-amber-300">{order.notes}</p>
            </div>
          )}
        </div>

        {/* Actions */}
        {order.status !== 'delivered' && order.status !== 'cancelled' && (
          <div className="sticky bottom-0 flex gap-2 border-t border-slate-100 bg-white/95 p-4 backdrop-blur dark:border-white/10 dark:bg-slate-900/95">
            <button
              onClick={cancelOrder}
              disabled={updating}
              className="rounded-xl border border-red-200 bg-white px-4 py-2.5 text-[13px] font-semibold text-red-500 transition-colors hover:bg-red-50 disabled:opacity-50 dark:border-red-500/20 dark:bg-transparent dark:hover:bg-red-500/10"
            >
              Cancelar pedido
            </button>
            {cfg.next && (
              <Button className="flex-1" loading={updating} onClick={advanceStatus}>
                {updating ? 'Actualizando...' : `Marcar como ${STATUS_CONFIG[cfg.next].label}`}
              </Button>
            )}
          </div>
        )}
      </div>
    </div>
  )
}

// -- Page ----------------------------------------------------------------------

export default function DashboardOrdersPage() {
  const [orders, setOrders] = useState<Order[]>([])
  const [loading, setLoading] = useState(true)
  const [loadError, setLoadError] = useState('')
  const [statusFilter, setStatusFilter] = useState<OrderStatus | 'all'>('all')
  const [search, setSearch] = useState('')
  const [selected, setSelected] = useState<Order | null>(null)
  const [page, setPage] = useState(1)
  const PER_PAGE = 20

  const load = async () => {
    setLoading(true)
    setLoadError('')
    try {
      const params = statusFilter !== 'all' ? `?status=${statusFilter}` : ''
      const { data } = await API.get(`/orders${params}`)
      setOrders(Array.isArray(data) ? data : data?.data ?? [])
    } catch (err: any) {
      setLoadError(err?.response?.data?.message || 'No se pudieron cargar los pedidos.')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => { load() }, [statusFilter])

  const updateStatus = async (id: number, status: OrderStatus) => {
    await API.put(`/orders/${id}/status`, { status })
    setOrders((prev) => prev.map((o) => (o.id === id ? { ...o, status } : o)))
    setSelected((prev) => (prev?.id === id ? { ...prev, status } : prev))
  }

  // -- Derived -----------------------------------------------------------------

  const counts = orders.reduce<Record<string, number>>((acc, o) => {
    acc[o.status] = (acc[o.status] || 0) + 1
    return acc
  }, {})

  const filtered = orders.filter((o) => {
    const q = search.toLowerCase()
    return (
      !q ||
      o.reference.toLowerCase().includes(q) ||
      o.customer_name.toLowerCase().includes(q) ||
      o.customer_email.toLowerCase().includes(q)
    )
  })

  const paginated = filtered.slice((page - 1) * PER_PAGE, page * PER_PAGE)
  const totalPages = Math.ceil(filtered.length / PER_PAGE)

  const todayTotal = orders
    .filter((o) => {
      const today = new Date()
      const d = new Date(o.created_at)
      return d.getDate() === today.getDate() && d.getMonth() === today.getMonth()
    })
    .reduce((sum, o) => sum + (o.total ?? 0), 0)

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <p className="text-[13px] text-slate-500 dark:text-white/50">Dashboard</p>
          <h1 className="font-display text-[32px] font-bold text-slate-950 dark:text-white">Pedidos</h1>
        </div>
        {todayTotal > 0 && (
          <div className="rounded-2xl border border-green-200 bg-green-50 px-4 py-2 dark:border-green-500/20 dark:bg-green-500/10">
            <p className="text-[11px] text-green-600 dark:text-green-400">Ventas hoy</p>
            <p className="text-[18px] font-black text-green-700 dark:text-green-300">{fmt(todayTotal)}</p>
          </div>
        )}
      </div>

      {/* Pipeline summary */}
      <div className="flex gap-2 overflow-x-auto pb-1">
        {PIPELINE.map((s, i) => {
          const cfg = STATUS_CONFIG[s]
          const count = counts[s] || 0
          return (
            <div key={s} className="flex items-center gap-2 flex-shrink-0">
              <button
                onClick={() => setStatusFilter(s)}
                className={`min-w-[90px] rounded-2xl border px-4 py-3 text-center transition-all ${
                  statusFilter === s
                    ? 'border-orange-400 bg-orange-50 dark:border-orange-500/50 dark:bg-orange-500/10'
                    : 'border-slate-200 bg-slate-50 hover:border-slate-300 dark:border-white/10 dark:bg-white/5'
                }`}
              >
                <p className="text-[10px] uppercase tracking-wider text-slate-400 dark:text-white/30">{cfg.label}</p>
                <p className={`mt-1 text-2xl font-black ${count > 0 ? 'text-slate-900 dark:text-white' : 'text-slate-300 dark:text-white/20'}`}>{count}</p>
              </button>
              {i < PIPELINE.length - 1 && (
                <span className="text-slate-300 dark:text-white/20">→</span>
              )}
            </div>
          )
        })}
        <button
          onClick={() => setStatusFilter('cancelled')}
          className={`min-w-[90px] flex-shrink-0 rounded-2xl border px-4 py-3 text-center transition-all ${
            statusFilter === 'cancelled'
              ? 'border-red-400 bg-red-50 dark:border-red-500/50 dark:bg-red-500/10'
              : 'border-slate-200 bg-slate-50 hover:border-slate-300 dark:border-white/10 dark:bg-white/5'
          }`}
        >
          <p className="text-[10px] uppercase tracking-wider text-slate-400 dark:text-white/30">Cancelados</p>
          <p className={`mt-1 text-2xl font-black ${counts['cancelled'] > 0 ? 'text-red-500' : 'text-slate-300 dark:text-white/20'}`}>
            {counts['cancelled'] || 0}
          </p>
        </button>
      </div>

      {loadError && (
        <div className="flex items-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">
          <span>⚠️</span><span>{loadError}</span>
          <button onClick={load} className="ml-auto text-[12px] underline">Reintentar</button>
        </div>
      )}

      {/* Table card */}
      <GlassCard className="overflow-hidden p-0">
        {/* Toolbar */}
        <div className="flex flex-wrap items-center gap-3 border-b border-slate-200 px-5 py-4 dark:border-white/10">
          <div className="relative flex-1 min-w-[200px]">
            <span className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">🔎</span>
            <input
              type="search"
              value={search}
              onChange={(e) => { setSearch(e.target.value); setPage(1) }}
              placeholder="Buscar por referencia, cliente o email..."
              className="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-[13px] text-slate-900 outline-none transition-colors placeholder:text-slate-400 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-white/30"
            />
          </div>
          <div className="flex gap-1">
            {(['all', ...PIPELINE, 'cancelled'] as const).map((f) => (
              <button
                key={f}
                onClick={() => setStatusFilter(f)}
                className={`rounded-lg px-3 py-1.5 text-[11px] font-semibold transition-colors ${
                  statusFilter === f
                    ? 'bg-orange-500 text-white'
                    : 'border border-slate-200 bg-white text-slate-500 hover:text-slate-800 dark:border-white/10 dark:bg-white/5 dark:text-white/50 dark:hover:text-white'
                }`}
              >
                {f === 'all' ? 'Todos' : STATUS_CONFIG[f as OrderStatus]?.label}
              </button>
            ))}
          </div>
        </div>

        {/* Content */}
        {loading ? (
          <div className="flex flex-col items-center justify-center py-16">
            <div className="mb-3 h-8 w-8 animate-spin rounded-full border-2 border-orange-500 border-t-transparent" />
            <p className="text-[13px] text-slate-400">Cargando pedidos...</p>
          </div>
        ) : paginated.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-16 text-slate-400 dark:text-white/30">
            <span className="mb-2 text-3xl">📭</span>
            <p className="text-[13px]">Sin pedidos para mostrar</p>
            {search && <p className="text-[12px] opacity-60">Prueba con otro termino de busqueda</p>}
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-slate-100 dark:border-white/5">
                  {['Referencia', 'Cliente', 'Productos', 'Total', 'Pago', 'Estado', 'Fecha', ''].map((h) => (
                    <th key={h} className="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-white/30">
                      {h}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {paginated.map((order) => {
                  const cfg = STATUS_CONFIG[order.status]
                  return (
                    <tr
                      key={order.id}
                      className="group cursor-pointer border-b border-slate-100 transition-colors hover:bg-slate-50/80 dark:border-white/5 dark:hover:bg-white/5"
                      onClick={() => setSelected(order)}
                    >
                      <td className="py-3 pl-4 pr-2">
                        <span className="font-mono text-[13px] font-bold text-orange-600 dark:text-orange-400">
                          {order.reference}
                        </span>
                      </td>
                      <td className="px-3 py-3">
                        <p className="text-[13px] font-semibold text-slate-900 dark:text-white">{order.customer_name}</p>
                        <p className="text-[11px] text-slate-400 dark:text-white/30">{order.customer_email}</p>
                      </td>
                      <td className="px-3 py-3 text-[13px] text-slate-500 dark:text-white/50">
                        {(order.items?.length ?? 0)} item{(order.items?.length ?? 0) !== 1 ? 's' : ''}
                      </td>
                      <td className="px-3 py-3">
                        <span className="text-[14px] font-bold text-slate-900 dark:text-white">{fmt(order.total)}</span>
                      </td>
                      <td className="px-3 py-3 text-[12px] text-slate-500 dark:text-white/50">{order.payment_method}</td>
                      <td className="px-3 py-3">
                        <Badge variant={cfg.variant}>{cfg.icon} {cfg.label}</Badge>
                      </td>
                      <td className="px-3 py-3 text-[11px] text-slate-400 dark:text-white/30">
                        {relative(order.created_at)}
                      </td>
                      <td className="pl-3 pr-4 py-3 text-slate-300 dark:text-white/20 group-hover:text-slate-500 dark:group-hover:text-white/50">
                        -
                      </td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          </div>
        )}

        {/* Pagination */}
        {totalPages > 1 && (
          <div className="flex items-center justify-between border-t border-slate-100 px-5 py-3 dark:border-white/5">
            <p className="text-[12px] text-slate-400 dark:text-white/30">
              {filtered.length} pedidos - Pagina {page} de {totalPages}
            </p>
            <div className="flex gap-1">
              <button
                disabled={page === 1}
                onClick={() => setPage((p) => p - 1)}
                className="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] text-slate-600 disabled:opacity-40 hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-white/60"
              >
                ← Anterior
              </button>
              <button
                disabled={page === totalPages}
                onClick={() => setPage((p) => p + 1)}
                className="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] text-slate-600 disabled:opacity-40 hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-white/60"
              >
                Siguiente →
              </button>
            </div>
          </div>
        )}
      </GlassCard>

      {/* Order detail drawer */}
      {selected && (
        <OrderDetail
          order={selected}
          onClose={() => setSelected(null)}
          onStatusUpdate={updateStatus}
        />
      )}
    </div>
  )
}


