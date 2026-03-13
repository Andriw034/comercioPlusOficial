import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import {
  AlertCircle,
  AlertTriangle,
  ArrowDownCircle,
  ArrowUpCircle,
  History,
  Loader2,
  PackageSearch,
  RefreshCw,
  SlidersHorizontal,
  X,
} from 'lucide-react'
import API from '@/lib/api'
import { ErpBadge, ErpBtn, ErpFilterSelect, ErpKpiCard, ErpPageHeader, ErpSearchBar } from '@/components/erp'
import useDebouncedValue from '@/hooks/useDebouncedValue'

// -- Types ---------------------------------------------------------------------

interface ApiSummaryItem {
  id?: number | string
  name?: string | null
  slug?: string | null
  sku?: string | null
  ref_adicional?: string | null
  unit?: string | null
  stock?: number | string | null
  reorder_point?: number | string | null
  stock_status?: string | null
  allow_backorder?: boolean | number | string | null
  cost_price?: number | string | null
  sale_price?: number | string | null
  price_with_iva?: number | string | null
  total_cost?: number | string | null
  total_sale?: number | string | null
  total_sale_with_iva?: number | string | null
  price?: number | string | null
  deficit?: number | string | null
}

interface ApiInventoryStats {
  total_products?: number
  products_with_inventory?: number
  normal_stock_products?: number
  low_stock_products?: number
  out_of_stock_products?: number
  inventory_value?: number
  inventory_total_value?: number
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
  product_code: string
  ref_adicional: string
  unit: string
  current_stock: number
  reorder_point: number
  allow_backorder: boolean
  cost_price: number
  sale_price: number
  sale_price_with_iva: number
  total_cost: number
  total_sale: number
  status: 'normal' | 'low' | 'out'
  deficit: number
}

interface InventoryStats {
  total_products: number
  products_with_inventory: number
  normal_stock_products: number
  low_stock_products: number
  out_of_stock_products: number
  inventory_total_value: number
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

const INVENTORY_CACHE_TTL_MS = 60_000

let inventoryCache: {
  updatedAt: number
  items: StockItem[]
  stats: InventoryStats | null
} | null = null

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
  const costPrice = Math.max(0, toNumber(item.cost_price))
  const salePrice = Math.max(0, toNumber(item.sale_price, toNumber(item.price)))
  const salePriceWithIva = Math.max(0, toNumber(item.price_with_iva, salePrice * 1.19))
  const statusToken = String(item.stock_status || '').toLowerCase()
  const status: StockItem['status'] =
    statusToken === 'agotado'
      ? 'out'
      : statusToken === 'bajo'
        ? 'low'
        : getStatus(currentStock, minStock)

