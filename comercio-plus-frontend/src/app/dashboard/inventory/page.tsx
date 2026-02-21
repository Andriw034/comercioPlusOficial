import { useEffect, useState } from 'react'
import { useMemo } from 'react'
import {
  AlertCircle,
  AlertTriangle,
  ArrowDownCircle,
  ArrowUpCircle,
  Boxes,
  ClipboardList,
  History,
  Loader2,
  PackageSearch,
  RefreshCw,
  Search,
  SlidersHorizontal,
  X,
} from 'lucide-react'
import API from '@/lib/api'
import GlassCard from '@/components/ui/GlassCard'
import Badge from '@/components/ui/Badge'
import Button from '@/components/ui/button'

// -- Types ---------------------------------------------------------------------

interface ApiSummaryItem {
  id?: number | string
  name?: string | null
  slug?: string | null
  stock?: number | string | null
  reorder_point?: number | string | null
  allow_backorder?: boolean | number | string | null
  price?: number | string | null
  deficit?: number | string | null
}

interface ApiInventoryStats {
  total_products?: number
  low_stock_products?: number
  out_of_stock_products?: number
  inventory_value?: number
}

interface ApiSummaryResponse {
  data?: ApiSummaryItem[]
  stats?: ApiInventoryStats
}

interface ApiMovementItem {
  id?: number | string
  type?: string | null
  direction?: string | null
  quantity?: number | string | null
  reason?: string | null
  note?: string | null
  created_at?: string | null
  created_by?: string | null
}

interface ApiMovementResponse {
  data?: ApiMovementItem[]
}

interface StockItem {
  product_id: number
  product_name: string
  sku: string
  current_stock: number
  min_stock: number
  unit: string
  allow_backorder: boolean
  price: number
  deficit: number
}

interface InventoryStats {
  total_products: number
  low_stock_products: number
  out_of_stock_products: number
  inventory_value: number
}

interface Movement {
  id: number
  direction: 'in' | 'out' | 'adjust'
  quantity: number
  reason: string
  note: string | null
  created_at: string | null
  user: string
}

type StockStatus = 'all' | 'ok' | 'low' | 'out'
type SortBy = 'name' | 'stock_asc' | 'stock_desc' | 'deficit'
type AdjustType = 'in' | 'out' | 'adjustment'

const MOVEMENT_TYPES: Array<{ value: AdjustType; label: string; hint: string }> = [
  { value: 'in', label: 'Entrada', hint: 'Suma unidades al stock actual.' },
  { value: 'out', label: 'Salida', hint: 'Resta unidades del stock actual.' },
  { value: 'adjustment', label: 'Ajuste total', hint: 'Define el nuevo stock final.' },
]

const REASONS: Record<AdjustType, string[]> = {
  in: ['Compra a proveedor', 'Devolucion de cliente', 'Reposicion interna', 'Produccion propia', 'Otro motivo'],
  out: ['Venta', 'Dano o merma', 'Devolucion a proveedor', 'Muestra o regalo', 'Otro motivo'],
  adjustment: ['Conteo fisico', 'Correccion de sistema', 'Ajuste por auditoria', 'Otro motivo'],
}

// -- Helpers -------------------------------------------------------------------

function toNumber(value: unknown, fallback = 0): number {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : fallback
}

function toBoolean(value: unknown): boolean {
  if (typeof value === 'boolean') return value
  if (typeof value === 'number') return value === 1
  if (typeof value === 'string') return ['1', 'true', 'yes', 'on'].includes(value.toLowerCase())
  return false
}

function normalizeSummaryItem(item: ApiSummaryItem): StockItem {
  const id = toNumber(item.id)
  const currentStock = Math.max(0, toNumber(item.stock))
  const minStock = Math.max(0, toNumber(item.reorder_point))

  return {
    product_id: id,
    product_name: (item.name || '').trim() || `Producto #${id || 'N/A'}`,
    sku: ((item.slug || '').trim() || `PROD-${id || 'NA'}`).toUpperCase(),
    current_stock: currentStock,
    min_stock: minStock,
    unit: 'u',
    allow_backorder: toBoolean(item.allow_backorder),
    price: Math.max(0, toNumber(item.price)),
    deficit: Math.max(0, toNumber(item.deficit, Math.max(0, minStock - currentStock))),
  }
}

