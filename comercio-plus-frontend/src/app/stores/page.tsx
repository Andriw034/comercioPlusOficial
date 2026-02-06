import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import API from '@/lib/api'
import type { Store } from '@/types/api'
import GlassCard from '@/components/ui/GlassCard'
import Badge from '@/components/ui/Badge'
import Input from '@/components/ui/Input'
import { buttonVariants } from '@/components/ui/button'

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
      setError(err.response?.data?.message || 'Error al cargar las tiendas. Inténtalo de nuevo.')
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
            filteredStores.map((store) => (
              <GlassCard key={store.id} className="flex flex-col gap-4">
                <div className="flex items-center gap-3">
                  {store.logo_url ? (
                    <img
                      src={store.logo_url}
                      alt={store.name}
                      className="h-12 w-12 rounded-2xl object-cover border border-white/10 bg-white"
                    />
                  ) : (
                    <div className="h-12 w-12 rounded-2xl bg-white/10 flex items-center justify-center text-white/70">
                      <svg className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.6" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                    </div>
                  )}
                  <div>
                    <h3 className="text-lg font-semibold text-white">{store.name}</h3>
                    <p className="text-sm text-white/60">{store.description || 'Sin descripción'}</p>
                  </div>
                </div>

                <div className="flex items-center justify-between text-xs text-white/50">
                  <span>{store.location?.city || 'Ubicación no especificada'}</span>
                  <Badge variant="brand">Activa</Badge>
                </div>

                <div className="mt-auto">
                  <Link to={`/store/${store.id}`} className={buttonVariants('secondary')}>
                    Ver tienda
                  </Link>
                </div>
              </GlassCard>
            ))
          )}
        </div>
      )}
    </div>
  )
}
