import { useMemo, useState } from 'react'
import { ChevronDown, ChevronUp, History, Loader2, RefreshCw } from 'lucide-react'
import type { InventoryMovementItem } from '@/services/inventoryReceive'

type Props = {
  movements: InventoryMovementItem[]
  loading: boolean
  onRefresh: () => void
}

function formatDate(value?: string | null) {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleString('es-CO', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function mapReason(reason?: string | null) {
  const normalized = (reason || '').trim().toLowerCase()
  if (normalized === 'purchase') return 'Compra'
  if (normalized === 'return') return 'Devolucion'
  if (normalized === 'adjustment') return 'Ajuste'
  return reason || 'Sin motivo'
}

export default function ReceiveHistoryList({ movements, loading, onRefresh }: Props) {
  const [mobileOpen, setMobileOpen] = useState(false)
  const list = useMemo(() => movements.slice(0, 10), [movements])

  return (
    <section className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2">
          <History size={16} className="text-slate-500 dark:text-white/60" />
          <h2 className="text-[15px] font-semibold text-slate-900 dark:text-white">Ultimos ingresos</h2>
        </div>

        <button
          type="button"
          onClick={onRefresh}
          className="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1.5 text-[12px] font-semibold text-slate-700 transition-colors hover:bg-slate-100 dark:border-white/10 dark:text-white/70 dark:hover:bg-white/10"
        >
          {loading ? <Loader2 size={13} className="animate-spin" /> : <RefreshCw size={13} />}
          Actualizar
        </button>
      </div>

      <button
        type="button"
        onClick={() => setMobileOpen((prev) => !prev)}
        className="mt-3 inline-flex w-full items-center justify-between rounded-xl border border-slate-200 px-3 py-2 text-[12px] font-semibold text-slate-700 sm:hidden dark:border-white/10 dark:text-white/70"
      >
        <span>{mobileOpen ? 'Ocultar historial' : 'Ver historial'}</span>
        {mobileOpen ? <ChevronUp size={14} /> : <ChevronDown size={14} />}
      </button>

      <div className={`${mobileOpen ? 'mt-3 block' : 'hidden'} space-y-2 sm:mt-3 sm:block`}>
        {loading && list.length === 0 && (
          <p className="text-[12px] text-slate-500 dark:text-white/50">Cargando historial...</p>
        )}

        {!loading && list.length === 0 && (
          <p className="text-[12px] text-slate-500 dark:text-white/50">Aun no hay ingresos recientes.</p>
        )}

        {list.map((movement) => (
          <article key={movement.id} className="rounded-xl border border-slate-200 p-3 dark:border-white/10">
            <p className="text-[13px] font-semibold text-slate-900 dark:text-white">
              +{movement.quantity} - {movement.product_name || `Producto #${movement.product_id}`}
            </p>
            <p className="mt-1 text-[11px] text-slate-500 dark:text-white/50">
              Motivo: {mapReason(movement.reason)} - Stock: {movement.stock_after}
            </p>
            {movement.reference && (
              <p className="mt-1 text-[11px] text-slate-500 dark:text-white/50">Ref: {movement.reference}</p>
            )}
            <p className="mt-1 text-[11px] text-slate-400 dark:text-white/40">{formatDate(movement.created_at)}</p>
          </article>
        ))}
      </div>
    </section>
  )
}
