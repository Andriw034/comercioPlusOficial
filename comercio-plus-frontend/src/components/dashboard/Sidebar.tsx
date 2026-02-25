import { useEffect, useState } from 'react'
import { Link, useLocation, useNavigate } from 'react-router-dom'
import { Icon, type IconName } from '@/components/Icon'
import { resolveMediaUrl } from '@/lib/format'
import API from '@/lib/api'
import { clearSession } from '@/services/auth-session'
import LogoImage from '@/ui/images/LogoImage'
import { getImageBrightness, getThemeClassesByBrightness, type ImageBrightness } from '@/utils/imageTheme'

type NavItem = {
  href: string
  label: string
  icon: IconName
  iconBg: string
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

const navGroups: NavGroup[] = [
  {
    label: 'PRINCIPAL',
    items: [
      { href: '/dashboard', icon: 'chart', iconBg: 'bg-sky-500/20', label: 'Dashboard' },
      { href: '/dashboard/products', icon: 'package', iconBg: 'bg-orange-500/20', label: 'Productos' },
      { href: '/dashboard/orders', icon: 'file-text', iconBg: 'bg-emerald-500/20', label: 'Pedidos' },
      { href: '/dashboard/customers', icon: 'users', iconBg: 'bg-violet-500/20', label: 'Clientes' },
    ],
  },
  {
    label: 'GESTION',
    items: [
      { href: '/dashboard/categories', icon: 'tag', iconBg: 'bg-amber-500/20', label: 'Categorias' },
      { href: '/dashboard/inventory', icon: 'package', iconBg: 'bg-cyan-500/20', label: 'Inventario' },
      { href: '/dashboard/inventory/receive', icon: 'camera', iconBg: 'bg-orange-500/20', label: 'Ingreso escaner' },
      { href: '/dashboard/reports', icon: 'trending', iconBg: 'bg-lime-500/20', label: 'Reportes' },
      { href: '/dashboard/settings', icon: 'settings', iconBg: 'bg-slate-500/30', label: 'Configuracion' },
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

const getStoredCover = (value: StoreLike): string => {
  if (!value) return ''
  return resolveMediaUrl(value.cover_url || value.cover_path || value.background_url || value.cover) || ''
}

export default function Sidebar({ store = null }: SidebarProps) {
  const location = useLocation()
  const navigate = useNavigate()
  const [storeName, setStoreName] = useState('Mi tienda')
  const [logoUrl, setLogoUrl] = useState('')
  const [coverUrl, setCoverUrl] = useState('')
  const [coverTheme, setCoverTheme] = useState<ImageBrightness>('dark')

  useEffect(() => {
    const readStoreData = () => {
      if (store) {
        setStoreName(getStoredName(store) || 'Mi tienda')
        setLogoUrl(getStoredLogo(store))
        setCoverUrl(getStoredCover(store))
        return
      }

      try {
        const raw = localStorage.getItem('store')
        const parsed = raw ? JSON.parse(raw) : null
        setStoreName(getStoredName(parsed) || 'Mi tienda')
        setLogoUrl(getStoredLogo(parsed))
        setCoverUrl(getStoredCover(parsed))
      } catch {
        setStoreName('Mi tienda')
        setLogoUrl('')
        setCoverUrl('')
      }
    }

    const onStoreUpdated = (event: Event) => {
      const custom = event as CustomEvent<StoreLike>
      if (custom.detail) {
        setStoreName(getStoredName(custom.detail) || 'Mi tienda')
        setLogoUrl(getStoredLogo(custom.detail))
        setCoverUrl(getStoredCover(custom.detail))
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

  useEffect(() => {
    if (!coverUrl) {
      setCoverTheme('dark')
      return
    }

    let mounted = true
    getImageBrightness(coverUrl).then((theme) => {
      if (!mounted) return
      setCoverTheme(theme)
    })

    return () => {
      mounted = false
    }
  }, [coverUrl])

  const themeClasses = getThemeClassesByBrightness(coverTheme)

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
      <div className="relative overflow-hidden border-b border-[#2A2F45] px-[16px] pb-[14px] pt-5">
        {coverUrl ? (
          <>
            <img src={coverUrl} alt="" className="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-25" />
            <div className={`pointer-events-none absolute inset-0 bg-gradient-to-b ${themeClasses.overlay}`} />
          </>
        ) : null}

        <div className="relative mb-2 flex items-center gap-2">
          <div className="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl border border-orange-400/40 bg-white/10 shadow-[0_8px_16px_rgba(0,0,0,0.25)]">
            {logoUrl ? (
              <LogoImage
                src={logoUrl}
                alt="Logo tienda"
                className="h-full w-full rounded-none border-0 bg-transparent p-1"
                imageClassName="h-full w-full"
              />
            ) : (
              <Icon name="store" size={18} className="text-orange-200" />
            )}
          </div>
          <div>
            <p className="max-w-[130px] truncate text-[15px] font-black leading-none tracking-tight text-[#FF8A3D]">{storeName}</p>
            <p className="mt-1 text-[9px] font-bold uppercase tracking-[1.2px] text-[#FFB37A]">Panel de ventas</p>
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
                  className={`mx-2 flex items-center gap-2.5 rounded-[10px] px-3 py-2.5 text-left text-[13px] transition-all ${
                    active
                      ? 'bg-orange-500/20 font-extrabold text-white shadow-[0_8px_20px_rgba(251,146,60,0.2)]'
                      : 'font-semibold text-[#E6ECFF] hover:bg-[#242B43] hover:text-white'
                  }`}
                  style={{ width: 'calc(100% - 16px)' }}
                >
                  <span className={`flex h-[22px] w-[22px] items-center justify-center rounded-md ${item.iconBg}`}>
                    <Icon name={item.icon} size={14} className={active ? 'text-white' : 'text-slate-100'} />
                  </span>
                  <span className="flex-1">{item.label}</span>
                  {active ? <span className="h-1.5 w-1.5 flex-shrink-0 rounded-full bg-orange-300" /> : null}
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
          className="mx-2 flex items-center gap-2.5 rounded-[10px] px-3 py-2.5 text-left text-[13px] font-semibold text-rose-100 transition-all hover:bg-rose-500/20 hover:text-white"
          style={{ width: 'calc(100% - 16px)' }}
        >
          <span className="flex h-[22px] w-[22px] items-center justify-center rounded-md bg-rose-500/20">
            <Icon name="logout" size={14} className="text-rose-100" />
          </span>
          <span className="flex-1">Cerrar sesion</span>
        </button>
      </div>
    </aside>
  )
}
