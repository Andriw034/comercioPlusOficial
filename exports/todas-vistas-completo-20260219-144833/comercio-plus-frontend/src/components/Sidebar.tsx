import type { FC } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { Icon, type IconName } from '@/components/Icon'
import type { SidebarItem } from '@/types'

interface SidebarProps {
  items?: SidebarItem[]
}

const defaultItems: SidebarItem[] = [
  { icon: 'package', label: 'Productos', href: '/dashboard/products' },
  { icon: 'users', label: 'Clientes', href: '/dashboard/customers' },
  { icon: 'chart', label: 'Pedidos', href: '/dashboard/orders' },
  { icon: 'settings', label: 'Configuracion', href: '/dashboard/store' },
]

const emojiToIconMap: Record<string, IconName> = {
  '📦': 'package',
  '👥': 'users',
  '📊': 'chart',
  '⚙️': 'settings',
  '⚙': 'settings',
}

function resolveSidebarIcon(icon: string): IconName {
  if (emojiToIconMap[icon]) return emojiToIconMap[icon]
  if (icon === 'package' || icon === 'users' || icon === 'chart' || icon === 'settings') {
    return icon
  }
  return 'grid'
}

const Sidebar: FC<SidebarProps> = ({ items = defaultItems }) => {
  const location = useLocation()

  return (
    <aside className="min-h-screen w-64 bg-dark-900">
      <div className="border-b border-white/10 p-8">
        <h3 className="font-display text-2xl font-bold text-white">ComercioPlus</h3>
      </div>

      <nav className="p-4">
        {items.map((item, index) => {
          const isActive = location.pathname === item.href

          return (
            <Link
              key={`${item.href}-${index}`}
              to={item.href}
              className={`mb-2 flex items-center gap-3 rounded-sm px-4 py-3 text-body transition-all duration-200 ${
                isActive
                  ? 'bg-primary text-white'
                  : 'text-white/70 hover:bg-white/10 hover:text-white'
              }`}
            >
              <Icon name={resolveSidebarIcon(item.icon)} size={20} />
              <span className="font-medium">{item.label}</span>
            </Link>
          )
        })}
      </nav>
    </aside>
  )
}

export default Sidebar
