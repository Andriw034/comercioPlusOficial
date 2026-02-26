import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import API from '@/lib/api'
import GlassCard from '@/components/ui/GlassCard'
import StatusBadge from '@/components/ui/StatusBadge'
import Button from '@/components/ui/button'

type OrderStatus = 'pending' | 'processing' | 'paid' | 'approved' | 'completed' | 'cancelled'
type OrderFilter = OrderStatus | 'all'

interface OrderItem {
  product_id: number
  product_name: string
  quantity: number
  unit_price: number
  line_subtotal: number
  tax_amount: number
  line_total: number
}

interface Order {
  id: number
  reference: string
  customer_name: string
  customer_email: string
  status: OrderStatus
  payment_method: string
  subtotal: number
  tax_total: number
  total: number
  items: OrderItem[]
  created_at: string
}

const STATUS_CONFIG: Record<
  OrderStatus,
  {
    label: string
    variant: 'warning' | 'neutral' | 'brand' | 'success' | 'danger'
    next?: OrderStatus
  }
> = {
  pending: { label: 'Pendiente', variant: 'warning', next: 'processing' },
  processing: { label: 'Procesando', variant: 'neutral', next: 'paid' },
  paid: { label: 'Pagado', variant: 'brand', next: 'approved' },
  approved: { label: 'Aprobado', variant: 'success', next: 'completed' },
  completed: { label: 'Completado', variant: 'success' },
  cancelled: { label: 'Cancelado', variant: 'danger' },
}

const PIPELINE: OrderStatus[] = ['pending', 'processing', 'paid', 'approved', 'completed']

function toNumber(value: unknown, fallback = 0): number {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : fallback
}

function toText(value: unknown, fallback = ''): string {
  if (typeof value === 'string') {
    const trimmed = value.trim()
    return trimmed.length > 0 ? trimmed : fallback
  }

  if (typeof value === 'number' && Number.isFinite(value)) {
    return String(value)
  }

  return fallback
}

function normalizeStatus(value: unknown): OrderStatus {
  const normalized = toText(value, 'pending').toLowerCase()
  if (normalized === 'pending') return 'pending'
  if (normalized === 'processing') return 'processing'
  if (normalized === 'paid') return 'paid'
  if (normalized === 'approved') return 'approved'
  if (normalized === 'completed') return 'completed'
  if (normalized === 'cancelled') return 'cancelled'
  return 'pending'
}

function normalizeOrderItem(item: any): OrderItem {
  return {
    product_id: toNumber(item?.product_id),
    product_name: toText(item?.product_name, 'Producto'),
    quantity: Math.max(0, toNumber(item?.quantity)),
    unit_price: toNumber(item?.unit_price),
    line_subtotal: toNumber(item?.line_subtotal),
    tax_amount: toNumber(item?.tax_amount),
    line_total: toNumber(item?.line_total),
  }
}

function normalizeOrder(order: any): Order {
  const id = toNumber(order?.id)
  const invoiceNumber = toText(order?.invoice_number)
  const createdAt = toText(order?.invoice_date || order?.date || order?.created_at)

  return {
    id,
    reference: invoiceNumber || `PED-${id}`,
    customer_name: toText(order?.customer_name, 'Cliente'),
    customer_email: toText(order?.customer_email, '-'),
    status: normalizeStatus(order?.status),
    payment_method: toText(order?.payment_method, '-'),
    subtotal: toNumber(order?.subtotal),
    tax_total: toNumber(order?.tax_total),
    total: toNumber(order?.total),
    items: Array.isArray(order?.items) ? order.items.map(normalizeOrderItem) : [],
    created_at: createdAt,
  }
}

function fmt(value: number) {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(value)
}

