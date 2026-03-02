import { useCallback, useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import API from '@/lib/api'
import GlassCard from '@/components/ui/GlassCard'
import { Icon } from '@/components/Icon'
import { ErpBadge, ErpBtn, ErpFilterSelect, ErpKpiCard, ErpPageHeader, ErpSearchBar } from '@/components/erp'

type OrderStatus = 'pending' | 'processing' | 'paid' | 'approved' | 'completed' | 'cancelled'
type OrderChannel = 'web' | 'whatsapp' | 'local'
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
  channel: OrderChannel
  payment_method: string
  subtotal: number
  tax_total: number
  total: number
  items: OrderItem[]
  created_at: string
}

interface OrdersMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

interface StatusConfig {
  label: string
  badge: 'pending' | 'processing' | 'paid' | 'approved' | 'completed' | 'cancelled'
  next?: OrderStatus
}

const STATUS_CONFIG: Record<OrderStatus, StatusConfig> = {
  pending: { label: 'Pendiente', badge: 'pending', next: 'processing' },
  processing: { label: 'Procesando', badge: 'processing', next: 'paid' },
  paid: { label: 'Pagado', badge: 'paid', next: 'approved' },
  approved: { label: 'Aprobado', badge: 'approved', next: 'completed' },
  completed: { label: 'Completado', badge: 'completed' },
  cancelled: { label: 'Cancelado', badge: 'cancelled' },
}

const CHANNEL_LABEL: Record<OrderChannel, string> = {
  web: 'Web',
  whatsapp: 'WhatsApp',
  local: 'Presencial',
}

