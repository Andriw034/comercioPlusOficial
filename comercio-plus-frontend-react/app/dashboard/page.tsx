import { useEffect, useMemo, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import API from '@/lib/api'
import Card from '@/components/ui/Card'
import Button from '@/components/ui/Button'
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
          <p className="text-sm text-muted">Hola, {user?.name || 'comerciante'}</p>
          <h1 className="text-3xl font-semibold text-white">Panel del comerciante</h1>
        </div>
        <Button variant="ghost" onClick={logout}>Cerrar sesión</Button>
      </div>

      {loading && (
        <div className="flex justify-center py-10">
          <div className="h-12 w-12 rounded-full border-2 border-white/20 border-t-brand-400 animate-spin"></div>
        </div>
      )}

      {!loading && error && (
        <div className="rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-100">
          {error}
        </div>
      )}

      {!loading && !error && (
        <div className="space-y-8">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Card className="p-5">
              <p className="text-sm text-muted">Tienda principal</p>
              <h3 className="mt-2 text-2xl font-semibold text-white">{primaryStore?.name || 'Sin tienda'}</h3>
              <p className="text-sm text-muted mt-1">{primaryStore?.description || 'Aún no creas tu tienda'}</p>
            </Card>
            <Card className="p-5">
              <p className="text-sm text-muted">Productos publicados</p>
              <h3 className="mt-2 text-2xl font-semibold text-white">{productsCount}</h3>
              <p className="text-xs text-muted mt-1">Usa filtros en Productos para editarlos</p>
            </Card>
            <Card className="p-5">
              <p className="text-sm text-muted">Ventas del mes</p>
              <h3 className="mt-2 text-2xl font-semibold text-white">${monthlySales.toLocaleString('es-CO')}</h3>
              <p className="text-xs text-muted mt-1">{ordersCount} pedidos registrados</p>
            </Card>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <Card className="lg:col-span-2 p-6">
              <div className="flex items-center justify-between mb-4">
                <div>
                  <h3 className="text-lg font-semibold text-white">Acciones rápidas</h3>
                  <p className="text-sm text-muted">Gestión diaria de tu tienda</p>
                </div>
              </div>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                {!primaryStore && (
                  <Link to="/dashboard/store" className="glass rounded-xl px-4 py-3 text-center hover:border-brand-400/60">
                    <p className="font-semibold text-white">Crear tienda</p>
                    <p className="text-xs text-muted mt-1">Publica tu catálogo</p>
                  </Link>
                )}
                <Link to="/dashboard/products" className="glass rounded-xl px-4 py-3 text-center hover:border-brand-400/60">
                  <p className="font-semibold text-white">Productos</p>
                  <p className="text-xs text-muted mt-1">Crea, edita y elimina</p>
                </Link>
                <Link to="/stores" className="glass rounded-xl px-4 py-3 text-center hover:border-brand-400/60">
                  <p className="font-semibold text-white">Explorar tiendas</p>
                  <p className="text-xs text-muted mt-1">Inspírate en otros catálogos</p>
                </Link>
              </div>
            </Card>

            <Card className="p-6">
              <h3 className="text-lg font-semibold text-white mb-3">Actividad reciente</h3>
              {recentActivity.length === 0 ? (
                <div className="text-sm text-muted">Aún no hay pedidos registrados.</div>
              ) : (
                <div className="space-y-3">
                  {recentActivity.map((item) => (
                    <div key={item.id} className="flex items-start justify-between border-b border-white/5 pb-3 last:border-b-0">
                      <div>
                        <p className="text-sm text-white font-medium">Pedido #{item.id}</p>
                        <p className="text-xs text-muted">{formatDate(item.date)}</p>
                      </div>
                      <div className="text-right">
                        <span className="chip capitalize">{item.status}</span>
                        <p className="text-sm text-brand-200 font-semibold mt-1">${item.amount.toLocaleString('es-CO')}</p>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </Card>
          </div>
        </div>
      )}
    </div>
  )
}


