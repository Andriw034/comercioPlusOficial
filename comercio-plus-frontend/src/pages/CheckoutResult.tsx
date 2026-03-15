import { useEffect, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { Icon } from '@/components/Icon'
import { useCart } from '@/context/CartContext'
import API from '@/services/api'

// MercadoPago redirect params:
// ?collection_status=approved|rejected|pending
// &external_reference=MP-{orderId}-{ts}
// &collection_id={mp_payment_id}

type MPStatus = 'approved' | 'rejected' | 'pending' | 'loading' | 'error'

interface ResultData {
  orderId:      number | null
  amount:       number | null
  currency:     string
  customerName: string | null
  reference:    string
}

const STATUS_CONFIG: Record<string, {
  bg: string; border: string; icon: string; iconColor: string; title: string; subtitle: string
}> = {
  approved: {
    bg:        'bg-emerald-50',
    border:    'border-emerald-200',
    icon:      'check-circle',
    iconColor: 'text-emerald-600',
    title:     '¡Pago exitoso!',
    subtitle:  'Tu orden fue confirmada. Pronto recibiras los detalles por email.',
  },
  rejected: {
    bg:        'bg-rose-50',
    border:    'border-rose-200',
    icon:      'x-circle',
    iconColor: 'text-rose-600',
    title:     'Pago rechazado',
    subtitle:  'Tu pago no pudo procesarse. Verifica tus datos e intenta de nuevo.',
  },
  pending: {
    bg:        'bg-amber-50',
    border:    'border-amber-200',
    icon:      'clock',
    iconColor: 'text-amber-600',
    title:     'Pago en proceso',
    subtitle:  'Tu pago esta siendo verificado. Te notificaremos cuando se complete.',
  },
  error: {
    bg:        'bg-rose-50',
    border:    'border-rose-200',
    icon:      'x-circle',
    iconColor: 'text-rose-600',
    title:     'Error en el pago',
    subtitle:  'Ocurrio un error al procesar tu pago. Intenta nuevamente.',
  },
}

export default function CheckoutResult() {
  const [searchParams] = useSearchParams()
  const { clearCart }  = useCart()

  // MercadoPago usa collection_status; también soportamos ?status= (back_urls manuales)
  const collectionStatus = searchParams.get('collection_status') ?? searchParams.get('status')
  const externalRef      = searchParams.get('external_reference') ?? searchParams.get('reference')

  const [status, setStatus]   = useState<MPStatus>(() => (collectionStatus ?? 'error') as MPStatus)
  const [data, setData]       = useState<ResultData | null>(null)

  useEffect(() => {
    if (!collectionStatus && !externalRef) return

    // Mapear el estado de MP a los nuestros
    const mapped = (collectionStatus ?? 'error') as MPStatus

    if (mapped === 'approved') {
      clearCart()
      localStorage.removeItem('last_order_id')
    }

    // Consultar detalles de la orden al backend
    if (externalRef) {
      API.get(`/payments/result?reference=${encodeURIComponent(externalRef)}`)
        .then((res) => {
          const d = res.data ?? {}
          setData({
            orderId:      d.orderId      ?? null,
            amount:       d.amount       ?? null,
            currency:     d.currency     ?? 'COP',
            customerName: d.customerName ?? null,
            reference:    externalRef,
          })
          // Sync status desde el backend si difiere
          if (d.status) {
            setStatus(String(d.status).toLowerCase() as MPStatus)
          }
        })
        .catch(() => {
          // No bloqueamos si falla la consulta; el status ya viene de MP
        })
    }
  }, [collectionStatus, externalRef, clearCart])

  // Sin parámetros en la URL
  if (!collectionStatus && !externalRef) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-slate-50 p-6">
        <div className="w-full max-w-md rounded-2xl bg-white p-8 text-center shadow-sm">
          <div className="mb-4 text-5xl">🔍</div>
          <h1 className="mb-2 text-2xl font-bold text-slate-900">Sin informacion de pago</h1>
          <p className="mb-6 text-slate-600">No se encontro un identificador de transaccion valido.</p>
          <Link
            to="/stores"
            className="inline-flex items-center gap-2 rounded-xl bg-comercioplus-600 px-6 py-3 font-semibold text-white hover:bg-comercioplus-700"
          >
            <Icon name="arrow-left" size={18} />
            Ir a tiendas
          </Link>
        </div>
      </div>
    )
  }

  // Loading spinner
  if (status === 'loading') {
    return (
      <div className="flex min-h-screen items-center justify-center bg-slate-50">
        <div className="text-center">
          <div className="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-4 border-slate-200 border-t-comercioplus-600" />
          <p className="text-slate-600">Verificando tu pago...</p>
        </div>
      </div>
    )
  }

  const cfgKey = status in STATUS_CONFIG ? status : 'error'
  const cfg    = STATUS_CONFIG[cfgKey]

  return (
    <div className="flex min-h-screen items-center justify-center bg-slate-50 p-6">
      <div className="w-full max-w-md">
        <div className={`rounded-2xl border-2 ${cfg.border} ${cfg.bg} p-8 text-center`}>
          <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-white">
            <Icon name={cfg.icon as any} size={36} className={cfg.iconColor} />
          </div>

          <h1 className="mb-2 text-2xl font-bold text-slate-900">{cfg.title}</h1>
          <p className="mb-6 text-slate-600">{cfg.subtitle}</p>

          {data && (
            <div className="mb-6 rounded-xl bg-white p-4 text-left">
              {data.customerName && (
                <div className="mb-2 flex justify-between text-sm">
                  <span className="text-slate-500">Cliente</span>
                  <span className="font-semibold text-slate-900">{data.customerName}</span>
                </div>
              )}
              {data.orderId && (
                <div className="mb-2 flex justify-between text-sm">
                  <span className="text-slate-500">Orden</span>
                  <span className="font-semibold text-slate-900">#{data.orderId}</span>
                </div>
              )}
              {data.amount !== null && (
                <div className="mb-2 flex justify-between text-sm">
                  <span className="text-slate-500">Total</span>
                  <span className="font-bold text-comercioplus-600">
                    ${Number(data.amount).toLocaleString('es-CO')} {data.currency}
                  </span>
                </div>
              )}
              <div className="flex justify-between text-sm">
                <span className="text-slate-500">Referencia</span>
                <span className="font-mono text-xs text-slate-700">{data.reference}</span>
              </div>
            </div>
          )}

          <div className="flex flex-col gap-3">
            {status === 'approved' && (
              <Link
                to="/orders/history"
                className="flex items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3 font-semibold text-white hover:bg-emerald-700"
              >
                <Icon name="package" size={18} />
                Ver mis pedidos
              </Link>
            )}

            {status === 'pending' && (
              <Link
                to="/orders/history"
                className="flex items-center justify-center gap-2 rounded-xl bg-amber-500 py-3 font-semibold text-white hover:bg-amber-600"
              >
                <Icon name="clock" size={18} />
                Ver estado del pedido
              </Link>
            )}

            {(status === 'rejected' || status === 'error') && (
              <Link
                to="/checkout"
                className="flex items-center justify-center gap-2 rounded-xl bg-comercioplus-600 py-3 font-semibold text-white hover:bg-comercioplus-700"
              >
                <Icon name="refresh" size={18} />
                Intentar de nuevo
              </Link>
            )}

            <Link
              to="/stores"
              className="flex items-center justify-center gap-2 rounded-xl border-2 border-slate-200 bg-white py-3 font-semibold text-slate-700 hover:bg-slate-50"
            >
              <Icon name="arrow-left" size={18} />
              Ir a tiendas
            </Link>
          </div>
        </div>
      </div>
    </div>
  )
}
