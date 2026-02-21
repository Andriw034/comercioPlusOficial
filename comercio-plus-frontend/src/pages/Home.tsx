import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { Icon } from '@/components/Icon'
import API from '@/lib/api'
import { extractList } from '@/lib/api-response'
import { resolveMediaUrl } from '@/lib/format'
import type { Category, Product, Store } from '@/types/api'

export default function Home() {
  const [currentImageIndex, setCurrentImageIndex] = useState(0)
  const [stores, setStores] = useState<Store[]>([])
  const [publicStoreCount, setPublicStoreCount] = useState(0)
  const [publicProductCount, setPublicProductCount] = useState(0)
  const [publicCategoryCount, setPublicCategoryCount] = useState(0)

  const backgroundImages = [
    'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=1920&q=80',
    'https://images.unsplash.com/photo-1553413077-190dd305871c?w=1920&q=80',
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
        const [storesResponse, productsResponse, categoriesResponse] = await Promise.all([
          API.get('/public-stores'),
          API.get('/products'),
          API.get('/categories'),
        ])

        const allStores = extractList<Store>(storesResponse.data)
        const allProducts = extractList<Product>(productsResponse.data)
        const allCategories = extractList<Category>(categoriesResponse.data)

        setStores(allStores.slice(0, 3))
        setPublicStoreCount(allStores.length)
        setPublicProductCount(allProducts.length)
        setPublicCategoryCount(allCategories.length)
      } catch {
        setStores([])
        setPublicStoreCount(0)
        setPublicProductCount(0)
        setPublicCategoryCount(0)
      }
    }

    loadStores()
  }, [])

  const formatMetric = (value: number) => value.toLocaleString('es-CO')
  const heroMetrics = [
    { label: 'Distribuidores activos', value: publicStoreCount },
    { label: 'Productos publicados', value: publicProductCount },
    { label: 'Categorias en uso', value: publicCategoryCount },
  ]

  return (
    <div className="bg-slate-50">
      <section className="relative overflow-hidden bg-slate-950">
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,106,0,0.28),transparent_55%)]" />

        <div className="relative z-10 mx-auto max-w-7xl px-6 py-10 lg:py-14">
          <div className="grid items-stretch gap-6 lg:grid-cols-[1.08fr_0.92fr]">
            <motion.div
              initial={{ opacity: 0, y: 16 }}
              animate={{ opacity: 1, y: 0 }}
              className="flex flex-col justify-center rounded-3xl border border-white/10 bg-slate-950/70 p-7 shadow-[0_20px_60px_rgba(0,0,0,0.35)] backdrop-blur-sm lg:p-10"
            >
              <div className="mb-5 inline-flex w-fit items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1.5">
                <Icon name="clock" size={14} className="text-comercioplus-400" />
                <span className="text-xs font-semibold uppercase tracking-wide text-white/80">B2B para mayoristas</span>
              </div>

              <h1 className="mb-4 max-w-xl text-4xl font-bold leading-tight text-white lg:text-5xl">
                Automatiza. Escala. Domina.
              </h1>
              <p className="mb-7 max-w-2xl text-base text-slate-200 lg:text-lg">
                Catalogo digital y gestion de pedidos para distribuidores mayoristas.
              </p>

              <div className="mb-8 flex flex-wrap gap-3">
                <Link
                  to="/login"
                  className="rounded-xl bg-comercioplus-600 px-7 py-3 font-semibold text-white transition-all hover:bg-comercioplus-700 hover:shadow-xl"
                >
                  Empezar gratis
                </Link>
                <Link
                  to="/how-it-works"
                  className="rounded-xl border border-white/35 px-7 py-3 font-semibold text-white transition-all hover:bg-white/10"
                >
                  Ver demo
                </Link>
              </div>

              <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                {heroMetrics.map((metric) => (
                  <div key={metric.label} className="rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                    <p className="text-2xl font-bold text-white">{formatMetric(metric.value)}</p>
                    <p className="mt-1 text-xs uppercase tracking-wide text-white/70">{metric.label}</p>
                  </div>
                ))}
              </div>
            </motion.div>

            <div className="relative hidden min-h-[460px] overflow-hidden rounded-3xl border border-white/10 bg-slate-900 lg:block">
              {backgroundImages.map((img, index) => (
                <motion.div
                  key={index}
                  initial={{ opacity: 0 }}
                  animate={{ opacity: index === currentImageIndex ? 1 : 0 }}
                  transition={{ duration: 1.2 }}
                  className="absolute inset-0"
                >
                  <img src={img} alt="Operacion logistica B2B" className="h-full w-full object-cover" />
                </motion.div>
              ))}
              <div className="absolute inset-0 bg-gradient-to-r from-black/85 via-black/55 to-black/30" />
              <div className="absolute bottom-5 left-5 right-5 rounded-2xl border border-white/15 bg-black/35 p-4 backdrop-blur-sm">
                <p className="text-sm font-semibold text-white">Operacion mayorista en tiempo real</p>
                <p className="mt-1 text-xs text-slate-200">Controla inventario, pedidos y rendimiento desde un solo panel.</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="py-[14px]">
        <div className="mx-auto max-w-7xl px-6">
          <div className="mb-[12px] flex items-end justify-between">
            <div>
              <h2 className="text-3xl font-bold text-slate-900">Tiendas Creadas</h2>
              <p className="text-slate-600">Negocios reales ya publicados en ComercioPlus</p>
            </div>
            <Link to="/stores" className="text-sm font-semibold text-comercioplus-600 hover:text-comercioplus-700">
              Ver todas
            </Link>
          </div>

          {stores.length > 0 ? (
            <div className="grid grid-cols-1 gap-[12px] md:grid-cols-3">
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
            <div className="rounded-2xl border border-slate-200 bg-white p-4 text-center text-slate-600">
              No hay tiendas creadas para mostrar.
            </div>
          )}
        </div>
      </section>

      <section className="py-[10px]">
        <div className="mx-auto max-w-7xl px-6">
          <div className="grid grid-cols-1 gap-[10px] md:grid-cols-2 lg:grid-cols-4">
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
                className="group rounded-2xl border border-slate-200 bg-white p-4 transition-all hover:-translate-y-2 hover:shadow-premium-lg"
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

      <section className="relative left-1/2 right-1/2 mb-0 w-screen -translate-x-1/2 bg-gradient-to-br from-comercioplus-500 to-comercioplus-600 py-20 md:py-24 text-white">
        <div className="mx-auto max-w-4xl px-6 text-center">
          <h2 className="mb-[10px] text-4xl font-bold">¿Listo para empezar a vender?</h2>
          <p className="mb-[10px] text-xl text-comercioplus-100">Únete a miles de vendedores exitosos en ComercioPlus</p>
          <div className="flex flex-wrap justify-center gap-[10px]">
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