function normalizeMovement(item: ApiMovementItem): Movement {
  const signedQty = toNumber(item.quantity)
  const direction: Movement['direction'] =
    item.direction === 'in' || item.direction === 'out'
      ? item.direction
      : signedQty > 0
        ? 'in'
        : signedQty < 0
          ? 'out'
          : 'adjust'

  return {
    id: toNumber(item.id),
    direction,
    quantity: Math.abs(signedQty),
    reason: (item.reason || '').trim() || (item.type || '').trim() || 'Movimiento manual',
    note: item.note || null,
    created_at: item.created_at || null,
    user: (item.created_by || '').trim() || 'Sistema',
  }
}

function getStatus(current: number, min: number): { label: string; variant: 'danger' | 'warning' | 'success' } {
  if (current <= 0) return { label: 'Agotado', variant: 'danger' }
  if (current < min) return { label: 'Stock bajo', variant: 'warning' }
  return { label: 'Normal', variant: 'success' }
}

function fmtDate(iso: string | null) {
  if (!iso) return '-'
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

function fmtCurrency(value: number) {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(Math.max(0, value))
}

function buildReason(reason: string, note: string) {
  const normalizedReason = reason.trim()
  const normalizedNote = note.trim()
  if (!normalizedNote) return normalizedReason
  return `${normalizedReason}. Nota: ${normalizedNote}`.slice(0, 500)
}

// -- AdjustDrawer --------------------------------------------------------------

function AdjustDrawer({
  item,
  onClose,
  onSaved,
}: {
  item: StockItem
  onClose: () => void
  onSaved: () => void
}) {
  const [type, setType] = useState<AdjustType>('in')
  const [quantity, setQuantity] = useState('')
  const [reason, setReason] = useState('')
  const [note, setNote] = useState('')
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState('')

  const qty = toNumber(quantity)
  const projected =
    type === 'in'
      ? item.current_stock + qty
      : type === 'out'
        ? item.current_stock - qty
        : qty

  const submit = async (e: React.FormEvent) => {
    e.preventDefault()

    if (!qty || qty <= 0) {
      setError('La cantidad debe ser mayor a 0.')
      return
    }

    if (!reason) {
      setError('Selecciona un motivo.')
      return
    }

    if (type === 'out' && !item.allow_backorder && qty > item.current_stock) {
      setError(`No hay suficiente stock. Disponible: ${item.current_stock} ${item.unit}.`)
      return
    }

    const delta = type === 'in' ? qty : type === 'out' ? -qty : qty - item.current_stock
    if (delta === 0) {
      setError('El ajuste no genera cambios de stock.')
      return
    }

    const fullReason = buildReason(reason, note)
    if (fullReason.length < 5) {
      setError('El motivo debe tener al menos 5 caracteres.')
      return
    }

    setSaving(true)
    setError('')

    try {
      await API.post('/inventory/adjust', {
        product_id: item.product_id,
        delta,
        reason: fullReason,
      })

      onSaved()
      onClose()
    } catch (err: any) {
      setError(err?.response?.data?.message || 'Error al registrar el movimiento.')
    } finally {
      setSaving(false)
    }
  }

  return (
    <div className="fixed inset-0 z-50 flex justify-end">
      <div className="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" onClick={onClose} />

      <div className="relative z-10 flex h-full w-full max-w-md flex-col bg-white shadow-2xl dark:bg-slate-900">
        <div className="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-white/10">
          <div>
            <h2 className="text-[16px] font-semibold text-slate-900 dark:text-white">Ajuste de stock</h2>
            <p className="text-[12px] text-slate-500 dark:text-white/40">{item.product_name}</p>
          </div>

          <button
            onClick={onClose}
            className="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-white/10"
            aria-label="Cerrar"
          >
            <X size={16} />
          </button>
        </div>

        <form onSubmit={submit} className="flex flex-1 flex-col space-y-5 overflow-y-auto p-6">
          <div className="grid grid-cols-2 gap-2">
            <div className="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
              <p className="text-[11px] uppercase tracking-wider text-slate-400 dark:text-white/30">Stock actual</p>
              <p className="mt-1 text-[22px] font-black text-slate-900 dark:text-white">{item.current_stock}</p>
            </div>

            <div className="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
              <p className="text-[11px] uppercase tracking-wider text-slate-400 dark:text-white/30">Stock proyectado</p>
              <p className={`mt-1 text-[22px] font-black ${projected < 0 ? 'text-red-500' : 'text-slate-900 dark:text-white'}`}>
                {projected}
              </p>
            </div>
          </div>

          <div>
            <p className="mb-2 text-[13px] font-medium text-slate-700 dark:text-white/70">Tipo de movimiento</p>
            <div className="grid grid-cols-1 gap-2 sm:grid-cols-3">
              {MOVEMENT_TYPES.map((movementType) => {
                const active = type === movementType.value
                return (
                <button
                  key={movementType.value}
                  type="button"
                  onClick={() => {
                    setType(movementType.value)
                    setReason('')
                  }}
                  className={`rounded-xl border px-3 py-2.5 text-left transition-all ${
                    active
                      ? 'border-orange-400 bg-orange-50 text-orange-700 dark:border-orange-500/50 dark:bg-orange-500/10 dark:text-orange-300'
                      : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-white/10 dark:bg-white/5 dark:text-white/60'
                  }`}
                >
                  <div className="flex items-center gap-2">
                    {movementType.value === 'in' && <ArrowUpCircle size={15} />}
                    {movementType.value === 'out' && <ArrowDownCircle size={15} />}
                    {movementType.value === 'adjustment' && <SlidersHorizontal size={15} />}
                    <span className="text-[12px] font-semibold">{movementType.label}</span>
                  </div>
                  <p className="mt-1 text-[11px] opacity-80">{movementType.hint}</p>
                </button>
              )})}
            </div>
          </div>

          <div>
            <label className="mb-1.5 block text-[13px] font-medium text-slate-700 dark:text-white/70">
              {type === 'adjustment' ? 'Nuevo stock total' : 'Cantidad'}
            </label>
            <input
              type="number"
              min={1}
              value={quantity}
              onChange={(e) => setQuantity(e.target.value)}
              placeholder="0"
              className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-[18px] font-bold text-slate-900 outline-none transition-colors focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
            />
            {qty > 0 && (
              <p className={`mt-1.5 text-[12px] font-medium ${projected < 0 ? 'text-red-500' : 'text-slate-500 dark:text-white/40'}`}>
                Resultado: <strong>{projected} {item.unit}</strong>
              </p>
            )}
          </div>

          <div>
            <label className="mb-1.5 block text-[13px] font-medium text-slate-700 dark:text-white/70">Motivo</label>
            <select
              value={reason}
              onChange={(e) => setReason(e.target.value)}
              className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-[13px] text-slate-900 outline-none transition-colors focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
            >
              <option value="">-- Seleccionar motivo --</option>
              {REASONS[type].map((reasonOption) => (
                <option key={reasonOption} value={reasonOption}>{reasonOption}</option>
              ))}
            </select>
          </div>

          <div>
            <label className="mb-1.5 block text-[13px] font-medium text-slate-700 dark:text-white/70">
              Nota interna <span className="text-slate-400">(opcional)</span>
            </label>
            <textarea
              rows={2}
              value={note}
              onChange={(e) => setNote(e.target.value)}
              placeholder="Detalle adicional para auditoria interna..."
              className="w-full resize-none rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-[13px] text-slate-700 outline-none transition-colors focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
            />
          </div>

          {error && (
            <p className="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-[12px] text-red-600 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">
              {error}
            </p>
          )}

          <div className="mt-auto flex gap-2 pt-4">
            <Button type="button" variant="outline" className="flex-1" onClick={onClose}>Cancelar</Button>
            <Button type="submit" className="flex-1" loading={saving}>
              {saving ? 'Guardando...' : 'Guardar ajuste'}
            </Button>
          </div>
        </form>
      </div>
    </div>
  )
}

// -- KardexDrawer --------------------------------------------------------------

function KardexDrawer({
  item,
  onClose,
}: {
  item: StockItem
  onClose: () => void
}) {
  const [movements, setMovements] = useState<Movement[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const loadMovements = async () => {
      setLoading(true)
      try {
        const { data } = await API.get('/inventory/movements', {
          params: {
            product_id: item.product_id,
            per_page: 50,
          },
        })

        const payload = data as ApiMovementResponse | ApiMovementItem[]
        const rows = Array.isArray(payload) ? payload : Array.isArray(payload?.data) ? payload.data : []
        setMovements(rows.map(normalizeMovement))
      } catch {
        setMovements([])
      } finally {
        setLoading(false)
      }
    }

    loadMovements()
  }, [item.product_id])

  const directionLabel: Record<Movement['direction'], string> = {
    in: 'Entrada',
    out: 'Salida',
    adjust: 'Ajuste',
  }

  const directionStyle: Record<Movement['direction'], string> = {
    in: 'text-emerald-600 dark:text-emerald-400',
    out: 'text-red-500 dark:text-red-400',
    adjust: 'text-blue-600 dark:text-blue-400',
  }

  return (
    <div className="fixed inset-0 z-50 flex justify-end">
      <div className="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" onClick={onClose} />

      <div className="relative z-10 flex h-full w-full max-w-md flex-col bg-white shadow-2xl dark:bg-slate-900">
        <div className="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-white/10">
          <div>
            <h2 className="text-[16px] font-semibold text-slate-900 dark:text-white">Historial de movimientos</h2>
            <p className="text-[12px] text-slate-500 dark:text-white/40">{item.product_name}</p>
          </div>

          <button
            onClick={onClose}
            className="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-white/10"
            aria-label="Cerrar"
          >
            <X size={16} />
          </button>
        </div>

        <div className="flex-1 space-y-2 overflow-y-auto p-4">
          {loading ? (
            <div className="flex flex-col items-center justify-center py-14 text-slate-400 dark:text-white/40">
              <Loader2 className="mb-2 h-6 w-6 animate-spin" />
              <p className="text-[13px]">Cargando kardex...</p>
            </div>
          ) : movements.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-14 text-slate-400 dark:text-white/30">
              <History className="mb-2 h-8 w-8" />
              <p className="text-[13px]">No hay movimientos registrados</p>
            </div>
          ) : (
            movements.map((movement) => (
              <div key={movement.id} className="rounded-2xl border border-slate-100 p-3 dark:border-white/10">
                <div className="flex items-start justify-between gap-2">
                  <div>
                    <p className={`text-[13px] font-semibold ${directionStyle[movement.direction]}`}>
                      {directionLabel[movement.direction]}
                    </p>
                    <p className="text-[12px] text-slate-500 dark:text-white/50">{movement.reason}</p>
                  </div>

                  <span className="text-[11px] text-slate-400 dark:text-white/30">
                    {fmtDate(movement.created_at)}
                  </span>
                </div>

                <div className="mt-2 flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 dark:bg-white/5">
                  <p className="text-[12px] text-slate-500 dark:text-white/50">Cantidad</p>
                  <p className={`text-[14px] font-bold ${directionStyle[movement.direction]}`}>
                    {movement.direction === 'out' ? '-' : '+'}{movement.quantity} {item.unit}
                  </p>
                </div>

                <p className="mt-2 text-[11px] text-slate-400 dark:text-white/30">Registrado por: {movement.user}</p>
                {movement.note && movement.note !== movement.reason && (
                  <p className="mt-1 text-[11px] italic text-slate-400 dark:text-white/30">{movement.note}</p>
                )}
              </div>
            ))
          )}
        </div>
      </div>
    </div>
  )
}

