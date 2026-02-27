import { useCallback, useEffect, useMemo, useState } from 'react'
import { useParams, Link } from 'react-router-dom'
import API from '@/lib/api'
import { extractList } from '@/lib/api-response'
import { resolveMediaUrl } from '@/lib/format'
import { Icon } from '@/components/Icon'
import ProductCard from '@/components/ProductCard'
import { useCart } from '@/context/CartContext'
import CoverImage from '@/ui/images/CoverImage'
import LogoImage from '@/ui/images/LogoImage'
import {
  getImageBrightness,
  getStoredHeaderTheme,
  getThemeClassesByBrightness,
  storeHeaderTheme,
  type ImageBrightness,
} from '@/utils/imageTheme'
import type { Product, Store } from '@/types/api'

type StoreWithMeta = Store & {
  products_count?: number
  followers_count?: number
  is_verified?: boolean
}

const slugify = (value: string) =>
  String(value || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')

function sanitizeWhatsApp(raw: string): string {
  const digits = raw.replace(/\D/g, '')
  if (digits.startsWith('57') && digits.length >= 12) return digits
  if (digits.startsWith('3') && digits.length === 10) return `57${digits}`
  return digits
}

export default function StoreProducts() {
  const { storeSlug = '' } = useParams()
  const { addToCart, items } = useCart()

  const [store, setStore] = useState<StoreWithMeta | null>(null)
  const [products, setProducts] = useState<Product[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [isFollowing, setIsFollowing] = useState(false)
  const [sortBy, setSortBy] = useState('popular')
  const [headerTheme, setHeaderTheme] = useState<ImageBrightness>('dark')
  const [addedNoticeProductId, setAddedNoticeProductId] = useState<string | null>(null)

  useEffect(() => {
    const loadStoreData = async () => {
      setIsLoading(true)
      try {
        const storesResponse = await API.get('/public-stores', {
          params: { _t: Date.now() },
        })
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
          params: { store_id: foundStore.id, per_page: 48, status: 'active', _t: Date.now() },
        })
        const freshProducts = extractList<Product>(productsResponse.data)
        setProducts(freshProducts.filter((product) => Number(product.stock || 0) > 0))
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
    if (!addedNoticeProductId) return
    const timer = window.setTimeout(() => setAddedNoticeProductId(null), 1200)
    return () => window.clearTimeout(timer)
  }, [addedNoticeProductId])

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

  const storePublicUrl = useMemo(() => {
    if (typeof window === 'undefined') return ''
    const resolvedSlug = store?.slug || storeSlug
    return `${window.location.origin}/stores/${resolvedSlug}/products`
  }, [store?.slug, storeSlug])

  const buildWhatsAppMessage = useCallback(() => {
    if (items.length === 0) {
      return encodeURIComponent(storePublicUrl || 'Hola, vi tu tienda en ComercioPlus y me gustaria conocer tus productos.')
    }

    const total = items.reduce((sum, item) => sum + item.price * item.quantity, 0)
    const lines = items
      .map((item) => `- ${item.name} x${item.quantity} = $${(item.price * item.quantity).toLocaleString('es-CO')}`)
      .join('\n')

    const message = `Hola, me interesa hacer un pedido:\n\n${lines}\n\nTotal: $${total.toLocaleString('es-CO')}`
    return encodeURIComponent(message)
  }, [items, storePublicUrl])

  const sanitizedWhatsApp = useMemo(() => sanitizeWhatsApp(String(store?.whatsapp || '')), [store?.whatsapp])
  const whatsappUrl = useMemo(() => {
    if (!sanitizedWhatsApp) return ''
    return `https://wa.me/${sanitizedWhatsApp}?text=${buildWhatsAppMessage()}`
  }, [buildWhatsAppMessage, sanitizedWhatsApp])

  const handleAddToCart = (product: Product) => {
    if (!product?.id) return

    addToCart({
      id: String(product.id),
      name: product.name,
      price: Number(product.price || 0),
      image: resolveMediaUrl(product.image_url || product.image) || '/placeholder-product.png',
      seller: store?.name || 'ComercioPlus',
      storeId: String(store?.id || ''),
    })

    setAddedNoticeProductId(String(product.id))
  }

  const handleWhatsAppClick = () => {
    try {
      sessionStorage.setItem('checkout_channel', 'whatsapp')
    } catch {
      // noop
    }
  }

  const logo = resolveMediaUrl(store?.logo_url || store?.logo_path || store?.logo)
  const cover = resolveMediaUrl(store?.cover_url || store?.cover_path || store?.background_url || store?.cover)
  const storeId = store?.id || null
  const adaptiveTheme = getThemeClassesByBrightness(headerTheme)

  useEffect(() => {
    if (!storeId) return

    const cached = getStoredHeaderTheme(storeId)
    if (cached) {
      setHeaderTheme(cached)
      return
    }
    if (!cover) {
      setHeaderTheme('dark')
      return
    }

    let mounted = true
    getImageBrightness(cover).then((theme) => {
      if (!mounted) return
      setHeaderTheme(theme)
      storeHeaderTheme(storeId, theme)
    })

    return () => {
      mounted = false
    }
  }, [cover, storeId])

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

  return (
    <div className="min-h-screen bg-slate-50">
      <div className="relative">
        <CoverImage
          src={cover}
          ratio="free"
          className="h-64"
          onBrightnessChange={(theme) => {
            setHeaderTheme(theme)
            storeHeaderTheme(store.id, theme)
          }}
        >
          <div className="mx-auto flex h-full max-w-7xl items-end px-6 pb-6">
            <div className={`rounded-2xl border px-4 py-3 backdrop-blur ${adaptiveTheme.chip}`}>
              <p className={`text-[11px] font-semibold uppercase tracking-[0.12em] ${adaptiveTheme.textMuted}`}>
                Catalogo
              </p>
              <p className={`text-xl font-black leading-tight ${adaptiveTheme.textPrimary}`}>{store.name}</p>
            </div>
          </div>
        </CoverImage>

        <div className="mx-auto max-w-7xl px-6">
          <div className="relative -mt-16 rounded-2xl border-2 border-slate-200 bg-white p-8 shadow-premium-xl">
            <div className="flex flex-wrap items-start gap-6">
              <div className="relative flex-shrink-0">
                {logo ? (
                  <LogoImage
                    src={logo}
                    alt={store.name}
                    className="h-32 w-32 rounded-2xl border-4 border-white bg-white p-2 shadow-lg"
                  />
                ) : (
                  <div className="flex h-32 w-32 items-center justify-center rounded-2xl border-4 border-white bg-gradient-to-br from-comercioplus-600 to-comercioplus-700 shadow-lg">
                    <Icon name="store" size={56} className="text-white" />
                  </div>
                )}
                {store.is_verified && (
                  <div className="absolute -right-2 -top-2 flex h-10 w-10 items-center justify-center rounded-full bg-blue-500 shadow-lg">
                    <Icon name="check" size={20} className="text-white" />
                  </div>
                )}
              </div>

              <div className="flex-1">
                <div className="mb-4 flex flex-wrap items-start justify-between gap-4">
                  <div>
                    <div className="mb-2 flex flex-wrap items-center gap-2">
                      <h1 className="text-3xl font-bold text-slate-900">{store.name}</h1>
                      {store.is_verified && (
                        <span className="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                          ✓ Verificada
                        </span>
                      )}
                    </div>
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

                    {sanitizedWhatsApp && (
                      <a
                        href={whatsappUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        onClick={handleWhatsAppClick}
                        className="rounded-xl border-2 border-slate-200 bg-white px-6 py-3 font-semibold text-slate-700 transition-all hover:bg-slate-50"
                      >
                        <Icon name="message" size={20} className="mr-2 inline" />
                        {items.length > 0 ? 'Pedir por WhatsApp' : 'Ver catalogo en WhatsApp'}
                      </a>
                    )}
                  </div>
                </div>

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
              <div key={product.id}>
                <ProductCard
                  id={String(product.id)}
                  name={product.name}
                  category={product.category?.name || 'General'}
                  price={Number(product.price || 0)}
                  image={resolveMediaUrl(product.image_url || product.image) || '/placeholder-product.png'}
                  stock={Number(product.stock || 0)}
                  onAddToCart={() => handleAddToCart(product)}
                />
                {addedNoticeProductId === String(product.id) ? (
                  <p className="mt-2 text-center text-xs font-semibold text-emerald-700">Agregado al carrito ✅</p>
                ) : null}
              </div>
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