const CHANNEL_BADGE: Record<OrderChannel, 'regular' | 'active' | 'medium'> = {
  web: 'regular',
  whatsapp: 'active',
  local: 'medium',
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

function normalizeChannel(value: unknown): OrderChannel {
  const normalized = toText(value, 'web').toLowerCase()
  if (normalized === 'whatsapp') return 'whatsapp'
  if (normalized === 'local') return 'local'
  return 'web'
}

function normalizeOrderItem(item: unknown): OrderItem {
  const row = (item ?? {}) as Record<string, unknown>

  return {
    product_id: toNumber(row.product_id),
    product_name: toText(row.product_name, 'Producto'),
    quantity: Math.max(0, toNumber(row.quantity)),
    unit_price: toNumber(row.unit_price),
    line_subtotal: toNumber(row.line_subtotal),
    tax_amount: toNumber(row.tax_amount),
    line_total: toNumber(row.line_total),
  }
}

function normalizeOrder(order: unknown): Order {
  const row = (order ?? {}) as Record<string, unknown>
  const id = toNumber(row.id)
  const invoiceNumber = toText(row.invoice_number)
  const createdAt = toText(row.invoice_date || row.date || row.created_at)
  const items = Array.isArray(row.items) ? row.items.map((item) => normalizeOrderItem(item)) : []

  return {
    id,
    reference: invoiceNumber || `PED-${id}`,
    customer_name: toText(row.customer_name, 'Cliente'),
    customer_email: toText(row.customer_email, '-'),
    status: normalizeStatus(row.status),
    channel: normalizeChannel(row.channel),
    payment_method: toText(row.payment_method, '-'),
    subtotal: toNumber(row.subtotal),
    tax_total: toNumber(row.tax_total),
    total: toNumber(row.total),
    items,
    created_at: createdAt,
  }
}

function fmt(value: number): string {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(value)
}

function fmtDate(iso: string): string {
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

function relative(iso: string): string {
  const parsed = new Date(iso).getTime()
  if (Number.isNaN(parsed)) return '-'

  const diff = (Date.now() - parsed) / 1000
  if (diff < 60) return 'ahora'
  if (diff < 3600) return `hace ${Math.floor(diff / 60)} min`
  if (diff < 86400) return `hace ${Math.floor(diff / 3600)} h`
  return `hace ${Math.floor(diff / 86400)} d`
}

interface OrderDetailProps {
  order: Order
  onClose: () => void
  onStatusUpdate: (id: number, status: OrderStatus) => Promise<void>
}

function OrderDetail({ order, onClose, onStatusUpdate }: OrderDetailProps) {
  const [updating, setUpdating] = useState(false)
  const [confirmingCancel, setConfirmingCancel] = useState(false)

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

  const confirmCancel = async () => {
    if (!canCancel || updating) return

    setUpdating(true)
    try {
      await onStatusUpdate(order.id, 'cancelled')
      setConfirmingCancel(false)
    } finally {
      setUpdating(false)
    }
  }

  return (
    <div className="fixed inset-0 z-50 flex justify-end">
      <div className="absolute inset-0 bg-slate-950/55 backdrop-blur-sm" onClick={onClose} />

      <div className="relative z-10 flex h-full w-full max-w-xl flex-col overflow-y-auto border-l border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-slate-900">
        <div className="sticky top-0 z-10 border-b border-slate-100 bg-white/95 px-5 py-4 backdrop-blur dark:border-white/10 dark:bg-slate-900/95">
          <div className="flex items-start justify-between gap-3">
            <div>
              <div className="flex items-center gap-2">
                <h2 className="text-[17px] font-black text-slate-900 dark:text-white">Pedido {order.reference}</h2>
                <ErpBadge status={STATUS_CONFIG[order.status].badge} label={STATUS_CONFIG[order.status].label} />
              </div>
              <p className="mt-1 text-[12px] text-slate-500 dark:text-white/40">{fmtDate(order.created_at)}</p>
            </div>

            <button
              type="button"
              onClick={onClose}
              className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 dark:text-white/60 dark:hover:bg-white/10"
              aria-label="Cerrar detalle"
            >
              <Icon name="x" size={16} />
            </button>
          </div>

          <div className="mt-3 flex flex-wrap items-center gap-2">
            <ErpBadge status={CHANNEL_BADGE[order.channel]} label={CHANNEL_LABEL[order.channel]} />
            <span className="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600 dark:bg-white/10 dark:text-white/60">
              {order.items.length} items
            </span>
            <span className="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
              Total {fmt(order.total)}
            </span>
          </div>
        </div>

        <div className="flex-1 space-y-4 p-5">
          <div className="rounded-2xl border border-slate-100 p-4 dark:border-white/10">
            <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400 dark:text-white/30">Pipeline</p>
            <div className="mt-3 flex items-center gap-1">
              {PIPELINE.map((status, index) => {
                const done = pipelineIndex >= 0 ? index < pipelineIndex : false
                const current = pipelineIndex >= 0 ? index === pipelineIndex : false

                return (
                  <div key={status} className="flex flex-1 items-center gap-1">
                    <div className={`h-1.5 flex-1 rounded-full ${done || current ? 'bg-orange-500' : 'bg-slate-200 dark:bg-white/10'}`} />
                    <div
                      className={`flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-bold ${
                        done
                          ? 'bg-orange-500 text-white'
                          : current
                            ? 'bg-orange-500 text-white ring-2 ring-orange-300 dark:ring-orange-500/40'
                            : 'bg-slate-200 text-slate-500 dark:bg-white/10 dark:text-white/40'
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
          </div>

          <div className="rounded-2xl border border-slate-100 p-4 dark:border-white/10">
            <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400 dark:text-white/30">Cliente</p>
            <p className="mt-2 text-[14px] font-semibold text-slate-900 dark:text-white">{order.customer_name}</p>
            <p className="text-[13px] text-slate-500 dark:text-white/50">{order.customer_email}</p>
          </div>

          <div className="overflow-hidden rounded-2xl border border-slate-100 dark:border-white/10">
            <p className="border-b border-slate-100 px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400 dark:border-white/10 dark:text-white/30">
              Productos ({order.items.length})
            </p>

            {order.items.map((item) => (
              <div
                key={`${order.id}-${item.product_id}`}
                className="flex items-center justify-between border-b border-slate-100 px-4 py-3 last:border-0 dark:border-white/10"
              >
                <div className="min-w-0 flex-1">
                  <p className="truncate text-[13px] font-semibold text-slate-800 dark:text-white">{item.product_name}</p>
                  <p className="text-[11px] text-slate-500 dark:text-white/40">Cantidad: {item.quantity}</p>
                </div>
                <p className="text-[13px] font-bold text-slate-900 dark:text-white">{fmt(item.line_total)}</p>
              </div>
            ))}

            <div className="space-y-1 border-t border-slate-100 bg-slate-50/70 px-4 py-3 dark:border-white/10 dark:bg-white/5">
              <div className="flex justify-between text-[12px] text-slate-500 dark:text-white/50">
                <span>Subtotal</span>
                <span>{fmt(order.subtotal)}</span>
              </div>
              <div className="flex justify-between text-[12px] text-slate-500 dark:text-white/50">
                <span>IVA</span>
                <span>{fmt(order.tax_total)}</span>
              </div>
              <div className="flex justify-between border-t border-slate-200 pt-2 text-[15px] font-black text-slate-900 dark:border-white/10 dark:text-white">
                <span>Total</span>
                <span>{fmt(order.total)}</span>
              </div>
            </div>
          </div>

          <div className="rounded-2xl border border-slate-100 p-4 dark:border-white/10">
            <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400 dark:text-white/30">Metodo de pago</p>
            <p className="mt-1 text-[13px] font-semibold text-slate-800 dark:text-white">{order.payment_method}</p>
          </div>
        </div>

        <div className="sticky bottom-0 space-y-2 border-t border-slate-100 bg-white/95 p-4 backdrop-blur dark:border-white/10 dark:bg-slate-900/95">
          {confirmingCancel && canCancel && (
            <div className="flex items-center justify-between gap-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 dark:border-rose-500/25 dark:bg-rose-500/10">
              <p className="text-[12px] font-semibold text-rose-700 dark:text-rose-300">Confirmar cancelacion del pedido</p>
              <div className="flex items-center gap-2">
                <ErpBtn variant="ghost" size="sm" onClick={() => setConfirmingCancel(false)} disabled={updating}>
                  No
                </ErpBtn>
                <ErpBtn variant="danger" size="sm" onClick={() => void confirmCancel()} disabled={updating}>
                  {updating ? 'Cancelando...' : 'Si, cancelar'}
                </ErpBtn>
              </div>
            </div>
          )}

          <div className="flex flex-wrap gap-2">
            <Link
              to={`/dashboard/orders/${order.id}/picking`}
              className="inline-flex h-9 items-center rounded-xl border border-blue-200 bg-blue-50 px-4 text-[12px] font-semibold text-blue-700 transition hover:bg-blue-100 dark:border-blue-500/25 dark:bg-blue-500/10 dark:text-blue-300"
            >
              Alistar pedido
            </Link>

            <ErpBtn variant="danger" size="md" onClick={() => setConfirmingCancel(true)} disabled={!canCancel || updating}>
              Cancelar pedido
            </ErpBtn>

            {canAdvance && cfg.next && (
              <ErpBtn
                variant="primary"
                size="md"
                onClick={() => void advanceStatus()}
                disabled={updating}
                className="flex-1 justify-center"
              >
                {updating ? 'Actualizando...' : `Marcar como ${STATUS_CONFIG[cfg.next].label}`}
              </ErpBtn>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}

function isOrderFilter(value: string): value is OrderFilter {
  return value === 'all' || value === 'pending' || value === 'processing' || value === 'paid' || value === 'approved' || value === 'completed' || value === 'cancelled'
}

export default function DashboardOrdersPage() {
  const [orders, setOrders] = useState<Order[]>([])
  const [meta, setMeta] = useState<OrdersMeta | null>(null)
  const [loading, setLoading] = useState(true)
  const [loadError, setLoadError] = useState('')
  const [statusFilter, setStatusFilter] = useState<OrderFilter>('all')
  const [search, setSearch] = useState('')
  const [selected, setSelected] = useState<Order | null>(null)
  const [page, setPage] = useState(1)

  const PER_PAGE = 20

  const load = useCallback(async () => {
    setLoading(true)
    setLoadError('')

    try {
      const params = statusFilter !== 'all' ? { status: statusFilter } : undefined
      const { data } = await API.get('/merchant/orders', { params })
      const rows = Array.isArray(data?.data) ? data.data : []
      const nextMeta = (data?.meta ?? null) as OrdersMeta | null

      setOrders(rows.map((row: unknown) => normalizeOrder(row)))
      setMeta(nextMeta)
    } catch (err: unknown) {
      const apiError = err as { response?: { data?: { message?: string } } }
      setLoadError(apiError?.response?.data?.message || 'No se pudieron cargar los pedidos.')
    } finally {
      setLoading(false)
    }
  }, [statusFilter])

  useEffect(() => {
    void load()
  }, [load])

  useEffect(() => {
    setPage(1)
  }, [search, statusFilter])

  const updateStatus = async (id: number, status: OrderStatus): Promise<void> => {
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
        CHANNEL_LABEL[order.channel].toLowerCase().includes(query) ||
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

  const revenueTotal = useMemo(() => orders.reduce((sum, order) => sum + order.total, 0), [orders])

  const activeFlow = counts.pending + counts.processing + counts.paid + counts.approved

  const escapeCsv = (value: string | number | null | undefined): string => {
    const raw = String(value ?? '')
    const escaped = raw.replace(/"/g, '""')
    return `"${escaped}"`
  }

  const exportCsv = (): void => {
    if (!orders.length) return

    const headers = ['Pedido', 'Cliente', 'Email', 'Canal', 'Estado', 'Metodo de pago', 'Total', 'Fecha']
    const records = orders.map((order) => [
      order.reference,
      order.customer_name,
      order.customer_email,
      CHANNEL_LABEL[order.channel],
      STATUS_CONFIG[order.status].label,
      order.payment_method,
      order.total,
      fmtDate(order.created_at),
    ])

    const csvContent = [
      headers.map((header) => escapeCsv(header)).join(','),
      ...records.map((record) => record.map((cell) => escapeCsv(cell)).join(',')),
    ].join('\n')

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const timestamp = new Date().toISOString().slice(0, 10)
    const link = document.createElement('a')
    link.href = url
    link.download = `pedidos-${timestamp}.csv`
    document.body.appendChild(link)
    link.click()
    link.remove()
    URL.revokeObjectURL(url)
  }

  const filterOptions: Array<{ value: string; label: string }> = [
    { value: 'all', label: `Todos (${orders.length})` },
    { value: 'pending', label: `Pendientes (${counts.pending})` },
    { value: 'processing', label: `Procesando (${counts.processing})` },
    { value: 'paid', label: `Pagados (${counts.paid})` },
    { value: 'approved', label: `Aprobados (${counts.approved})` },
    { value: 'completed', label: `Completados (${counts.completed})` },
    { value: 'cancelled', label: `Cancelados (${counts.cancelled})` },
  ]

  return (
    <div className="space-y-4">
      <ErpPageHeader
        breadcrumb="Dashboard / Pedidos"
        title="Pedidos ERP"
        subtitle="Gestiona el flujo completo de pedidos, estados y alistamiento."
        actions={
          <>
            <ErpBtn variant="secondary" size="md" icon={<Icon name="refresh" size={14} />} onClick={() => void load()}>
              Recargar
            </ErpBtn>
            <ErpBtn
              variant="primary"
              size="md"
              icon={<Icon name="download" size={14} />}
              onClick={exportCsv}
              disabled={!orders.length}
            >
              Exportar CSV
            </ErpBtn>
          </>
        }
      />

      <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <ErpKpiCard
          label="Total pedidos"
          value={meta?.total ?? orders.length}
          hint="Registros disponibles"
          icon="file-text"
          iconBg="rgba(59,130,246,0.14)"
          iconColor="#3B82F6"
        />
        <ErpKpiCard
          label="En flujo"
          value={activeFlow}
          hint="Pendiente a aprobado"
          icon="clock"
          iconBg="rgba(245,158,11,0.14)"
          iconColor="#F59E0B"
        />
        <ErpKpiCard
          label="Ventas hoy"
          value={fmt(todayTotal)}
          hint="Facturado en el dia"
          icon="trending"
          iconBg="rgba(16,185,129,0.14)"
          iconColor="#10B981"
        />
        <ErpKpiCard
          label="Facturacion"
          value={fmt(revenueTotal)}
          hint="Suma total de pedidos"
          icon="dollar"
          iconBg="rgba(255,161,79,0.16)"
          iconColor="#FFA14F"
        />
      </div>

      {loadError && (
        <div className="flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-[13px] text-rose-700 dark:border-rose-500/25 dark:bg-rose-500/10 dark:text-rose-300">
          <Icon name="alert" size={14} />
          <span>{loadError}</span>
          <div className="ml-auto">
            <ErpBtn variant="ghost" size="sm" onClick={() => void load()}>
              Reintentar
            </ErpBtn>
          </div>
        </div>
      )}

      <GlassCard className="overflow-hidden border-[#DDE3EF] bg-[linear-gradient(145deg,#FFFFFF_0%,#F8FAFC_100%)] p-0 shadow-[0_20px_45px_rgba(15,23,42,0.08)]">
        <div className="border-b border-slate-200 px-4 py-4 dark:border-white/10 sm:px-5">
          <div className="flex flex-wrap items-end justify-between gap-3">
            <div>
              <h2 className="text-[28px] font-black leading-none tracking-[-0.025em] text-slate-900 sm:text-[32px] dark:text-white">Control de pedidos</h2>
              <p className="mt-1 text-[13px] text-slate-500 dark:text-white/50">Filtra por estado, busca clientes y abre el detalle para gestionar el flujo.</p>
            </div>
          </div>

          <div className="mt-4 grid gap-2 lg:grid-cols-[minmax(0,1fr)_260px_auto]">
            <ErpSearchBar
              value={search}
              onChange={(value: string) => setSearch(value)}
              placeholder="Buscar por pedido, cliente, correo o canal"
            />

            <ErpFilterSelect
              value={statusFilter}
              onChange={(value: string) => {
                if (isOrderFilter(value)) {
                  setStatusFilter(value)
                }
              }}
              options={filterOptions}
              placeholder="Estado"
            />

            <div className="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 dark:border-white/10 dark:bg-white/5">
              <ErpBadge status="pending" label={`${counts.pending} pendientes`} />
              <ErpBadge status="cancelled" label={`${counts.cancelled} cancelados`} />
            </div>
          </div>
        </div>

        {loading ? (
          <div className="flex flex-col items-center justify-center py-16">
            <div className="mb-3 h-8 w-8 animate-spin rounded-full border-2 border-orange-500 border-t-transparent" />
            <p className="text-[13px] text-slate-500 dark:text-white/50">Cargando pedidos...</p>
          </div>
        ) : paginated.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-16 text-slate-500 dark:text-white/40">
            <p className="text-[13px]">Sin pedidos para mostrar</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full min-w-[1040px]">
              <thead>
                <tr className="border-b border-slate-200 bg-slate-50/90 dark:border-white/10 dark:bg-white/5">
                  <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-white/40">Pedido</th>
                  <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-white/40">Cliente</th>
                  <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-white/40">Canal</th>
                  <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-white/40">Items</th>
                  <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-white/40">Total</th>
                  <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-white/40">Pago</th>
                  <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-white/40">Estado</th>
                  <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-white/40">Fecha</th>
                  <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-white/40">Acciones</th>
                </tr>
              </thead>
              <tbody>
                {paginated.map((order) => (
                  <tr
                    key={order.id}
                    className="border-b border-slate-100 transition-colors hover:bg-slate-50/90 dark:border-white/10 dark:hover:bg-white/5"
                  >
                    <td className="px-4 py-3">
                      <button
                        type="button"
                        onClick={() => setSelected(order)}
                        className="text-left font-mono text-[12px] font-bold text-orange-600 hover:underline dark:text-orange-400"
                      >
                        {order.reference}
                      </button>
                    </td>
                    <td className="px-4 py-3">
                      <p className="text-[13px] font-semibold text-slate-900 dark:text-white">{order.customer_name}</p>
                      <p className="text-[11px] text-slate-500 dark:text-white/40">{order.customer_email}</p>
                    </td>
                    <td className="px-4 py-3">
                      <ErpBadge status={CHANNEL_BADGE[order.channel]} label={CHANNEL_LABEL[order.channel]} />
                    </td>
                    <td className="px-4 py-3 text-[12px] text-slate-600 dark:text-white/60">
                      {order.items.length} item{order.items.length !== 1 ? 's' : ''}
                    </td>
                    <td className="px-4 py-3 text-[13px] font-black text-slate-900 dark:text-white">{fmt(order.total)}</td>
                    <td className="px-4 py-3 text-[12px] text-slate-600 dark:text-white/60">{order.payment_method}</td>
                    <td className="px-4 py-3">
                      <ErpBadge status={STATUS_CONFIG[order.status].badge} label={STATUS_CONFIG[order.status].label} />
                    </td>
                    <td className="px-4 py-3 text-[12px] text-slate-500 dark:text-white/40">{relative(order.created_at)}</td>
                    <td className="px-4 py-3">
                      <div className="flex items-center gap-2">
                        <ErpBtn
                          variant="secondary"
                          size="sm"
                          icon={<Icon name="eye" size={13} />}
                          onClick={() => setSelected(order)}
                        >
                          Ver
                        </ErpBtn>
                        <Link
                          to={`/dashboard/orders/${order.id}/picking`}
                          className="inline-flex h-8 items-center rounded-lg border border-blue-200 bg-blue-50 px-3 text-[11px] font-semibold text-blue-700 transition hover:bg-blue-100 dark:border-blue-500/25 dark:bg-blue-500/10 dark:text-blue-300"
                        >
                          Alistar
                        </Link>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {totalPages > 1 && (
          <div className="flex items-center justify-between border-t border-slate-100 px-4 py-3 dark:border-white/10 sm:px-5">
            <p className="text-[12px] text-slate-500 dark:text-white/40">
              {filtered.length} pedidos · Pagina {page} de {totalPages}
            </p>
            <div className="flex items-center gap-2">
              <ErpBtn variant="secondary" size="sm" disabled={page === 1} onClick={() => setPage((previous) => previous - 1)}>
                Anterior
              </ErpBtn>
              <ErpBtn variant="secondary" size="sm" disabled={page === totalPages} onClick={() => setPage((previous) => previous + 1)}>
                Siguiente
              </ErpBtn>
            </div>
          </div>
        )}
      </GlassCard>

      {selected && <OrderDetail order={selected} onClose={() => setSelected(null)} onStatusUpdate={updateStatus} />}
    </div>
  )
}