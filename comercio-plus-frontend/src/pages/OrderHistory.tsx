import { useEffect, useState, useCallback } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import API from '@/lib/api'
import { getStoredToken } from '@/services/auth-session'

type OrderSummary = {
  id: number
  invoice_number: string | null
  invoice_date: string | null
  date: string | null
  status: string
  total: number
  currency: string
  store_name: string | null
}

type ApiResponse = {
  data: OrderSummary[]
  meta?: { count: number }
}

const STATUS_MAP: Record<string, { label: string; bg: string; text: string; border: string }> = {
  pending:    { label: 'Pendiente',   bg: 'bg-amber-100',   text: 'text-amber-700',   border: 'border-amber-200' },
  processing: { label: 'En proceso',  bg: 'bg-blue-100',    text: 'text-blue-700',    border: 'border-blue-200' },
  paid:       { label: 'Pagado',      bg: 'bg-blue-100',    text: 'text-blue-700',    border: 'border-blue-200' },
  approved:   { label: 'Aprobado',    bg: 'bg-blue-100',    text: 'text-blue-700',    border: 'border-blue-200' },
  completed:  { label: 'Completado',  bg: 'bg-emerald-100', text: 'text-emerald-700', border: 'border-emerald-200' },
  cancelled:  { label: 'Cancelado',   bg: 'bg-red-100',     text: 'text-red-700',     border: 'border-red-200' },
}

function statusBadge(status: string) {
  const cfg = STATUS_MAP[status.toLowerCase()] ?? {
    label: status,
    bg: 'bg-slate-100',
    text: 'text-slate-600',
    border: 'border-slate-200',
  }
  return (
    <span
      className={`inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold ${cfg.bg} ${cfg.text} ${cfg.border}`}
    >
      {cfg.label}
    </span>
  )
}

const money = (value: number, currency = 'COP') =>
  new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency,
    maximumFractionDigits: 0,
  }).format(value || 0)

const formatDate = (value: string | null | undefined) => {
  if (!value) return '-'
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return '-'
  return d.toLocaleDateString('es-CO', { dateStyle: 'medium' })
}

function SkeletonRow() {
  return (
    <div className="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
      <div className="h-4 w-20 animate-pulse rounded bg-slate-200" />
      <div className="h-4 w-24 animate-pulse rounded bg-slate-200" />
      <div className="h-5 w-20 animate-pulse rounded-full bg-slate-200" />
      <div className="ml-auto h-4 w-16 animate-pulse rounded bg-slate-200" />
      <div className="h-8 w-24 animate-pulse rounded-lg bg-slate-200" />
    </div>
  )
}

export default function OrderHistory() {
  const navigate = useNavigate()
  const [orders, setOrders] = useState<OrderSummary[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  const fetchOrders = useCallback(async () => {
    setLoading(true)
    setError('')
    try {
      const res = await API.get<ApiResponse>('/orders')
      const raw = res?.data?.data ?? res?.data
      setOrders(Array.isArray(raw) ? (raw as OrderSummary[]) : [])
    } catch (err: unknown) {
      const axiosErr = err as { response?: { data?: { message?: string }; status?: number }; message?: string }
      if (axiosErr?.response?.status === 401) {
        navigate('/login', { replace: true })
        return
      }
      setError(axiosErr?.response?.data?.message ?? axiosErr?.message ?? 'No se pudieron cargar los pedidos.')
    } finally {
      setLoading(false)
    }
  }, [navigate])

  useEffect(() => {
    const token = getStoredToken()
    if (!token) {
      navigate('/login', { replace: true })
      return
    }
    void fetchOrders()
  }, [fetchOrders, navigate])

  return (
    <div className="min-h-screen bg-slate-50 py-10">
      <div className="mx-auto max-w-4xl space-y-6 px-4 sm:px-6">
        {/* Header */}
        <div>
          <h1 className="text-2xl font-bold text-slate-900">Mis pedidos</h1>
          <p className="mt-1 text-sm text-slate-500">Historial de todas tus compras realizadas.</p>
        </div>

        {/* Loading skeletons */}
        {loading && (
          <div className="space-y-3">
            <SkeletonRow />
            <SkeletonRow />
            <SkeletonRow />
          </div>
        )}

        {/* Error state */}
        {!loading && error && (
          <div className="rounded-xl border border-red-200 bg-red-50 p-5">
            <p className="text-sm font-medium text-red-800">{error}</p>
            <button
              onClick={() => void fetchOrders()}
              className="mt-3 inline-flex items-center rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50"
            >
              Reintentar
            </button>
          </div>
        )}

        {/* Empty state */}
        {!loading && !error && orders.length === 0 && (
          <div className="flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-white py-16 shadow-sm">
            <span className="text-5xl" role="img" aria-label="Caja vacía">📦</span>
            <h2 className="mt-4 text-lg font-semibold text-slate-700">Aún no tienes pedidos</h2>
            <p className="mt-1 text-sm text-slate-500">Cuando realices una compra aparecerá aquí.</p>
            <Link
              to="/products"
              className="mt-6 inline-flex items-center rounded-lg bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-600"
            >
              Ver productos
            </Link>
          </div>
        )}

        {/* Orders list */}
        {!loading && !error && orders.length > 0 && (
          <div className="space-y-3">
            {/* Desktop header */}
            <div className="hidden grid-cols-[1fr_1fr_1fr_1fr_auto] items-center gap-4 px-4 text-xs font-semibold uppercase tracking-wide text-slate-400 sm:grid">
              <span>Orden</span>
              <span>Fecha</span>
              <span>Estado</span>
              <span>Total</span>
              <span />
            </div>

            {orders.map((order) => (
              <div
                key={order.id}
                className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-slate-300 hover:shadow"
              >
                {/* Mobile layout */}
                <div className="flex items-start justify-between gap-3 sm:hidden">
                  <div className="space-y-1">
                    <p className="text-sm font-bold text-slate-900">
                      #{order.invoice_number ?? order.id}
                    </p>
                    <p className="text-xs text-slate-500">
                      {formatDate(order.invoice_date ?? order.date)}
                    </p>
                    {statusBadge(order.status)}
                  </div>
                  <div className="text-right">
                    <p className="text-sm font-bold text-slate-900">
                      {money(order.total, order.currency)}
                    </p>
                    <Link
                      to={`/orders/${order.id}`}
                      className="mt-2 inline-flex items-center rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-slate-700"
                    >
                      Ver detalle
                    </Link>
                  </div>
                </div>

                {/* Desktop layout */}
                <div className="hidden grid-cols-[1fr_1fr_1fr_1fr_auto] items-center gap-4 sm:grid">
                  <p className="text-sm font-semibold text-slate-900">
                    #{order.invoice_number ?? order.id}
                  </p>
                  <p className="text-sm text-slate-600">
                    {formatDate(order.invoice_date ?? order.date)}
                  </p>
                  <div>{statusBadge(order.status)}</div>
                  <p className="text-sm font-bold text-slate-900">
                    {money(order.total, order.currency)}
                  </p>
                  <Link
                    to={`/orders/${order.id}`}
                    className="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-slate-700"
                  >
                    Ver detalle
                  </Link>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  )
}
