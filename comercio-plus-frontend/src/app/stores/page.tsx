import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import API from '@/lib/api'
import type { Store } from '@/types/api'
import GlassCard from '@/components/ui/GlassCard'
import Badge from '@/components/ui/Badge'
import Input from '@/components/ui/Input'
import { buttonVariants } from '@/components/ui/button'
import { resolveMediaUrl } from '@/lib/format'

type StoreWithMeta = Store & {
  products_count?: number
}

export default function Stores() {
  const [stores, setStores] = useState<StoreWithMeta[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [query, setQuery] = useState('')

  const fetchStores = async () => {
    try {
      setLoading(true)
      setError('')

      const response = await API.get('/public-stores')
      setStores((response.data || []) as StoreWithMeta[])
    } catch (err: any) {
      console.error('Error fetching stores:', err)
      if ([401, 403].includes(err.response?.status)) {
        setError('No se pudieron cargar las tiendas publicas en este momento.')
      } else {
        setError(err.response?.data?.message || 'Error al cargar las tiendas. Intentalo de nuevo.')
      }
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    fetchStores()
  }, [])

  const filteredStores = useMemo(() => {
    const normalized = query.trim().toLowerCase()
    if (!normalized) return stores
    return stores.filter((store) =>
      `${store.name} ${store.description ?? ''}`.toLowerCase().includes(normalized),
    )
  }, [stores, query])

  return (
    <div className="space-y-6 sm:space-y-8">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 className="text-[30px] font-semibold leading-[1.12] text-slate-900 dark:text-white sm:text-[34px]">Tiendas</h1>
          <p className="text-[13px] text-slate-600 dark:text-white/60">Descubre tiendas confiables de repuestos para moto.</p>
        </div>
        <div className="w-full sm:max-w-sm">
          <Input
            value={query}
            onChange={(event) => setQuery(event.target.value)}
            placeholder="Buscar tienda"
            className="!py-2.5"
          />
        </div>
      </div>

      {loading && (
        <div className="flex justify-center">
          <div className="h-10 w-10 animate-spin rounded-full border-2 border-slate-900/10 border-t-brand-500 dark:border-white/20" />
        </div>
      )}

      {!loading && error && (
        <GlassCard className="border-red-500/30 bg-red-500/10 text-red-700 dark:text-red-100">
          {error}
        </GlassCard>
      )}

      {!loading && !error && (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {filteredStores.length === 0 ? (
            <GlassCard className="col-span-full text-center">
              <p className="text-slate-600 dark:text-white/70">No hay tiendas disponibles.</p>
            </GlassCard>
          ) : (
            filteredStores.map((store) => {
              const logo = resolveMediaUrl(store.logo_url || store.logo_path || store.logo)
              const productsCount = Number(store.products_count)
              const hasProductsCount = Number.isFinite(productsCount) && productsCount >= 0

              return (
                <GlassCard key={store.id} className="flex h-full flex-col gap-4">
                  <div className="flex items-start gap-4">
                    {logo ? (
                      <img
                        src={logo}
                        alt={store.name}
                        className="h-14 w-14 shrink-0 rounded-full border border-slate-200 bg-white object-cover sm:h-16 sm:w-16 dark:border-white/10 dark:bg-white/5"
                        loading="lazy"
                        decoding="async"
                      />
                    ) : (
                      <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-[16px] font-bold text-white sm:h-16 sm:w-16">
                        {(store.name?.trim().charAt(0) || 'C').toUpperCase()}
                      </div>
                    )}

                    <div className="min-w-0 flex-1">
                      <h3 className="line-clamp-1 text-[18px] font-semibold leading-tight text-slate-900 dark:text-white">{store.name}</h3>
                      <p className="mt-1 line-clamp-2 text-[13px] text-slate-600 dark:text-white/60">
                        {store.description || 'Sin descripcion disponible.'}
                      </p>
                    </div>
                  </div>

                  <div className="flex flex-wrap items-center gap-2">
                    <Badge variant="success">Activa</Badge>
                    {hasProductsCount ? (
                      <Badge variant="neutral">Productos: {productsCount}</Badge>
                    ) : (
                      <Badge variant="neutral">Verificada</Badge>
                    )}
                  </div>

                  <div className="mt-auto flex items-center justify-between text-[12px] text-slate-500 dark:text-white/60">
                    <span>{store.location?.city || 'Ubicacion no especificada'}</span>
                    <Link to={`/store/${store.id}`} className={buttonVariants('secondary', 'h-10 px-4 text-[13px]')}>
                      Ver tienda
                    </Link>
                  </div>
                </GlassCard>
              )
            })
          )}
        </div>
      )}
    </div>
  )
}