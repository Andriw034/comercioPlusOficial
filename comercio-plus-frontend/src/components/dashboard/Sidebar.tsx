import { useEffect, useState } from 'react'
import { Link, useLocation, useNavigate } from 'react-router-dom'
import { Icon, type IconName } from '@/components/Icon'
import { resolveMediaUrl } from '@/lib/format'
import API from '@/lib/api'
import { clearSession } from '@/services/auth-session'
import LogoImage from '@/ui/images/LogoImage'

type NavItem = {
  href: string
  label: string
  icon: IconName
}

type NavGroup = {
  label: string
  items: NavItem[]
}

type StoreLike = {
  name?: string
  logo?: string
  logo_url?: string
  logo_path?: string
  cover?: string
  cover_url?: string
  cover_path?: string
  background_url?: string
} | null

type SidebarProps = {
  store?: StoreLike
}

// Sin color por item: el naranja de marca se reserva para el item activo. Antes
// habia 10 fondos distintos (sky, indigo, emerald, violet, lime...) compitiendo
// entre si, que es lo que hacia ver el panel saturado.
const navGroups: NavGroup[] = [
  {
    label: 'PRINCIPAL',
    items: [
      { href: '/dashboard', icon: 'chart', label: 'Dashboard' },
      { href: '/dashboard/store', icon: 'store', label: 'Mi Tienda' },
      { href: '/dashboard/products', icon: 'package', label: 'Productos' },
      { href: '/dashboard/orders', icon: 'file', label: 'Pedidos' },
      { href: '/dashboard/customers', icon: 'users', label: 'Clientes' },
      { href: '/dashboard/credit', icon: 'wallet', label: 'Fiado' },
      { href: '/dashboard/invoicing', icon: 'file', label: 'Facturación DIAN' },
    ],
  },
  {
    label: 'GESTIÓN',
    items: [
      { href: '/dashboard/categories', icon: 'tag', label: 'Categorías' },
      { href: '/dashboard/inventory', icon: 'package', label: 'Inventario' },
      { href: '/dashboard/inventory/import', icon: 'upload', label: 'Importar inventario' },
      { href: '/dashboard/inventory/receive', icon: 'camera', label: 'Ingreso escáner' },
      { href: '/dashboard/reports', icon: 'trending', label: 'IA Comercial' },
      { href: '/dashboard/settings', icon: 'settings', label: 'Configuración' },
    ],
  },
]

const isRouteActive = (pathname: string, href: string) => {
  if (href === '/dashboard') return pathname === href
  return pathname === href || pathname.startsWith(`${href}/`)
}

const getStoredName = (value: StoreLike): string => {
  if (!value) return ''
  return String(value.name || '').trim()
}

const getStoredLogo = (value: StoreLike): string => {
  if (!value) return ''
  return resolveMediaUrl(value.logo_url || value.logo_path || value.logo) || ''
}

