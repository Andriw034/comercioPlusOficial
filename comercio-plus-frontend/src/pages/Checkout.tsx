import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Icon } from '@/components/Icon'
import { useCart } from '@/context/CartContext'

type PaymentMethod = 'PSE' | 'NEQUI' | 'BANCOLOMBIA' | 'CARD'

interface CheckoutForm {
  email: string
  name: string
  phone: string
  address: string
  city: string
  notes: string
}

export default function Checkout() {
  const navigate = useNavigate()
  const { items, totalPrice } = useCart()
  const [selectedMethod, setSelectedMethod] = useState<PaymentMethod | null>(null)
  const [isProcessing, setIsProcessing] = useState(false)
  const [formData, setFormData] = useState<CheckoutForm>({
    email: '',
    name: '',
    phone: '',
    address: '',
    city: '',
    notes: '',
  })

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    })
  }

  const handlePayment = async () => {
    if (!selectedMethod) {
      alert('Por favor selecciona un método de pago')
      return
    }

    // Validar formulario
    if (!formData.email || !formData.name || !formData.phone) {
      alert('Por favor completa todos los campos obligatorios')
      return
    }

    setIsProcessing(true)

    try {
      // 1. Crear orden en backend
      const orderResponse = await fetch('/api/orders/create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          items: items.map(item => ({
            productId: item.productId,
            name: item.name,
            price: item.price,
            quantity: item.quantity,
            storeId: item.storeId,
          })),
          customer: formData,
          totalAmount: totalPrice,
          paymentMethod: selectedMethod,
        }),
      })

      if (!orderResponse.ok) throw new Error('Error al crear orden')

      const { orderId } = await orderResponse.json()

      // 2. Iniciar pago con Wompi
      const paymentResponse = await fetch('/api/payments/wompi/create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          orderId,
          amount: totalPrice * 100, // Wompi usa centavos
          currency: 'COP',
          paymentMethod: selectedMethod,
          customer: {
            email: formData.email,
            fullName: formData.name,
            phoneNumber: formData.phone,
          },
          redirectUrl: `${window.location.origin}/payment/success`,
        }),
      })

      if (!paymentResponse.ok) throw new Error('Error al procesar pago')

      const paymentData = await paymentResponse.json()

      // 3. Redirigir a página de pago de Wompi
      window.location.href = paymentData.checkoutUrl

      // 4. Limpiar carrito (se hace después de pago exitoso en webhook)
      // clearCart()
    } catch (error) {
      console.error('Error:', error)
      alert('Error al procesar el pago. Por favor intenta nuevamente.')
    } finally {
      setIsProcessing(false)
    }
  }

  if (items.length === 0) {
    navigate('/cart')
    return null
  }

  return (
    <div className="min-h-screen bg-slate-50 py-12">
      <div className="mx-auto max-w-6xl px-6">
        <h1 className="mb-8 text-4xl font-bold text-slate-900">Finalizar compra</h1>

        <div className="grid gap-8 lg:grid-cols-3">
          {/* Formulario */}
          <div className="lg:col-span-2 space-y-6">
            {/* Datos de contacto */}
            <div className="rounded-2xl border-2 border-slate-200 bg-white p-6">
              <h2 className="mb-6 text-2xl font-bold text-slate-900">Datos de contacto</h2>
              <div className="space-y-4">
                <div>
                  <label className="mb-2 block text-sm font-semibold text-slate-900">
                    Email *
                  </label>
                  <input
                    type="email"
                    name="email"
                    value={formData.email}
                    onChange={handleInputChange}
                    className="input-dark w-full"
                    placeholder="tu@email.com"
                    required
                  />
                </div>
                <div>
                  <label className="mb-2 block text-sm font-semibold text-slate-900">
                    Nombre completo *
                  </label>
                  <input
                    type="text"
                    name="name"
                    value={formData.name}
                    onChange={handleInputChange}
                    className="input-dark w-full"
                    placeholder="Juan Pérez"
                    required
                  />
                </div>
                <div>
                  <label className="mb-2 block text-sm font-semibold text-slate-900">
                    Teléfono *
                  </label>
                  <input
                    type="tel"
                    name="phone"
                    value={formData.phone}
                    onChange={handleInputChange}
                    className="input-dark w-full"
                    placeholder="3001234567"
                    required
                  />
                </div>
              </div>
            </div>

            {/* Dirección de envío */}
            <div className="rounded-2xl border-2 border-slate-200 bg-white p-6">
              <h2 className="mb-6 text-2xl font-bold text-slate-900">Dirección de envío</h2>
              <div className="space-y-4">
                <div>
                  <label className="mb-2 block text-sm font-semibold text-slate-900">
                    Dirección
                  </label>
                  <input
                    type="text"
                    name="address"
                    value={formData.address}
                    onChange={handleInputChange}
                    className="input-dark w-full"
                    placeholder="Calle 123 #45-67"
                  />
                </div>
                <div>
                  <label className="mb-2 block text-sm font-semibold text-slate-900">
                    Ciudad
                  </label>
                  <input
                    type="text"
                    name="city"
                    value={formData.city}
                    onChange={handleInputChange}
                    className="input-dark w-full"
                    placeholder="Bogotá"
                  />
                </div>
                <div>
                  <label className="mb-2 block text-sm font-semibold text-slate-900">
                    Notas adicionales (opcional)
                  </label>
                  <textarea
                    name="notes"
                    value={formData.notes}
                    onChange={handleInputChange}
                    className="textarea-dark w-full"
                    rows={3}
                    placeholder="Instrucciones de entrega, apartamento, etc."
                  />
                </div>
              </div>
            </div>

            {/* Métodos de pago */}
            <div className="rounded-2xl border-2 border-slate-200 bg-white p-6">
              <h2 className="mb-6 text-2xl font-bold text-slate-900">Método de pago</h2>
              <div className="grid gap-4 md:grid-cols-2">
                {/* PSE */}
                <button
                  onClick={() => setSelectedMethod('PSE')}
                  className={`group relative overflow-hidden rounded-xl border-2 p-6 text-left transition-all ${
                    selectedMethod === 'PSE'
                      ? 'border-comercioplus-600 bg-comercioplus-50'
                      : 'border-slate-200 bg-white hover:border-comercioplus-300'
                  }`}
                >
                  <div className="mb-3 flex items-center justify-between">
                    <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100">
                      <Icon name="bank" size={24} className="text-blue-600" />
                    </div>
                    {selectedMethod === 'PSE' && (
                      <div className="flex h-6 w-6 items-center justify-center rounded-full bg-comercioplus-600">
                        <Icon name="check" size={14} className="text-white" />
                      </div>
                    )}
                  </div>
                  <h3 className="mb-1 font-bold text-slate-900">PSE</h3>
                  <p className="text-sm text-slate-600">Pago Seguro en Línea</p>
                  <p className="mt-2 text-xs text-slate-500">Débito directo desde tu banco</p>
                </button>

                {/* Nequi */}
                <button
                  onClick={() => setSelectedMethod('NEQUI')}
                  className={`group relative overflow-hidden rounded-xl border-2 p-6 text-left transition-all ${
                    selectedMethod === 'NEQUI'
                      ? 'border-comercioplus-600 bg-comercioplus-50'
                      : 'border-slate-200 bg-white hover:border-comercioplus-300'
                  }`}
                >
                  <div className="mb-3 flex items-center justify-between">
                    <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100">
                      <Icon name="smartphone" size={24} className="text-purple-600" />
                    </div>
                    {selectedMethod === 'NEQUI' && (
                      <div className="flex h-6 w-6 items-center justify-center rounded-full bg-comercioplus-600">
                        <Icon name="check" size={14} className="text-white" />
                      </div>
                    )}
                  </div>
                  <h3 className="mb-1 font-bold text-slate-900">Nequi</h3>
                  <p className="text-sm text-slate-600">Billetera digital</p>
                  <p className="mt-2 text-xs text-slate-500">Paga desde tu app Nequi</p>
                </button>

                {/* Bancolombia */}
                <button
                  onClick={() => setSelectedMethod('BANCOLOMBIA')}
                  className={`group relative overflow-hidden rounded-xl border-2 p-6 text-left transition-all ${
                    selectedMethod === 'BANCOLOMBIA'
                      ? 'border-comercioplus-600 bg-comercioplus-50'
                      : 'border-slate-200 bg-white hover:border-comercioplus-300'
                  }`}
                >
                  <div className="mb-3 flex items-center justify-between">
                    <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-yellow-100">
                      <Icon name="credit-card" size={24} className="text-yellow-600" />
                    </div>
                    {selectedMethod === 'BANCOLOMBIA' && (
                      <div className="flex h-6 w-6 items-center justify-center rounded-full bg-comercioplus-600">
                        <Icon name="check" size={14} className="text-white" />
                      </div>
                    )}
                  </div>
                  <h3 className="mb-1 font-bold text-slate-900">Bancolombia</h3>
                  <p className="text-sm text-slate-600">Botón de pago</p>
                  <p className="mt-2 text-xs text-slate-500">Pago rápido y seguro</p>
                </button>

                {/* Tarjeta */}
                <button
                  onClick={() => setSelectedMethod('CARD')}
                  className={`group relative overflow-hidden rounded-xl border-2 p-6 text-left transition-all ${
                    selectedMethod === 'CARD'
                      ? 'border-comercioplus-600 bg-comercioplus-50'
                      : 'border-slate-200 bg-white hover:border-comercioplus-300'
                  }`}
                >
                  <div className="mb-3 flex items-center justify-between">
                    <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-green-100">
                      <Icon name="credit-card" size={24} className="text-green-600" />
                    </div>
                    {selectedMethod === 'CARD' && (
                      <div className="flex h-6 w-6 items-center justify-center rounded-full bg-comercioplus-600">
                        <Icon name="check" size={14} className="text-white" />
                      </div>
                    )}
                  </div>
                  <h3 className="mb-1 font-bold text-slate-900">Tarjeta</h3>
                  <p className="text-sm text-slate-600">Débito o crédito</p>
                  <p className="mt-2 text-xs text-slate-500">Visa, Mastercard, Amex</p>
                </button>
              </div>
            </div>
          </div>

          {/* Resumen */}
          <div className="lg:col-span-1">
            <div className="sticky top-24 rounded-2xl border-2 border-slate-200 bg-white p-6">
              <h2 className="mb-6 text-2xl font-bold text-slate-900">Resumen</h2>

              {/* Productos */}
              <div className="mb-6 max-h-60 space-y-3 overflow-y-auto">
                {items.map((item) => (
                  <div key={item.id} className="flex gap-3">
                    <img
                      src={item.image}
                      alt={item.name}
                      className="h-16 w-16 rounded-lg object-cover"
                    />
                    <div className="flex-1">
                      <p className="text-sm font-semibold text-slate-900">{item.name}</p>
                      <p className="text-xs text-slate-500">x{item.quantity}</p>
                      <p className="text-sm font-bold text-comercioplus-600">
                        ${(item.price * item.quantity).toLocaleString('es-CO')}
                      </p>
                    </div>
                  </div>
                ))}
              </div>

              {/* Totales */}
              <div className="mb-6 space-y-3 border-t border-slate-200 pt-4">
                <div className="flex justify-between text-slate-600">
                  <span>Subtotal</span>
                  <span className="font-semibold">${totalPrice.toLocaleString('es-CO')}</span>
                </div>
                <div className="flex justify-between text-slate-600">
                  <span>Envío</span>
                  <span className="font-semibold text-green-600">Gratis</span>
                </div>
                <div className="border-t border-slate-200 pt-3">
                  <div className="flex justify-between">
                    <span className="text-lg font-bold text-slate-900">Total</span>
                    <span className="text-2xl font-bold text-comercioplus-600">
                      ${totalPrice.toLocaleString('es-CO')} COP
                    </span>
                  </div>
                </div>
              </div>

              {/* Botón pagar */}
              <button
                onClick={handlePayment}
                disabled={!selectedMethod || isProcessing}
                className="mb-4 flex w-full items-center justify-center gap-2 rounded-xl bg-comercioplus-600 py-4 font-bold text-white transition-all hover:bg-comercioplus-700 disabled:opacity-50 disabled:cursor-not-allowed"
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

              {/* Seguridad */}
              <div className="rounded-xl bg-green-50 p-4">
                <div className="flex items-start gap-3">
                  <Icon name="shield" size={20} className="text-green-600" />
                  <div>
                    <p className="text-sm font-semibold text-green-900">
                      Pago 100% seguro
                    </p>
                    <p className="text-xs text-green-700">
                      Protegido por Wompi y encriptación SSL
                    </p>
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
