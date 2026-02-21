import { useEffect, useMemo, useState } from 'react'
import { Outlet, Link, useLocation } from 'react-router-dom'
import { Icon } from '@/components/Icon'
import API from '@/lib/api'
import { resolveMediaUrl } from '@/lib/format'

interface Store {
  id: string | number
  name: string
  logo?: string
  cover?: string
}

interface NavItem {
  icon: 'package' | 'users' | 'settings' | 'chart' | 'store'
  label: string
  href: string
  active?: boolean
}

export default function DashboardLayout() {
  const location = useLocation()
  const [store, setStore] = useState<Store | null>(null)
  const [isLoading, setIsLoading] = useState(true)
  const [logoFailed, setLogoFailed] = useState(false)

  useEffect(() => {
    loadStoreData()
  }, [])

  const loadStoreData = async () => {
    try {
      const { data } = await API.get('/my/store')
      const mapped: Store = {
        id: data?.id || '',
        name: data?.name || 'ComercioPlus',
        logo: resolveMediaUrl(data?.logo_url || data?.logo_path || data?.logo) || '',
        cover: resolveMediaUrl(data?.cover_url || data?.cover_path || data?.background_url || data?.cover) || '',
      }
      setLogoFailed(false)
      setStore(mapped)
      localStorage.setItem('store', JSON.stringify(data || mapped))
    } catch {
      try {
        const cached = localStorage.getItem('store')
        const parsed = cached ? JSON.parse(cached) : null
        if (parsed) {
          setStore({
            id: parsed.id || '',
            name: parsed.name || 'ComercioPlus',
            logo: resolveMediaUrl(parsed.logo_url || parsed.logo_path || parsed.logo) || '',
            cover: resolveMediaUrl(parsed.cover_url || parsed.cover_path || parsed.background_url || parsed.cover) || '',
          })
        } else {
          setStore({ id: '', name: 'ComercioPlus', logo: '', cover: '' })
        }
      } catch {
        setStore({ id: '', name: 'ComercioPlus', logo: '', cover: '' })
      }
    } finally {
      setIsLoading(false)
    }
  }

  const navItems: NavItem[] = useMemo(
    () => [
      {
        icon: 'package',
        label: 'Productos',
        href: '/dashboard/products',
        active: location.pathname.startsWith('/dashboard/products'),
      },
      {
        icon: 'users',
        label: 'Clientes',
        href: '/dashboard/customers',
        active: location.pathname === '/dashboard/customers',
      },
      {
        icon: 'settings',
        label: 'Configuracion',
        href: '/dashboard/store',
        active: location.pathname === '/dashboard/store',
      },
    ],
    [location.pathname],
  )

  if (isLoading) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-slate-50">
        <div className="text-center">
          <div className="mb-4 inline-block h-12 w-12 animate-spin rounded-full border-4 border-comercioplus-600 border-t-transparent" />
          <p className="text-body text-slate-600">Cargando...</p>
        </div>
      </div>
    )
  }

  return (
    <div className="flex min-h-screen bg-slate-50">
      <aside className="fixed left-0 top-0 z-40 h-screen w-60 bg-slate-900">
        <div className="border-b border-slate-800 p-6">
          {store?.logo && !logoFailed ? (
            <div className="flex items-center gap-3">
              <img
                src={store.logo}
                alt={store.name}
                className="h-12 w-12 rounded-lg object-cover ring-2 ring-slate-700"
                onError={() => setLogoFailed(true)}
              />
              <div className="flex-1 overflow-hidden">
                <h2 className="truncate text-base font-bold text-white">{store.name}</h2>
                <p className="text-xs text-slate-400">Panel de control</p>
              </div>
            </div>
          ) : (
            <div className="flex items-center gap-3">
              <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-comercioplus-600 text-white font-semibold">
                {(store?.name?.trim()?.charAt(0) || 'C').toUpperCase()}
              </div>
              <div className="min-w-0">
                <h2 className="truncate text-base font-bold text-white">{store?.name || 'ComercioPlus'}</h2>
                <p className="text-xs text-slate-400">Panel de control</p>
              </div>
            </div>
          )}
        </div>

        <nav className="space-y-1 p-4">
          {navItems.map((item) => (
            <Link
              key={item.href}
              to={item.href}
              className={`flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-all ${
                item.active
                  ? 'bg-comercioplus-600 text-white shadow-lg'
                  : 'text-slate-300 hover:bg-slate-800 hover:text-white'
              }`}
            >
              <Icon name={item.icon} size={20} />
              <span>{item.label}</span>
            </Link>
          ))}
        </nav>

        <div className="absolute bottom-0 left-0 right-0 border-t border-slate-800 p-4">
          <button
            onClick={() => {
              localStorage.removeItem('user')
              localStorage.removeItem('token')
              window.location.href = '/login'
            }}
            className="flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-slate-300 transition-all hover:bg-slate-800 hover:text-white"
          >
            <Icon name='logout' size={20} />
            <span>Cerrar sesion</span>
          </button>
        </div>
      </aside>

      <main className="ml-60 flex-1 p-8">
        <Outlet context={{ store }} />
      </main>
    </div>
  )
}