function fmtDate(iso: string) {
  const date = new Date(iso)
  if (Number.isNaN(date.getTime())) return '-'

  return date.toLocaleString('es-CO', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function relative(iso: string) {
  const parsed = new Date(iso).getTime()
  if (Number.isNaN(parsed)) return '-'

  const diff = (Date.now() - parsed) / 1000
  if (diff < 60) return 'ahora'
  if (diff < 3600) return `hace ${Math.floor(diff / 60)} min`
  if (diff < 86400) return `hace ${Math.floor(diff / 3600)} h`
  return `hace ${Math.floor(diff / 86400)} d`
}

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

  const pipelineIndex = PIPELINE.indexOf(order.status)
  const canAdvance = Boolean(cfg.next)
  const canCancel = order.status !== 'cancelled' && order.status !== 'completed'

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
    if (!canCancel) return
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
        <div className="sticky top-0 z-10 flex items-center justify-between border-b border-slate-100 bg-white/95 px-6 py-4 backdrop-blur dark:border-white/10 dark:bg-slate-900/95">
          <div>
            <div className="flex items-center gap-2">
              <h2 className="text-[16px] font-semibold text-slate-900 dark:text-white">Pedido {order.reference}</h2>
              <StatusBadge status={order.status} />
            </div>
            <p className="text-[12px] text-slate-400 dark:text-white/30">{fmtDate(order.created_at)}</p>
          </div>
          <button onClick={onClose} className="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10">
            x
          </button>
        </div>

        <div className="flex-1 space-y-5 p-6">
          <div className="flex items-center gap-1">
            {PIPELINE.map((status, index) => {
              const done = pipelineIndex >= 0 ? index < pipelineIndex : false
              const current = pipelineIndex >= 0 ? index === pipelineIndex : false

              return (
                <div key={status} className="flex flex-1 items-center gap-1">
                  <div className={`h-1.5 flex-1 rounded-full ${done || current ? 'bg-orange-500' : 'bg-slate-200 dark:bg-white/10'}`} />
                  <div
                    className={`flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full text-[10px] font-bold ${
                      done
                        ? 'bg-orange-500 text-white'
                        : current
                          ? 'bg-orange-500 text-white ring-2 ring-orange-300 dark:ring-orange-500/40'
                          : 'bg-slate-200 text-slate-400 dark:bg-white/10 dark:text-white/30'
                    }`}
                  >
                    {done ? 'OK' : index + 1}
                  </div>
                  {index < PIPELINE.length - 1 && (
                    <div className={`h-1.5 flex-1 rounded-full ${done ? 'bg-orange-500' : 'bg-slate-200 dark:bg-white/10'}`} />
                  )}
                </div>
              )
            })}
          </div>

          <div className="rounded-2xl border border-slate-100 p-4 dark:border-white/10">
            <p className="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-white/30">Cliente</p>
            <p className="text-[14px] font-semibold text-slate-900 dark:text-white">{order.customer_name}</p>
            <p className="text-[13px] text-slate-500 dark:text-white/50">{order.customer_email}</p>
          </div>

          <div className="rounded-2xl border border-slate-100 dark:border-white/10">
            <p className="border-b border-slate-100 px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:border-white/10 dark:text-white/30">
              Productos ({order.items.length})
            </p>
            {order.items.map((item) => (
              <div key={`${order.id}-${item.product_id}`} className="flex items-center justify-between border-b border-slate-50 px-4 py-3 last:border-0 dark:border-white/5">
                <div className="min-w-0 flex-1">
                  <p className="truncate text-[13px] font-medium text-slate-800 dark:text-white">{item.product_name}</p>
                  <p className="text-[11px] text-slate-400 dark:text-white/30">Cantidad: {item.quantity}</p>
                </div>
                <p className="text-[13px] font-semibold text-slate-900 dark:text-white">{fmt(item.line_total)}</p>
              </div>
            ))}

            <div className="space-y-1 border-t border-slate-100 px-4 py-3 dark:border-white/10">
              <div className="flex justify-between text-[12px] text-slate-500 dark:text-white/50">
                <span>Subtotal</span>
                <span>{fmt(order.subtotal)}</span>
              </div>
              <div className="flex justify-between text-[12px] text-slate-500 dark:text-white/50">
                <span>IVA</span>
                <span>{fmt(order.tax_total)}</span>
              </div>
              <div className="flex justify-between border-t border-slate-100 pt-2 text-[15px] font-bold text-slate-900 dark:border-white/10 dark:text-white">
                <span>Total</span>
                <span>{fmt(order.total)}</span>
              </div>
            </div>
          </div>

          <div className="flex items-center justify-between rounded-2xl border border-slate-100 px-4 py-3 dark:border-white/10">
            <div>
              <p className="text-[11px] text-slate-400 dark:text-white/30">Metodo de pago</p>
              <p className="text-[13px] font-semibold text-slate-800 dark:text-white">{order.payment_method}</p>
            </div>
          </div>
        </div>

        <div className="sticky bottom-0 flex gap-2 border-t border-slate-100 bg-white/95 p-4 backdrop-blur dark:border-white/10 dark:bg-slate-900/95">
          <Link
            to={`/dashboard/orders/${order.id}/picking`}
            className="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-[13px] font-semibold text-blue-700 transition-colors hover:bg-blue-100 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300"
          >
            Alistar pedido
          </Link>
          <button
            onClick={cancelOrder}
            disabled={updating || !canCancel}
            className="rounded-xl border border-red-200 bg-white px-4 py-2.5 text-[13px] font-semibold text-red-500 transition-colors hover:bg-red-50 disabled:opacity-50 dark:border-red-500/20 dark:bg-transparent dark:hover:bg-red-500/10"
          >
            Cancelar pedido
          </button>
          {canAdvance && (
            <Button className="flex-1" loading={updating} onClick={advanceStatus}>
              {updating ? 'Actualizando...' : `Marcar como ${cfg.next ? STATUS_CONFIG[cfg.next].label : ''}`}
            </Button>
          )}
        </div>
      </div>
    </div>
  )
}

