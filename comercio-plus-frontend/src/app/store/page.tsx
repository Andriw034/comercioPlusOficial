import { useEffect, useMemo, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import API from '@/lib/api'
import type { Product, Store } from '@/types/api'
import GlassCard from '@/components/ui/GlassCard'
import Badge from '@/components/ui/Badge'
import { buttonVariants } from '@/components/ui/button'

export default function StoreDetail() {
  const { id } = useParams()
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [store, setStore] = useState<Store | null>(null)
  const [storeProducts, setStoreProducts] = useState<Product[]>([])

  useEffect(() => {
    const fetchStoreDetail = async () => {
      try {
        setLoading(true)
        setError('')

        const storeResponse = await API.get(`/public-stores/${id}`)
        const storeData = storeResponse.data
        setStore(storeData)

        const productsResponse = await API.get('/products', {
          params: { store_id: storeData.id, per_page: 20 },
        })
        setStoreProducts(productsResponse.data.data || [])
      } catch (err: any) {
        console.error('Store detail loading error:', err)
        setError(err.response?.data?.message || 'Error al cargar la tienda')
      } finally {
        setLoading(false)
      }
    }

    fetchStoreDetail()
  }, [id])

  const featuredProducts = useMemo(() => storeProducts.slice(0, 6), [storeProducts])

  if (loading) {
    return (
      <div className="flex justify-center py-12">
        <div className="h-10 w-10 animate-spin rounded-full border-2 border-white/20 border-t-brand-500" />
      </div>
    )
  }

  if (error) {
    return (
      <GlassCard className="border-red-500/30 bg-red-500/10 text-red-100">
        {error}
      </GlassCard>
    )
  }

  if (!store) {
    return (
      <GlassCard className="text-center">
        <p className="text-white/70">Tienda no encontrada.</p>
        <Link to="/stores" className={buttonVariants('secondary')}>Ver todas las tiendas</Link>
      </GlassCard>
    )
  }

  const mapsLink = store?.address
    ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(store.address)}`
    : ''

  return (
    <div className="space-y-10">
      <GlassCard className="relative overflow-hidden p-0">
        {store.cover_url && (
          <img
            src={store.cover_url}
            alt="Portada"
            className="absolute inset-0 h-full w-full object-cover opacity-30"
          />
        )}
        <div className="absolute inset-0 bg-gradient-to-b from-slate-950/60 via-slate-950/30 to-slate-950/80" />
        <div className="relative p-8 sm:p-10 flex flex-col gap-6">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div className="flex items-center gap-4">
              {store.logo_url ? (
                <img
                  src={store.logo_url}
                  alt={store.name}
                  className="h-16 w-16 rounded-2xl object-cover border border-white/10 bg-white"
                />
              ) : (
                <div className="h-16 w-16 rounded-2xl bg-white/10 flex items-center justify-center text-white/70">
                  {store.name?.slice(0, 2).toUpperCase()}
                </div>
              )}
              <div>
                <Badge variant="brand">Tienda activa</Badge>
                <h1 className="mt-2 text-3xl font-semibold text-white">{store.name}</h1>
                <p className="mt-1 text-sm text-white/60">{store.description}</p>
              </div>
            </div>
            <div className="text-right text-white/70">
              <p className="text-2xl font-semibold text-white">{storeProducts.length}</p>
              <p className="text-xs">Productos publicados</p>
            </div>
          </div>

          <div className="flex flex-wrap gap-3 text-sm text-white/70">
            <span>{store.address || 'Dirección no especificada'}</span>
            <span>•</span>
            <span>{store.phone || 'Teléfono no especificado'}</span>
          </div>

          <div className="flex flex-wrap gap-3">
            {store.whatsapp && (
              <a
                href={`https://wa.me/${store.whatsapp.replace(/\D/g, '')}`}
                target="_blank"
                rel="noreferrer"
                className={buttonVariants('secondary')}
              >
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
            {mapsLink && (
              <a href={mapsLink} target="_blank" rel="noreferrer" className={buttonVariants('primary')}>
                Ver en Maps
              </a>
            )}
          </div>
        </div>
      </GlassCard>

      <section className="space-y-6">
        <div className="flex items-center justify-between">
          <h2 className="section-heading">Destacados</h2>
          <Badge variant="neutral">Top 6</Badge>
        </div>
        {featuredProducts.length === 0 ? (
          <GlassCard className="text-center text-white/60">Aún no hay productos destacados.</GlassCard>
        ) : (
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {featuredProducts.map((product) => (
              <GlassCard key={product.id} className="flex flex-col gap-4">
                <div className="aspect-[4/3] overflow-hidden rounded-xl border border-white/10 bg-white/5">
                  {product.image_url || product.image ? (
                    <img src={product.image_url || product.image} alt={product.name} className="h-full w-full object-cover" />
                  ) : (
                    <div className="h-full w-full flex items-center justify-center text-white/40">Sin imagen</div>
                  )}
                </div>
                <div className="space-y-2">
                  <h3 className="text-sm font-semibold text-white">{product.name}</h3>
                  <p className="text-xs text-white/60 line-clamp-2">{product.description}</p>
                </div>
                <div className="flex items-center justify-between text-sm text-white/70">
                  <span className="text-brand-200 font-semibold">${product.price}</span>
                  <Link to={`/product/${product.id}`} className={buttonVariants('ghost')}>
                    Ver detalle
                  </Link>
                </div>
              </GlassCard>
            ))}
          </div>
        )}
      </section>

      <section className="space-y-6">
        <div className="flex items-center justify-between">
          <h2 className="section-heading">Catálogo</h2>
          <p className="text-sm text-white/60">Explora todo el catálogo disponible.</p>
        </div>
        {storeProducts.length === 0 ? (
          <GlassCard className="text-center text-white/60">Esta tienda aún no tiene productos disponibles.</GlassCard>
        ) : (
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {storeProducts.map((product) => (
              <GlassCard key={product.id} className="flex flex-col gap-4">
                <div className="aspect-[4/3] overflow-hidden rounded-xl border border-white/10 bg-white/5">
                  {product.image_url || product.image ? (
                    <img src={product.image_url || product.image} alt={product.name} className="h-full w-full object-cover" />
                  ) : (
                    <div className="h-full w-full flex items-center justify-center text-white/40">Sin imagen</div>
                  )}
                </div>
                <div className="space-y-2">
                  <h3 className="text-sm font-semibold text-white">{product.name}</h3>
                  <p className="text-xs text-white/60 line-clamp-2">{product.description}</p>
                </div>
                <div className="flex items-center justify-between text-sm text-white/70">
                  <span className="text-brand-200 font-semibold">${product.price}</span>
                  <Link to={`/product/${product.id}`} className={buttonVariants('secondary')}>
                    Ver detalle
                  </Link>
                </div>
              </GlassCard>
            ))}
          </div>
        )}
      </section>

      <GlassCard className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <Link to={`/store/${store.id}`} className="flex items-center gap-3">
          {store.logo_url ? (
            <img src={store.logo_url} alt={store.name} className="h-12 w-12 rounded-xl object-cover" />
          ) : (
            <div className="h-12 w-12 rounded-xl bg-white/10 flex items-center justify-center text-white/70">
              {store.name?.slice(0, 2).toUpperCase()}
            </div>
          )}
          <div>
            <p className="text-xs text-white/50">Tienda</p>
            <p className="text-base font-semibold text-white">{store.name}</p>
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
    </div>
  )
}
