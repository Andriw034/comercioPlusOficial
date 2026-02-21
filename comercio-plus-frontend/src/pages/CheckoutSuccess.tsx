import { useEffect, useMemo, useState } from 'react'
import { Link, useParams, useSearchParams } from 'react-router-dom'
import API from '@/lib/api'
import { Icon } from '@/components/Icon'

type InvoiceItem = {
  id: number
  product_id: number
  product_name?: string
  quantity: number
  unit_price: number
  line_subtotal: number
  tax_amount: number
  tax_rate_applied: number
  line_total: number
}

type InvoiceOrder = {
  id: number
  invoice_number?: string | null
  invoice_date?: string
  date?: string
  status?: string
  payment_method?: string
  store_name?: string
  subtotal: number
  tax_total: number
  total: number
  currency?: string
  items: InvoiceItem[]
}

const money = (value: number) =>
  new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 2,
  }).format(value || 0)

const formatDateTime = (value?: string) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleString('es-CO', {
    dateStyle: 'medium',
    timeStyle: 'short',
  })
}

const toNumber = (value: unknown) => {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : 0
}

const mapInvoice = (payload: any): InvoiceOrder | null => {
  if (!payload || typeof payload !== 'object') return null

  return {
    id: Number(payload.id || 0),
    invoice_number: payload.invoice_number || null,
    invoice_date: payload.invoice_date || payload.date || payload.created_at,
    date: payload.date || payload.created_at,
    status: payload.status || '',
    payment_method: payload.payment_method || '',
    store_name: payload.store_name || '',
    subtotal: toNumber(payload.subtotal),
    tax_total: toNumber(payload.tax_total),
    total: toNumber(payload.total),
    currency: payload.currency || 'COP',
    items: Array.isArray(payload.items)
      ? payload.items.map((item: any, index: number) => ({
          id: Number(item?.id || index + 1),
          product_id: Number(item?.product_id || 0),
          product_name: item?.product_name || 'Producto',
          quantity: Number(item?.quantity || 0),
          unit_price: toNumber(item?.unit_price),
          line_subtotal: toNumber(item?.line_subtotal),
          tax_amount: toNumber(item?.tax_amount),
          tax_rate_applied: toNumber(item?.tax_rate_applied),
          line_total: toNumber(item?.line_total),
        }))
      : [],
  }
}

export default function CheckoutSuccess() {
  const params = useParams<{ id?: string }>()
  const [searchParams] = useSearchParams()
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [order, setOrder] = useState<InvoiceOrder | null>(null)

  const orderId = useMemo(() => {
    const fromParams = Number(params.id || 0)
    if (fromParams > 0) return fromParams
    const fromQuery = Number(searchParams.get('order_id') || 0)
    if (fromQuery > 0) return fromQuery
    const fromStorage = Number(localStorage.getItem('last_order_id') || 0)
    return fromStorage > 0 ? fromStorage : 0
  }, [params.id, searchParams])

  useEffect(() => {
    const load = async () => {
      let resolved = false

      try {
        setError('')
        if (orderId > 0) {
          const response = await API.get(`/orders/${orderId}`)
          const payload = response?.data?.data || response?.data || {}
          const mapped = mapInvoice(payload)
          if (mapped) {
            setOrder(mapped)
            localStorage.setItem('last_order_invoice', JSON.stringify(mapped))
            resolved = true
            return
          }
        }
      } catch (err: any) {
        setError(err?.response?.data?.message || err?.message || 'No se pudo cargar la factura desde la API.')
      } finally {
        if (!resolved) {
          const cachedRaw = localStorage.getItem('last_order_invoice')
          if (cachedRaw) {
            try {
              const cached = JSON.parse(cachedRaw)
              const mappedCached = mapInvoice(cached)
              if (mappedCached) setOrder(mappedCached)
            } catch {
              // Sin cache valido.
            }
          }
        }
        setLoading(false)
      }
    }

    load()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [orderId])

  return (
    <div className="min-h-screen bg-slate-50 py-10">
      <div className="mx-auto max-w-4xl space-y-6 px-6">
        <div className="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
          <div className="flex items-start gap-3">
            <Icon name="check-circle" size={22} className="text-emerald-600" />
            <div>
              <h1 className="text-2xl font-bold text-emerald-900">Pedido registrado</h1>
              <p className="text-sm text-emerald-800">
                Tu orden fue creada correctamente. Esta es tu factura de compra.
              </p>
            </div>
          </div>
        </div>

        {loading && (
          <div className="rounded-2xl border border-slate-200 bg-white p-6 text-slate-600">
            Cargando factura...
          </div>
        )}

        {!loading && error && (
          <div className="rounded-2xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
            {error}
          </div>
        )}

        {!loading && order && (
          <div className="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div className="grid gap-4 text-sm text-slate-700 sm:grid-cols-2">
              <div>
                <p className="text-xs uppercase tracking-wide text-slate-500">Pedido</p>
                <p className="font-semibold text-slate-900">#{order.id}</p>
              </div>
              <div>
                <p className="text-xs uppercase tracking-wide text-slate-500">Factura</p>
                <p className="font-semibold text-slate-900">{order.invoice_number || '-'}</p>
              </div>
              <div>
                <p className="text-xs uppercase tracking-wide text-slate-500">Fecha</p>
                <p className="font-semibold text-slate-900">{formatDateTime(order.invoice_date || order.date)}</p>
              </div>
              <div>
                <p className="text-xs uppercase tracking-wide text-slate-500">Tienda</p>
                <p className="font-semibold text-slate-900">{order.store_name || '-'}</p>
              </div>
            </div>

            <div className="overflow-x-auto">
              <table className="min-w-full text-sm">
                <thead>
                  <tr className="border-b border-slate-200 text-left text-slate-500">
                    <th className="py-2 pr-3">Producto</th>
                    <th className="py-2 pr-3">Cantidad</th>
                    <th className="py-2 pr-3">Unitario</th>
                    <th className="py-2 pr-3">Base</th>
                    <th className="py-2 pr-3">IVA</th>
                    <th className="py-2">Total</th>
                  </tr>
                </thead>
                <tbody>
                  {order.items.map((item) => (
                    <tr key={item.id} className="border-b border-slate-100 text-slate-700">
                      <td className="py-3 pr-3">{item.product_name || 'Producto'}</td>
                      <td className="py-3 pr-3">{item.quantity}</td>
                      <td className="py-3 pr-3">{money(item.unit_price)}</td>
                      <td className="py-3 pr-3">{money(item.line_subtotal)}</td>
                      <td className="py-3 pr-3">{money(item.tax_amount)}</td>
                      <td className="py-3 font-semibold text-slate-900">{money(item.line_total)}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <div className="ml-auto max-w-sm space-y-2 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm">
              <div className="flex items-center justify-between">
                <span className="text-slate-600">Subtotal</span>
                <strong>{money(order.subtotal)}</strong>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-slate-600">IVA</span>
                <strong>{money(order.tax_total)}</strong>
              </div>
              <div className="flex items-center justify-between border-t border-slate-200 pt-2 text-base text-slate-900">
                <span>Total</span>
                <strong>{money(order.total)}</strong>
              </div>
            </div>
          </div>
        )}

        {!loading && !order && (
          <div className="rounded-2xl border border-slate-200 bg-white p-6 text-slate-600">
            No se encontro una factura para mostrar.
          </div>
        )}

        <div className="flex flex-wrap gap-3">
          <Link to="/" className="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
            Ir al inicio
          </Link>
          <Link to="/cart" className="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">
            Volver al carrito
          </Link>
        </div>
      </div>
    </div>
  )
}
