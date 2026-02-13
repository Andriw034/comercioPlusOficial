import type { FC } from 'react'
import { Link, useLocation } from 'react-router-dom'
import type { SidebarItem } from '@/types'

interface SidebarProps {
  items?: SidebarItem[]
}

const defaultItems: SidebarItem[] = [
  { icon: '📦', label: 'Productos', href: '/dashboard/products' },
  { icon: '👥', label: 'Clientes', href: '/dashboard/customers' },
  { icon: '📊', label: 'Pedidos', href: '/dashboard/orders' },
  { icon: '⚙️', label: 'Configuración', href: '/dashboard/store' },
]

const Sidebar: FC<SidebarProps> = ({ items = defaultItems }) => {
  const location = useLocation()

  return (
    <aside className="w-64 bg-dark-900 min-h-screen">
      <div className="p-8 border-b border-white/10">
        <h3 className="text-2xl font-display font-bold text-white">
          ComercioPlus
        </h3>
      </div>

      <nav className="p-4">
        {items.map((item, index) => {
          const isActive = location.pathname === item.href

          return (
            <Link
              key={`${item.href}-${index}`}
              to={item.href}
              className={`flex items-center gap-3 px-4 py-3 rounded-sm mb-2 text-body transition-all duration-200 ${
                isActive
                  ? 'bg-primary text-white'
                  : 'text-white/70 hover:bg-white/10 hover:text-white'
              }`}
            >
              <span className="text-xl">{item.icon}</span>
              <span className="font-medium">{item.label}</span>
            </Link>
          )
        })}
      </nav>
    </aside>
  )
}

export default Sidebar
