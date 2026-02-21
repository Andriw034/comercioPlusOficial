import { useEffect, useMemo, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import API from '@/lib/api'
import { clearSession } from '@/services/auth-session'
import GlassCard from '@/components/ui/GlassCard'
import Button, { buttonVariants } from '@/components/ui/button'
import StatCard from '@/components/ui/StatCard'
import Badge from '@/components/ui/Badge'
import { formatDate } from '@/lib/format'
import type { Store } from '@/types/api'

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
  const [productsCount, setProductsCount] = useState(0)
  const [orders, setOrders] = useState<Order[]>([])
  const [monthlySales, setMonthlySales] = useState(0)
  const [monthlyOrdersCount, setMonthlyOrdersCount] = useState(0)

  const user = useMemo(() => {
    const userData = localStorage.getItem('user')
    return userData ? JSON.parse(userData) : null
  }, [])

  const primaryStore = useMemo(() => stores[0] || null, [stores])
  const monthlySalesHint = useMemo(
    () =>
      monthlySales === 0
        ? 'Aún no hay ventas este mes · Publica productos o comparte tu tienda'
        : `${monthlyOrdersCount} pedidos registrados este mes`,
    [monthlyOrdersCount, monthlySales],
  )
  const publicStoreHref = useMemo(() => {
    if (primaryStore?.id) return `/store/${primaryStore.id}`
    if (primaryStore?.slug) return `/store/${primaryStore.slug}`
    return '/dashboard/store'
  }, [primaryStore])
  const publicStoreLabel = primaryStore ? 'Ver mi tienda pública' : 'Crear tienda primero'
  const publicStoreHint = primaryStore
    ? 'Revisa cómo la ven tus clientes'
    : 'Configura tu tienda para habilitar la vista pública'

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

  const normalizeTotal = (response: any) => {
    if (typeof response?.data?.total === 'number') return response.data.total
    if (typeof response?.data?.meta?.total === 'number') return response.data.meta.total
    const rows = normalizeList(response)
    return Array.isArray(rows) ? rows.length : 0
  }

  const fetchDashboardData = async () => {
    try {
      setLoading(true)
      setError('')
      const storeRes = await API.get('/my/store')
      const store = storeRes?.data ? [storeRes.data] : []
      setStores(store)

      if (!storeRes?.data?.id) {
        setProductsCount(0)
        setOrders([])
        setMonthlySales(0)
        setMonthlyOrdersCount(0)
        return
      }

      const [productsRes, ordersRes, summaryRes] = await Promise.all([
        API.get('/products', {
          params: {
            store_id: storeRes.data.id,
            per_page: 1,
          },
        }),
        API.get('/merchant/orders', {
          params: {
            per_page: 5,
          },
        }),
        API.get('/reports/summary'),
      ])

      setProductsCount(normalizeTotal(productsRes))
      setOrders(normalizeList(ordersRes))
      setMonthlySales(Number(summaryRes?.data?.data?.gross_sales ?? 0))
      setMonthlyOrdersCount(Number(summaryRes?.data?.data?.orders_count ?? 0))
    } catch (err: any) {
      if (err.response?.status === 404) {
        setStores([])
        setProductsCount(0)
        setOrders([])
        setMonthlySales(0)
        setMonthlyOrdersCount(0)
        localStorage.removeItem('store')
        setError('')
      } else {
        console.error('Dashboard loading error:', err)
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
      clearSession()
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
    <div className="space-y-6">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p className="text-[13px] text-slate-600 dark:text-white/60">Hola, {user?.name || 'comerciante'}</p>
          <h1 className="text-[22px] font-semibold text-slate-900 dark:text-white sm:text-[26px]">Panel del comerciante</h1>
        </div>
        <Button variant="ghost" onClick={logout}>
          Cerrar sesion
        </Button>
      </div>

      {loading && (
        <div className="flex justify-center py-10">
          <div className="h-12 w-12 animate-spin rounded-full border-2 border-slate-900/10 border-t-brand-500 dark:border-white/20" />
        </div>
      )}

      {!loading && error && (
        <GlassCard className="border-red-500/30 bg-red-500/10 text-red-700 dark:text-red-100">
          {error}
        </GlassCard>
      )}

      {!loading && !error && (
        <div className="space-y-6">
          <div className="grid gap-4 md:grid-cols-3">
            <StatCard label="Tienda principal" value={primaryStore?.name || 'Sin tienda'} hint={primaryStore?.description || 'Aun no creas tu tienda'} />
            <StatCard label="Productos publicados" value={productsCount} hint="Gestiona tu catalogo" />
            <StatCard label="Ventas del mes" value={`$${monthlySales.toLocaleString('es-CO')}`} hint={monthlySalesHint} />
          </div>

          <div className="grid gap-6 lg:grid-cols-3">
            <GlassCard className="lg:col-span-2 space-y-4">
              <div>
                <h3 className="text-[16px] font-semibold text-slate-900 dark:text-white">Acciones rapidas</h3>
                <p className="text-[13px] text-slate-600 dark:text-white/60">Gestion diaria de tu tienda</p>
              </div>
              <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                  to="/dashboard/store"
                  className="rounded-2xl border border-slate-200 bg-white p-4 transition-colors hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10"
                >
                  <p className="text-[14px] font-semibold text-slate-900 dark:text-white">Configurar tienda</p>
                  <p className="mt-1 text-[12px] text-slate-500 dark:text-white/60">Logo, portada y datos de contacto</p>
                </Link>
                <Link
                  to="/dashboard/products"
                  className="rounded-2xl border border-slate-200 bg-white p-4 transition-colors hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10"
                >
                  <p className="text-[14px] font-semibold text-slate-900 dark:text-white">Gestionar productos</p>
                  <p className="mt-1 text-[12px] text-slate-500 dark:text-white/60">Crea, edita y organiza tu catalogo</p>
                </Link>
                <Link
                  to={publicStoreHref}
                  className="rounded-2xl border border-slate-200 bg-white p-4 transition-colors hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10"
                >
                  <p className="text-[14px] font-semibold text-slate-900 dark:text-white">{publicStoreLabel}</p>
                  <p className="mt-1 text-[12px] text-slate-500 dark:text-white/60">{publicStoreHint}</p>
                </Link>
                <Link
                  to="/dashboard/customers"
                  className="rounded-2xl border border-slate-200 bg-white p-4 transition-colors hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10"
                >
                  <p className="text-[14px] font-semibold text-slate-900 dark:text-white">Ver clientes</p>
                  <p className="mt-1 text-[12px] text-slate-500 dark:text-white/60">Consulta visitas y potenciales compradores</p>
                </Link>
              </div>
            </GlassCard>

            <GlassCard className="space-y-3">
              <h3 className="text-[16px] font-semibold text-slate-900 dark:text-white">Actividad reciente</h3>
              {recentActivity.length === 0 ? (
                <div className="space-y-3">
                  <p className="text-[13px] text-slate-600 dark:text-white/60">
                    Aun no hay pedidos. Cuando recibas pedidos, apareceran aqui.
                  </p>
                  <Link to="/dashboard/products" className={buttonVariants('secondary', 'h-10 text-[13px]')}>
                    Ver productos
                  </Link>
                </div>
              ) : (
                <div className="space-y-3">
                  {recentActivity.map((item) => (
                    <div key={item.id} className="flex items-start justify-between border-b border-slate-200 pb-3 last:border-b-0 dark:border-white/10">
                      <div>
                        <p className="text-[13px] font-medium text-slate-900 dark:text-white">Pedido #{item.id}</p>
                        <p className="text-[12px] text-slate-500 dark:text-white/60">{formatDate(item.date)}</p>
                      </div>
                      <div className="text-right">
                        <Badge variant="neutral" className="capitalize">{item.status}</Badge>
                        <p className="mt-1 text-[13px] font-semibold text-brand-600 dark:text-brand-200">${item.amount.toLocaleString('es-CO')}</p>
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