  return {
    product_id: id,
    product_name: (item.name || '').trim() || `Producto #${id || 'N/A'}`,
    product_code: ((item.sku || item.slug || '').trim() || `PROD-${id || 'NA'}`).toUpperCase(),
    ref_adicional: (item.ref_adicional || '').trim(),
    unit: ((item.unit || '').trim() || 'UND').toUpperCase(),
    current_stock: currentStock,
    reorder_point: minStock,
    allow_backorder: toBoolean(item.allow_backorder),
    cost_price: costPrice,
    sale_price: salePrice,
    sale_price_with_iva: salePriceWithIva,
    total_cost: Math.max(0, toNumber(item.total_cost, costPrice * currentStock)),
    total_sale: Math.max(0, toNumber(item.total_sale_with_iva, salePriceWithIva * currentStock)),
    status,
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

function getStatus(current: number, min: number): 'out' | 'low' | 'normal' {
  if (current <= 0) return 'out'
  if (current <= min) return 'low'
  return 'normal'
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
      console.error('[inventory] Error al registrar ajuste:', err)
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
            <ErpBtn type="button" variant="secondary" className="flex-1 justify-center" onClick={onClose}>
              Cancelar
            </ErpBtn>
            <ErpBtn type="submit" variant="primary" className="flex-1 justify-center" disabled={saving}>
              {saving ? 'Guardando...' : 'Guardar ajuste'}
            </ErpBtn>
          </div>
        </form>
      </div>
    </div>
  )
}

// -- MovementsDrawer -----------------------------------------------------------

function MovementsDrawer({
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
      } catch (err) {
        console.error('[inventory] Error al cargar movimientos:', err)
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
              <p className="text-[13px]">Cargando movimientos...</p>
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
  const [searchInput, setSearchInput] = useState('')
  const [statusFilter, setStatusFilter] = useState<StockStatus>('all')
  const [adjustItem, setAdjustItem] = useState<StockItem | null>(null)
  const [movementItem, setMovementItem] = useState<StockItem | null>(null)
  const [sortBy, setSortBy] = useState<SortBy>('name')
  const [selectedIds, setSelectedIds] = useState<Set<number>>(new Set())
  const [deleting, setDeleting] = useState(false)
  const debouncedSearch = useDebouncedValue(searchInput, 350)

  const load = async (force = false) => {
    if (!force && inventoryCache && Date.now() - inventoryCache.updatedAt < INVENTORY_CACHE_TTL_MS) {
      setItems(inventoryCache.items)
      setStats(inventoryCache.stats)
      setLoadError('')
      setLoading(false)
      return
    }

    setLoading(true)
    setLoadError('')

    try {
      const [{ data: summaryData }, statsResponse] = await Promise.all([
        API.get('/inventory/summary', {
          params: {
            per_page: 200,
          },
        }),
        API.get('/inventory/stats').catch(() => null),
      ])

      const payload = summaryData as ApiSummaryResponse | ApiSummaryItem[]
      const rows = Array.isArray(payload) ? payload : Array.isArray(payload?.data) ? payload.data : []
      const mapped = rows.map(normalizeSummaryItem)

      setItems(mapped)
      setSelectedIds((prev) => {
        if (prev.size === 0) return prev
        const validIds = new Set(mapped.map((item) => item.product_id))
        const next = new Set<number>()
        prev.forEach((id) => {
          if (validIds.has(id)) next.add(id)
        })
        return next
      })

      let statsSource: ApiInventoryStats | null = null
      if (!Array.isArray(payload) && payload?.stats) {
        statsSource = payload.stats
      }

      const statsPayload = (statsResponse?.data || null) as
        | { data?: ApiInventoryStats }
        | ApiInventoryStats
        | null

      if (statsPayload && typeof statsPayload === 'object') {
        const backendStats = 'data' in statsPayload && statsPayload.data ? statsPayload.data : statsPayload
        if (backendStats) {
          statsSource = {
            ...statsSource,
            ...backendStats,
          }
        }
      }

      let nextStats: InventoryStats | null = null
      if (statsSource) {
        nextStats = {
          total_products: toNumber(statsSource.total_products),
          products_with_inventory: toNumber(statsSource.products_with_inventory, toNumber(statsSource.total_products)),
          normal_stock_products: toNumber(statsSource.normal_stock_products),
          low_stock_products: toNumber(statsSource.low_stock_products),
          out_of_stock_products: toNumber(statsSource.out_of_stock_products),
          inventory_total_value: toNumber(statsSource.inventory_total_value, toNumber(statsSource.inventory_value)),
        }
      }

      setStats(nextStats)
      inventoryCache = {
        updatedAt: Date.now(),
        items: mapped,
        stats: nextStats,
      }
    } catch (err: any) {
      const status = err?.response?.status
      const message = err?.response?.data?.message
      console.error('[inventory] Error al cargar inventario:', err)

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

  const outOfStock = useMemo(() => items.filter((item) => item.status === 'out'), [items])
  const lowStock = useMemo(() => items.filter((item) => item.status === 'low'), [items])
  const normalStock = useMemo(() => items.filter((item) => item.status === 'normal'), [items])

  const totals = useMemo(() => {
    const totalProducts = stats?.total_products || items.length
    const productsWithInventory = Math.max(
      0,
      stats?.products_with_inventory ?? items.filter((item) => item.current_stock > 0).length,
    )
    const outProducts = Math.max(0, stats?.out_of_stock_products ?? outOfStock.length)
    const lowProducts = Math.max(0, stats?.low_stock_products ?? lowStock.length)
    const normalProducts = Math.max(
      0,
      stats?.normal_stock_products ?? normalStock.length ?? (productsWithInventory - lowProducts),
    )
    const inventoryValue = stats?.inventory_total_value ?? items.reduce((acc, item) => acc + item.total_cost, 0)

    return {
      totalProducts,
      productsWithInventory,
      lowProducts,
      outProducts,
      normalProducts,
      inventoryValue,
    }
  }, [items, lowStock.length, normalStock.length, outOfStock.length, stats])

  const displayed = useMemo(() => {
    const query = debouncedSearch.trim().toLowerCase()

    const filtered = items.filter((item) => {
      const matchesSearch =
        !query ||
        item.product_name.toLowerCase().includes(query) ||
        item.product_code.toLowerCase().includes(query) ||
        item.ref_adicional.toLowerCase().includes(query)

      const matchesStatus =
        statusFilter === 'all' ||
        (statusFilter === 'out' && item.status === 'out') ||
        (statusFilter === 'low' && item.status === 'low') ||
        (statusFilter === 'ok' && item.status === 'normal')

      return matchesSearch && matchesStatus
    })

    return filtered.sort((a, b) => {
      if (sortBy === 'stock_asc') return a.current_stock - b.current_stock
      if (sortBy === 'stock_desc') return b.current_stock - a.current_stock
      if (sortBy === 'deficit') return b.deficit - a.deficit
      return a.product_name.localeCompare(b.product_name)
    })
  }, [debouncedSearch, items, sortBy, statusFilter])

  const statusFilters: Array<{ key: StockStatus; label: string; count: number }> = [
    { key: 'all', label: 'Todos', count: items.length },
    { key: 'out', label: 'Agotados', count: outOfStock.length },
    { key: 'low', label: 'Stock bajo', count: lowStock.length },
    { key: 'ok', label: 'Stock normal', count: normalStock.length },
  ]

  const statusFilterValue = statusFilter === 'all' ? '' : statusFilter
  const statusFilterOptions: Array<{ value: string; label: string }> = statusFilters
    .filter((filter) => filter.key !== 'all')
    .map((filter) => ({
      value: filter.key,
      label: `${filter.label} (${filter.count})`,
    }))

  const sortOptions: Array<{ value: string; label: string }> = [
    { value: 'name', label: 'Nombre (A-Z)' },
    { value: 'stock_asc', label: 'Stock ascendente' },
    { value: 'stock_desc', label: 'Stock descendente' },
    { value: 'deficit', label: 'Mayor faltante' },
  ]

  const displayedIds = useMemo(() => displayed.map((item) => item.product_id), [displayed])
  const selectedCount = selectedIds.size
  const allDisplayedSelected = displayedIds.length > 0 && displayedIds.every((id) => selectedIds.has(id))

  const toggleSelectAllDisplayed = () => {
    setSelectedIds((prev) => {
      const next = new Set(prev)
      const everySelected = displayedIds.length > 0 && displayedIds.every((id) => next.has(id))

      if (everySelected) {
        displayedIds.forEach((id) => next.delete(id))
      } else {
        displayedIds.forEach((id) => next.add(id))
      }

      return next
    })
  }

  const toggleSelectOne = (productId: number) => {
    setSelectedIds((prev) => {
      const next = new Set(prev)
      if (next.has(productId)) {
        next.delete(productId)
      } else {
        next.add(productId)
      }
      return next
    })
  }

  const deleteInventory = async (deleteAll: boolean) => {
    if (deleting) return

    if (!deleteAll && selectedCount <= 0) {
      alert('Selecciona al menos un producto para eliminar.')
      return
    }

    let payload: Record<string, unknown>
    if (deleteAll) {
      const confirmToken = window.prompt('Escribe ELIMINAR para borrar TODO el inventario de esta tienda:')
      if (confirmToken === null) return
      if (confirmToken.trim().toUpperCase() !== 'ELIMINAR') {
        alert('Confirmacion invalida. No se elimino inventario.')
        return
      }

      if (!window.confirm('Se borrara todo el inventario de esta tienda. Esta accion no se puede deshacer.')) {
        return
      }

      payload = { all: true, confirm: 'ELIMINAR' }
    } else {
      if (!window.confirm(`Se eliminaran ${selectedCount} producto(s) seleccionados. Deseas continuar?`)) {
        return
      }

      payload = { ids: Array.from(selectedIds) }
    }

    setDeleting(true)
    try {
      const { data } = await API.post('/inventory/bulk-delete', payload)
      alert(data?.message || 'Inventario actualizado correctamente.')
      setSelectedIds(new Set())
      await load(true)
    } catch (err: any) {
      const message = err?.response?.data?.message || 'No se pudo eliminar inventario.'
      alert(message)
    } finally {
      setDeleting(false)
    }
  }

  return (
    <div className="space-y-5">
      <ErpPageHeader
        breadcrumb="Dashboard"
        title="Inventario"
        subtitle="Control de stock en tiempo real"
        actions={
          <>
            <ErpBtn variant="secondary" size="md" icon={<RefreshCw size={14} />} onClick={() => load(true)}>
              Recargar
            </ErpBtn>
            <Link
              to="/dashboard/inventory/receive"
              className="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-[13px] font-semibold text-white transition-colors hover:bg-orange-600"
            >
              <ArrowUpCircle size={14} />
              + Movimiento
            </Link>
          </>
        }
      />

      {!loadError && (totals.outProducts > 0 || totals.lowProducts > 0) && (
        <div className="flex flex-wrap items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-[13px] text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">
          <AlertTriangle size={16} className="shrink-0" />
          <div className="flex-1">
            <p className="font-semibold">
              {totals.outProducts + totals.lowProducts} productos requieren reposicion
            </p>
            <p className="text-[12px] opacity-80">
              {totals.outProducts} agotado(s) y {totals.lowProducts} con stock bajo.
            </p>
          </div>
          <ErpBtn
            variant="secondary"
            size="sm"
            onClick={() => setStatusFilter(totals.outProducts > 0 ? 'out' : 'low')}
            className="!border-amber-300 !bg-white !text-amber-700"
          >
            Ver criticos
          </ErpBtn>
        </div>
      )}

      <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
        <ErpKpiCard
          label="Productos con inventario"
          value={totals.productsWithInventory}
          hint={`Total catalogo: ${totals.totalProducts}`}
          icon="package"
          iconBg="rgba(59,130,246,0.12)"
          iconColor="#3B82F6"
        />
        <ErpKpiCard
          label="Stock normal"
          value={totals.normalProducts}
          hint="Sin riesgo inmediato"
          icon="check-circle"
          iconBg="rgba(16,185,129,0.12)"
          iconColor="#10B981"
        />
        <ErpKpiCard
          label="Stock bajo"
          value={totals.lowProducts}
          hint="Requiere reposicion"
          icon="alert"
          iconBg="rgba(245,158,11,0.12)"
          iconColor="#F59E0B"
        />
        <ErpKpiCard
          label="Agotados"
          value={totals.outProducts}
          hint="Sin unidades disponibles"
          icon="x-circle"
          iconBg="rgba(239,68,68,0.12)"
          iconColor="#EF4444"
        />
        <ErpKpiCard
          label="Valor inventario total"
          value={fmtCurrency(totals.inventoryValue)}
          hint="Valorizado en COP"
          icon="dollar"
          iconBg="rgba(255,161,79,0.12)"
          iconColor="#FFA14F"
        />
      </div>

      {loadError && (
        <div className="flex items-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
          <AlertCircle size={16} />
          <span>{loadError}</span>
          <ErpBtn variant="ghost" size="sm" className="ml-auto !text-red-700" onClick={() => load(true)}>
            Reintentar
          </ErpBtn>
        </div>
      )}

      <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_8px_24px_rgba(15,23,42,0.06)] dark:border-white/10 dark:bg-white/5">
        <div className="flex flex-wrap items-center gap-3 border-b border-slate-100 px-4 py-3.5 dark:border-white/10">
          <div className="min-w-[220px] flex-1">
            <ErpSearchBar
              value={searchInput}
              onChange={(value: string) => setSearchInput(value)}
              placeholder="Buscar por producto o referencia..."
            />
          </div>

          <ErpFilterSelect
            value={statusFilterValue}
            onChange={(value: string) => setStatusFilter((value || 'all') as StockStatus)}
            options={statusFilterOptions}
            placeholder={`Todos (${items.length})`}
          />

          <ErpFilterSelect
            value={sortBy}
            onChange={(value: string) => setSortBy((value || 'name') as SortBy)}
            options={sortOptions}
            placeholder="Ordenar por"
          />

          <ErpBtn
            variant="secondary"
            size="sm"
            onClick={toggleSelectAllDisplayed}
            disabled={displayed.length === 0 || deleting}
          >
            {allDisplayedSelected ? 'Quitar seleccion' : 'Seleccionar todo'}
          </ErpBtn>

          <ErpBtn
            variant="secondary"
            size="sm"
            className="!border-red-300 !text-red-600 hover:!bg-red-50"
            onClick={() => deleteInventory(false)}
            disabled={selectedCount === 0 || deleting}
          >
            {deleting ? 'Eliminando...' : `Eliminar seleccionados (${selectedCount})`}
          </ErpBtn>

          <ErpBtn
            variant="secondary"
            size="sm"
            className="!border-red-500 !text-red-700 hover:!bg-red-50"
            onClick={() => deleteInventory(true)}
            disabled={items.length === 0 || deleting}
          >
            Eliminar todo inventario
          </ErpBtn>
        </div>

        {loading ? (
          <div className="flex flex-col items-center justify-center py-16 text-slate-500 dark:text-white/40">
            <Loader2 className="mb-2 h-7 w-7 animate-spin text-orange-500" />
            <p className="text-[13px]">Cargando inventario...</p>
          </div>
        ) : items.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-16 text-center text-slate-500 dark:text-white/40">
            <PackageSearch className="mb-2 h-8 w-8" />
            <p className="text-[14px] font-semibold">Aun no hay productos en inventario</p>
            <p className="mt-1 text-[12px] opacity-80">Crea tu primer producto para comenzar a gestionar stock.</p>
            <Link
              to="/dashboard/products"
              className="mt-4 inline-flex rounded-xl bg-orange-500 px-4 py-2 text-[12px] font-semibold text-white transition-colors hover:bg-orange-600"
            >
              Ir a Productos
            </Link>
          </div>
        ) : displayed.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-16 text-slate-400 dark:text-white/30">
            <PackageSearch className="mb-2 h-8 w-8" />
            <p className="text-[13px]">No hay resultados con los filtros actuales</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full min-w-[1400px]">
              <thead>
                <tr className="border-b border-slate-100 bg-slate-50/80 dark:border-white/5 dark:bg-white/5">
                  <th className="px-3 py-2.5 text-left">
                    <input
                      type="checkbox"
                      checked={allDisplayedSelected}
                      onChange={toggleSelectAllDisplayed}
                      aria-label="Seleccionar todos los productos visibles"
                    />
                  </th>
                  <th className="px-4 py-2.5 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Producto</th>
                  <th className="px-4 py-2.5 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Codigo</th>
                  <th className="px-4 py-2.5 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Ref.Ad.</th>
                  <th className="px-4 py-2.5 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Unidad</th>
                  <th className="px-4 py-2.5 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Stock</th>
                  <th className="px-4 py-2.5 text-right text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Compra Unit.</th>
                  <th className="px-4 py-2.5 text-right text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Venta s/IVA</th>
                  <th className="px-4 py-2.5 text-right text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Venta c/IVA</th>
                  <th className="px-4 py-2.5 text-right text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Total Compra</th>
                  <th className="px-4 py-2.5 text-right text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Total Venta</th>
                  <th className="px-4 py-2.5 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Estado</th>
                  <th className="px-4 py-2.5 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Acciones</th>
                </tr>
              </thead>
              <tbody>
                {displayed.map((item, index) => {
                  const badgeStatus = item.status === 'out' ? 'critical' : item.status === 'low' ? 'low' : 'ok'
                  const badgeLabel = item.status === 'out' ? 'Agotado' : item.status === 'low' ? 'Stock bajo' : 'Normal'

                  return (
                    <tr
                      key={`${item.product_id}-${index}`}
                      className="border-b border-slate-100 transition-colors hover:bg-slate-50/80 dark:border-white/5 dark:hover:bg-white/5"
                    >
                      <td className="px-3 py-3">
                        <input
                          type="checkbox"
                          checked={selectedIds.has(item.product_id)}
                          onChange={() => toggleSelectOne(item.product_id)}
                          aria-label={`Seleccionar ${item.product_name}`}
                        />
                      </td>
                      <td className="px-4 py-3">
                        <p className="text-[13px] font-semibold text-slate-900 dark:text-white">{item.product_name}</p>
                        <p className="mt-0.5 text-[11px] text-slate-400 dark:text-white/30">Min: {item.reorder_point} {item.unit}</p>
                      </td>
                      <td className="px-4 py-3 font-mono text-[12px] text-slate-700 dark:text-white/70">{item.product_code}</td>
                      <td className="px-4 py-3 text-[12px] text-slate-700 dark:text-white/70">{item.ref_adicional || '-'}</td>
                      <td className="px-4 py-3 text-[12px] font-semibold text-slate-700 dark:text-white/70">{item.unit}</td>
                      <td className="px-4 py-3 text-[13px] font-bold text-slate-900 dark:text-white">{item.current_stock}</td>
                      <td className="px-4 py-3 text-right text-[12px] font-semibold text-slate-700 dark:text-white/70">{fmtCurrency(item.cost_price)}</td>
                      <td className="px-4 py-3 text-right text-[12px] font-semibold text-slate-700 dark:text-white/70">{fmtCurrency(item.sale_price)}</td>
                      <td className="px-4 py-3 text-right text-[12px] font-semibold text-slate-700 dark:text-white/70">{fmtCurrency(item.sale_price_with_iva)}</td>
                      <td className="px-4 py-3 text-right text-[12px] font-semibold text-slate-900 dark:text-white">{fmtCurrency(item.total_cost)}</td>
                      <td className="px-4 py-3 text-right text-[12px] font-semibold text-slate-900 dark:text-white">{fmtCurrency(item.total_sale)}</td>
                      <td className="px-4 py-3">
                        <ErpBadge status={badgeStatus} label={badgeLabel} />
                      </td>
                      <td className="px-4 py-3">
                        <div className="flex items-center gap-2">
                          <ErpBtn
                            variant="primary"
                            size="sm"
                            icon={<SlidersHorizontal size={12} />}
                            onClick={() => setAdjustItem(item)}
                          >
                            Ajustar
                          </ErpBtn>
                          <ErpBtn
                            variant="secondary"
                            size="sm"
                            icon={<History size={12} />}
                            onClick={() => setMovementItem(item)}
                          >
                            Movimientos
                          </ErpBtn>
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
          <div className="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 px-4 py-3 text-[12px] text-slate-500 dark:border-white/5 dark:text-white/40">
            <span>Mostrando {displayed.length} de {items.length} productos | Seleccionados: {selectedCount}</span>
            <span className="font-semibold text-slate-700 dark:text-white/70">
              Valor total inventario: {fmtCurrency(totals.inventoryValue)}
            </span>
          </div>
        )}
      </div>

      {adjustItem && (
        <AdjustDrawer item={adjustItem} onClose={() => setAdjustItem(null)} onSaved={() => load(true)} />
      )}
      {movementItem && (
        <MovementsDrawer item={movementItem} onClose={() => setMovementItem(null)} />
      )}
    </div>
  )
}
