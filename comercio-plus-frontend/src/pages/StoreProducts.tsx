import React, { useCallback, useEffect, useMemo, useState } from 'react'
import { useParams, Link, useNavigate } from 'react-router-dom'
import API from '@/lib/api'
import { extractList } from '@/lib/api-response'
import { resolveMediaUrl } from '@/lib/format'
import { Icon } from '@/components/Icon'
import { useCart } from '@/context/CartContext'
import type { CartProductInput } from '@/context/CartContext'
import { useStoreContext } from '@/context/StoreContext'
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

type ConflictDetail = {
  newStoreId: string
  newStoreName: string
  newStoreSlug: string
  pendingItem: CartProductInput
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

const gradients = [
  'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
  'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
  'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
  'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
  'linear-gradient(135deg, #30cfd0 0%, #330867 100%)',
]

const gradientFromSeed = (seed: string) => {
  let hash = 0
  for (let i = 0; i < seed.length; i += 1) {
    hash = (hash * 31 + seed.charCodeAt(i)) >>> 0
  }
  return gradients[hash % gradients.length]
}

function ProductImage({ src, fallback, name }: { src: string | null; fallback: string; name: string }) {
  const [failed, setFailed] = React.useState(false)

  if (!src || failed) {
    return (
      <div
        className="h-full w-full flex items-center justify-center"
        style={{ background: fallback }}
      >
        <div className="text-center">
          <div style={{ fontSize: 32 }}>📦</div>
          <div style={{ fontSize: 10, color: 'rgba(255,255,255,0.8)', marginTop: 4, fontWeight: 700, letterSpacing: 1, textTransform: 'uppercase' }}>
            {name.substring(0, 12)}{name.length > 12 ? '…' : ''}
          </div>
        </div>
      </div>
    )
  }

  return (
    <img
      src={src}
      alt={name}
      className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
      onError={() => setFailed(true)}
    />
  )
}

export default function StoreProducts() {
  const { storeSlug = '' } = useParams()
  const navigate = useNavigate()
  const { addToCart, switchStoreAndAdd, items, totalItems, cart } = useCart()
  const { setActiveStore } = useStoreContext()

  const [store, setStore] = useState<StoreWithMeta | null>(null)
  const [products, setProducts] = useState<Product[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState(false)
  const [isFollowing, setIsFollowing] = useState(false)
  const [sortBy, setSortBy] = useState('popular')
  const [search, setSearch] = useState('')
  const [debouncedSearch, setDebouncedSearch] = useState('')
  const [selectedCategory, setSelectedCategory] = useState<string>('all')
  const [headerTheme, setHeaderTheme] = useState<ImageBrightness>('dark')
  const [toast, setToast] = useState<{ visible: boolean; name: string }>({ visible: false, name: '' })
  const [toastFading, setToastFading] = useState(false)
  const [showConflictModal, setShowConflictModal] = useState(false)
  const [conflictPending, setConflictPending] = useState<ConflictDetail | null>(null)

  // Debounce search 300ms
  useEffect(() => {
    const timer = setTimeout(() => setDebouncedSearch(search), 300)
    return () => clearTimeout(timer)
  }, [search])

  // Listen for cart store-conflict events dispatched by CartContext
  useEffect(() => {
    const handler = (e: Event) => {
      const ev = e as CustomEvent<ConflictDetail>
      setConflictPending(ev.detail)
      setShowConflictModal(true)
    }
    window.addEventListener('cart:store-conflict', handler)
    return () => window.removeEventListener('cart:store-conflict', handler)
  }, [])

  const loadStoreData = useCallback(async () => {
    setIsLoading(true)
    setError(false)
    try {
      const storesResponse = await API.get('/public/stores', {
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
      setProducts(freshProducts)
    } catch (err) {
      console.error('Error loading store:', err)
      setError(true)
      setStore(null)
      setProducts([])
    } finally {
      setIsLoading(false)
    }
  }, [storeSlug])

  useEffect(() => {
    loadStoreData()
  }, [loadStoreData])

  // Sync active store into StoreContext (for Navbar branding)
  const logo = store ? resolveMediaUrl(store.logo_url || store.logo_path || store.logo) : null
  useEffect(() => {
    if (!store) {
      setActiveStore(null)
      window.dispatchEvent(new CustomEvent('publicStore:changed', { detail: null }))
      return
    }

    setActiveStore({
      id: String(store.id),
      name: store.name,
      logo: logo || null,
      slug: store.slug || storeSlug,
    })

    window.dispatchEvent(
      new CustomEvent('publicStore:changed', {
        detail: {
          name: store.name,
          logoUrl: logo || null,
        },
      }),
    )

    return () => {
      setActiveStore(null)
      window.dispatchEvent(new CustomEvent('publicStore:changed', { detail: null }))
    }
  }, [store, logo, storeSlug, setActiveStore])

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

  const categories = useMemo(() => {
    const cats = new Set<string>()
    products.forEach((p) => {
      if (p.category?.name) cats.add(p.category.name)
    })
    return Array.from(cats)
  }, [products])

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
        return list.sort(
          (a, b) => Number(b.average_rating || b.rating || 0) - Number(a.average_rating || a.rating || 0),
        )
    }
  }, [products, sortBy])

  const filteredProducts = useMemo(() => {
    let list = sortedProducts
    if (debouncedSearch) {
      const q = debouncedSearch.toLowerCase()
      list = list.filter((p) => p.name.toLowerCase().includes(q))
    }
    if (selectedCategory !== 'all') {
      list = list.filter((p) => p.category?.name === selectedCategory)
    }
    return list
  }, [sortedProducts, debouncedSearch, selectedCategory])

  const storePublicUrl = useMemo(() => {
    if (typeof window === 'undefined') return ''
    const resolvedSlug = store?.slug || storeSlug
    return `${window.location.origin}/stores/${resolvedSlug}/products`
  }, [store?.slug, storeSlug])

  const buildWhatsAppMessage = useCallback(() => {
    if (items.length === 0) {
      return encodeURIComponent(
        storePublicUrl || 'Hola, vi tu tienda en ComercioPlus y me gustaria conocer tus productos.',
      )
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

  const showToast = useCallback((name: string) => {
    setToast({ visible: true, name })
    setToastFading(false)
    const fadeTimer = setTimeout(() => setToastFading(true), 2000)
    const hideTimer = setTimeout(() => {
      setToast({ visible: false, name: '' })
      setToastFading(false)
    }, 2500)
    return () => {
      clearTimeout(fadeTimer)
      clearTimeout(hideTimer)
    }
  }, [])

  const handleAddToCart = (product: Product) => {
    if (!product?.id) return
    const item: CartProductInput = {
      id: String(product.id),
      name: product.name,
      price: Number(product.price || 0),
      image: resolveMediaUrl(product.image_url || product.image) || '/placeholder-product.png',
      seller: store?.name || 'ComercioPlus',
      storeId: String(store?.id || ''),
    }
    addToCart(item, String(store?.id || ''), store?.name || '', store?.slug || storeSlug)
    // Toast only shows if no conflict (conflict event is dispatched instead when different store)
    const targetStoreId = String(store?.id || '')
    if (!cart || cart.storeId === targetStoreId) {
      showToast(product.name)
    }
  }

  const handleWhatsAppClick = () => {
    try {
      sessionStorage.setItem('checkout_channel', 'whatsapp')
    } catch {
      // noop
    }
  }

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

  // --- Loading skeleton ---
  if (isLoading) {
    return (
      <div className="min-h-screen bg-slate-50">
        <div className="h-[220px] w-full animate-pulse bg-slate-200" />
        <div className="mx-auto max-w-7xl px-6">
          <div className="relative -mt-16 rounded-2xl border-2 border-slate-200 bg-white p-8 shadow-lg">
            <div className="flex gap-6">
              <div className="h-32 w-32 flex-shrink-0 animate-pulse rounded-2xl bg-slate-200" />
              <div className="flex-1 space-y-3 pt-2">
                <div className="h-7 w-48 animate-pulse rounded bg-slate-200" />
                <div className="h-4 w-72 animate-pulse rounded bg-slate-200" />
                <div className="h-4 w-40 animate-pulse rounded bg-slate-200" />
              </div>
            </div>
          </div>
        </div>
        <div className="mx-auto max-w-7xl px-6 py-12">
          <div className="grid grid-cols-2 gap-5 md:grid-cols-3 lg:grid-cols-4">
            {Array.from({ length: 8 }).map((_, i) => (
              <div key={i} className="overflow-hidden rounded-2xl border-2 border-slate-100 bg-white">
                <div className="h-48 w-full animate-pulse bg-slate-200" />
                <div className="space-y-2 p-4">
                  <div className="h-4 w-3/4 animate-pulse rounded bg-slate-200" />
                  <div className="h-4 w-1/2 animate-pulse rounded bg-slate-200" />
                  <div className="mt-3 h-9 w-full animate-pulse rounded-xl bg-slate-200" />
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    )
  }

  // --- Error state ---
  if (error) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-slate-50 px-6">
        <div className="w-full max-w-md rounded-2xl border-2 border-red-200 bg-red-50 p-8 text-center">
          <div className="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
            <Icon name="alert" size={32} className="text-red-500" />
          </div>
          <h2 className="mb-2 text-xl font-bold text-red-900">Error al cargar la tienda</h2>
          <p className="mb-6 text-red-700">
            No pudimos obtener la informacion. Verifica tu conexion e intenta nuevamente.
          </p>
          <button
            onClick={loadStoreData}
            className="rounded-xl bg-red-600 px-6 py-3 font-semibold text-white transition-all hover:bg-red-700"
          >
            Reintentar
          </button>
        </div>
      </div>
    )
  }

  // --- Not found ---
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
      {/* Cover + store header card */}
      <div className="relative">
        <CoverImage
          src={cover}
          ratio="free"
          className="h-[220px]"
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
              {/* Logo */}
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

              {/* Info + actions */}
              <div className="flex-1">
                <div className="mb-4 flex flex-wrap items-start justify-between gap-4">
                  <div>
                    <div className="mb-2 flex flex-wrap items-center gap-2">
                      <h1 className="text-3xl font-bold text-slate-900">{store.name}</h1>
                      {store.is_verified && (
                        <span className="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                          Verificada
                        </span>
                      )}
                    </div>
                    <p className="mb-4 text-slate-600">
                      {store.description || 'Tienda especializada en productos de alta calidad'}
                    </p>

                    {/* Stats row */}
                    <div className="flex flex-wrap items-center gap-5 text-sm">
                      <span className="flex items-center gap-1.5 text-slate-600">
                        <Icon name="package" size={16} className="text-slate-400" />
                        <span className="font-semibold text-slate-900">
                          {Number(store.products_count || products.length)}
                        </span>{' '}
                        productos
                      </span>
                      {Number(store.rating || 0) > 0 && (
                        <span className="flex items-center gap-1 text-slate-600">
                          <Icon name="star" size={16} className="fill-amber-400 text-amber-400" />
                          <span className="font-semibold text-slate-900">{Number(store.rating || 0)}</span>
                        </span>
                      )}
                      {store.followers_count !== undefined && (
                        <span className="flex items-center gap-1.5 text-slate-600">
                          <Icon name="users" size={16} className="text-slate-400" />
                          <span className="font-semibold text-slate-900">{Number(store.followers_count)}</span>{' '}
                          seguidores
                        </span>
                      )}
                    </div>
                  </div>

                  {/* Action buttons */}
                  <div className="flex flex-wrap gap-3">
                    <button
                      onClick={handleFollowToggle}
                      className={`rounded-xl border-2 px-5 py-2.5 text-sm font-semibold transition-all ${
                        isFollowing
                          ? 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                          : 'border-comercioplus-500 bg-white text-comercioplus-600 hover:bg-comercioplus-50'
                      }`}
                    >
                      {isFollowing ? 'Siguiendo' : 'Seguir tienda'}
                    </button>

                    <Link
                      to="/cart"
                      className="relative inline-flex items-center gap-2 rounded-xl bg-comercioplus-600 px-5 py-2.5 text-sm font-semibold text-white transition-all hover:bg-comercioplus-700"
                    >
                      Ver carrito
                      {totalItems > 0 && (
                        <span className="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                          {totalItems > 99 ? '99+' : totalItems}
                        </span>
                      )}
                    </Link>

                    {sanitizedWhatsApp && (
                      <a
                        href={whatsappUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        onClick={handleWhatsAppClick}
                        style={{
                          display: 'inline-flex',
                          alignItems: 'center',
                          gap: 8,
                          background: 'linear-gradient(135deg, #25D366 0%, #128C7E 100%)',
                          color: '#fff',
                          borderRadius: 12,
                          padding: '10px 18px',
                          fontWeight: 700,
                          fontSize: 13,
                          textDecoration: 'none',
                          boxShadow: '0 4px 15px rgba(37,211,102,0.4)',
                          border: 'none',
                          cursor: 'pointer',
                          transition: 'all 0.2s',
                        }}
                        onMouseEnter={(e) => (e.currentTarget.style.boxShadow = '0 6px 20px rgba(37,211,102,0.6)')}
                        onMouseLeave={(e) => (e.currentTarget.style.boxShadow = '0 4px 15px rgba(37,211,102,0.4)')}
                      >
                        <svg width={18} height={18} viewBox="0 0 24 24" fill="white" style={{ flexShrink: 0 }}>
                          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                          <path d="M12 0C5.373 0 0 5.373 0 12c0 2.124.555 4.118 1.528 5.855L0 24l6.335-1.505A11.95 11.95 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.885 0-3.65-.49-5.19-1.348l-.372-.22-3.762.894.954-3.668-.243-.388A9.975 9.975 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z" />
                        </svg>
                        {items.length > 0 ? 'Pedir por WhatsApp' : 'Ver en WhatsApp'}
                      </a>
                    )}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Search, categories, sort */}
      <div className="mx-auto max-w-7xl px-6 pt-8">
        <div className="flex flex-wrap items-center gap-3">
          <div className="relative min-w-[200px] flex-1">
            <Icon name="search" size={17} className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              type="text"
              placeholder="Buscar productos..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="w-full rounded-xl border-2 border-slate-200 bg-white py-2.5 pl-11 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:border-comercioplus-500 focus:outline-none"
            />
          </div>

          <select
            value={sortBy}
            onChange={(e) => setSortBy(e.target.value)}
            className="rounded-xl border-2 border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-comercioplus-500 focus:outline-none"
          >
            <option value="popular">Relevancia</option>
            <option value="price_asc">Precio ↑</option>
            <option value="price_desc">Precio ↓</option>
            <option value="recent">Mas vendidos</option>
          </select>
        </div>

        {categories.length > 0 && (
          <div className="mt-4 flex flex-wrap gap-2">
            <button
              onClick={() => setSelectedCategory('all')}
              className={`rounded-full px-4 py-1.5 text-sm font-semibold transition-all ${
                selectedCategory === 'all'
                  ? 'bg-comercioplus-600 text-white'
                  : 'border-2 border-slate-200 bg-white text-slate-600 hover:border-comercioplus-400 hover:text-comercioplus-600'
              }`}
            >
              Todos
            </button>
            {categories.map((cat) => (
              <button
                key={cat}
                onClick={() => setSelectedCategory(cat)}
                className={`rounded-full px-4 py-1.5 text-sm font-semibold transition-all ${
                  selectedCategory === cat
                    ? 'bg-comercioplus-600 text-white'
                    : 'border-2 border-slate-200 bg-white text-slate-600 hover:border-comercioplus-400 hover:text-comercioplus-600'
                }`}
              >
                {cat}
              </button>
            ))}
          </div>
        )}
      </div>

      {/* Products grid */}
      <div className="mx-auto max-w-7xl px-6 py-8">
        {filteredProducts.length > 0 ? (
          <div className="grid grid-cols-2 gap-5 md:grid-cols-3 lg:grid-cols-4">
            {filteredProducts.map((product) => {
              const outOfStock = Number(product.stock || 0) === 0
              const productImage = resolveMediaUrl(product.image_url || product.image)
              const fallback = gradientFromSeed(`${product.id}-${product.name}`)

              return (
                <div
                  key={product.id}
                  onClick={() => navigate(`/products/${product.id}`)}
                  className="group cursor-pointer overflow-hidden rounded-2xl border-2 border-slate-200 bg-white transition-all hover:border-comercioplus-400 hover:shadow-lg"
                >
                  <div className="relative h-48 overflow-hidden">
                    <ProductImage src={productImage} fallback={fallback} name={product.name} />
                    {outOfStock && (
                      <span className="absolute right-2 top-2 rounded-full bg-red-500 px-2 py-0.5 text-[11px] font-bold text-white">
                        Sin stock
                      </span>
                    )}
                  </div>

                  <div className="p-4">
                    <h3 className="mb-1 line-clamp-2 text-sm font-semibold leading-snug text-slate-900">
                      {product.name}
                    </h3>
                    <p className="mb-3 text-lg font-bold text-comercioplus-600">
                      ${Number(product.price || 0).toLocaleString('es-CO')}
                    </p>
                    <button
                      disabled={outOfStock}
                      onClick={(e) => {
                        e.stopPropagation()
                        handleAddToCart(product)
                      }}
                      className={`w-full rounded-xl py-2 text-sm font-semibold transition-all ${
                        outOfStock
                          ? 'cursor-not-allowed bg-slate-100 text-slate-400'
                          : 'bg-comercioplus-600 text-white hover:bg-comercioplus-700 active:scale-95'
                      }`}
                    >
                      {outOfStock ? 'Sin stock' : '+ Agregar'}
                    </button>
                  </div>
                </div>
              )
            })}
          </div>
        ) : (
          <div className="rounded-2xl border-2 border-dashed border-slate-200 bg-white p-16 text-center">
            <div className="mb-4 text-5xl">📦</div>
            <p className="text-lg font-semibold text-slate-900">
              {debouncedSearch || selectedCategory !== 'all'
                ? 'No se encontraron productos con esos filtros'
                : 'Esta tienda aun no tiene productos'}
            </p>
            {(debouncedSearch || selectedCategory !== 'all') && (
              <button
                onClick={() => {
                  setSearch('')
                  setSelectedCategory('all')
                }}
                className="mt-4 text-sm text-comercioplus-600 hover:underline"
              >
                Limpiar filtros
              </button>
            )}
          </div>
        )}
      </div>

      {/* Cart toast */}
      {toast.visible && (
        <div
          className={`fixed bottom-4 left-1/2 z-50 -translate-x-1/2 whitespace-nowrap rounded-2xl bg-slate-900 px-5 py-3 text-sm text-white shadow-xl transition-opacity duration-500 ${
            toastFading ? 'opacity-0' : 'opacity-100'
          }`}
        >
          <span className="mr-1.5">✅</span>
          <span className="font-semibold">{toast.name}</span>
          <span className="text-slate-400"> agregado al carrito</span>
        </div>
      )}

      {/* Store-conflict modal */}
      {showConflictModal && conflictPending && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
          <div className="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-2xl">
            <div className="text-5xl">🛒</div>

            <h3 className="mt-3 text-lg font-bold text-slate-900">Cambiar de tienda?</h3>

            <p className="mt-2 mb-5 text-sm text-slate-500">
              Tu carrito tiene productos de{' '}
              <span className="font-semibold text-slate-700">{cart?.storeName}</span>.{' '}
              Si agregas de{' '}
              <span className="font-semibold text-slate-700">{conflictPending.newStoreName}</span>,
              se vaciara el carrito anterior.
            </p>

            <button
              onClick={() => {
                switchStoreAndAdd(
                  conflictPending.pendingItem,
                  conflictPending.newStoreId,
                  conflictPending.newStoreName,
                  conflictPending.newStoreSlug,
                )
                showToast(conflictPending.pendingItem.name)
                setShowConflictModal(false)
                setConflictPending(null)
              }}
              className="w-full rounded-xl bg-orange-500 py-2.5 font-bold text-white hover:bg-orange-600 transition-all"
            >
              Si, cambiar tienda
            </button>

            <button
              onClick={() => {
                setShowConflictModal(false)
                setConflictPending(null)
              }}
              className="mt-2 w-full rounded-xl border-2 border-slate-200 py-2.5 font-semibold text-slate-700 hover:bg-slate-50 transition-all"
            >
              Conservar carrito actual
            </button>

            <p className="mt-3 text-xs text-slate-400">
              Puedes ver tu carrito actual haciendo click en el icono 🛒
            </p>
          </div>
        </div>
      )}
    </div>
  )
}
