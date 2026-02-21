import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import API from '@/lib/api'
import type { Product, Store } from '@/types/api'
import { buttonVariants } from '@/components/ui/button'
import { formatPrice, resolveMediaUrl } from '@/lib/format'

type StoreMedia = Store & {
  logo?: string
  cover?: string
}

export default function StoreDetail() {
  const { id } = useParams()
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [store, setStore] = useState<StoreMedia | null>(null)
  const [storeProducts, setStoreProducts] = useState<Product[]>([])

  useEffect(() => {
    const fetchStoreDetail = async () => {
      try {
        setLoading(true)
        setError('')

        const storeResponse = await API.get(`/public-stores/${id}`)
        const storeData: StoreMedia = storeResponse.data
        setStore(storeData)

        const productsResponse = await API.get('/products', {
          params: { store_id: storeData.id, per_page: 40 },
        })
        setStoreProducts(productsResponse.data.data || [])
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
      const token = localStorage.getItem('token')
      if (!token) return

      let role = ''
      try {
        const userRaw = localStorage.getItem('user')
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

  if (loading) return <p className="text-[15px] text-[#4B5563]">Cargando tienda...</p>
  if (error) return <p className="text-[15px] text-red-600">{error}</p>
  if (!store) return <p className="text-[15px] text-[#4B5563]">Tienda no encontrada.</p>

  const logo = resolveMediaUrl(store.logo_url || store.logo_path || store.logo)
  const cover = resolveMediaUrl(store.cover_url || store.cover_path || store.background_url || store.cover)

  return (
    <div className="overflow-hidden rounded-2xl border border-[#E5E7EB] bg-white shadow-[0_4px_24px_rgba(0,0,0,0.08)]">
      <div className="border-b border-[#E5E7EB] px-6 py-4 sm:px-10">
        <Link to="/" className="text-[15px] font-medium text-[#4B5563] hover:text-[#FF6B35]">← Volver a Tiendas</Link>
      </div>

      <div className="relative">
        <div
          className="h-[280px]"
          style={{
            background: cover
              ? `url(${cover}) center/cover no-repeat`
              : 'linear-gradient(135deg, #004E89 0%, #FF6B35 100%)',
          }}
        />

        <div className="px-6 sm:px-10">
          <div className="-mt-14 flex flex-col gap-6 sm:flex-row sm:items-end">
            <div className="h-28 w-28 overflow-hidden rounded-2xl border-[6px] border-white bg-white shadow-lg sm:h-36 sm:w-36">
              {logo ? <img src={logo} alt={store.name} className="h-full w-full object-cover" /> : null}
            </div>
            <div className="pb-4">
              <h1 className="font-display text-[36px]">{store.name}</h1>
              <p className="mt-1 max-w-3xl text-[16px] text-[#4B5563]">
                {store.description || 'Tienda verificada en ComercioPlus'}
              </p>
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
                    <button className={`${buttonVariants('primary')} w-full`}>Agregar al Carrito</button>
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