// -- Page ----------------------------------------------------------------------

export default function DashboardInventoryPage() {
  const [items, setItems] = useState<StockItem[]>([])
  const [stats, setStats] = useState<InventoryStats | null>(null)
  const [loading, setLoading] = useState(true)
  const [loadError, setLoadError] = useState('')
  const [search, setSearch] = useState('')
  const [statusFilter, setStatusFilter] = useState<StockStatus>('all')
  const [adjustItem, setAdjustItem] = useState<StockItem | null>(null)
  const [kardexItem, setKardexItem] = useState<StockItem | null>(null)
  const [sortBy, setSortBy] = useState<SortBy>('name')

  const load = async () => {
    setLoading(true)
    setLoadError('')

    try {
      const { data } = await API.get('/inventory/summary', {
        params: {
          per_page: 200,
        },
      })

      const payload = data as ApiSummaryResponse | ApiSummaryItem[]
      const rows = Array.isArray(payload) ? payload : Array.isArray(payload?.data) ? payload.data : []
      const mapped = rows.map(normalizeSummaryItem)

      setItems(mapped)

      if (!Array.isArray(payload) && payload?.stats) {
        setStats({
          total_products: toNumber(payload.stats.total_products),
          low_stock_products: toNumber(payload.stats.low_stock_products),
          out_of_stock_products: toNumber(payload.stats.out_of_stock_products),
          inventory_value: toNumber(payload.stats.inventory_value),
        })
      } else {
        setStats(null)
      }
    } catch (err: any) {
      const status = err?.response?.status
      const message = err?.response?.data?.message

      if (status === 404) {
        setLoadError('No existe la ruta GET /api/inventory/summary en el backend.')
      } else {
        setLoadError(message || 'No se pudo cargar el inventario.')
      }
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    load()
  }, [])

  // -- Derived -----------------------------------------------------------------

  const outOfStock = useMemo(() => items.filter((item) => item.current_stock <= 0), [items])
  const lowStock = useMemo(() => items.filter((item) => item.current_stock > 0 && item.current_stock < item.min_stock), [items])

  const totals = useMemo(() => {
    const totalProducts = stats?.total_products || items.length
    const lowProducts = stats?.low_stock_products ?? lowStock.length
    const outProducts = stats?.out_of_stock_products ?? outOfStock.length
    const normalProducts = Math.max(0, totalProducts - lowProducts - outProducts)
    const inventoryValue = stats?.inventory_value ?? items.reduce((acc, item) => acc + item.price * item.current_stock, 0)

    return {
      totalProducts,
      lowProducts,
      outProducts,
      normalProducts,
      inventoryValue,
    }
  }, [items, lowStock.length, outOfStock.length, stats])

  const displayed = useMemo(() => {
    const query = search.trim().toLowerCase()

    const filtered = items.filter((item) => {
      const matchesSearch =
        !query ||
        item.product_name.toLowerCase().includes(query) ||
        item.sku.toLowerCase().includes(query)

      const matchesStatus =
        statusFilter === 'all' ||
        (statusFilter === 'out' && item.current_stock <= 0) ||
        (statusFilter === 'low' && item.current_stock > 0 && item.current_stock < item.min_stock) ||
        (statusFilter === 'ok' && item.current_stock >= item.min_stock)

      return matchesSearch && matchesStatus
    })

    return filtered.sort((a, b) => {
      if (sortBy === 'stock_asc') return a.current_stock - b.current_stock
      if (sortBy === 'stock_desc') return b.current_stock - a.current_stock
      if (sortBy === 'deficit') return b.deficit - a.deficit
      return a.product_name.localeCompare(b.product_name)
    })
  }, [items, search, sortBy, statusFilter])

  const statusFilters: Array<{ key: StockStatus; label: string; count: number }> = [
    { key: 'all', label: 'Todos', count: items.length },
    { key: 'out', label: 'Agotados', count: outOfStock.length },
    { key: 'low', label: 'Bajo', count: lowStock.length },
    { key: 'ok', label: 'Normal', count: Math.max(0, items.length - outOfStock.length - lowStock.length) },
  ]

  return (
    <div className="space-y-6">
      <div className="relative overflow-hidden rounded-3xl border border-slate-200 bg-[linear-gradient(135deg,#0F172A_0%,#1E293B_55%,#111827_100%)] p-6 text-white shadow-[0_18px_45px_rgba(15,23,42,0.28)]">
        <div className="pointer-events-none absolute -left-24 -top-16 h-52 w-52 rounded-full bg-orange-500/20 blur-3xl" />
        <div className="pointer-events-none absolute -right-12 -bottom-20 h-56 w-56 rounded-full bg-cyan-400/15 blur-3xl" />

        <div className="relative z-10 flex flex-wrap items-end justify-between gap-4">
          <div>
            <p className="text-[12px] uppercase tracking-[0.2em] text-slate-300">Dashboard</p>
            <h1 className="mt-1 font-display text-[30px] font-black tracking-tight">Inventario</h1>
            <p className="mt-1 text-[13px] text-slate-300">
              Control de stock, ajustes manuales y trazabilidad por producto.
            </p>
          </div>

          <div className="flex flex-wrap items-center gap-2">
            <span className="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-[12px] font-semibold backdrop-blur">
              <Boxes size={14} /> Valor actual: {fmtCurrency(totals.inventoryValue)}
            </span>
            <button
              onClick={load}
              className="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-3 py-2 text-[12px] font-semibold text-white transition-colors hover:bg-white/20"
            >
              <RefreshCw size={14} /> Recargar
            </button>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div className="rounded-2xl border border-slate-200/90 bg-gradient-to-br from-white via-slate-50 to-slate-100/80 px-4 py-3 shadow-[0_8px_22px_rgba(15,23,42,0.06)] dark:border-white/10 dark:bg-white/5">
          <p className="text-[11px] uppercase tracking-wider text-slate-400 dark:text-white/30">Total SKU</p>
          <p className="mt-1 text-[30px] font-black text-slate-900 dark:text-white">{totals.totalProducts}</p>
        </div>

        <div className="rounded-2xl border border-red-200 bg-gradient-to-br from-red-50 to-rose-50 px-4 py-3 shadow-[0_8px_22px_rgba(185,28,28,0.08)] dark:border-red-500/20 dark:bg-red-500/10">
          <p className="text-[11px] uppercase tracking-wider text-red-600/70 dark:text-red-300/70">Agotados</p>
          <p className="mt-1 text-[30px] font-black text-red-600 dark:text-red-300">{totals.outProducts}</p>
        </div>

        <div className="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 px-4 py-3 shadow-[0_8px_22px_rgba(217,119,6,0.08)] dark:border-amber-500/20 dark:bg-amber-500/10">
          <p className="text-[11px] uppercase tracking-wider text-amber-700/70 dark:text-amber-300/70">Stock bajo</p>
          <p className="mt-1 text-[30px] font-black text-amber-600 dark:text-amber-300">{totals.lowProducts}</p>
        </div>

        <div className="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-green-50 px-4 py-3 shadow-[0_8px_22px_rgba(5,150,105,0.08)] dark:border-emerald-500/20 dark:bg-emerald-500/10">
          <p className="text-[11px] uppercase tracking-wider text-emerald-700/70 dark:text-emerald-300/70">Normales</p>
          <p className="mt-1 text-[30px] font-black text-emerald-600 dark:text-emerald-300">{totals.normalProducts}</p>
        </div>
      </div>

      {!loadError && (totals.outProducts > 0 || totals.lowProducts > 0) && (
        <div className="grid gap-2 sm:grid-cols-2">
          {totals.outProducts > 0 && (
            <button
              onClick={() => setStatusFilter('out')}
              className="flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-left text-[13px] text-red-700 transition-colors hover:bg-red-100 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300"
            >
              <AlertTriangle size={16} />
              <span>
                <strong>{totals.outProducts} producto{totals.outProducts !== 1 ? 's' : ''} agotado{totals.outProducts !== 1 ? 's' : ''}.</strong>
                {' '}Haz clic para filtrar.
              </span>
            </button>
          )}

          {totals.lowProducts > 0 && (
            <button
              onClick={() => setStatusFilter('low')}
              className="flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-left text-[13px] text-amber-700 transition-colors hover:bg-amber-100 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300"
            >
              <AlertTriangle size={16} />
              <span>
                <strong>{totals.lowProducts} producto{totals.lowProducts !== 1 ? 's' : ''} con stock bajo.</strong>
                {' '}Haz clic para filtrar.
              </span>
            </button>
          )}
        </div>
      )}

      {loadError && (
        <div className="flex items-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
          <AlertCircle size={16} />
          <span>{loadError}</span>
          <button onClick={load} className="ml-auto text-[12px] font-semibold underline">Reintentar</button>
        </div>
      )}

      <GlassCard className="overflow-hidden border border-slate-200/90 bg-gradient-to-br from-white via-white to-slate-50/70 p-0 shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
        <div className="flex flex-wrap items-center gap-3 border-b border-slate-200 px-5 py-4 dark:border-white/10">
          <div className="relative min-w-[220px] flex-1">
            <Search size={15} className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              type="search"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Buscar por nombre o referencia..."
              className="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-[13px] text-slate-900 outline-none transition-colors placeholder:text-slate-400 focus:border-orange-400 focus:ring-2 focus:ring-orange-400/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-white/30"
            />
          </div>

          <div className="flex flex-wrap gap-1.5">
            {statusFilters.map((filter) => (
              <button
                key={filter.key}
                onClick={() => setStatusFilter(filter.key)}
                className={`rounded-lg border px-3 py-1.5 text-[11px] font-semibold transition-colors ${
                  statusFilter === filter.key
                    ? 'border-orange-500 bg-orange-500 text-white'
                    : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-white/10 dark:bg-white/5 dark:text-white/60 dark:hover:text-white'
                }`}
              >
                {filter.label} ({filter.count})
              </button>
            ))}
          </div>

          <div className="relative">
            <SlidersHorizontal size={14} className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <select
              value={sortBy}
              onChange={(e) => setSortBy(e.target.value as SortBy)}
              className="rounded-xl border border-slate-200 bg-white py-2 pl-8 pr-8 text-[12px] text-slate-600 outline-none focus:border-orange-400 dark:border-white/10 dark:bg-white/5 dark:text-white/60"
            >
              <option value="name">Ordenar: Nombre</option>
              <option value="stock_asc">Ordenar: Stock asc</option>
              <option value="stock_desc">Ordenar: Stock desc</option>
              <option value="deficit">Ordenar: Mayor deficit</option>
            </select>
          </div>
        </div>

        {loading ? (
          <div className="flex flex-col items-center justify-center py-16 text-slate-500 dark:text-white/40">
            <Loader2 className="mb-2 h-7 w-7 animate-spin text-orange-500" />
            <p className="text-[13px]">Cargando inventario...</p>
          </div>
        ) : displayed.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-16 text-slate-400 dark:text-white/30">
            <PackageSearch className="mb-2 h-8 w-8" />
            <p className="text-[13px]">No hay productos para mostrar</p>
            <p className="mt-1 text-[12px] opacity-70">Prueba con otro termino o cambia el filtro.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-slate-100 dark:border-white/5">
                  {['Producto', 'Referencia', 'Stock', 'Minimo', 'Estado', 'Deficit', 'Precio', 'Acciones'].map((header) => (
                    <th key={header} className="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-white/30">
                      {header}
                    </th>
                  ))}
                </tr>
              </thead>

              <tbody>
                {displayed.map((item) => {
                  const status = getStatus(item.current_stock, item.min_stock)
                  const stockRatio = item.min_stock > 0 ? Math.min(100, Math.round((item.current_stock / item.min_stock) * 100)) : item.current_stock > 0 ? 100 : 0

                  return (
                    <tr key={item.product_id} className="group border-b border-slate-100 transition-colors hover:bg-slate-50/80 dark:border-white/5 dark:hover:bg-white/5">
                      <td className="py-3 pl-4 pr-2">
                        <p className="text-[13px] font-semibold text-slate-900 dark:text-white">{item.product_name}</p>
                      </td>

                      <td className="px-3 py-3">
                        <span className="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 font-mono text-[11px] text-slate-600 dark:border-white/10 dark:bg-white/5 dark:text-white/50">
                          {item.sku}
                        </span>
                      </td>

                      <td className="px-3 py-3">
                        <div className="min-w-[90px]">
                          <p className={`text-[18px] font-black ${item.current_stock <= 0 ? 'text-red-500' : item.current_stock < item.min_stock ? 'text-amber-500' : 'text-slate-900 dark:text-white'}`}>
                            {item.current_stock}
                            <span className="ml-1 text-[11px] font-medium text-slate-400 dark:text-white/30">{item.unit}</span>
                          </p>
                          <div className="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-200 dark:bg-white/10">
                            <div
                              className={`h-full rounded-full ${item.current_stock <= 0 ? 'bg-red-500' : item.current_stock < item.min_stock ? 'bg-amber-500' : 'bg-emerald-500'}`}
                              style={{ width: `${stockRatio}%` }}
                            />
                          </div>
                        </div>
                      </td>

                      <td className="px-3 py-3 text-[13px] text-slate-500 dark:text-white/50">{item.min_stock} {item.unit}</td>

                      <td className="px-3 py-3">
                        <Badge variant={status.variant}>{status.label}</Badge>
                      </td>

                      <td className="px-3 py-3 text-[13px]">
                        <span className={item.deficit > 0 ? 'font-semibold text-red-500' : 'text-slate-400 dark:text-white/30'}>
                          {item.deficit > 0 ? `${item.deficit} ${item.unit}` : '-'}
                        </span>
                      </td>

                      <td className="px-3 py-3 text-[13px] font-semibold text-slate-700 dark:text-white/80">
                        {fmtCurrency(item.price)}
                      </td>

                      <td className="pl-3 pr-4 py-3">
                        <div className="flex items-center gap-1.5">
                          <button
                            onClick={() => setAdjustItem(item)}
                            className="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600 transition-colors hover:border-orange-300 hover:text-orange-600 dark:border-white/10 dark:bg-white/5 dark:text-white/60 dark:hover:text-orange-300"
                          >
                            <SlidersHorizontal size={12} /> Ajustar
                          </button>

                          <button
                            onClick={() => setKardexItem(item)}
                            className="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600 transition-colors hover:border-slate-300 dark:border-white/10 dark:bg-white/5 dark:text-white/60 dark:hover:text-white"
                          >
                            <ClipboardList size={12} /> Kardex
                          </button>
                        </div>
                      </td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          </div>
        )}

        {displayed.length > 0 && (
          <div className="flex items-center justify-between border-t border-slate-100 px-5 py-3 text-[12px] text-slate-400 dark:border-white/5 dark:text-white/30">
            <span>Mostrando {displayed.length} de {items.length} productos cargados</span>
            <span className="inline-flex items-center gap-1.5">
              <Boxes size={13} /> {statusFilter === 'all' ? 'Vista general' : `Filtro: ${statusFilters.find((filter) => filter.key === statusFilter)?.label || 'Personalizado'}`}
            </span>
          </div>
        )}
      </GlassCard>

      {adjustItem && (
        <AdjustDrawer item={adjustItem} onClose={() => setAdjustItem(null)} onSaved={load} />
      )}
      {kardexItem && (
        <KardexDrawer item={kardexItem} onClose={() => setKardexItem(null)} />
      )}
    </div>
  )
}