export default function Sidebar({ store = null }: SidebarProps) {
  const location = useLocation()
  const navigate = useNavigate()
  const [storeName, setStoreName] = useState('Mi tienda')
  const [logoUrl, setLogoUrl] = useState('')

  useEffect(() => {
    const readStoreData = () => {
      if (store) {
        setStoreName(getStoredName(store) || 'Mi tienda')
        setLogoUrl(getStoredLogo(store))
        return
      }

      try {
        const raw = localStorage.getItem('store')
        const parsed = raw ? JSON.parse(raw) : null
        setStoreName(getStoredName(parsed) || 'Mi tienda')
        setLogoUrl(getStoredLogo(parsed))
      } catch {
        setStoreName('Mi tienda')
        setLogoUrl('')
      }
    }

    const onStoreUpdated = (event: Event) => {
      const custom = event as CustomEvent<StoreLike>
      if (custom.detail) {
        setStoreName(getStoredName(custom.detail) || 'Mi tienda')
        setLogoUrl(getStoredLogo(custom.detail))
        return
      }
      readStoreData()
    }

    const onStorage = (event: StorageEvent) => {
      if (event.key === 'store') {
        readStoreData()
      }
    }

    readStoreData()
    window.addEventListener('store:updated', onStoreUpdated as EventListener)
    window.addEventListener('storage', onStorage)

    return () => {
      window.removeEventListener('store:updated', onStoreUpdated as EventListener)
      window.removeEventListener('storage', onStorage)
    }
  }, [store])

  const handleLogout = async () => {
    try {
      await API.post('/logout')
    } catch {
      // ignore backend logout errors
    } finally {
      clearSession()
      localStorage.removeItem('store')
      navigate('/login')
    }
  }

  return (
    <aside className="flex h-screen min-h-0 w-[205px] flex-shrink-0 flex-col border-r border-[#2A2F45] bg-[#171C2B]">
      {/* Cabecera sobre fondo solido: la portada de la tienda se muestra en la
          tienda publica, no detras del texto de una herramienta de trabajo. */}
      <div className="border-b border-[#2A2F45] px-4 pb-4 pt-5">
        <div className="flex items-start gap-2.5">
          <div className="flex h-10 w-10 flex-shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white/5">
            {logoUrl ? (
              <LogoImage
                src={logoUrl}
                alt={`Logo de ${storeName}`}
                className="h-full w-full rounded-none border-0 bg-transparent p-0.5"
                imageClassName="h-full w-full"
              />
            ) : (
              <Icon name="store" variant="fa" className="h-5 w-5 text-[#8C97B8]" />
            )}
          </div>
          <div className="min-w-0 pt-0.5">
            {/* El nombre es identidad: se permite una segunda linea antes que cortarlo. */}
            <p className="line-clamp-2 text-[14px] font-semibold leading-tight text-white">{storeName}</p>
            <p className="mt-1 text-[10px] font-medium uppercase tracking-[1px] text-[#8C97B8]">
              Panel de ventas
            </p>
          </div>
        </div>
      </div>

      <nav className="min-h-0 flex-1 overflow-y-auto py-2">
        {navGroups.map((group) => (
          <div key={group.label}>
            {group.label !== 'PRINCIPAL' && (
              <p className="px-[16px] pb-1.5 pt-3 text-[9px] font-extrabold uppercase tracking-[1.2px] text-[#B9C3E0]">
                {group.label}
              </p>
            )}

            {group.items.map((item) => {
              const active = isRouteActive(location.pathname, item.href)

              return (
                <Link
                  key={item.href}
                  to={item.href}
                  aria-current={active ? 'page' : undefined}
                  className={`relative mx-2 flex items-center gap-2.5 rounded-[10px] px-3 py-2.5 text-left text-[13px] transition-colors ${
                    active
                      ? 'bg-comercioplus-600/15 font-semibold text-white'
                      : 'font-medium text-[#B9C3E0] hover:bg-white/5 hover:text-white'
                  }`}
                  style={{ width: 'calc(100% - 16px)' }}
                >
                  {/* Barra de acento: marca el item activo sin agregar otro color. */}
                  {active && (
                    <span className="absolute left-0 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-r-full bg-comercioplus-600" />
                  )}
                  <span className={`flex h-[22px] w-[22px] flex-shrink-0 items-center justify-center ${active ? 'text-comercioplus-600' : 'text-current'}`}>
                    <Icon name={item.icon} variant="fa" className="h-[17px] w-[17px]" />
                  </span>
                  <span className="flex-1">{item.label}</span>
                </Link>
              )
            })}
          </div>
        ))}
      </nav>

      <div className="border-t border-[#2A2F45] p-2">
        <button
          type="button"
          onClick={handleLogout}
          className="mx-2 flex items-center gap-2.5 rounded-[10px] px-3 py-2.5 text-left text-[13px] font-medium text-[#B9C3E0] transition-colors hover:bg-rose-500/10 hover:text-rose-200"
          style={{ width: 'calc(100% - 16px)' }}
        >
          <span className="flex h-[22px] w-[22px] flex-shrink-0 items-center justify-center">
            <Icon name="logout" variant="fa" className="h-[17px] w-[17px]" />
          </span>
          <span className="flex-1">Cerrar sesión</span>
        </button>
      </div>
    </aside>
  )
}
