import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import API from '@/lib/api'
import type { Store } from '@/types/api'
import { buttonVariants } from '@/components/ui/button'
import { resolveMediaUrl } from '@/lib/format'

type StoreWithMeta = Store & {
  products_count?: number
}

const coverGradients = [
  'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
  'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
  'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
  'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
]

export default function Home() {
  const [stores, setStores] = useState<StoreWithMeta[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    const load = async () => {
      try {
        setLoading(true)
        setError('')
        const { data } = await API.get('/public-stores')
        setStores((data || []) as StoreWithMeta[])
      } catch (err: any) {
        setError(err?.response?.data?.message || 'No se pudieron cargar las tiendas.')
      } finally {
        setLoading(false)
      }
    }

    load()
  }, [])

  return (
    <div className="space-y-0 overflow-hidden rounded-2xl border border-[#E5E7EB] bg-white shadow-[0_4px_24px_rgba(0,0,0,0.08)]">
      <section className="relative overflow-hidden bg-[linear-gradient(135deg,#004E89_0%,#FF6B35_100%)] px-6 py-20 text-white sm:px-12">
        <div className="absolute -right-28 -top-24 h-[420px] w-[420px] rounded-full bg-[radial-gradient(circle,rgba(255,255,255,0.15)_0%,transparent_70%)]" />

        <div className="relative z-10 mx-auto max-w-3xl text-center">
          <h1 className="font-display text-[44px] text-white sm:text-[56px]">Descubre Tiendas Unicas</h1>
          <p className="mx-auto mt-5 max-w-2xl text-[20px] text-white/90">
            Explora una seleccion curada de tiendas locales y encuentra productos artesanales de calidad.
          </p>
          <Link to="/stores" className={`mt-8 ${buttonVariants('primary')}`}>
            Explorar Tiendas
          </Link>
        </div>
      </section>

      <section className="p-6 sm:p-12">
        {loading && <p className="text-[15px] text-[#4B5563]">Cargando tiendas...</p>}
        {!loading && error && <p className="text-[15px] text-red-600">{error}</p>}

        {!loading && !error && (
          <div className="grid gap-8 sm:grid-cols-2 xl:grid-cols-3">
            {stores.map((store, index) => {
              const logo = resolveMediaUrl(store.logo_url || store.logo_path || store.logo)
              const cover = resolveMediaUrl(store.cover_url || store.cover_path || store.cover)

              return (
                <Link
                  key={store.id}
                  to={`/store/${store.id}`}
                  className="group overflow-hidden rounded-xl border border-[#E5E7EB] bg-white transition-all duration-300 hover:-translate-y-2 hover:shadow-[0_12px_32px_rgba(0,0,0,0.12)]"
                >
                  <div
                    className="relative h-40"
                    style={{
                      background: cover
                        ? `url(${cover}) center/cover no-repeat`
                        : coverGradients[index % coverGradients.length],
                    }}
                  >
                    <div className="absolute -bottom-8 left-6 h-16 w-16 overflow-hidden rounded-xl border-4 border-white bg-white shadow-md">
                      {logo ? <img src={logo} alt={store.name} className="h-full w-full object-cover" /> : null}
                    </div>
                  </div>

                  <div className="space-y-2 p-6 pt-10">
                    <h3 className="text-[18px] font-bold text-[#1A1A2E]">{store.name}</h3>
                    <p className="line-clamp-2 text-[14px] text-[#4B5563]">
                      {store.description || 'Tienda verificada en ComercioPlus'}
                    </p>
                    <div className="flex gap-3 text-[13px] text-[#4B5563]">
                      <span>⭐ 4.8</span>
                      <span>•</span>
                      <span>{store.products_count ?? 0} productos</span>
                    </div>
                  </div>
                </Link>
              )
            })}
          </div>
        )}
      </section>
    </div>
  )
}
