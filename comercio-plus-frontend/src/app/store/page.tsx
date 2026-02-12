import { useEffect, useMemo, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import API from '@/lib/api'
import type { Product, Store } from '@/types/api'
import GlassCard from '@/components/ui/GlassCard'
import Badge from '@/components/ui/Badge'
import { buttonVariants } from '@/components/ui/button'
import { formatPrice, resolveMediaUrl } from '@/lib/format'

export default function StoreDetail() {
  const { id } = useParams()
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [store, setStore] = useState<Store | null>(null)
  const [storeProducts, setStoreProducts] = useState<Product[]>([])
  const [coverViewerOpen, setCoverViewerOpen] = useState(false)

  const normalizeStoreMedia = (value: any): Store => ({
    ...value,
    logo_url: resolveMediaUrl(value?.logo_url || value?.logo_path || value?.logo),
    cover_url: resolveMediaUrl(
      value?.cover_url || value?.cover_path || value?.background_url || value?.background_path || value?.cover,
    ),
  })

  useEffect(() => {
    const fetchStoreDetail = async () => {
      try {
        setLoading(true)
        setError('')

        const storeResponse = await API.get(`/public-stores/${id}`)
        const storeData = normalizeStoreMedia(storeResponse.data)
        setStore(storeData)

        const productsResponse = await API.get('/products', {
          params: { store_id: storeData.id, per_page: 20 },
        })
        setStoreProducts(productsResponse.data.data || [])
      } catch (err: any) {
        console.error('Store detail loading error:', err)
        if ([401, 403].includes(err.response?.status)) {
          setError('Esta tienda no esta disponible para vista publica.')
        } else {
          setError(err.response?.data?.message || 'Error al cargar la tienda')
        }
      } finally {
        setLoading(false)
      }
    }

    fetchStoreDetail()
  }, [id])

  const storeLogo = store
    ? resolveMediaUrl(store.logo_url || store.logo_path || store.logo) || ''
    : ''
  const storeCover = store
    ? resolveMediaUrl(store.cover_url || store.cover_path || store.background_url || store.cover) || ''
    : ''

  useEffect(() => {
    const detail = store
      ? {
          name: store.name,
          logoUrl: storeLogo || null,
        }
      : null

    window.dispatchEvent(new CustomEvent('publicStore:changed', { detail }))

    return () => {
      window.dispatchEvent(new CustomEvent('publicStore:changed', { detail: null }))
    }
  }, [store, storeLogo])

  useEffect(() => {
    if (!coverViewerOpen) return

    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') setCoverViewerOpen(false)
    }

    window.addEventListener('keydown', onKeyDown)
    document.body.style.overflow = 'hidden'

    return () => {
      window.removeEventListener('keydown', onKeyDown)
      document.body.style.overflow = ''
    }
  }, [coverViewerOpen])

  const featuredProducts = useMemo(() => storeProducts.slice(0, 6), [storeProducts])

  if (loading) {
    return (
      <div className="flex justify-center py-12">
        <div className="h-10 w-10 animate-spin rounded-full border-2 border-slate-900/10 border-t-brand-500 dark:border-white/20" />
      </div>
    )
  }

  if (error) {
    return (
      <GlassCard className="border-red-500/30 bg-red-500/10 text-red-700 dark:text-red-100">
        {error}
      </GlassCard>
    )
  }

  if (!store) {
    return (
      <GlassCard className="text-center">
        <p className="text-slate-600 dark:text-white/70">Tienda no encontrada.</p>
        <Link to="/stores" className={buttonVariants('secondary')}>Ver todas las tiendas</Link>
      </GlassCard>
    )
  }

  const mapsLink = store.address
    ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(store.address)}`
    : ''

  const heroGhostClass = '!border-white/20 !bg-white/10 !text-white hover:!border-white/35 hover:!bg-white/15'

  return (
    <div className="space-y-6 bg-gradient-to-b from-slate-50 via-white to-slate-50 dark:bg-gradient-to-b dark:from-slate-950 dark:via-slate-950 dark:to-slate-900 sm:space-y-7">
      <section className="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen">
        <div className="relative h-[340px] overflow-hidden rounded-none sm:h-[400px] sm:rounded-2xl lg:h-[440px]">
          {storeCover ? (
            <img
              src={storeCover}
              alt={`Portada de ${store.name}`}
              className="absolute inset-0 h-full w-full object-cover object-center"
              loading="lazy"
              decoding="async"
            />
          ) : (
            <div className="absolute inset-0 bg-slate-300 dark:bg-slate-900" />
          )}

          <div className="absolute inset-0 bg-gradient-to-r from-slate-950/70 via-slate-950/35 to-slate-950/10 dark:from-black/75 dark:via-black/40 dark:to-black/15" />
          <div className="absolute inset-0 bg-gradient-to-b from-black/15 via-transparent to-black/35" />

          <div className="relative mx-auto flex h-full max-w-7xl px-4 sm:px-6">
            <div className="flex w-full flex-col justify-end py-6 sm:py-8">
              <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div className="max-w-2xl space-y-4">
                  <div className="inline-flex items-center rounded-full bg-emerald-500/15 px-3 py-1 text-[12px] font-semibold text-emerald-100 ring-1 ring-emerald-200/30">
                    Tienda activa
                  </div>

                  <div className="flex items-center gap-4">
                    {storeLogo ? (
                      <img
                        src={storeLogo}
                        alt={store.name}
                        className="h-14 w-14 rounded-2xl border border-white/20 bg-white/90 object-contain p-1 sm:h-16 sm:w-16"
                        loading="lazy"
                        decoding="async"
                      />
                    ) : (
                      <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 text-white/80 sm:h-16 sm:w-16">
                        {store.name?.slice(0, 2).toUpperCase()}
                      </div>
                    )}

                    <div>
                      <h1 className="text-[26px] font-semibold leading-[1.1] text-white sm:text-[32px]">{store.name}</h1>
                      <p className="mt-1 line-clamp-2 text-[13px] text-white/75 sm:text-[14px]">
                        {store.description || 'Sin descripcion por ahora.'}
                      </p>
                    </div>
                  </div>

                  <div className="flex flex-wrap items-center gap-3 text-[12px] text-white/70">
                    <span>{store.address || 'Direccion no especificada'}</span>
                    <span className="opacity-60">|</span>
                    <span>{store.phone || 'Telefono no especificado'}</span>
                  </div>

                  <div className="flex flex-wrap gap-3">
                    {store.whatsapp && (
                      <a
                        href={`https://wa.me/${store.whatsapp.replace(/\D/g, '')}`}
                        target="_blank"
                        rel="noreferrer"
                        className={buttonVariants('ghost', `${heroGhostClass} h-10 px-4 text-[13px] font-semibold`)}
                      >
                        WhatsApp
                      </a>
                    )}
                    {store.support_email && (
                      <a
                        href={`mailto:${store.support_email}`}
                        className={buttonVariants('ghost', `${heroGhostClass} h-10 px-4 text-[13px] font-semibold`)}
                      >
                        Correo
                      </a>
                    )}
                    {mapsLink && (
                      <a href={mapsLink} target="_blank" rel="noreferrer" className={buttonVariants('primary', 'h-10 px-4 text-[13px] font-semibold')}>
                        Ver en Maps
                      </a>
                    )}
                    {storeCover && (
                      <button
                        type="button"
                        onClick={() => setCoverViewerOpen(true)}
                        className={buttonVariants('ghost', `${heroGhostClass} h-10 px-4 text-[13px] font-semibold`)}
                      >
                        Ver portada
                      </button>
                    )}
                  </div>

                  <div className="mt-2 flex items-center justify-between gap-3 lg:hidden">
                    <div className="inline-flex items-center rounded-full bg-brand-500/20 px-3 py-1 text-[12px] font-semibold text-white ring-1 ring-white/15">
                      Tienda verificada
                    </div>
                    <div className="flex items-baseline gap-2">
                      <span className="text-[18px] font-extrabold leading-none text-white">{storeProducts.length}</span>
                      <span className="text-[12px] text-white/80">productos publicados</span>
                    </div>
                  </div>
                </div>

                <div className="ml-auto hidden h-full items-center lg:flex">
                  <div className="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                    <div className="inline-flex items-center rounded-full bg-brand-500/20 px-3 py-1 text-[12px] font-semibold text-white ring-1 ring-white/15">
                      Tienda verificada
                    </div>
                    <p className="mt-3 text-[28px] font-extrabold leading-none text-white">{storeProducts.length}</p>
                    <p className="mt-1 text-[12px] text-white/70">Productos publicados</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="space-y-5">
        <div className="flex items-center justify-between">
          <h2 className="section-heading">Destacados</h2>
          <Badge variant="neutral">Top 6</Badge>
        </div>

        {featuredProducts.length === 0 ? (
          <GlassCard className="text-center text-[14px] text-slate-600 dark:text-white/70">Aun no hay productos destacados.</GlassCard>
        ) : (
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {featuredProducts.map((product) => {
              const image = resolveMediaUrl(product.image_url || product.image)

              return (
                <article
                  key={product.id}
                  className="flex flex-col gap-3 overflow-hidden rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition-all duration-200 hover:-translate-y-[1px] hover:shadow-md dark:border-white/10 dark:bg-white/5"
                >
                  <div className="aspect-square overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/5">
                    {image ? (
                      <img
                        src={image}
                        alt={product.name}
                        className="h-full w-full object-contain"
                        loading="lazy"
                        decoding="async"
                      />
                    ) : (
                      <div className="flex h-full w-full items-center justify-center text-[12px] text-slate-500 dark:text-white/60">Sin imagen</div>
                    )}
                  </div>

                  <div className="space-y-1">
                    <h3 className="line-clamp-2 min-h-[34px] text-[13px] font-semibold leading-[1.25] text-slate-900 dark:text-white sm:min-h-[38px] sm:text-[14px]">
                      {product.name}
                    </h3>
                    <p className="line-clamp-2 text-[12px] text-slate-600 dark:text-white/70">{product.description || 'Sin descripcion.'}</p>
                    <p className="text-[12px] text-slate-500 dark:text-white/60">Stock: {product.stock}</p>
                  </div>

                  <div className="mt-auto flex items-center justify-between gap-3">
                    <span className="text-[18px] font-extrabold text-slate-900 dark:text-white sm:text-[20px]">
                      ${formatPrice(product.price)}
                    </span>
                    <Link to={`/product/${product.id}`} className={buttonVariants('secondary', 'h-10 px-4 text-[13px] font-semibold')}>
                      Ver detalle
                    </Link>
                  </div>
                </article>
              )
            })}
          </div>
        )}
      </section>

      <section className="space-y-5">
        <div className="flex items-center justify-between gap-3">
          <h2 className="section-heading">Catalogo</h2>
          <p className="text-[13px] text-slate-600 dark:text-white/70">Explora todo el catalogo disponible.</p>
        </div>

        {storeProducts.length === 0 ? (
          <GlassCard className="text-center text-[14px] text-slate-600 dark:text-white/70">Esta tienda aun no tiene productos disponibles.</GlassCard>
        ) : (
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {storeProducts.map((product) => {
              const image = resolveMediaUrl(product.image_url || product.image)

              return (
                <article
                  key={product.id}
                  className="flex flex-col gap-3 overflow-hidden rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition-all duration-200 hover:-translate-y-[1px] hover:shadow-md dark:border-white/10 dark:bg-white/5"
                >
                  <div className="aspect-square overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/5">
                    {image ? (
                      <img
                        src={image}
                        alt={product.name}
                        className="h-full w-full object-contain"
                        loading="lazy"
                        decoding="async"
                      />
                    ) : (
                      <div className="flex h-full w-full items-center justify-center text-[12px] text-slate-500 dark:text-white/60">Sin imagen</div>
                    )}
                  </div>

                  <div className="space-y-1">
                    <h3 className="line-clamp-2 min-h-[34px] text-[13px] font-semibold leading-[1.25] text-slate-900 dark:text-white sm:min-h-[38px] sm:text-[14px]">
                      {product.name}
                    </h3>
                    <p className="line-clamp-2 text-[12px] text-slate-600 dark:text-white/70">{product.description || 'Sin descripcion.'}</p>
                    <p className="text-[12px] text-slate-500 dark:text-white/60">Stock: {product.stock}</p>
                  </div>

                  <div className="mt-auto flex items-center justify-between gap-3">
                    <span className="text-[18px] font-extrabold text-slate-900 dark:text-white sm:text-[20px]">
                      ${formatPrice(product.price)}
                    </span>
                    <Link to={`/product/${product.id}`} className={buttonVariants('secondary', 'h-10 px-4 text-[13px] font-semibold')}>
                      Ver detalle
                    </Link>
                  </div>
                </article>
              )
            })}
          </div>
        )}
      </section>

      <GlassCard className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <Link to={`/store/${store.id}`} className="flex items-center gap-3">
          {storeLogo ? (
            <img
              src={storeLogo}
              alt={store.name}
              className="h-12 w-12 rounded-xl border border-slate-200 bg-slate-50 object-contain p-1 dark:border-white/10 dark:bg-white/5"
              loading="lazy"
              decoding="async"
            />
          ) : (
            <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-white/70">
              {store.name?.slice(0, 2).toUpperCase()}
            </div>
          )}

          <div>
            <p className="text-[12px] text-slate-500 dark:text-white/60">Tienda</p>
            <p className="text-[16px] font-semibold text-slate-900 dark:text-white">{store.name}</p>
          </div>
        </Link>

        <div className="flex flex-wrap gap-2">
          {store.whatsapp && (
            <a href={`https://wa.me/${store.whatsapp.replace(/\D/g, '')}`} target="_blank" rel="noreferrer" className={buttonVariants('ghost')}>
              WhatsApp
            </a>
          )}
          {store.facebook && (
            <a href={store.facebook} target="_blank" rel="noreferrer" className={buttonVariants('ghost')}>
              Facebook
            </a>
          )}
          {store.instagram && (
            <a href={store.instagram} target="_blank" rel="noreferrer" className={buttonVariants('ghost')}>
              Instagram
            </a>
          )}
          {store.support_email && (
            <a href={`mailto:${store.support_email}`} className={buttonVariants('ghost')}>
              Correo
            </a>
          )}
        </div>
      </GlassCard>

      {coverViewerOpen && storeCover && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center bg-black/85 px-4 py-6"
          role="dialog"
          aria-modal="true"
          aria-label="Portada de la tienda"
          onMouseDown={(event) => {
            if (event.target === event.currentTarget) setCoverViewerOpen(false)
          }}
        >
          <div className="w-full max-w-6xl overflow-hidden rounded-2xl border border-white/15 bg-slate-950/95 p-4 shadow-2xl">
            <div className="mb-3 flex items-center justify-between">
              <h2 className="text-[14px] font-semibold text-white">Portada de {store.name}</h2>
              <button
                type="button"
                onClick={() => setCoverViewerOpen(false)}
                className={buttonVariants('ghost', '!border-white/20 !bg-white/10 !text-white hover:!bg-white/15')}
              >
                Cerrar
              </button>
            </div>
            <div className="h-[65vh] rounded-xl bg-black/30 p-4">
              <img src={storeCover} alt={`Portada de ${store.name}`} className="h-full w-full object-contain" />
            </div>
          </div>
        </div>
      )}
    </div>
  )
}