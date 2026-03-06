import { useCallback, useEffect, useState } from 'react'
import API from '@/lib/api'
import { Icon } from '@/components/Icon'
import { ErpBtn, ErpPageHeader } from '@/components/erp'

// ─── Types ───────────────────────────────────────────────────────────────────

type RestockSetting = {
  min_stock_threshold: number
  days_of_stock_target: number
  auto_approve: boolean
  supplier_email: string | null
  supplier_whatsapp: string | null
}

type RestockPrediction = {
  days_until_depletion: number
  avg_daily_sales: number
  recommended_restock_qty: number
}

type RestockProduct = {
  product_id: number
  name: string
  sku: string | null
  stock: number
  reorder_point: number
  cost_price: number
  image_url: string | null
  setting: RestockSetting
  prediction: RestockPrediction | null
}

type SettingDraft = {
  min_stock_threshold: string
  supplier_whatsapp: string
  days_of_stock_target: string
}

type Toast = {
  id: number
  type: 'ok' | 'error'
  message: string
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

function toInt(value: string, fallback = 0): number {
  const parsed = parseInt(value, 10)
  return Number.isFinite(parsed) && parsed >= 0 ? parsed : fallback
}

function formatCop(value: number): string {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(value)
}

function daysLabel(days: number | null | undefined): string {
  if (days === null || days === undefined || days <= 0) return 'Sin datos'
  if (days === 1) return '1 día restante'
  return `${days} días restantes`
}

function stockColor(stock: number, reorder: number): string {
  if (stock === 0) return '#EF4444'
  if (stock <= Math.floor(reorder * 0.5)) return '#EF4444'
  return '#F59E0B'
}

let toastCounter = 0

// ─── Component ───────────────────────────────────────────────────────────────

export default function RestockPage() {
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [products, setProducts] = useState<RestockProduct[]>([])
  const [expandedId, setExpandedId] = useState<number | null>(null)
  const [drafts, setDrafts] = useState<Record<number, SettingDraft>>({})
  const [saving, setSaving] = useState<number | null>(null)
  const [requesting, setRequesting] = useState<number | null>(null)
  const [dismissing, setDismissing] = useState<number | null>(null)
  const [toasts, setToasts] = useState<Toast[]>([])

  // ── Toast helpers ──────────────────────────────────────────────────────────

  const addToast = (type: 'ok' | 'error', message: string) => {
    const id = ++toastCounter
    setToasts((prev) => [...prev, { id, type, message }])
    setTimeout(() => setToasts((prev) => prev.filter((t) => t.id !== id)), 4000)
  }

  // ── Fetch ──────────────────────────────────────────────────────────────────

  const load = useCallback(async () => {
    setLoading(true)
    setError('')
    try {
      const resp = await API.get<{ data: RestockProduct[] }>('/merchant/restock')
      const items = Array.isArray(resp.data?.data) ? resp.data.data : []
      setProducts(items)
      const initialDrafts: Record<number, SettingDraft> = {}
      items.forEach((p) => {
        initialDrafts[p.product_id] = {
          min_stock_threshold: String(p.setting.min_stock_threshold),
          supplier_whatsapp: p.setting.supplier_whatsapp ?? '',
          days_of_stock_target: String(p.setting.days_of_stock_target),
        }
      })
      setDrafts(initialDrafts)
    } catch {
      setError('No se pudo cargar el inventario crítico.')
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    void load()
  }, [load])

  // ── Handlers ──────────────────────────────────────────────────────────────

  const toggleExpand = (id: number) => {
    setExpandedId((prev) => (prev === id ? null : id))
  }

  const updateDraft = (productId: number, field: keyof SettingDraft, value: string) => {
    setDrafts((prev) => ({
      ...prev,
      [productId]: { ...prev[productId], [field]: value },
    }))
  }

  const handleSaveSettings = async (productId: number) => {
    const draft = drafts[productId]
    if (!draft) return
    setSaving(productId)
    try {
      await API.put(`/merchant/restock/${productId}`, {
        min_stock_threshold: toInt(draft.min_stock_threshold, 5),
        supplier_whatsapp: draft.supplier_whatsapp.trim() || null,
        days_of_stock_target: toInt(draft.days_of_stock_target, 30),
      })
      addToast('ok', 'Configuración guardada correctamente.')
      setExpandedId(null)
      await load()
    } catch {
      addToast('error', 'No se pudo guardar la configuración.')
    } finally {
      setSaving(null)
    }
  }

  const handleRequest = async (product: RestockProduct) => {
    setRequesting(product.product_id)
    try {
      type RequestResp = { data: { whatsapp_url: string | null; ordered_qty: number } }
      const resp = await API.post<RequestResp>(`/merchant/restock/${product.product_id}/request`)
      const waUrl = resp.data?.data?.whatsapp_url ?? null
      const qty = resp.data?.data?.ordered_qty ?? 0
      addToast('ok', `Pedido creado — ${qty} unidades solicitadas.`)
      if (waUrl) {
        window.open(waUrl, '_blank', 'noopener,noreferrer')
      }
      await load()
    } catch {
      addToast('error', 'No se pudo crear la solicitud de pedido.')
    } finally {
      setRequesting(null)
    }
  }

  const handleDismiss = async (productId: number) => {
    setDismissing(productId)
    try {
      await API.post(`/merchant/restock/${productId}/dismiss`)
      setProducts((prev) => prev.filter((p) => p.product_id !== productId))
      addToast('ok', 'Producto ocultado de alertas.')
    } catch {
      addToast('error', 'No se pudo ocultar el producto.')
    } finally {
      setDismissing(null)
    }
  }

  // ── Render ─────────────────────────────────────────────────────────────────

  return (
    <div className="space-y-4 text-[#0F172A]">
      {/* Toasts */}
      <div className="pointer-events-none fixed bottom-6 right-6 z-50 flex flex-col gap-2">
        {toasts.map((t) => (
          <div
            key={t.id}
            className={`pointer-events-auto flex items-center gap-2 rounded-xl px-4 py-2.5 text-[13px] font-semibold shadow-xl transition-all ${
              t.type === 'ok'
                ? 'border border-emerald-200 bg-emerald-50 text-emerald-800'
                : 'border border-rose-200 bg-rose-50 text-rose-800'
            }`}
          >
            <Icon name={t.type === 'ok' ? 'check-circle' : 'alert'} size={15} />
            {t.message}
          </div>
        ))}
      </div>

      <ErpPageHeader
        breadcrumb="Inventario / Reabastecimiento"
        title="Reabastecimiento automático"
        subtitle="Productos con stock crítico que necesitan atención."
        actions={
          <ErpBtn variant="secondary" size="md" icon={<Icon name="refresh" size={14} />} onClick={() => void load()} disabled={loading}>
            Recargar
          </ErpBtn>
        }
      />

      {/* Error */}
      {error ? (
        <div className="flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3">
          <Icon name="alert" size={16} className="shrink-0 text-rose-500" />
          <span className="text-[13px] text-rose-700">{error}</span>
          <ErpBtn variant="secondary" size="sm" className="ml-auto" onClick={() => void load()}>
            Reintentar
          </ErpBtn>
        </div>
      ) : null}

      {/* Loading skeleton */}
      {loading ? (
        <div className="space-y-3">
          {[1, 2, 3].map((n) => (
            <div key={n} className="h-24 animate-pulse rounded-2xl border border-slate-200 bg-slate-100" />
          ))}
        </div>
      ) : null}

      {/* Empty state */}
      {!loading && !error && products.length === 0 ? (
        <div className="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-emerald-200 bg-emerald-50 px-8 py-16 text-center">
          <span className="mb-3 text-5xl">📦</span>
          <h3 className="text-[18px] font-black text-slate-800">¡Inventario en buen estado!</h3>
          <p className="mt-1 max-w-sm text-[13px] text-slate-500">
            Todos tus productos tienen stock por encima del umbral configurado. Cuando alguno esté en nivel crítico aparecerá aquí.
          </p>
        </div>
      ) : null}

      {/* Product cards */}
      {!loading && products.length > 0 ? (
        <div className="space-y-3">
          {products.map((product) => {
            const color = stockColor(product.stock, product.reorder_point)
            const isExpanded = expandedId === product.product_id
            const draft = drafts[product.product_id] ?? {
              min_stock_threshold: String(product.setting.min_stock_threshold),
              supplier_whatsapp: product.setting.supplier_whatsapp ?? '',
              days_of_stock_target: String(product.setting.days_of_stock_target),
            }

            return (
              <div
                key={product.product_id}
                className="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-[0_4px_12px_rgba(15,23,42,0.06)]"
              >
                {/* Main row */}
                <div className="flex flex-wrap items-center gap-3 p-4">
                  {/* Stock badge */}
                  <div
                    className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-[15px] font-black text-white"
                    style={{ background: color }}
                  >
                    {product.stock}
                  </div>

                  {/* Info */}
                  <div className="min-w-0 flex-1">
                    <p className="truncate text-[14px] font-black text-slate-900">{product.name}</p>
                    <div className="mt-0.5 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                      {product.sku ? <span className="font-mono">{product.sku}</span> : null}
                      <span>•</span>
                      <span>Mínimo: <strong className="text-slate-700">{product.reorder_point}</strong></span>
                      <span>•</span>
                      <span style={{ color }}>
                        {product.prediction
                          ? daysLabel(product.prediction.days_until_depletion)
                          : 'Sin predicción'}
                      </span>
                      {product.cost_price > 0 ? (
                        <>
                          <span>•</span>
                          <span>Costo: {formatCop(product.cost_price)}</span>
                        </>
                      ) : null}
                    </div>
                  </div>

                  {/* Actions */}
                  <div className="flex shrink-0 items-center gap-2">
                    <ErpBtn
                      variant="secondary"
                      size="sm"
                      icon={<Icon name="settings" size={13} />}
                      onClick={() => toggleExpand(product.product_id)}
                    >
                      {isExpanded ? 'Cerrar' : 'Configurar'}
                    </ErpBtn>
                    <ErpBtn
                      variant="primary"
                      size="sm"
                      icon={<Icon name="truck" size={13} />}
                      disabled={requesting === product.product_id}
                      onClick={() => void handleRequest(product)}
                    >
                      {requesting === product.product_id ? 'Enviando…' : 'Solicitar pedido'}
                    </ErpBtn>
                    <ErpBtn
                      variant="ghost"
                      size="sm"
                      icon={<Icon name="x-circle" size={13} />}
                      disabled={dismissing === product.product_id}
                      onClick={() => void handleDismiss(product.product_id)}
                    >
                      Ignorar
                    </ErpBtn>
                  </div>
                </div>

                {/* Expandable settings panel */}
                {isExpanded ? (
                  <div className="border-t border-slate-100 bg-slate-50 px-4 py-4">
                    <p className="mb-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                      Configurar umbral y proveedor
                    </p>
                    <div className="grid gap-3 sm:grid-cols-3">
                      <label className="text-[11px] font-semibold text-slate-600">
                        Stock mínimo (unidades)
                        <input
                          type="number"
                          min="0"
                          value={draft.min_stock_threshold}
                          onChange={(e) => updateDraft(product.product_id, 'min_stock_threshold', e.target.value)}
                          className="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-900 outline-none focus:border-orange-400 focus:shadow-[0_0_0_3px_rgba(255,160,79,0.15)]"
                        />
                      </label>

                      <label className="text-[11px] font-semibold text-slate-600">
                        Días de stock objetivo
                        <input
                          type="number"
                          min="1"
                          max="365"
                          value={draft.days_of_stock_target}
                          onChange={(e) => updateDraft(product.product_id, 'days_of_stock_target', e.target.value)}
                          className="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-900 outline-none focus:border-orange-400 focus:shadow-[0_0_0_3px_rgba(255,160,79,0.15)]"
                        />
                      </label>

                      <label className="text-[11px] font-semibold text-slate-600">
                        WhatsApp proveedor
                        <input
                          type="tel"
                          placeholder="+573001234567"
                          value={draft.supplier_whatsapp}
                          onChange={(e) => updateDraft(product.product_id, 'supplier_whatsapp', e.target.value)}
                          className="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-900 outline-none focus:border-orange-400 focus:shadow-[0_0_0_3px_rgba(255,160,79,0.15)]"
                        />
                      </label>
                    </div>

                    <div className="mt-3 flex gap-2">
                      <ErpBtn
                        variant="primary"
                        size="sm"
                        icon={<Icon name="save" size={13} />}
                        disabled={saving === product.product_id}
                        onClick={() => void handleSaveSettings(product.product_id)}
                      >
                        {saving === product.product_id ? 'Guardando…' : 'Guardar configuración'}
                      </ErpBtn>
                      <ErpBtn
                        variant="ghost"
                        size="sm"
                        onClick={() => setExpandedId(null)}
                      >
                        Cancelar
                      </ErpBtn>
                    </div>
                  </div>
                ) : null}
              </div>
            )
          })}
        </div>
      ) : null}
    </div>
  )
}
