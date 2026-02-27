import { useEffect, useMemo, useState } from 'react'
import { Link, useLocation, useNavigate } from 'react-router-dom'
import { Icon } from '@/components/Icon'
import { useCart } from '@/context/CartContext'
import API from '@/services/api'
import { getStoredToken } from '@/services/auth-session'

type PaymentMethod = 'PSE' | 'NEQUI' | 'BANCOLOMBIA' | 'CARD'
type OrderChannel = 'web' | 'whatsapp' | 'local'

interface CheckoutForm {
  email: string
  name: string
  phone: string
  address: string
  city: string
  notes: string
}

type CheckoutNotice = {
  variant: 'error' | 'success'
  message: string
}

const isOrderChannel = (value: string | null): value is OrderChannel =>
  value === 'web' || value === 'whatsapp' || value === 'local'

export default function Checkout() {
  const navigate = useNavigate()
  const location = useLocation()
  const { items, totalPrice } = useCart()
  const token = getStoredToken()
  const [selectedMethod, setSelectedMethod] = useState<PaymentMethod | null>(null)
  const [isProcessing, setIsProcessing] = useState(false)
  const [notice, setNotice] = useState<CheckoutNotice | null>(null)
  const [formData, setFormData] = useState<CheckoutForm>({
    email: '',
    name: '',
    phone: '',
    address: '',
    city: '',
    notes: '',
  })
  const channel = useMemo<OrderChannel>(() => {
    const fromQuery = new URLSearchParams(location.search).get('channel')
    if (isOrderChannel(fromQuery)) return fromQuery

    if (typeof window !== 'undefined') {
      const fromStorage = sessionStorage.getItem('checkout_channel')
      if (isOrderChannel(fromStorage)) return fromStorage
    }

    return 'web'
  }, [location.search])

  useEffect(() => {
    const fromQuery = new URLSearchParams(location.search).get('channel')
    if (!isOrderChannel(fromQuery)) return

    try {
      sessionStorage.setItem('checkout_channel', fromQuery)
    } catch {
      // noop
    }
  }, [location.search])

  useEffect(() => {
    if (items.length === 0) {
      navigate('/cart', { replace: true })
      return
    }

    if (!token) {
      navigate(`/login?next=${encodeURIComponent(`/checkout${location.search}`)}`, { replace: true })
    }
  }, [items.length, location.search, navigate, token])

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData((prev) => ({
      ...prev,
      [e.target.name]: e.target.value,
    }))
  }

  const handlePayment = async () => {
    if (!selectedMethod) {
      setNotice({ variant: 'error', message: 'Selecciona un metodo de pago para continuar.' })
      return
    }

    if (!formData.email || !formData.name || !formData.phone) {
      setNotice({ variant: 'error', message: 'Completa email, nombre y telefono para continuar.' })
      return
    }

    setNotice(null)
    setIsProcessing(true)

    try {
      const orderResponse = await API.post('/orders/create', {
        items: items.map((item) => ({
          productId: item.productId,
          name: item.name,
          price: item.price,
          quantity: item.quantity,
          storeId: item.storeId,
        })),
        customer: formData,
        totalAmount: totalPrice,
        paymentMethod: selectedMethod,
        channel,
      })

      const orderId = Number(orderResponse?.data?.orderId || 0)
      if (!orderId) {
        throw new Error('No se pudo crear la orden')
      }

      localStorage.setItem('last_order_id', String(orderId))
      localStorage.setItem('last_order_invoice', JSON.stringify(orderResponse?.data?.order || {}))

      const paymentResponse = await API.post('/payments/wompi/create', {
        orderId,
        amount: Math.round(totalPrice * 100),
        currency: 'COP',
        paymentMethod: selectedMethod,
        customer: {
          email: formData.email,
          fullName: formData.name,
          phoneNumber: formData.phone,
        },
        redirectUrl: `${window.location.origin}/checkout/success?order_id=${orderId}`,
      })

      const paymentData = paymentResponse?.data || {}
      if (!paymentData.checkoutUrl) {
        throw new Error('No se recibio URL de pago')
      }

      window.location.href = paymentData.checkoutUrl
    } catch (error: any) {
      console.error('checkout error', error)
      setNotice({
        variant: 'error',
        message:
          error?.response?.data?.error ||
          error?.response?.data?.message ||
          error?.message ||
          'Error al procesar el pago. Intenta nuevamente.',
      })
    } finally {
      setIsProcessing(false)
    }
  }

  if (items.length === 0 || !token) {
    return null
  }

  return (
    <div className="min-h-screen bg-slate-50 py-12">
      <div className="mx-auto max-w-6xl px-6">
        <div className="mb-4 flex flex-wrap items-center gap-2 text-sm">
          <Link to="/stores" className="font-medium text-slate-600 transition hover:text-slate-900">
            Tiendas
          </Link>
          <span className="text-slate-400">/</span>
          <Link to="/cart" className="font-medium text-slate-600 transition hover:text-slate-900">
            Carrito
          </Link>
          <span className="text-slate-400">/</span>
          <span className="font-semibold text-slate-900">Checkout</span>
        </div>
        <h1 className="mb-8 text-4xl font-bold text-slate-900">Finalizar compra</h1>

        {notice ? (
          <div
            className={`mb-6 rounded-xl border px-4 py-3 text-sm ${
              notice.variant === 'error'
                ? 'border-rose-200 bg-rose-50 text-rose-900'
                : 'border-emerald-200 bg-emerald-50 text-emerald-900'
            }`}
          >
            {notice.message}
          </div>
        ) : null}

        <div className="grid gap-8 lg:grid-cols-3">
          <div className="space-y-6 lg:col-span-2">
            <div className="rounded-2xl border-2 border-slate-200 bg-white p-6">
              <h2 className="mb-6 text-2xl font-bold text-slate-900">Datos de contacto</h2>
              <div className="space-y-4">
                <div>
                  <label className="mb-2 block text-sm font-semibold text-slate-900">Email *</label>
                  <input type="email" name="email" value={formData.email} onChange={handleInputChange} className="input-dark w-full" placeholder="tu@email.com" required />
                </div>
                <div>
                  <label className="mb-2 block text-sm font-semibold text-slate-900">Nombre completo *</label>
                  <input type="text" name="name" value={formData.name} onChange={handleInputChange} className="input-dark w-full" placeholder="Juan Perez" required />
                </div>
                <div>
                  <label className="mb-2 block text-sm font-semibold text-slate-900">Telefono *</label>
                  <input type="tel" name="phone" value={formData.phone} onChange={handleInputChange} className="input-dark w-full" placeholder="3001234567" required />
                </div>
              </div>
            </div>

            <div className="rounded-2xl border-2 border-slate-200 bg-white p-6">
              <h2 className="mb-6 text-2xl font-bold text-slate-900">Direccion de envio</h2>
              <div className="space-y-4">
                <div>
                  <label className="mb-2 block text-sm font-semibold text-slate-900">Direccion</label>
                  <input type="text" name="address" value={formData.address} onChange={handleInputChange} className="input-dark w-full" placeholder="Calle 123 #45-67" />
                </div>
                <div>
                  <label className="mb-2 block text-sm font-semibold text-slate-900">Ciudad</label>
                  <input type="text" name="city" value={formData.city} onChange={handleInputChange} className="input-dark w-full" placeholder="Bogota" />
                </div>
                <div>
                  <label className="mb-2 block text-sm font-semibold text-slate-900">Notas adicionales (opcional)</label>
                  <textarea name="notes" value={formData.notes} onChange={handleInputChange} className="textarea-dark w-full" rows={3} placeholder="Instrucciones de entrega" />
                </div>
              </div>
            </div>

            <div className="rounded-2xl border-2 border-slate-200 bg-white p-6">
              <h2 className="mb-6 text-2xl font-bold text-slate-900">Metodo de pago</h2>
              <div className="grid gap-4 md:grid-cols-2">
                {([
                  { key: 'PSE', label: 'PSE', hint: 'Pago Seguro en Linea', icon: 'bank' },
                  { key: 'NEQUI', label: 'Nequi', hint: 'Billetera digital', icon: 'smartphone' },
                  { key: 'BANCOLOMBIA', label: 'Bancolombia', hint: 'Boton de pago', icon: 'credit-card' },
                  { key: 'CARD', label: 'Tarjeta', hint: 'Debito o credito', icon: 'credit-card' },
                ] as const).map((method) => (
                  <button
                    key={method.key}
                    onClick={() => setSelectedMethod(method.key)}
                    className={`group relative overflow-hidden rounded-xl border-2 p-6 text-left transition-all ${
                      selectedMethod === method.key
                        ? 'border-comercioplus-600 bg-comercioplus-50'
                        : 'border-slate-200 bg-white hover:border-comercioplus-300'
                    }`}
                  >
                    <div className="mb-3 flex items-center justify-between">
                      <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100">
                        <Icon name={method.icon} size={24} className="text-slate-700" />
                      </div>
                      {selectedMethod === method.key && (
                        <div className="flex h-6 w-6 items-center justify-center rounded-full bg-comercioplus-600">
                          <Icon name="check" size={14} className="text-white" />
                        </div>
                      )}
                    </div>
                    <h3 className="mb-1 font-bold text-slate-900">{method.label}</h3>
                    <p className="text-sm text-slate-600">{method.hint}</p>
                  </button>
                ))}
              </div>
            </div>
          </div>

          <div className="lg:col-span-1">
            <div className="sticky top-24 rounded-2xl border-2 border-slate-200 bg-white p-6">
              <h2 className="mb-6 text-2xl font-bold text-slate-900">Resumen</h2>

              <div className="mb-6 max-h-60 space-y-3 overflow-y-auto">
                {items.map((item) => (
                  <div key={item.id} className="flex gap-3">
                    <img src={item.image} alt={item.name} className="h-16 w-16 rounded-lg object-cover" />
                    <div className="flex-1">
                      <p className="text-sm font-semibold text-slate-900">{item.name}</p>
                      <p className="text-xs text-slate-500">x{item.quantity}</p>
                      <p className="text-sm font-bold text-comercioplus-600">${(item.price * item.quantity).toLocaleString('es-CO')}</p>
                    </div>
                  </div>
                ))}
              </div>

              <div className="mb-6 space-y-3 border-t border-slate-200 pt-4">
                <div className="flex justify-between text-slate-600">
                  <span>Subtotal</span>
                  <span className="font-semibold">${totalPrice.toLocaleString('es-CO')}</span>
                </div>
                <div className="flex justify-between text-slate-600">
                  <span>Envio</span>
                  <span className="font-semibold text-green-600">Gratis</span>
                </div>
                <div className="border-t border-slate-200 pt-3">
                  <div className="flex justify-between">
                    <span className="text-lg font-bold text-slate-900">Total</span>
                    <span className="text-2xl font-bold text-comercioplus-600">${totalPrice.toLocaleString('es-CO')} COP</span>
                  </div>
                </div>
              </div>

              <button
                onClick={handlePayment}
                disabled={!selectedMethod || isProcessing}
                className="mb-4 flex w-full items-center justify-center gap-2 rounded-xl bg-comercioplus-600 py-4 font-bold text-white transition-all hover:bg-comercioplus-700 disabled:cursor-not-allowed disabled:opacity-50"
              >
                {isProcessing ? (
                  <>
                    <div className="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent" />
                    Procesando...
                  </>
                ) : (
                  <>
                    <Icon name="lock" size={20} />
                    Pagar ahora
                  </>
                )}
              </button>

              <Link
                to="/cart"
                className="mb-4 flex w-full items-center justify-center gap-2 rounded-xl border-2 border-slate-200 bg-white py-3 font-semibold text-slate-700 transition-all hover:bg-slate-50"
              >
                <Icon name="arrow-left" size={18} />
                Volver al carrito
              </Link>

              <div className="rounded-xl bg-green-50 p-4">
                <div className="flex items-start gap-3">
                  <Icon name="shield" size={20} className="text-green-600" />
                  <div>
                    <p className="text-sm font-semibold text-green-900">Pago 100% seguro</p>
                    <p className="text-xs text-green-700">Protegido por Wompi y encriptacion SSL</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
