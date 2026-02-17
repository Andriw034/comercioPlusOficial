import { useEffect, useMemo, useState } from 'react'
import { useParams, Link, useSearchParams } from 'react-router-dom'
import API from '@/lib/api'
import { extractList } from '@/lib/api-response'
import { resolveMediaUrl } from '@/lib/format'
import { Icon } from '@/components/Icon'
import Header from '@/components/Header'
import ProductCard from '@/components/ProductCard'
import { useCart } from '@/context/CartContext'
import type { Product, Store } from '@/types/api'

type StoreWithMeta = Store & {
  products_count?: number
  followers_count?: number
  verified?: boolean
}

const slugify = (value: string) =>
  String(value || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')

export default function StoreProducts() {
  const { storeSlug = '' } = useParams()
  const [searchParams] = useSearchParams()
  const { addToCart, totalItems } = useCart()

  const [store, setStore] = useState<StoreWithMeta | null>(null)
  const [products, setProducts] = useState<Product[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [isFollowing, setIsFollowing] = useState(false)
  const [sortBy, setSortBy] = useState('popular')

  useEffect(() => {
    const loadStoreData = async () => {
      setIsLoading(true)
      try {
        const storesResponse = await API.get('/public-stores')
        const allStores = extractList<StoreWithMeta>(storesResponse.data)

        const foundStore = allStores.find((item) => {
          const candidate = item.slug || slugify(item.name) || String(item.id)
          return candidate === storeSlug
        })

        if (!foundStore) {
          setStore(null)
          setProducts([])
          return
        }

        setStore(foundStore)

        const productsResponse = await API.get('/products', {
          params: { store_id: foundStore.id, per_page: 48, status: 'active' },
        })
        setProducts(extractList<Product>(productsResponse.data))
      } catch (error) {
        console.error('Error loading store:', error)
        setStore(null)
        setProducts([])
      } finally {
        setIsLoading(false)
      }
    }

    loadStoreData()
  }, [storeSlug])

  useEffect(() => {
    if (!store) {
      window.dispatchEvent(new CustomEvent('publicStore:changed', { detail: null }))
      return
    }

    const detail = {
      name: store.name,
      logoUrl: resolveMediaUrl(store.logo_url || store.logo_path || store.logo) || null,
    }

    window.dispatchEvent(new CustomEvent('publicStore:changed', { detail }))
    return () => {
      window.dispatchEvent(new CustomEvent('publicStore:changed', { detail: null }))
    }
  }, [store])

  const handleFollowToggle = async () => {
    if (!store) return

    try {
      if (isFollowing) {
        await API.delete(`/stores/${store.id}/follow`)
      } else {
        await API.post(`/stores/${store.id}/follow`)
      }
      setIsFollowing((prev) => !prev)
    } catch {
      setIsFollowing((prev) => !prev)
    }
  }

  const sortedProducts = useMemo(() => {
    const list = [...products]
    switch (sortBy) {
      case 'price_asc':
        return list.sort((a, b) => Number(a.price || 0) - Number(b.price || 0))
      case 'price_desc':
        return list.sort((a, b) => Number(b.price || 0) - Number(a.price || 0))
      case 'recent':
        return list.reverse()
      default:
        return list.sort((a, b) => Number(b.average_rating || b.rating || 0) - Number(a.average_rating || a.rating || 0))
    }
  }, [products, sortBy])

  const handleAddToCart = (product: Product) => {
    addToCart({
      id: String(product.id),
      name: product.name,
      price: Number(product.price || 0),
      image: resolveMediaUrl(product.image_url || product.image) || '/placeholder-product.png',
      seller: store?.name || 'ComercioPlus',
      storeId: String(store?.id || ''),
    })
  }

  if (isLoading) {
    return (
      <div className="flex min-h-screen items-center justify-center">
        <div className="text-center">
          <div className="mb-4 inline-block h-12 w-12 animate-spin rounded-full border-4 border-comercioplus-600 border-t-transparent" />
          <p className="text-slate-600">Cargando tienda...</p>
        </div>
      </div>
    )
  }

  if (!store) {
    return (
      <div className="flex min-h-screen items-center justify-center">
        <div className="text-center">
          <Icon name="alert" size={48} className="mx-auto mb-4 text-slate-400" />
          <h2 className="mb-2 text-2xl font-bold text-slate-900">Tienda no encontrada</h2>
          <Link to="/stores" className="text-comercioplus-600 hover:text-comercioplus-700">
            Ver todas las tiendas
          </Link>
        </div>
      </div>
    )
  }

  const logo = resolveMediaUrl(store.logo_url || store.logo_path || store.logo)
  const cover = resolveMediaUrl(store.cover_url || store.cover_path || store.background_url || store.cover)
  const showRegisteredAlert = searchParams.get('registered') === '1'

  return (
    <div className="min-h-screen bg-slate-50">
      <Header
        links={[
          { label: 'Tiendas', href: '/stores' },
          { label: store.name, href: `/stores/${storeSlug}/products`, active: true },
        ]}
        showAuth={false}
        showBell
        showCart
        cartCount={totalItems}
      />

      <div className="relative">
        <div className="relative h-64 overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-comercioplus-900">
          {cover ? (
            <img src={cover} alt="" className="h-full w-full object-cover opacity-40" />
          ) : (
            <div className="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-comercioplus-900" />
          )}
        </div>

        <div className="mx-auto max-w-7xl px-6">
          <div className="relative -mt-16 rounded-2xl border-2 border-slate-200 bg-white p-8 shadow-premium-xl">
            <div className="flex flex-wrap items-start gap-6">
              <div className="relative flex-shrink-0">
                {logo ? (
                  <img
                    src={logo}
                    alt={store.name}
                    className="h-32 w-32 rounded-2xl border-4 border-white object-cover shadow-lg"
                  />
                ) : (
                  <div className="flex h-32 w-32 items-center justify-center rounded-2xl border-4 border-white bg-gradient-to-br from-comercioplus-600 to-comercioplus-700 shadow-lg">
                    <Icon name="store" size={56} className="text-white" />
                  </div>
                )}
                {(store.verified ?? store.is_visible ?? true) && (
                  <div className="absolute -right-2 -top-2 flex h-10 w-10 items-center justify-center rounded-full bg-blue-500 shadow-lg">
                    <Icon name="check" size={20} className="text-white" />
                  </div>
                )}
              </div>

              <div className="flex-1">
                <div className="mb-4 flex flex-wrap items-start justify-between gap-4">
                  <div>
                    <h1 className="mb-2 text-3xl font-bold text-slate-900">{store.name}</h1>
                    <p className="mb-4 text-slate-600">
                      {store.description || 'Tienda especializada en productos de alta calidad'}
                    </p>

                    <div className="flex flex-wrap items-center gap-6">
                      <div className="flex items-center gap-2">
                        <Icon name="star" size={20} className="fill-amber-400 text-amber-400" />
                        <span className="font-semibold text-slate-900">{Number(store.rating || 0) || 'N/A'}</span>
                        <span className="text-slate-500">Rating</span>
                      </div>
                      <div className="flex items-center gap-2">
                        <Icon name="package" size={20} className="text-slate-600" />
                        <span className="font-semibold text-slate-900">{Number(store.products_count || products.length)}</span>
                        <span className="text-slate-500">Productos</span>
                      </div>
                      <div className="flex items-center gap-2">
                        <Icon name="users" size={20} className="text-slate-600" />
                        <span className="font-semibold text-slate-900">{Number(store.followers_count || 0)}</span>
                        <span className="text-slate-500">Seguidores</span>
                      </div>
                    </div>
                  </div>

                  <div className="flex gap-3">
                    <button
                      onClick={handleFollowToggle}
                      className={`rounded-xl px-6 py-3 font-semibold transition-all ${
                        isFollowing
                          ? 'border-2 border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                          : 'bg-comercioplus-600 text-white hover:bg-comercioplus-700 hover:shadow-lg'
                      }`}
                    >
                      {isFollowing ? (
                        <>
                          <Icon name="check" size={20} className="mr-2 inline" />
                          Siguiendo
                        </>
                      ) : (
                        <>
                          <Icon name="heart" size={20} className="mr-2 inline" />
                          Seguir tienda
                        </>
                      )}
                    </button>

                    <button className="rounded-xl border-2 border-slate-200 bg-white px-6 py-3 font-semibold text-slate-700 transition-all hover:bg-slate-50">
                      <Icon name="message" size={20} className="mr-2 inline" />
                      Contactar
                    </button>
                  </div>
                </div>

                {showRegisteredAlert && (
                  <div className="rounded-xl bg-green-50 p-4">
                    <div className="flex items-start gap-3">
                      <div className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-green-100">
                        <Icon name="check" size={16} className="text-green-600" />
                      </div>
                      <div>
                        <p className="font-semibold text-green-900">Te has registrado como cliente!</p>
                        <p className="text-sm text-green-700">
                          Ahora recibiras ofertas exclusivas y notificaciones de nuevos productos.
                        </p>
                      </div>
                    </div>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="mx-auto max-w-7xl px-6 py-12">
        <div className="mb-8 flex items-center justify-between">
          <div>
            <h2 className="mb-2 text-3xl font-bold text-slate-900">Productos Destacados</h2>
            <p className="text-slate-600">Lo mejor de {store.name}</p>
          </div>

          <select
            className="rounded-lg border-2 border-slate-200 bg-white px-4 py-2"
            value={sortBy}
            onChange={(e) => setSortBy(e.target.value)}
          >
            <option value="popular">Mas populares</option>
            <option value="price_asc">Precio: menor a mayor</option>
            <option value="price_desc">Precio: mayor a menor</option>
            <option value="recent">Mas recientes</option>
          </select>
        </div>

        {sortedProducts.length > 0 ? (
          <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {sortedProducts.map((product) => (
              <ProductCard
                key={product.id}
                id={String(product.id)}
                name={product.name}
                category={product.category?.name || 'General'}
                price={Number(product.price || 0)}
                image={resolveMediaUrl(product.image_url || product.image) || '/placeholder-product.png'}
                stock={Number(product.stock || 0)}
                onAddToCart={() => handleAddToCart(product)}
              />
            ))}
          </div>
        ) : (
          <div className="rounded-2xl border-2 border-dashed border-slate-200 bg-white p-12 text-center">
            <Icon name="package" size={48} className="mx-auto mb-4 text-slate-400" />
            <p className="text-lg font-semibold text-slate-900">No hay productos disponibles</p>
            <p className="text-slate-600">Esta tienda aun no ha publicado productos.</p>
          </div>
        )}
      </div>
    </div>
  )
}
