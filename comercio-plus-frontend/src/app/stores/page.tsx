import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import API from '@/lib/api'
import type { Store } from '@/types/api'
import GlassCard from '@/components/ui/GlassCard'
import Badge from '@/components/ui/Badge'
import Input from '@/components/ui/Input'
import { buttonVariants } from '@/components/ui/button'
import { resolveMediaUrl } from '@/lib/format'

export default function Stores() {
  const [stores, setStores] = useState<Store[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [query, setQuery] = useState('')

  const fetchStores = async () => {
    try {
      setLoading(true)
      setError('')

      const response = await API.get('/public-stores')
      setStores(response.data || [])
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
    <div className="space-y-8">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 className="text-3xl font-semibold text-white">Tiendas</h1>
          <p className="text-sm text-white/60">Descubre las mejores tiendas de repuestos para moto.</p>
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
          <div className="h-10 w-10 animate-spin rounded-full border-2 border-white/20 border-t-brand-500" />
        </div>
      )}

      {!loading && error && (
        <GlassCard className="border-red-500/30 bg-red-500/10 text-red-100">
          {error}
        </GlassCard>
      )}

      {!loading && !error && (
        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {filteredStores.length === 0 ? (
            <GlassCard className="col-span-full text-center">
              <p className="text-white/70">No hay tiendas disponibles.</p>
            </GlassCard>
          ) : (
            filteredStores.map((store) => {
              const logo = resolveMediaUrl(store.logo_url || store.logo_path || store.logo)

              return (
                <GlassCard key={store.id} className="flex flex-col gap-4">
                  <div className="flex items-start gap-4">
                    {logo ? (
                      <img
                        src={logo}
                        alt={store.name}
                        className="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover border border-white/10 bg-black/20 shrink-0"
                      />
                    ) : (
                      <div className="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white font-bold shrink-0">
                        {(store.name?.trim().charAt(0) || 'C').toUpperCase()}
                      </div>
                    )}

                    <div className="flex-1 min-w-0">
                      <h3 className="text-lg font-semibold leading-tight text-white">{store.name}</h3>
                      <p className="mt-1 text-sm text-white/60 line-clamp-2">{store.description || 'Sin descripcion'}</p>
                    </div>
                  </div>

                  <div className="flex items-center justify-between text-xs text-white/50">
                    <span>{store.location?.city || 'Ubicacion no especificada'}</span>
                    <Badge variant="brand">Activa</Badge>
                  </div>

                  <div className="mt-auto">
                    <Link to={`/store/${store.id}`} className={buttonVariants('secondary')}>
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
