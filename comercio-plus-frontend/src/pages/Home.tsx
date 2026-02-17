import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { Icon } from '@/components/Icon'
import API from '@/lib/api'
import { extractList } from '@/lib/api-response'
import { resolveMediaUrl } from '@/lib/format'
import type { Store } from '@/types/api'

export default function Home() {
  const [currentImageIndex, setCurrentImageIndex] = useState(0)
  const [stores, setStores] = useState<Store[]>([])

  const backgroundImages = [
    'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=1920&q=80',
    'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=1920&q=80',
  ]

  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentImageIndex((prev) => (prev + 1) % backgroundImages.length)
    }, 8000)
    return () => clearInterval(interval)
  }, [])

  useEffect(() => {
    const loadStores = async () => {
      try {
        const response = await API.get('/public-stores')
        setStores(extractList<Store>(response.data).slice(0, 3))
      } catch {
        setStores([])
      }
    }

    loadStores()
  }, [])

  return (
    <div className="bg-slate-50">
      <section className="relative min-h-[600px] overflow-hidden">
        <div className="absolute inset-0">
          {backgroundImages.map((img, index) => (
            <motion.div
              key={index}
              initial={{ opacity: 0 }}
              animate={{ opacity: index === currentImageIndex ? 1 : 0 }}
              transition={{ duration: 1.5 }}
              className="absolute inset-0"
            >
              <img src={img} alt="" className="h-full w-full object-cover" />
            </motion.div>
          ))}
          <div className="absolute inset-0 bg-gradient-to-r from-slate-900/95 via-slate-900/80 to-slate-900/60" />
        </div>

        <div className="relative z-10 mx-auto max-w-7xl px-6 py-24">
          <div className="max-w-2xl">
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              className="mb-6 inline-flex items-center gap-2 rounded-full bg-comercioplus-500/20 px-4 py-2 backdrop-blur-sm"
            >
              <Icon name="clock" size={16} className="text-comercioplus-400" />
              <span className="text-sm font-semibold text-white">Ahorra hasta 15 horas semanales en gestión de pedidos</span>
            </motion.div>

            <motion.h1
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.1 }}
              className="mb-6 text-5xl font-bold leading-tight text-white lg:text-6xl"
            >
              Automatiza. Escala. Domina.
            </motion.h1>

            <motion.p
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.2 }}
              className="mb-8 text-xl text-slate-200"
            >
              Solución B2B integral para distribuidores mayoristas: catálogo digital en la nube, procesamiento automático de pedidos y analítica avanzada de rotación. Expande tu red comercial sin expandir tus costos.
            </motion.p>

            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.3 }}
              className="mb-8"
            >
              <div className="flex gap-3">
                <div className="relative flex-1">
                  <Icon
                    name="search"
                    size={20}
                    className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
                  />
                  <input
                    type="text"
                    placeholder="Buscar productos, tiendas..."
                    className="w-full rounded-xl border-0 bg-white/95 py-4 pl-12 pr-4 text-slate-900 backdrop-blur-sm placeholder:text-slate-500 focus:outline-none focus:ring-4 focus:ring-comercioplus-500/50"
                  />
                </div>
                <button className="rounded-xl bg-comercioplus-600 px-8 py-4 font-semibold text-white transition-all hover:bg-comercioplus-700 hover:shadow-xl">
                  Buscar
                </button>
              </div>
            </motion.div>

            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ delay: 0.4 }}
              className="flex flex-wrap gap-4"
            >
              <div className="flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 backdrop-blur-sm">
                <Icon name="shield" size={18} className="text-green-400" />
                <span className="text-sm font-medium text-white">SSL Seguro</span>
              </div>
              <div className="flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 backdrop-blur-sm">
                <Icon name="package" size={18} className="text-blue-400" />
                <span className="text-sm font-medium text-white">5,000+ Productos</span>
              </div>
              <div className="flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 backdrop-blur-sm">
                <Icon name="headset" size={18} className="text-comercioplus-400" />
                <span className="text-sm font-medium text-white">Soporte 24/7</span>
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      <section className="py-10">
        <div className="mx-auto max-w-7xl px-6">
          <div className="mb-6 flex items-end justify-between">
            <div>
              <h2 className="text-3xl font-bold text-slate-900">Tiendas Creadas</h2>
              <p className="text-slate-600">Negocios reales ya publicados en ComercioPlus</p>
            </div>
            <Link to="/stores" className="text-sm font-semibold text-comercioplus-600 hover:text-comercioplus-700">
              Ver todas
            </Link>
          </div>

          {stores.length > 0 ? (
            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
              {stores.map((store) => {
                const logo = resolveMediaUrl(store.logo_url || store.logo_path || store.logo)
                const cover = resolveMediaUrl(
                  store.cover_url || store.cover_path || store.background_url || store.cover,
                )
                return (
                  <Link
                    key={store.id}
                    to="/stores"
                    className="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition-all hover:-translate-y-1 hover:shadow-lg"
                  >
                    <div className="h-28 bg-slate-800">
                      {cover ? (
                        <img src={cover} alt="" className="h-full w-full object-cover opacity-80" />
                      ) : (
                        <div className="h-full w-full bg-gradient-to-r from-slate-800 to-slate-700" />
                      )}
                    </div>
                    <div className="-mt-6 p-4">
                      <div className="mb-3 flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl border-2 border-white bg-white shadow">
                        {logo ? (
                          <img src={logo} alt={store.name} className="h-full w-full object-cover" />
                        ) : (
                          <Icon name="store" size={18} className="text-comercioplus-600" />
                        )}
                      </div>
                      <h3 className="line-clamp-1 text-lg font-bold text-slate-900">{store.name}</h3>
                      <p className="line-clamp-2 text-sm text-slate-600">
                        {store.description || 'Tienda verificada dentro de ComercioPlus'}
                      </p>
                    </div>
                  </Link>
                )
              })}
            </div>
          ) : (
            <div className="rounded-2xl border border-slate-200 bg-white p-6 text-center text-slate-600">
              No hay tiendas creadas para mostrar.
            </div>
          )}
        </div>
      </section>

      <section className="py-20">
        <div className="mx-auto max-w-7xl px-6">
          <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            {[
              { icon: 'rocket', title: 'Fácil de usar', desc: 'Crea tu tienda en 5 minutos' },
              { icon: 'shield', title: 'Pagos seguros', desc: 'Encriptación bancaria' },
              { icon: 'truck', title: 'Envíos rápidos', desc: 'Integración con transportadoras' },
              { icon: 'headset', title: 'Soporte 24/7', desc: 'Siempre disponibles' },
            ].map((feature, i) => (
              <motion.div
                key={i}
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ delay: i * 0.1 }}
                viewport={{ once: true }}
                className="group rounded-2xl border border-slate-200 bg-white p-8 transition-all hover:-translate-y-2 hover:shadow-premium-lg"
              >
                <div className="mb-4 inline-flex h-14 w-14 items-center justify-center rounded-xl bg-comercioplus-100 text-comercioplus-600">
                  <Icon name={feature.icon as any} size={28} />
                </div>
                <h3 className="mb-2 text-xl font-bold text-slate-900">{feature.title}</h3>
                <p className="text-slate-600">{feature.desc}</p>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      <section className="bg-gradient-to-br from-comercioplus-600 to-comercioplus-700 py-20 text-white">
        <div className="mx-auto max-w-4xl px-6 text-center">
          <h2 className="mb-4 text-4xl font-bold">¿Listo para empezar a vender?</h2>
          <p className="mb-8 text-xl text-comercioplus-100">Únete a miles de vendedores exitosos en ComercioPlus</p>
          <div className="flex flex-wrap justify-center gap-4">
            <Link
              to="/register"
              className="rounded-xl bg-white px-8 py-4 font-bold text-comercioplus-600 transition-all hover:bg-slate-50 hover:shadow-xl"
            >
              Crear mi tienda gratis
            </Link>
            <Link
              to="/how-it-works"
              className="rounded-xl border-2 border-white px-8 py-4 font-bold transition-all hover:bg-white/10"
            >
              Ver demostración
            </Link>
          </div>
        </div>
      </section>
    </div>
  )
}