export default function DashboardOrdersPage() {
  const [orders, setOrders] = useState<Order[]>([])
  const [loading, setLoading] = useState(true)
  const [loadError, setLoadError] = useState('')
  const [statusFilter, setStatusFilter] = useState<OrderFilter>('all')
  const [search, setSearch] = useState('')
  const [selected, setSelected] = useState<Order | null>(null)
  const [page, setPage] = useState(1)

  const PER_PAGE = 20

  const load = async () => {
    setLoading(true)
    setLoadError('')

    try {
      const params = statusFilter !== 'all' ? { status: statusFilter } : undefined
      const { data } = await API.get('/merchant/orders', { params })
      const rows = Array.isArray(data?.data) ? data.data : []
      setOrders(rows.map((row: any) => normalizeOrder(row)))
    } catch (err: any) {
      setLoadError(err?.response?.data?.message || 'No se pudieron cargar los pedidos.')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    void load()
  }, [statusFilter])

  useEffect(() => {
    setPage(1)
  }, [search, statusFilter])

  const updateStatus = async (id: number, status: OrderStatus) => {
    await API.put(`/merchant/orders/${id}/status`, { status })

    setOrders((previous) => previous.map((order) => (order.id === id ? { ...order, status } : order)))
    setSelected((previous) => (previous?.id === id ? { ...previous, status } : previous))
  }

  const counts = useMemo(() => {
    return orders.reduce<Record<OrderStatus, number>>(
      (acc, order) => {
        acc[order.status] += 1
        return acc
      },
      {
        pending: 0,
        processing: 0,
        paid: 0,
        approved: 0,
        completed: 0,
        cancelled: 0,
      },
    )
  }, [orders])

  const filtered = useMemo(() => {
    const query = search.trim().toLowerCase()
    if (!query) return orders

    return orders.filter((order) => {
      return (
        order.reference.toLowerCase().includes(query) ||
        order.customer_name.toLowerCase().includes(query) ||
        order.customer_email.toLowerCase().includes(query) ||
        String(order.id).includes(query)
      )
    })
  }, [orders, search])

  const paginated = useMemo(() => {
    return filtered.slice((page - 1) * PER_PAGE, page * PER_PAGE)
  }, [filtered, page])

  const totalPages = Math.max(1, Math.ceil(filtered.length / PER_PAGE))

  const todayTotal = useMemo(() => {
    const now = new Date()

    return orders
      .filter((order) => {
        const date = new Date(order.created_at)
        if (Number.isNaN(date.getTime())) return false
        return date.getDate() === now.getDate() && date.getMonth() === now.getMonth() && date.getFullYear() === now.getFullYear()
      })
      .reduce((sum, order) => sum + order.total, 0)
  }, [orders])

  return (
    <div className="space-y-6">
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

      <div className="flex gap-2 overflow-x-auto pb-1">
        {PIPELINE.map((status) => {
          const config = STATUS_CONFIG[status]
          const count = counts[status] || 0
          return (
            <button
              key={status}
              onClick={() => setStatusFilter(status)}
              className={`min-w-[100px] rounded-2xl border px-4 py-3 text-center transition-all ${
                statusFilter === status
                  ? 'border-orange-400 bg-orange-50 dark:border-orange-500/50 dark:bg-orange-500/10'
                  : 'border-slate-200 bg-slate-50 hover:border-slate-300 dark:border-white/10 dark:bg-white/5'
              }`}
            >
              <p className="text-[10px] uppercase tracking-wider text-slate-400 dark:text-white/30">{config.label}</p>
              <p className={`mt-1 text-2xl font-black ${count > 0 ? 'text-slate-900 dark:text-white' : 'text-slate-300 dark:text-white/20'}`}>{count}</p>
            </button>
          )
        })}

        <button
          onClick={() => setStatusFilter('cancelled')}
          className={`min-w-[100px] rounded-2xl border px-4 py-3 text-center transition-all ${
            statusFilter === 'cancelled'
              ? 'border-red-400 bg-red-50 dark:border-red-500/50 dark:bg-red-500/10'
              : 'border-slate-200 bg-slate-50 hover:border-slate-300 dark:border-white/10 dark:bg-white/5'
          }`}
        >
          <p className="text-[10px] uppercase tracking-wider text-slate-400 dark:text-white/30">Cancelados</p>
          <p className={`mt-1 text-2xl font-black ${counts.cancelled > 0 ? 'text-red-500' : 'text-slate-300 dark:text-white/20'}`}>{counts.cancelled}</p>
        </button>
      </div>

      {loadError && (
        <div className="flex items-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">
          <span>{loadError}</span>
          <button onClick={() => void load()} className="ml-auto text-[12px] underline">
            Reintentar
          </button>
        </div>
      )}

      <GlassCard className="overflow-hidden p-0">
        <div className="flex flex-wrap items-center gap-3 border-b border-slate-200 px-5 py-4 dark:border-white/10">
          <div className="relative min-w-[200px] flex-1">
            <input
              type="search"
              value={search}
              onChange={(event) => setSearch(event.target.value)}
              placeholder="Buscar por pedido, cliente o correo..."
              className="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-3 pr-3 text-[13px] text-slate-900 outline-none transition-colors placeholder:text-slate-400 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-white/30"
            />
          </div>

          <div className="flex gap-1">
            {(['all', ...PIPELINE, 'cancelled'] as const).map((filter) => (
              <button
                key={filter}
                onClick={() => setStatusFilter(filter)}
                className={`rounded-lg px-3 py-1.5 text-[11px] font-semibold transition-colors ${
                  statusFilter === filter
                    ? 'bg-orange-500 text-white'
                    : 'border border-slate-200 bg-white text-slate-500 hover:text-slate-800 dark:border-white/10 dark:bg-white/5 dark:text-white/50 dark:hover:text-white'
                }`}
              >
                {filter === 'all' ? 'Todos' : STATUS_CONFIG[filter as OrderStatus].label}
              </button>
            ))}
          </div>
        </div>

        {loading ? (
          <div className="flex flex-col items-center justify-center py-16">
            <div className="mb-3 h-8 w-8 animate-spin rounded-full border-2 border-orange-500 border-t-transparent" />
            <p className="text-[13px] text-slate-400">Cargando pedidos...</p>
          </div>
        ) : paginated.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-16 text-slate-400 dark:text-white/30">
            <p className="text-[13px]">Sin pedidos para mostrar</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-slate-100 dark:border-white/5">
                  {['Pedido', 'Cliente', 'Productos', 'Total', 'Pago', 'Estado', 'Fecha', 'Alistar'].map((header) => (
                    <th key={header} className="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-white/30">
                      {header}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {paginated.map((order) => {
                  return (
                    <tr
                      key={order.id}
                      className="group cursor-pointer border-b border-slate-100 transition-colors hover:bg-slate-50/80 dark:border-white/5 dark:hover:bg-white/5"
                      onClick={() => setSelected(order)}
                    >
                      <td className="py-3 pl-4 pr-2">
                        <span className="font-mono text-[13px] font-bold text-orange-600 dark:text-orange-400">{order.reference}</span>
                      </td>
                      <td className="px-3 py-3">
                        <p className="text-[13px] font-semibold text-slate-900 dark:text-white">{order.customer_name}</p>
                        <p className="text-[11px] text-slate-400 dark:text-white/30">{order.customer_email}</p>
                      </td>
                      <td className="px-3 py-3 text-[13px] text-slate-500 dark:text-white/50">{order.items.length} item{order.items.length !== 1 ? 's' : ''}</td>
                      <td className="px-3 py-3">
                        <span className="text-[14px] font-bold text-slate-900 dark:text-white">{fmt(order.total)}</span>
                      </td>
                      <td className="px-3 py-3 text-[12px] text-slate-500 dark:text-white/50">{order.payment_method}</td>
                      <td className="px-3 py-3">
                        <StatusBadge status={order.status} />
                      </td>
                      <td className="px-3 py-3 text-[11px] text-slate-400 dark:text-white/30">{relative(order.created_at)}</td>
                      <td className="py-3 pl-3 pr-4">
                        <Link
                          to={`/dashboard/orders/${order.id}/picking`}
                          onClick={(event) => event.stopPropagation()}
                          className="rounded-lg bg-blue-600 px-3 py-1.5 text-[11px] font-semibold text-white"
                        >
                          Alistar
                        </Link>
                      </td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          </div>
        )}

        {totalPages > 1 && (
          <div className="flex items-center justify-between border-t border-slate-100 px-5 py-3 dark:border-white/5">
            <p className="text-[12px] text-slate-400 dark:text-white/30">
              {filtered.length} pedidos - Pagina {page} de {totalPages}
            </p>
            <div className="flex gap-1">
              <button
                disabled={page === 1}
                onClick={() => setPage((previous) => previous - 1)}
                className="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] text-slate-600 disabled:opacity-40 hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-white/60"
              >
                Anterior
              </button>
              <button
                disabled={page === totalPages}
                onClick={() => setPage((previous) => previous + 1)}
                className="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] text-slate-600 disabled:opacity-40 hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-white/60"
              >
                Siguiente
              </button>
            </div>
          </div>
        )}
      </GlassCard>

      {selected && <OrderDetail order={selected} onClose={() => setSelected(null)} onStatusUpdate={updateStatus} />}
    </div>
  )
}

