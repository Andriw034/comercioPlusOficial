import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import API from '@/services/api'
import { getStoredToken, getStoredUserRaw } from '@/services/auth-session'
import type { Product, Store } from '@/types/api'
import { buttonVariants } from '@/components/ui/button'
import PriceAlertButton from '@/components/PriceAlertButton'
import { useCart } from '@/context/CartContext'
import { extractList } from '@/lib/api-response'
import { formatPrice, resolveMediaUrl } from '@/lib/format'
import CoverImage from '@/ui/images/CoverImage'
import LogoImage from '@/ui/images/LogoImage'
import {
  getImageBrightness,
  getStoredHeaderTheme,
  getThemeClassesByBrightness,
  storeHeaderTheme,
  type ImageBrightness,
} from '@/utils/imageTheme'

type StoreMedia = Store & {
  logo?: string
  cover?: string
}

function sanitizeWhatsApp(raw: string): string {
  const digits = raw.replace(/\D/g, '')
  if (digits.startsWith('57') && digits.length >= 12) return digits
  if (digits.startsWith('3') && digits.length === 10) return `57${digits}`
  return digits
}

export default function StoreDetail() {
  const { id } = useParams()
  const { addToCart } = useCart()
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [store, setStore] = useState<StoreMedia | null>(null)
  const [storeProducts, setStoreProducts] = useState<Product[]>([])
  const [headerTheme, setHeaderTheme] = useState<ImageBrightness>('dark')
  const [addedNotice, setAddedNotice] = useState<{ productId: string; visible: boolean } | null>(null)

  useEffect(() => {
    const fetchStoreDetail = async () => {
      try {
        setLoading(true)
        setError('')

        const storeResponse = await API.get(`/public/stores/${id}`, {
          params: { _t: Date.now() },
        })
        const storeData: StoreMedia = storeResponse.data
        setStore(storeData)

        const productsResponse = await API.get('/products', {
          params: { store_id: storeData.id, per_page: 40, status: 'active', _t: Date.now() },
        })
        const freshProducts = extractList<Product>(productsResponse.data)
        setStoreProducts(
          freshProducts.filter((product) => {
            const status = String(product.status || '').toLowerCase()
            const hasValidStatus = status === '' || status === 'active' || status === '1' || status === 'true'
            return hasValidStatus && Number(product.stock || 0) > 0
          }),
        )
      } catch (err: any) {
        setError(err.response?.data?.message || 'Error al cargar la tienda')
      } finally {
        setLoading(false)
      }
    }

    fetchStoreDetail()
  }, [id])

  useEffect(() => {
    const detail = store
      ? {
          name: store.name,
          logoUrl: resolveMediaUrl(store.logo_url || store.logo_path || store.logo) || null,
        }
      : null

    window.dispatchEvent(new CustomEvent('publicStore:changed', { detail }))
    return () => {
      window.dispatchEvent(new CustomEvent('publicStore:changed', { detail: null }))
    }
  }, [store])

  useEffect(() => {
    const registerVisit = async () => {
      if (!store?.id) return
      const token = getStoredToken()
      if (!token) return

      let role = ''
      try {
        const userRaw = getStoredUserRaw()
        role = userRaw ? JSON.parse(userRaw)?.role : ''
      } catch {
        role = ''
      }
      if (role !== 'client') return

      try {
        await API.post(`/stores/${store.id}/visit`)
      } catch {
        // ignore
      }
    }

    registerVisit()
  }, [store?.id])

  const logo = resolveMediaUrl(store?.logo_url || store?.logo_path || store?.logo)
  const cover = resolveMediaUrl(store?.cover_url || store?.cover_path || store?.background_url || store?.cover)
  const storeId = store?.id || null
  const sanitizedWhatsApp = sanitizeWhatsApp(String(store?.whatsapp || ''))
  const whatsappUrl = sanitizedWhatsApp
    ? `https://wa.me/${sanitizedWhatsApp}?text=${encodeURIComponent('Hola, vi tu tienda en ComercioPlus y me gustaria conocer tus productos.')}`
    : ''
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

  useEffect(() => {
    if (!addedNotice?.visible) return

    const timeoutId = window.setTimeout(() => {
      setAddedNotice((current) => (current ? { ...current, visible: false } : null))
    }, 1200)

    return () => window.clearTimeout(timeoutId)
  }, [addedNotice])

  const handleAddToCart = (product: Product) => {
    if (!store || product?.id == null) return

    addToCart(
      {
        id: product.id,
        name: product.name || 'Producto',
        price: Number(product.price || 0),
        image: resolveMediaUrl(product.image_url || product.image) || '',
        seller: store.name || store.description || 'Tienda ComercioPlus',
        storeId: store.id,
      },
      String(store.id),
      store.name || 'Tienda ComercioPlus',
      store.slug ?? String(store.id),
    )

    setAddedNotice({
      productId: String(product.id),
      visible: true,
    })
  }

  if (loading) return <p className="text-[15px] text-[#4B5563]">Cargando tienda...</p>
  if (error) return <p className="text-[15px] text-red-600">{error}</p>
  if (!store) return <p className="text-[15px] text-[#4B5563]">Tienda no encontrada.</p>

  return (
    <div className="overflow-hidden rounded-2xl border border-[#E5E7EB] bg-white shadow-[0_4px_24px_rgba(0,0,0,0.08)]">
      <div className="border-b border-[#E5E7EB] px-6 py-4 sm:px-10">
        <Link to="/" className="text-[15px] font-medium text-[#4B5563] hover:text-[#FF6B35]">← Volver a Tiendas</Link>
      </div>

      <div className="relative">
        <CoverImage
          src={cover}
          ratio="free"
          className="h-[280px]"
          overlay
          overlayMode="header"
          onBrightnessChange={(theme) => {
            setHeaderTheme(theme)
            if (store?.id) storeHeaderTheme(store.id, theme)
          }}
        >
          <div className="flex h-full items-end px-6 pb-5 sm:px-10">
            <div className={`rounded-2xl border px-4 py-3 backdrop-blur ${adaptiveTheme.chip}`}>
              <p className={`text-[11px] font-semibold uppercase tracking-[0.12em] ${adaptiveTheme.textMuted}`}>
                Tienda oficial
              </p>
              <p className={`text-xl font-black leading-tight ${adaptiveTheme.textPrimary}`}>{store.name}</p>
            </div>
          </div>
        </CoverImage>

        <div className="px-6 sm:px-10">
          <div className="-mt-14 flex flex-col gap-6 sm:flex-row sm:items-end">
            <div className="h-28 w-28 overflow-hidden rounded-2xl border-[6px] border-white bg-white shadow-lg sm:h-36 sm:w-36">
              <LogoImage src={logo} alt={store.name} className="h-full w-full rounded-none border-0 bg-white p-2" />
            </div>
            <div className="pb-4">
              <div className="flex flex-wrap items-center gap-2">
                <h1 className="text-[28px] font-bold text-[#1A1A2E]">{store.name}</h1>
                {store.is_verified && (
                  <span className="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                    ✓ Verificada
                  </span>
                )}
              </div>
              <p className="mt-1 max-w-3xl text-[16px] text-[#4B5563] dark:text-white/50">
                {store.description || 'Tienda verificada en ComercioPlus'}
              </p>
              <div className="mt-3 space-y-1">
                {store.whatsapp && <p className="text-[13px] text-slate-600 dark:text-white/50">WhatsApp: {store.whatsapp}</p>}
                {store.support_email && <p className="text-[13px] text-slate-600 dark:text-white/50">Email: {store.support_email}</p>}
                {store.address && <p className="text-[13px] text-slate-600 dark:text-white/50">Direccion: {store.address}</p>}
                {store.instagram && <p className="text-[13px] text-slate-600 dark:text-white/50">Instagram: {store.instagram}</p>}
                {store.facebook && <p className="text-[13px] text-slate-600 dark:text-white/50">Facebook: {store.facebook}</p>}
              </div>
              {whatsappUrl && (
                <a
                  href={whatsappUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="mt-3 inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                  WhatsApp
                </a>
              )}
            </div>
          </div>
        </div>
      </div>

      <div className="space-y-8 px-6 py-10 sm:px-10">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <h2 className="font-display text-[30px]">Nuestros Productos</h2>
          <div className="flex gap-2">
            <button className={buttonVariants('outline')}>Filtrar</button>
            <button className={buttonVariants('outline')}>Ordenar</button>
          </div>
        </div>

        {storeProducts.length === 0 ? (
          <p className="text-[15px] text-[#4B5563]">Esta tienda aun no tiene productos disponibles.</p>
        ) : (
          <div className="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
            {storeProducts.map((product, index) => {
              const image = resolveMediaUrl(product.image_url || product.image)
              const fallback = [
                'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
              ][index % 4]

              return (
                <article key={product.id} className="overflow-hidden rounded-xl border border-[#E5E7EB] bg-white">
                  <div
                    className="h-44"
                    style={{
                      background: image ? `url(${image}) center/cover no-repeat` : fallback,
                    }}
                  />

                  <div className="space-y-2 p-5">
                    <h3 className="text-[16px] font-semibold text-[#1A1A2E]">{product.name}</h3>
                    <p className="text-[20px] font-bold text-[#FF6B35]">${formatPrice(product.price)}</p>
                    <PriceAlertButton productId={Number(product.id)} currentPrice={Number(product.price || 0)} />
                    <button
                      type="button"
                      onClick={() => handleAddToCart(product)}
                      className={`${buttonVariants('primary')} w-full`}
                    >
                      Agregar al Carrito
                    </button>
                    {addedNotice?.visible && addedNotice.productId === String(product.id) ? (
                      <p className="text-xs font-semibold text-emerald-700">Agregado al carrito ✅</p>
                    ) : null}
                  </div>
                </article>
              )
            })}
          </div>
        )}
      </div>
    </div>
  )
}
