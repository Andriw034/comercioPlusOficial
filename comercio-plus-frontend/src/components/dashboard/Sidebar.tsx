import { useEffect, useMemo, useState } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { resolveMediaUrl } from '@/lib/format'

type NavItem = {
  href: string
  label: string
  emoji: string
  emojiBg: string
}

type NavGroup = {
  label: string
  items: NavItem[]
}

type StoreLike = {
  logo?: string
  logo_url?: string
  logo_path?: string
} | null

const navGroups: NavGroup[] = [
  {
    label: 'PRINCIPAL',
    items: [
      { href: '/dashboard', emoji: '📊', emojiBg: 'bg-sky-500/20', label: 'Dashboard' },
      { href: '/dashboard/products', emoji: '📦', emojiBg: 'bg-orange-500/20', label: 'Productos' },
      { href: '/dashboard/orders', emoji: '🧾', emojiBg: 'bg-emerald-500/20', label: 'Pedidos' },
      { href: '/dashboard/customers', emoji: '👥', emojiBg: 'bg-violet-500/20', label: 'Clientes' },
    ],
  },
  {
    label: 'GESTION',
    items: [
      { href: '/dashboard/categories', emoji: '🏷️', emojiBg: 'bg-amber-500/20', label: 'Categorias' },
      { href: '/dashboard/inventory', emoji: '📚', emojiBg: 'bg-cyan-500/20', label: 'Inventario' },
      { href: '/dashboard/reports', emoji: '📈', emojiBg: 'bg-lime-500/20', label: 'Reportes' },
      { href: '/dashboard/settings', emoji: '⚙️', emojiBg: 'bg-slate-500/30', label: 'Configuracion' },
    ],
  },
]

const isRouteActive = (pathname: string, href: string) => {
  if (href === '/dashboard') return pathname === href
  return pathname === href || pathname.startsWith(`${href}/`)
}

const getStoredLogo = (value: StoreLike): string => {
  if (!value) return ''
  return resolveMediaUrl(value.logo_url || value.logo_path || value.logo) || ''
}

export default function Sidebar() {
  const location = useLocation()
  const [logoUrl, setLogoUrl] = useState('')

  const fallbackIcon = useMemo(() => '🏍️', [])

  useEffect(() => {
    const readStoreLogo = () => {
      try {
        const raw = localStorage.getItem('store')
        const parsed = raw ? JSON.parse(raw) : null
        setLogoUrl(getStoredLogo(parsed))
      } catch {
        setLogoUrl('')
      }
    }

    const onStoreUpdated = (event: Event) => {
      const custom = event as CustomEvent<StoreLike>
      if (custom.detail) {
        setLogoUrl(getStoredLogo(custom.detail))
        return
      }
      readStoreLogo()
    }

    const onStorage = (event: StorageEvent) => {
      if (event.key === 'store') {
        readStoreLogo()
      }
    }

    readStoreLogo()
    window.addEventListener('store:updated', onStoreUpdated as EventListener)
    window.addEventListener('storage', onStorage)

    return () => {
      window.removeEventListener('store:updated', onStoreUpdated as EventListener)
      window.removeEventListener('storage', onStorage)
    }
  }, [])

  return (
    <aside className="flex min-h-screen w-[205px] flex-shrink-0 flex-col border-r border-[#2A2F45] bg-[#171C2B]">
      <div className="border-b border-[#2A2F45] px-[16px] pb-[14px] pt-5">
        <div className="mb-2 flex items-center gap-2">
          <div className="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl border border-orange-400/40 bg-white/10 shadow-[0_8px_16px_rgba(0,0,0,0.25)]">
            {logoUrl ? (
              <img src={logoUrl} alt="Logo tienda" className="h-full w-full object-contain p-1" />
            ) : (
              <span className="text-[20px] leading-none">{fallbackIcon}</span>
            )}
          </div>
          <div>
            <p className="text-[15px] font-black leading-none tracking-tight text-[#FF8A3D]">ComercioPlus</p>
            <p className="mt-1 text-[9px] font-bold uppercase tracking-[1.2px] text-[#FFB37A]">Panel de ventas</p>
          </div>
        </div>
      </div>

      <nav className="flex-1 overflow-y-auto py-2">
        {navGroups.map((group) => (
          <div key={group.label}>
            <p className="px-[16px] pb-1.5 pt-3 text-[9px] font-extrabold uppercase tracking-[1.2px] text-[#B9C3E0]">
              {group.label}
            </p>

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
                  <span className={`flex h-[22px] w-[22px] items-center justify-center rounded-md text-[14px] ${item.emojiBg}`}>
                    {item.emoji}
                  </span>
                  <span className="flex-1">{item.label}</span>
                  {active ? <span className="h-1.5 w-1.5 flex-shrink-0 rounded-full bg-orange-300" /> : null}
                </Link>
              )
            })}
          </div>
        ))}
      </nav>
    </aside>
  )
}
