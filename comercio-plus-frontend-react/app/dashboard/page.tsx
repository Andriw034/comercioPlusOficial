import { useEffect, useMemo, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import API from '@/lib/api'
import GlassCard from '@/components/ui/GlassCard'
import Button from '@/components/ui/Button'
import StatCard from '@/components/ui/StatCard'
import Badge from '@/components/ui/Badge'
import { formatDate } from '@/lib/format'
import type { Product, Store } from '@/types/api'

type Order = {
  id: number
  status?: string
  total_amount?: number
  total?: number
  date?: string
  created_at?: string
}

export default function Dashboard() {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [stores, setStores] = useState<Store[]>([])
  const [products, setProducts] = useState<Product[]>([])
  const [orders, setOrders] = useState<Order[]>([])

  const user = useMemo(() => {
    const userData = localStorage.getItem('user')
    return userData ? JSON.parse(userData) : null
  }, [])

  const primaryStore = useMemo(() => stores[0] || null, [stores])
  const productsCount = useMemo(() => products.length, [products])
  const ordersCount = useMemo(() => orders.length, [orders])
  const monthlySales = useMemo(() => {
    const now = new Date()
    return orders.reduce((acc, order) => {
      const date = new Date(order.date || order.created_at || now)
      if (date.getMonth() === now.getMonth() && date.getFullYear() === now.getFullYear()) {
        return acc + Number(order.total_amount ?? order.total ?? 0)
      }
      return acc
    }, 0)
  }, [orders])

  const recentActivity = useMemo(
    () =>
      [...orders]
        .sort((a, b) => new Date(b.date || b.created_at || 0).getTime() - new Date(a.date || a.created_at || 0).getTime())
        .slice(0, 5)
        .map((order) => ({
          id: order.id,
          status: order.status || 'creada',
          amount: Number(order.total_amount ?? order.total ?? 0),
          date: order.date || order.created_at,
        })),
    [orders],
  )

  const normalizeList = (response: any) => {
    if (Array.isArray(response?.data?.data)) return response.data.data
    if (Array.isArray(response?.data)) return response.data
    return []
  }

  const fetchDashboardData = async () => {
    try {
      setLoading(true)
      setError('')

      const [storeRes, ordersRes] = await Promise.all([
        API.get('/my/store'),
        API.get('/merchant/orders'),
      ])

      const store = storeRes?.data ? [storeRes.data] : []
      setStores(store)

      if (storeRes?.data?.id) {
        const productsRes = await API.get('/products', {
          params: {
            store_id: storeRes.data.id,
            per_page: 100,
          },
        })
        setProducts(normalizeList(productsRes))
      } else {
        setProducts([])
      }

      setOrders(normalizeList(ordersRes))
    } catch (err: any) {
      console.error('Dashboard loading error:', err)
      if (err.response?.status === 404) {
        setStores([])
        setProducts([])
        setError('')
      } else {
        setError(err.response?.data?.message || 'Error al cargar el dashboard')
      }
    } finally {
      setLoading(false)
    }
  }

  const logout = async () => {
    try {
      await API.post('/logout')
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      localStorage.removeItem('user')
      localStorage.removeItem('token')
      navigate('/login')
    }
  }

  useEffect(() => {
    if (!user) {
      navigate('/login')
      return
    }
    fetchDashboardData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  return (
    <div className="space-y-8">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div>
          <p className="text-sm text-white/60">Hola, {user?.name || 'comerciante'}</p>
          <h1 className="text-3xl font-semibold text-white">Panel del comerciante</h1>
        </div>
        <Button variant="ghost" onClick={logout}>Cerrar sesion</Button>
      </div>

      {loading && (
        <div className="flex justify-center py-10">
          <div className="h-12 w-12 rounded-full border-2 border-white/20 border-t-brand-500 animate-spin" />
        </div>
      )}

      {!loading && error && (
        <GlassCard className="border-red-500/30 bg-red-500/10 text-red-100">
          {error}
        </GlassCard>
      )}

      {!loading && !error && (
        <div className="space-y-8">
          <div className="grid gap-4 md:grid-cols-3">
            <StatCard label="Tienda principal" value={primaryStore?.name || 'Sin tienda'} hint={primaryStore?.description || 'Aun no creas tu tienda'} />
            <StatCard label="Productos publicados" value={productsCount} hint="Gestiona tu catalogo" />
            <StatCard label="Ventas del mes" value={`$${monthlySales.toLocaleString('es-CO')}`} hint={`${ordersCount} pedidos registrados`} />
          </div>

          <div className="grid gap-6 lg:grid-cols-3">
            <GlassCard className="lg:col-span-2 space-y-4">
              <div>
                <h3 className="text-lg font-semibold text-white">Acciones rapidas</h3>
                <p className="text-sm text-white/60">Gestion diaria de tu tienda</p>
              </div>
              <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                {!primaryStore && (
                  <Link to="/dashboard/store" className="glass rounded-xl px-4 py-3 text-center hover:border-brand-400/60">
                    <p className="font-semibold text-white">Crear tienda</p>
                    <p className="text-xs text-white/60 mt-1">Publica tu catalogo</p>
                  </Link>
                )}
                <Link to="/dashboard/products" className="glass rounded-xl px-4 py-3 text-center hover:border-brand-400/60">
                  <p className="font-semibold text-white">Productos</p>
                  <p className="text-xs text-white/60 mt-1">Crea, edita y elimina</p>
                </Link>
                <Link to="/stores" className="glass rounded-xl px-4 py-3 text-center hover:border-brand-400/60">
                  <p className="font-semibold text-white">Explorar tiendas</p>
                  <p className="text-xs text-white/60 mt-1">Inspirate en otros catalogos</p>
                </Link>
              </div>
            </GlassCard>

            <GlassCard className="space-y-3">
              <h3 className="text-lg font-semibold text-white">Actividad reciente</h3>
              {recentActivity.length === 0 ? (
                <p className="text-sm text-white/60">Aun no hay pedidos registrados.</p>
              ) : (
                <div className="space-y-3">
                  {recentActivity.map((item) => (
                    <div key={item.id} className="flex items-start justify-between border-b border-white/10 pb-3 last:border-b-0">
                      <div>
                        <p className="text-sm text-white font-medium">Pedido #{item.id}</p>
                        <p className="text-xs text-white/50">{formatDate(item.date)}</p>
                      </div>
                      <div className="text-right">
                        <Badge variant="neutral" className="capitalize">{item.status}</Badge>
                        <p className="text-sm text-brand-200 font-semibold mt-1">${item.amount.toLocaleString('es-CO')}</p>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </GlassCard>
          </div>
        </div>
      )}
    </div>
  )
}
