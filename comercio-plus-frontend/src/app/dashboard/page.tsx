import { useEffect, useMemo, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import API from '@/lib/api'
import { clearSession } from '@/services/auth-session'
import GlassCard from '@/components/ui/GlassCard'
import Button, { buttonVariants } from '@/components/ui/button'
import Badge from '@/components/ui/Badge'
import { formatDate, formatPrice } from '@/lib/format'
import { ROUTES } from '@/lib/routes'
import { getInventoryMovements, type InventoryMovementItem } from '@/services/inventoryReceive'
import { getRecentPickingEvents, type PickingEventItem } from '@/services/picking'
import type { Store } from '@/types/api'

type Order = {
  id: number
  status?: string
  fulfillment_status?: string
  total_amount?: number
  total?: number
  date?: string
  created_at?: string
}

type KpiCard = {
  label: string
  value: string | number
  hint: string
  action: string
  badge: 'brand' | 'neutral' | 'success' | 'warning'
}

type RecentOrder = {
  id: number
  status: string
  amount: number
  date?: string
}

type ActivityItem = {
  id: string
  source: 'inventory' | 'picking' | 'order'
  title: string
  detail: string
  statusLabel: string
  statusTone: 'brand' | 'neutral' | 'success' | 'warning' | 'danger'
  date?: string
}

function LoadingSkeleton({ className = '' }: { className?: string }) {
  return <div className={`animate-pulse rounded-xl bg-slate-200/80 dark:bg-white/10 ${className}`.trim()} />
}

const getSaludo = (date: Date) => {
  const hour = date.getHours()
  if (hour < 12) return 'Buenos dias'
  if (hour < 18) return 'Buenas tardes'
  return 'Buenas noches'
}

const FRASES_DIA = [
  'Hoy puede ser un gran dia para mover tu inventario.',
  'Un catalogo claro vende mas rapido.',
  'Los pedidos salen mejor con alistamiento ordenado.',
  'Actualizar stock evita cancelaciones y retrabajo.',
  'Responder rapido mejora la recompra de clientes.',
  'Pequenos ajustes diarios mantienen la tienda saludable.',
  'Revisa tus productos top y asegura existencia.',
]

const readableStatus = (value?: string) => {
  const key = String(value || 'pending').toLowerCase()
  const map: Record<string, string> = {
    pending: 'Pendiente',
    paid: 'Pagado',
    picking: 'Alistando',
    picked: 'Alistado',
    packed: 'Empacado',
    ready: 'Listo',
    shipped: 'Enviado',
    delivered: 'Entregado',
    cancelled: 'Cancelado',
    canceled: 'Cancelado',
  }
  return map[key] || key
}

const badgeByStatus = (value?: string): 'brand' | 'neutral' | 'success' | 'warning' | 'danger' => {
  const key = String(value || 'pending').toLowerCase()
  if (['delivered', 'paid'].includes(key)) return 'success'
  if (['picking', 'picked', 'packed', 'ready', 'shipped'].includes(key)) return 'brand'
  if (['cancelled', 'canceled'].includes(key)) return 'danger'
  return 'warning'
}

const toTimestamp = (value?: string) => {
  if (!value) return 0
  const ms = new Date(value).getTime()
  return Number.isFinite(ms) ? ms : 0
}

const pickingActionLabel = (action?: string) => {
  const key = String(action || '').toLowerCase()
  const map: Record<string, string> = {
    scan_ok: 'Escaneo OK',
    scan_error: 'Error de escaneo',
    manual_pick: 'Pick manual',
    manual_missing: 'Faltante manual',
    manual_note: 'Nota manual',
    fallback_triggered: 'Fallback manual',
    picking_completed: 'Picking completado',
    picking_reset: 'Picking reiniciado',
  }
  return map[key] || 'Evento picking'
}

const pickingActionTone = (action?: string): 'brand' | 'neutral' | 'success' | 'warning' | 'danger' => {
  const key = String(action || '').toLowerCase()
  if (['scan_error'].includes(key)) return 'danger'
  if (['picking_completed', 'scan_ok', 'manual_pick'].includes(key)) return 'success'
  if (['fallback_triggered', 'manual_missing'].includes(key)) return 'warning'
  if (['picking_reset'].includes(key)) return 'neutral'
  return 'brand'
}

const movementReasonLabel = (reason?: string) => {
  const key = String(reason || '').toLowerCase()
  const map: Record<string, string> = {
    purchase: 'Compra',
    return: 'Devolucion',
    adjustment: 'Ajuste',
  }
  return map[key] || (reason ? reason : 'Ingreso')
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
  const [inventoryMovements, setInventoryMovements] = useState<InventoryMovementItem[]>([])
  const [pickingEvents, setPickingEvents] = useState<PickingEventItem[]>([])
  const [activityLoading, setActivityLoading] = useState(true)

  const user = useMemo(() => {
    try {
      const userData = localStorage.getItem('user')
      return userData ? JSON.parse(userData) : null
    } catch {
      return null
    }
  }, [])

  const primaryStore = useMemo(() => stores[0] || null, [stores])
  const saludo = useMemo(() => getSaludo(new Date()), [])
  const fraseDelDia = useMemo(() => FRASES_DIA[new Date().getDay()] || FRASES_DIA[0], [])

  const publicStoreHref = useMemo(() => {
    if (primaryStore?.id) return `/store/${primaryStore.id}`
    if (primaryStore?.slug) return `/store/${primaryStore.slug}`
    return ROUTES.store
  }, [primaryStore])

  const publicStoreLabel = primaryStore ? 'Ver mi tienda publica' : 'Crear tienda primero'
  const publicStoreHint = primaryStore
    ? 'Revisa como la ven tus clientes'
    : 'Configura tu tienda para habilitar la vista publica'

  const recentOrders = useMemo<RecentOrder[]>(
    () =>
      [...orders]
        .sort((a, b) => toTimestamp(b.date || b.created_at) - toTimestamp(a.date || a.created_at))
        .slice(0, 5)
        .map((order) => ({
          id: order.id,
          status: order.fulfillment_status || order.status || 'pending',
          amount: Number(order.total_amount ?? order.total ?? 0),
          date: order.date || order.created_at,
        })),
    [orders],
  )

  const todaySummary = useMemo(() => {
    const today = new Date().toDateString()
    const rows = recentOrders.filter((order) => (order.date ? new Date(order.date).toDateString() === today : false))
    return {
      orders: rows.length,
      sales: rows.reduce((sum, row) => sum + row.amount, 0),
    }
  }, [recentOrders])

  const pendingPickingCount = useMemo(
    () => recentOrders.filter((row) => ['paid', 'picking', 'ready'].includes(String(row.status).toLowerCase())).length,
    [recentOrders],
  )

  const kpis = useMemo<KpiCard[]>(
    () => [
      {
        label: 'Tienda principal',
        value: primaryStore?.name || 'Sin tienda',
        hint: primaryStore?.description || 'Aun no creas tu tienda',
        action: primaryStore ? 'Actualiza logo y horarios para generar confianza.' : 'Completa tu tienda para empezar a vender.',
        badge: primaryStore ? 'success' : 'warning',
      },
      {
        label: 'Productos activos',
        value: productsCount,
        hint: productsCount === 0 ? 'Tu catalogo esta vacio' : `${productsCount} referencias en catalogo`,
        action:
          productsCount === 0
            ? 'Sube tus primeros repuestos para activar ventas.'
            : productsCount < 5
              ? 'Agrega mas referencias de alta rotacion.'
              : 'Buen inventario base, revisa stock diario.',
        badge: productsCount === 0 ? 'warning' : 'brand',
      },
      {
        label: 'Pedidos del mes',
        value: monthlyOrdersCount,
        hint: monthlyOrdersCount === 0 ? 'Sin pedidos en el mes' : `${monthlyOrdersCount} pedidos registrados`,
        action:
          monthlyOrdersCount === 0
            ? 'Comparte tu tienda y activa promociones locales.'
            : pendingPickingCount > 0
              ? `${pendingPickingCount} pedidos requieren alistamiento.`
              : 'Buen ritmo. Mantener tiempos bajos mejora recompra.',
        badge: monthlyOrdersCount === 0 ? 'warning' : 'brand',
      },
      {
        label: 'Ventas del mes',
        value: `$${formatPrice(monthlySales)}`,
        hint: monthlySales === 0 ? 'Sin ventas registradas en el mes' : 'Ingresos brutos acumulados',
        action: monthlySales === 0 ? 'Valida precios y productos destacados.' : 'Analiza margen por producto para crecer mejor.',
        badge: monthlySales === 0 ? 'warning' : 'success',
      },
    ],
    [monthlySales, monthlyOrdersCount, pendingPickingCount, primaryStore, productsCount],
  )

  const setupChecklist = useMemo(() => {
    const items = [
      {
        done: Boolean(primaryStore),
        title: 'Completar datos de tienda',
        detail: 'Logo, portada y datos de contacto.',
        to: ROUTES.store,
      },
      {
        done: productsCount > 0,
        title: 'Publicar productos',
        detail: 'Agrega repuestos con precio y stock.',
        to: ROUTES.products,
      },
      {
        done: recentOrders.length > 0,
        title: 'Recibir primer pedido',
        detail: 'Cuando llegue, alistalo desde pedidos.',
        to: ROUTES.orders,
      },
      {
        done: false,
        title: 'Usar ingreso por escaner',
        detail: 'Acelera reposicion en inventario.',
        to: ROUTES.inventoryReceive,
      },
    ]
    const completed = items.filter((item) => item.done).length
    return {
      items,
      completed,
      percent: Math.round((completed / items.length) * 100),
    }
  }, [primaryStore, productsCount, recentOrders.length])

  const activityItems = useMemo<ActivityItem[]>(() => {
    const inventoryItems: ActivityItem[] = inventoryMovements.map((movement) => ({
      id: `inv-${movement.id}`,
      source: 'inventory',
      title: `Ingreso inventario +${movement.quantity}`,
      detail: `${movement.product_name || 'Producto'} - ${movementReasonLabel(movement.reason)}${movement.reference ? ` - Ref ${movement.reference}` : ''}`,
      statusLabel: 'Inventario',
      statusTone: 'success',
      date: movement.created_at || undefined,
    }))

    const pickingItems: ActivityItem[] = pickingEvents.map((event) => ({
      id: `pick-${event.id}`,
      source: 'picking',
      title: `${pickingActionLabel(event.action)} - Pedido #${event.order_id}`,
      detail: `${event.product_name || 'Sin producto'}${event.message ? ` - ${event.message}` : ''}`,
      statusLabel: readableStatus(event.fulfillment_status || event.status || event.action),
      statusTone: pickingActionTone(event.action),
      date: event.created_at || undefined,
    }))

    const merged = [...inventoryItems, ...pickingItems]
      .sort((a, b) => toTimestamp(b.date) - toTimestamp(a.date))
      .slice(0, 6)

    if (merged.length > 0) return merged

    return recentOrders.slice(0, 4).map((order) => ({
      id: `order-${order.id}`,
      source: 'order',
      title: `Pedido #${order.id} de repuestos`,
      detail:
        String(order.status).toLowerCase() === 'picking'
          ? 'En alistamiento de bodega.'
          : `Estado actual: ${readableStatus(order.status)}.`,
      statusLabel: readableStatus(order.status),
      statusTone: badgeByStatus(order.status),
      date: order.date,
    }))
  }, [inventoryMovements, pickingEvents, recentOrders])

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
      setActivityLoading(true)
      setError('')
      const storeRes = await API.get('/my/store')
      const store = storeRes?.data ? [storeRes.data] : []
      setStores(store)

      if (!storeRes?.data?.id) {
        setProductsCount(0)
        setOrders([])
        setMonthlySales(0)
        setMonthlyOrdersCount(0)
        setInventoryMovements([])
        setPickingEvents([])
        setActivityLoading(false)
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
            per_page: 8,
          },
        }),
        API.get('/reports/summary'),
      ])

      setProductsCount(normalizeTotal(productsRes))
      setOrders(normalizeList(ordersRes))
      setMonthlySales(Number(summaryRes?.data?.data?.gross_sales ?? 0))
      setMonthlyOrdersCount(Number(summaryRes?.data?.data?.orders_count ?? 0))
      setLoading(false)

      const [inventoryResult, pickingResult] = await Promise.allSettled([
        getInventoryMovements(12, null),
        getRecentPickingEvents(12),
      ])

      setInventoryMovements(inventoryResult.status === 'fulfilled' ? inventoryResult.value.data : [])
      setPickingEvents(pickingResult.status === 'fulfilled' ? pickingResult.value.data : [])
      setActivityLoading(false)
    } catch (err: any) {
      if (err.response?.status === 404) {
        setStores([])
        setProductsCount(0)
        setOrders([])
        setMonthlySales(0)
        setMonthlyOrdersCount(0)
        setInventoryMovements([])
        setPickingEvents([])
        setActivityLoading(false)
        localStorage.removeItem('store')
        setError('')
      } else {
        console.error('Dashboard loading error:', err)
        setError(err.response?.data?.message || 'Error al cargar el dashboard')
        setActivityLoading(false)
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
      navigate(ROUTES.login)
    }
  }

  useEffect(() => {
    if (!user) {
      navigate(ROUTES.login)
      return
    }
    fetchDashboardData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  return (
    <div
      className="space-y-6"
      style={{
        backgroundImage:
          'radial-gradient(circle at 12% -15%, rgba(249,115,22,0.18), transparent 48%), radial-gradient(circle at 92% 0%, rgba(59,130,246,0.08), transparent 42%)',
      }}
    >
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p className="text-[13px] font-medium text-slate-600 dark:text-white/60">
            {saludo}, {user?.name || 'comerciante'}
          </p>
          <h1 className="text-[24px] font-semibold text-slate-900 dark:text-white sm:text-[30px]">Panel del comerciante</h1>
          <p className="mt-1 text-[13px] text-slate-600 dark:text-white/60">{fraseDelDia}</p>
        </div>
        <Button variant="ghost" onClick={logout} className="bg-white/60 backdrop-blur-sm dark:bg-white/5">
          Cerrar sesion
        </Button>
      </div>

      {loading && (
        <div className="space-y-6">
          <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            {[...Array(4)].map((_, idx) => (
              <div key={`kpi-skeleton-${idx}`} className="dashboard-reveal" style={{ animationDelay: `${idx * 50}ms` }}>
                <GlassCard className="space-y-3 border-slate-200/90 bg-white/80">
                  <LoadingSkeleton className="h-3 w-1/3" />
                  <LoadingSkeleton className="h-8 w-1/2" />
                  <LoadingSkeleton className="h-3 w-3/4" />
                  <LoadingSkeleton className="h-3 w-2/3" />
                </GlassCard>
              </div>
            ))}
          </div>

          <div className="grid gap-6 lg:grid-cols-3">
            <GlassCard className="space-y-4 lg:col-span-2 border-slate-200/90 bg-white/80">
              <LoadingSkeleton className="h-4 w-40" />
              <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                {[...Array(6)].map((_, idx) => (
                  <div key={`action-skeleton-${idx}`} className="rounded-2xl border border-slate-200 bg-white p-4">
                    <LoadingSkeleton className="mb-2 h-4 w-1/2" />
                    <LoadingSkeleton className="h-3 w-4/5" />
                  </div>
                ))}
              </div>
            </GlassCard>

            <GlassCard className="space-y-3 border-slate-200/90 bg-white/80">
              <LoadingSkeleton className="h-4 w-32" />
              <LoadingSkeleton className="h-16 w-full" />
              <LoadingSkeleton className="h-16 w-full" />
              <LoadingSkeleton className="h-16 w-full" />
            </GlassCard>
          </div>
        </div>
      )}

      {!loading && error && <GlassCard className="border-red-500/30 bg-red-500/10 text-red-700 dark:text-red-100">{error}</GlassCard>}

      {!loading && !error && (
        <div className="space-y-6">
          <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            {kpis.map((card, idx) => (
              <GlassCard
                key={card.label}
                className="dashboard-reveal space-y-3 border-orange-200/80 bg-gradient-to-br from-white via-white to-orange-50/70 shadow-[0_20px_40px_rgba(249,115,22,0.14)]"
                aria-label={`${card.label}: ${card.value}`}
                style={{ animationDelay: `${idx * 55}ms` }}
              >
                <div className="flex items-center justify-between gap-2">
                  <p className="text-[12px] uppercase tracking-wide text-slate-500">{card.label}</p>
                  <Badge variant={card.badge}>
                    {card.badge === 'warning' ? 'Atencion' : card.badge === 'success' ? 'Estable' : 'Accion'}
                  </Badge>
                </div>
                <p className="text-[28px] font-bold leading-none text-[#1A1A2E]">{card.value}</p>
                <p className="text-[12px] text-slate-500 dark:text-white/60">{card.hint}</p>
                <p className="text-[12px] font-medium text-[#C2410C] dark:text-[#FDBA74]">{card.action}</p>
              </GlassCard>
            ))}
          </div>

          <div className="grid gap-6 lg:grid-cols-3">
            <div className="space-y-6 lg:col-span-2">
              <GlassCard className="dashboard-reveal space-y-4 border-slate-200/90 bg-gradient-to-br from-white to-slate-50/90 shadow-[0_18px_38px_rgba(15,23,42,0.10)]" style={{ animationDelay: '120ms' }}>
                <div className="space-y-3">
                  <h3 className="text-[16px] font-semibold text-slate-900 dark:text-white">Acciones rapidas</h3>
                  <p className="text-[13px] text-slate-600 dark:text-white/60">Gestion diaria enfocada en ventas y alistamiento.</p>
                </div>
                <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                  {[
                    { to: ROUTES.store, title: 'Configurar tienda', hint: 'Logo, portada y datos de contacto.', tag: 'ST' },
                    { to: ROUTES.products, title: 'Gestionar repuestos', hint: 'Crea, edita y organiza tu catalogo.', tag: 'PR' },
                    { to: ROUTES.inventoryReceive, title: 'Ingreso por escaner (IN)', hint: 'Repone stock rapido con codigo.', tag: 'IN' },
                    { to: ROUTES.orders, title: 'Alistar pedidos', hint: 'Gestiona picking y despacho.', tag: 'PK' },
                    { to: publicStoreHref, title: publicStoreLabel, hint: publicStoreHint, tag: 'TV' },
                    { to: ROUTES.customers, title: 'Ver clientes', hint: 'Consulta visitas y compradores activos.', tag: 'CL' },
                  ].map((action) => (
                    <Link
                      key={action.title}
                      to={action.to}
                      className="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:border-orange-300 hover:shadow-[0_12px_24px_rgba(249,115,22,0.14)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B35]/40 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10"
                    >
                      <div className="mb-2 inline-flex h-7 w-7 items-center justify-center rounded-full bg-orange-100 text-[10px] font-semibold text-orange-700 dark:bg-orange-500/20 dark:text-orange-200">
                        {action.tag}
                      </div>
                      <p className="text-[14px] font-semibold text-slate-900 dark:text-white">{action.title}</p>
                      <p className="mt-1 text-[12px] text-slate-500 dark:text-white/60">{action.hint}</p>
                    </Link>
                  ))}
                </div>
              </GlassCard>

              <GlassCard className="dashboard-reveal space-y-4 border-slate-200/90 bg-gradient-to-br from-white to-slate-50/90 shadow-[0_18px_38px_rgba(15,23,42,0.10)]" style={{ animationDelay: '180ms' }}>
                <div className="flex items-start justify-between gap-3">
                  <div>
                    <h3 className="text-[16px] font-semibold text-slate-900 dark:text-white">Completa tu tienda</h3>
                    <p className="text-[13px] text-slate-600 dark:text-white/60">Avance operativo para vender sin fricciones.</p>
                  </div>
                  <Badge variant={setupChecklist.percent === 100 ? 'success' : 'brand'}>{setupChecklist.percent}%</Badge>
                </div>
                <div
                  className="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-white/10"
                  role="progressbar"
                  aria-valuenow={setupChecklist.percent}
                  aria-valuemin={0}
                  aria-valuemax={100}
                >
                  <div className="h-full bg-gradient-to-r from-orange-500 to-amber-400 transition-all duration-500" style={{ width: `${setupChecklist.percent}%` }} />
                </div>
                <div className="space-y-2">
                  {setupChecklist.items.map((item) => (
                    <Link
                      key={item.title}
                      to={item.to}
                      className="flex items-start justify-between gap-3 rounded-xl border border-slate-200/80 bg-white/70 px-3 py-2 transition-all hover:border-orange-300 hover:bg-orange-50/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B35]/40 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10"
                    >
                      <div>
                        <p className="text-[13px] font-medium text-slate-900 dark:text-white">{item.title}</p>
                        <p className="text-[12px] text-slate-500 dark:text-white/60">{item.detail}</p>
                      </div>
                      <Badge variant={item.done ? 'success' : 'warning'}>{item.done ? 'Listo' : 'Pendiente'}</Badge>
                    </Link>
                  ))}
                </div>
              </GlassCard>
            </div>

            <div className="space-y-6">
              <GlassCard className="dashboard-reveal space-y-4 border-slate-200/90 bg-gradient-to-br from-white to-slate-50/90 shadow-[0_16px_30px_rgba(15,23,42,0.10)]" style={{ animationDelay: '220ms' }}>
                <h3 className="text-[16px] font-semibold text-slate-900 dark:text-white">Resumen de hoy</h3>
                <div className="space-y-3">
                  <div className="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 dark:bg-white/5">
                    <span className="text-[13px] text-slate-600 dark:text-white/60">Pedidos hoy</span>
                    <span className="text-[14px] font-semibold text-slate-900 dark:text-white">{todaySummary.orders}</span>
                  </div>
                  <div className="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 dark:bg-white/5">
                    <span className="text-[13px] text-slate-600 dark:text-white/60">Ventas hoy</span>
                    <span className="text-[14px] font-semibold text-slate-900 dark:text-white">${formatPrice(todaySummary.sales)}</span>
                  </div>
                  <div className="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 dark:bg-white/5">
                    <span className="text-[13px] text-slate-600 dark:text-white/60">Por alistar</span>
                    <span className="text-[14px] font-semibold text-[#C2410C] dark:text-[#FDBA74]">{pendingPickingCount}</span>
                  </div>
                </div>
              </GlassCard>

              <GlassCard className="dashboard-reveal space-y-3 border-slate-200/90 bg-gradient-to-br from-white to-slate-50/90 shadow-[0_16px_30px_rgba(15,23,42,0.10)]" style={{ animationDelay: '260ms' }}>
                <div className="flex items-center justify-between gap-3">
                  <h3 className="text-[16px] font-semibold text-slate-900 dark:text-white">Actividad operativa</h3>
                  <Badge variant={activityLoading ? 'warning' : inventoryMovements.length + pickingEvents.length > 0 ? 'success' : 'neutral'}>
                    {activityLoading ? 'Cargando' : inventoryMovements.length + pickingEvents.length > 0 ? 'En vivo' : 'Sin eventos'}
                  </Badge>
                </div>

                {activityLoading ? (
                  <div className="space-y-3">
                    {[...Array(4)].map((_, idx) => (
                      <div key={`activity-skeleton-${idx}`} className="rounded-xl border border-slate-200/80 bg-white/80 p-3 dark:border-white/10 dark:bg-white/5">
                        <LoadingSkeleton className="mb-2 h-3 w-24" />
                        <LoadingSkeleton className="mb-2 h-4 w-2/3" />
                        <LoadingSkeleton className="h-3 w-5/6" />
                      </div>
                    ))}
                  </div>
                ) : activityItems.length === 0 ? (
                  <div className="space-y-3">
                    <p className="text-[13px] text-slate-600 dark:text-white/60">Cuando entren pedidos o movimientos de inventario, veras eventos aqui.</p>
                    <Link to={ROUTES.products} className={buttonVariants('secondary', 'h-10 text-[13px]')}>
                      Cargar catalogo
                    </Link>
                  </div>
                ) : (
                  <div className="space-y-3">
                    {activityItems.map((item) => (
                      <div key={item.id} className="rounded-xl border border-slate-200/80 bg-white/80 p-3 dark:border-white/10 dark:bg-white/5">
                        <div className="flex items-start justify-between gap-3">
                          <div>
                            <div className="mb-1 inline-flex items-center gap-2">
                              <span className="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600 dark:bg-white/10 dark:text-white/70">
                                {item.source}
                              </span>
                              <p className="text-[11px] text-slate-500 dark:text-white/50">{formatDate(item.date)}</p>
                            </div>
                            <p className="text-[13px] font-semibold text-slate-900 dark:text-white">{item.title}</p>
                            <p className="text-[12px] text-slate-500 dark:text-white/60">{item.detail}</p>
                          </div>
                          <Badge variant={item.statusTone}>{item.statusLabel}</Badge>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </GlassCard>
            </div>
          </div>

          <GlassCard className="dashboard-reveal space-y-4 border-slate-200/90 bg-gradient-to-br from-white to-slate-50/90 shadow-[0_18px_38px_rgba(15,23,42,0.10)]" style={{ animationDelay: '300ms' }}>
            <div className="flex items-center justify-between gap-3">
              <div>
                <h3 className="text-[16px] font-semibold text-slate-900 dark:text-white">Pedidos recientes</h3>
                <p className="text-[13px] text-slate-600 dark:text-white/60">Control de pedidos para despacho y entrega.</p>
              </div>
              <Link to={ROUTES.orders} className={buttonVariants('ghost', 'h-9 px-4 text-[13px]')}>
                Ver todos
              </Link>
            </div>

            {recentOrders.length === 0 ? (
              <div className="space-y-3 rounded-xl border border-dashed border-slate-300 p-4 text-center dark:border-white/20">
                <p className="text-[13px] text-slate-600 dark:text-white/60">
                  {monthlyOrdersCount > 0
                    ? 'No hay pedidos recientes en esta vista. Revisa el historico completo.'
                    : 'Todavia no hay pedidos. Publica tus productos para empezar a vender.'}
                </p>
                <Link to={ROUTES.products} className={buttonVariants('secondary', 'h-10 text-[13px]')}>
                  Gestionar productos
                </Link>
              </div>
            ) : (
              <div className="space-y-3">
                {recentOrders.map((order) => (
                  <div
                    key={order.id}
                    className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200/80 bg-white/80 px-4 py-3 dark:border-white/10 dark:bg-white/5"
                  >
                    <div>
                      <p className="text-[13px] font-semibold text-slate-900 dark:text-white">Pedido #{order.id}</p>
                      <p className="text-[12px] text-slate-500 dark:text-white/60">{formatDate(order.date)}</p>
                    </div>
                    <div className="flex items-center gap-3">
                      <Badge variant={badgeByStatus(order.status)}>{readableStatus(order.status)}</Badge>
                      <p className="text-[13px] font-semibold text-[#C2410C] dark:text-[#FDBA74]">${formatPrice(order.amount)}</p>
                      <Link to={ROUTES.orderPicking(order.id)} className={buttonVariants('outline', 'h-9 px-3 text-[12px]')}>
                        Alistar
                      </Link>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </GlassCard>
        </div>
      )}
    </div>
  )
}
