import { Link } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import { Icon } from '@/components/Icon'
import { useCart } from '@/context/CartContext'

export default function Cart() {
  const { items, removeFromCart, updateQuantity, totalItems, totalPrice } = useCart()

  if (items.length === 0) {
    return (
      <div className="min-h-screen bg-slate-50 py-12">
        <div className="mx-auto max-w-7xl px-6">
          <div className="rounded-2xl bg-white p-12 text-center shadow-premium">
            <div className="mb-6 inline-flex h-24 w-24 items-center justify-center rounded-full bg-slate-100">
              <Icon name="cart" size={48} className="text-slate-400" />
            </div>
            <h2 className="mb-4 text-3xl font-bold text-slate-900">Tu carrito está vacío</h2>
            <p className="mb-8 text-lg text-slate-600">
              Agrega productos para empezar a comprar
            </p>
            <Link
              to="/products"
              className="inline-flex items-center gap-2 rounded-xl bg-comercioplus-600 px-8 py-4 font-semibold text-white transition-all hover:bg-comercioplus-700"
            >
              <Icon name="search" size={20} />
              Explorar productos
            </Link>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-slate-50 py-12">
      <div className="mx-auto max-w-7xl px-6">
        {/* Header */}
        <div className="mb-8">
          <h1 className="mb-2 text-4xl font-bold text-slate-900">Carrito de compras</h1>
          <p className="text-lg text-slate-600">{totalItems} productos</p>
        </div>

        <div className="grid gap-8 lg:grid-cols-3">
          {/* Items del carrito */}
          <div className="lg:col-span-2">
            <div className="space-y-4">
              <AnimatePresence>
                {items.map((item) => (
                  <motion.div
                    key={item.id}
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, x: -100 }}
                    className="rounded-2xl border-2 border-slate-200 bg-white p-6 transition-all hover:border-comercioplus-500"
                  >
                    <div className="flex gap-6">
                      {/* Imagen */}
                      <div className="h-24 w-24 flex-shrink-0 overflow-hidden rounded-xl bg-slate-100">
                        <img
                          src={item.image}
                          alt={item.name}
                          className="h-full w-full object-cover"
                        />
                      </div>

                      {/* Info */}
                      <div className="flex-1">
                        <div className="mb-2 flex items-start justify-between">
                          <div>
                            <h3 className="mb-1 text-lg font-bold text-slate-900">
                              {item.name}
                            </h3>
                            <p className="text-sm text-slate-500">
                              Vendido por: {item.seller}
                            </p>
                          </div>
                          <button
                            onClick={() => removeFromCart(item.productId)}
                            className="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-danger/10 hover:text-danger"
                          >
                            <Icon name="trash" size={18} />
                          </button>
                        </div>

                        <div className="flex items-center justify-between">
                          {/* Precio */}
                          <div>
                            <span className="text-2xl font-bold text-comercioplus-600">
                              ${(item.price * item.quantity).toLocaleString('es-CO')}
                            </span>
                            <span className="ml-2 text-sm text-slate-500">
                              ${item.price.toLocaleString('es-CO')} c/u
                            </span>
                          </div>

                          {/* Cantidad */}
                          <div className="flex items-center gap-2">
                            <button
                              onClick={() => updateQuantity(item.productId, item.quantity - 1)}
                              className="flex h-8 w-8 items-center justify-center rounded-lg border-2 border-slate-200 text-slate-600 transition-all hover:border-comercioplus-500 hover:bg-comercioplus-50 hover:text-comercioplus-600"
                            >
                              <Icon name="minus" size={16} />
                            </button>
                            <span className="w-12 text-center font-semibold text-slate-900">
                              {item.quantity}
                            </span>
                            <button
                              onClick={() => updateQuantity(item.productId, item.quantity + 1)}
                              className="flex h-8 w-8 items-center justify-center rounded-lg border-2 border-slate-200 text-slate-600 transition-all hover:border-comercioplus-500 hover:bg-comercioplus-50 hover:text-comercioplus-600"
                            >
                              <Icon name="plus" size={16} />
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </motion.div>
                ))}
              </AnimatePresence>
            </div>
          </div>

          {/* Resumen */}
          <div className="lg:col-span-1">
            <div className="sticky top-24 rounded-2xl border-2 border-slate-200 bg-white p-6">
              <h2 className="mb-6 text-2xl font-bold text-slate-900">Resumen del pedido</h2>

              <div className="mb-6 space-y-4">
                <div className="flex justify-between text-slate-600">
                  <span>Subtotal</span>
                  <span className="font-semibold">
                    ${totalPrice.toLocaleString('es-CO')}
                  </span>
                </div>
                <div className="flex justify-between text-slate-600">
                  <span>Envío</span>
                  <span className="font-semibold text-green-600">Gratis</span>
                </div>
                <div className="border-t-2 border-slate-100 pt-4">
                  <div className="flex justify-between">
                    <span className="text-lg font-semibold text-slate-900">Total</span>
                    <span className="text-2xl font-bold text-comercioplus-600">
                      ${totalPrice.toLocaleString('es-CO')} COP
                    </span>
                  </div>
                </div>
              </div>

              {/* Botón checkout */}
              <Link
                to="/checkout"
                className="mb-4 flex w-full items-center justify-center gap-2 rounded-xl bg-comercioplus-600 py-4 font-bold text-white transition-all hover:bg-comercioplus-700 hover:shadow-lg hover:shadow-comercioplus-600/25"
              >
                <Icon name="lock" size={20} />
                Proceder al pago
              </Link>

              <Link
                to="/products"
                className="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-slate-200 bg-white py-4 font-semibold text-slate-700 transition-all hover:bg-slate-50"
              >
                <Icon name="arrow-left" size={20} />
                Seguir comprando
              </Link>

              {/* Trust badges */}
              <div className="mt-6 space-y-3 rounded-xl bg-slate-50 p-4">
                <div className="flex items-center gap-3">
                  <Icon name="shield" size={20} className="text-green-600" />
                  <span className="text-sm text-slate-700">Compra 100% segura</span>
                </div>
                <div className="flex items-center gap-3">
                  <Icon name="truck" size={20} className="text-blue-600" />
                  <span className="text-sm text-slate-700">Envío rápido</span>
                </div>
                <div className="flex items-center gap-3">
                  <Icon name="refresh" size={20} className="text-comercioplus-600" />
                  <span className="text-sm text-slate-700">Devoluciones fáciles</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
