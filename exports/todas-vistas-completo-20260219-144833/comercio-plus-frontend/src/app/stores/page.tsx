import { useEffect, useMemo, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import API from '@/lib/api'
import { extractList } from '@/lib/api-response'
import { resolveMediaUrl } from '@/lib/format'
import { Icon } from '@/components/Icon'
import type { Store } from '@/types/api'

type StoreCard = Store & {
  products_count?: number
  followers_count?: number
  featured?: boolean
  verified?: boolean
}

const slugify = (value: string) =>
  String(value || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')

export default function Stores() {
  const navigate = useNavigate()
  const [stores, setStores] = useState<StoreCard[]>([])
  const [searchQuery, setSearchQuery] = useState('')
  const [selectedStore, setSelectedStore] = useState<StoreCard | null>(null)
  const [showRegisterModal, setShowRegisterModal] = useState(false)
  const [isRegistering, setIsRegistering] = useState(false)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    const loadStores = async () => {
      try {
        setLoading(true)
        setError('')
        const response = await API.get('/public-stores')
        const list = extractList<StoreCard>(response.data)
        setStores(list)
      } catch (err: any) {
        setError(err?.response?.data?.message || 'No se pudieron cargar las tiendas')
        setStores([])
      } finally {
        setLoading(false)
      }
    }

    loadStores()
  }, [])

  const filteredStores = useMemo(
    () =>
      stores.filter((store) =>
        `${store.name} ${store.description || ''}`.toLowerCase().includes(searchQuery.toLowerCase()),
      ),
    [stores, searchQuery],
  )

  const getStoreSlug = (store: StoreCard) => store.slug || slugify(store.name) || String(store.id)

  const handleStoreClick = (store: StoreCard) => {
    setSelectedStore(store)
    setShowRegisterModal(true)
  }

  const handleRegisterAsCustomer = async () => {
    if (!selectedStore) return

    const userRaw = localStorage.getItem('user')
    const parsedUser = userRaw ? JSON.parse(userRaw) : null
    const userId = parsedUser?.id || localStorage.getItem('userId')

    setIsRegistering(true)
    try {
      await API.post('/stores/register-customer', {
        storeId: selectedStore.id,
        userId,
      })

      setShowRegisterModal(false)
      navigate(`/stores/${getStoreSlug(selectedStore)}/products?registered=1`)
    } catch {
      setShowRegisterModal(false)
      navigate(`/stores/${getStoreSlug(selectedStore)}/products`)
    } finally {
      setIsRegistering(false)
    }
  }

  const handleSkipRegistration = () => {
    if (!selectedStore) return
    setShowRegisterModal(false)
    navigate(`/stores/${getStoreSlug(selectedStore)}/products`)
  }

  return (
    <div className="min-h-screen bg-slate-50">
      <div className="bg-white py-12 shadow-sm">
        <div className="mx-auto max-w-7xl px-6">
          <div className="mb-6">
            <h1 className="mb-2 text-4xl font-bold text-slate-900">Tiendas</h1>
            <p className="text-lg text-slate-600">Descubre vendedores unicos y encuentra productos exclusivos</p>
          </div>

          <div className="relative max-w-2xl">
            <Icon
              name="search"
              size={20}
              className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
            />
            <input
              type="text"
              placeholder="Buscar tienda..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full rounded-xl border-2 border-slate-200 bg-white py-4 pl-12 pr-4 text-slate-900 transition-all focus:border-comercioplus-500 focus:outline-none focus:ring-4 focus:ring-comercioplus-500/10"
            />
          </div>
        </div>
      </div>

      <div className="mx-auto max-w-7xl px-6 py-12">
        <div className="mb-6 flex items-center justify-between">
          <p className="text-sm text-slate-600">{filteredStores.length} tiendas encontradas</p>
          <select className="rounded-lg border-2 border-slate-200 bg-white px-4 py-2 text-sm">
            <option>Mas populares</option>
            <option>Mejor valoradas</option>
            <option>Mas recientes</option>
          </select>
        </div>

        {loading && (
          <div className="flex justify-center py-8">
            <div className="h-10 w-10 animate-spin rounded-full border-4 border-comercioplus-600 border-t-transparent" />
          </div>
        )}

        {!loading && error && (
          <div className="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{error}</div>
        )}

        {!loading && !error && (
          <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            {filteredStores.map((store, index) => {
              const logo = resolveMediaUrl(store.logo_url || store.logo_path || store.logo)
              const cover = resolveMediaUrl(
                store.cover_url || store.cover_path || store.background_url || store.cover,
              )
              const rating = Number(store.rating || 0)
              const productsCount = Number(store.products_count || 0)
              const followers = Number(store.followers_count || 0)

              return (
                <motion.div
                  key={store.id}
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: index * 0.05 }}
                  className="group cursor-pointer"
                  onClick={() => handleStoreClick(store)}
                >
                  <div className="relative overflow-hidden rounded-2xl border-2 border-slate-200 bg-white transition-all duration-300 hover:-translate-y-2 hover:border-comercioplus-500 hover:shadow-premium-xl">
                    <div className="absolute inset-0 rounded-2xl bg-gradient-to-br from-comercioplus-500/0 to-comercioplus-600/0 opacity-0 transition-opacity duration-300 group-hover:opacity-10" />

                    <div className="relative h-32 overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-comercioplus-900">
                      {cover ? (
                        <img src={cover} alt="" className="h-full w-full object-cover opacity-50" />
                      ) : (
                        <div className="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-comercioplus-900" />
                      )}

                      {(store.featured || false) && (
                        <div className="absolute left-3 top-3">
                          <span className="flex items-center gap-1 rounded-lg bg-amber-500 px-3 py-1 text-xs font-bold text-white shadow-lg">
                            <Icon name="star" size={14} className="fill-white" />
                            Destacada
                          </span>
                        </div>
                      )}
                    </div>

                    <div className="relative -mt-12 px-6">
                      <div className="relative inline-block">
                        {logo ? (
                          <img
                            src={logo}
                            alt={store.name}
                            className="h-24 w-24 rounded-2xl border-4 border-white bg-white object-cover shadow-lg"
                          />
                        ) : (
                          <div className="flex h-24 w-24 items-center justify-center rounded-2xl border-4 border-white bg-gradient-to-br from-comercioplus-600 to-comercioplus-700 shadow-lg">
                            <Icon name="store" size={40} className="text-white" />
                          </div>
                        )}
                        {(store.verified ?? store.is_visible ?? true) && (
                          <div className="absolute -right-2 -top-2 flex h-8 w-8 items-center justify-center rounded-full bg-blue-500 shadow-lg">
                            <Icon name="check" size={16} className="text-white" />
                          </div>
                        )}
                      </div>
                    </div>

                    <div className="p-6 pt-4">
                      <h3 className="mb-2 text-xl font-bold text-slate-900 transition-colors group-hover:text-comercioplus-600">
                        {store.name}
                      </h3>

                      <p className="mb-4 line-clamp-2 text-sm text-slate-600">
                        {store.description || 'Tienda especializada con productos de alta calidad'}
                      </p>

                      <div className="mb-4 flex items-center gap-4">
                        <div className="flex items-center gap-1">
                          <Icon name="star" size={16} className="fill-amber-400 text-amber-400" />
                          <span className="text-sm font-semibold text-slate-900">{rating > 0 ? rating : 'N/A'}</span>
                        </div>
                        <div className="flex items-center gap-1 text-sm text-slate-600">
                          <Icon name="package" size={16} />
                          {productsCount} productos
                        </div>
                        <div className="flex items-center gap-1 text-sm text-slate-600">
                          <Icon name="users" size={16} />
                          {followers}
                        </div>
                      </div>

                      <div className="mb-4">
                        <span className="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                          <Icon name="tag" size={14} />
                          {store.location?.city || 'General'}
                        </span>
                      </div>

                      <button className="w-full rounded-xl bg-comercioplus-600 py-3 font-semibold text-white transition-all hover:bg-comercioplus-700 hover:shadow-lg hover:shadow-comercioplus-600/25">
                        Ver tienda
                      </button>
                    </div>
                  </div>
                </motion.div>
              )
            })}
          </div>
        )}
      </div>

      <AnimatePresence>
        {showRegisterModal && selectedStore && (
          <>
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              className="fixed inset-0 z-[110] bg-slate-900/80 backdrop-blur-sm"
              onClick={() => !isRegistering && setShowRegisterModal(false)}
            />

            <div className="fixed inset-0 z-[120] flex items-center justify-center p-3">
            <motion.div
              initial={{ opacity: 0, scale: 0.95, y: 20 }}
              animate={{ opacity: 1, scale: 1, y: 0 }}
              exit={{ opacity: 0, scale: 0.95, y: 20 }}
              className="max-h-[88vh] w-full max-w-[430px] overflow-y-auto rounded-2xl bg-white p-6 shadow-premium-xl"
            >
              <div className="mb-4 flex justify-center">
                {resolveMediaUrl(selectedStore.logo_url || selectedStore.logo_path || selectedStore.logo) ? (
                  <img
                    src={resolveMediaUrl(selectedStore.logo_url || selectedStore.logo_path || selectedStore.logo) || ''}
                    alt={selectedStore.name}
                    className="h-16 w-16 rounded-xl object-cover shadow-lg"
                  />
                ) : (
                  <div className="flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br from-comercioplus-600 to-comercioplus-700 shadow-lg">
                    <Icon name="store" size={26} className="text-white" />
                  </div>
                )}
              </div>

              <h3 className="mb-1 text-center text-2xl font-bold leading-tight text-slate-900">Quieres registrarte como cliente?</h3>

              <p className="mb-2 text-center text-base font-semibold text-comercioplus-600">{selectedStore.name}</p>

              <p className="mb-4 text-center text-[14px] text-slate-600">
                Al registrarte, recibiras ofertas exclusivas, notificaciones de nuevos productos y seguimiento
                personalizado de tus pedidos.
              </p>

              <div className="mb-4 space-y-2 rounded-xl bg-slate-50 p-3">
                <div className="flex items-start gap-2.5">
                  <div className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-green-100">
                    <Icon name="check" size={14} className="text-green-600" />
                  </div>
                  <div>
                    <p className="font-semibold text-slate-900">Ofertas exclusivas</p>
                    <p className="text-[13px] text-slate-600">Descuentos solo para clientes</p>
                  </div>
                </div>
                <div className="flex items-start gap-2.5">
                  <div className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100">
                    <Icon name="bell" size={14} className="text-blue-600" />
                  </div>
                  <div>
                    <p className="font-semibold text-slate-900">Notificaciones</p>
                    <p className="text-[13px] text-slate-600">Enterate de nuevos productos primero</p>
                  </div>
                </div>
                <div className="flex items-start gap-2.5">
                  <div className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-comercioplus-100">
                    <Icon name="package" size={14} className="text-comercioplus-600" />
                  </div>
                  <div>
                    <p className="font-semibold text-slate-900">Seguimiento</p>
                    <p className="text-[13px] text-slate-600">Historial completo de pedidos</p>
                  </div>
                </div>
              </div>

              <div className="flex gap-3">
                <button
                  onClick={handleSkipRegistration}
                  disabled={isRegistering}
                  className="flex-1 rounded-xl border-2 border-slate-200 bg-white px-4 py-2.5 font-semibold text-slate-700 transition-all hover:border-slate-300 hover:bg-slate-50 disabled:opacity-50"
                >
                  Solo ver productos
                </button>
                <button
                  onClick={handleRegisterAsCustomer}
                  disabled={isRegistering}
                  className="flex-1 rounded-xl bg-comercioplus-600 px-4 py-2.5 font-semibold text-white transition-all hover:bg-comercioplus-700 hover:shadow-lg disabled:opacity-50"
                >
                  {isRegistering ? (
                    <span className="flex items-center justify-center gap-2">
                      <div className="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent" />
                      Registrando...
                    </span>
                  ) : (
                    'Si, registrarme!'
                  )}
                </button>
              </div>
            </motion.div>
            </div>
          </>
        )}
      </AnimatePresence>
    </div>
  )
}
