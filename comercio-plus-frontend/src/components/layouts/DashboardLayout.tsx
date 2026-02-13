import { useEffect, useState } from 'react'
import { Link, Outlet, useLocation, useNavigate } from 'react-router-dom'
import API from '@/lib/api'
import AppShell from './AppShell'

const navItems = [
  { label: 'Productos', to: '/dashboard/products', icon: '📦' },
  { label: 'Clientes', to: '/dashboard/customers', icon: '👥' },
  { label: 'Configuracion', to: '/dashboard/store', icon: '⚙️' },
]

export default function DashboardLayout() {
  const navigate = useNavigate()
  const location = useLocation()
  const [storeName, setStoreName] = useState('ComercioPlus')

  useEffect(() => {
    const loadStore = async () => {
      try {
        const { data } = await API.get('/my/store')
        if (data?.name) setStoreName(data.name)
      } catch {
        setStoreName('ComercioPlus')
      }
    }

    loadStore()
  }, [])

  const logout = async () => {
    try {
      await API.post('/logout')
    } catch {
      // ignore
    } finally {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      navigate('/login')
    }
  }

  return (
    <AppShell variant="dashboard" containerClassName="max-w-none" mainClassName="px-0 py-0">
      <div className="grid min-h-[calc(100vh-32px)] grid-cols-1 lg:grid-cols-[260px_1fr]">
        <aside className="bg-[#1A1A2E] px-4 py-8">
          <div className="border-b border-white/10 px-2 pb-6">
            <h3 className="font-display text-[28px] font-bold text-white">ComercioPlus</h3>
            <p className="mt-2 text-[13px] text-white/70">{storeName}</p>
          </div>

          <nav className="mt-6 space-y-2">
            {navItems.map((item) => {
              const active = location.pathname.startsWith(item.to)
              return (
                <Link
                  key={item.to}
                  to={item.to}
                  className={`flex items-center gap-3 rounded-lg px-4 py-3 text-[15px] transition-all ${
                    active
                      ? 'bg-[#FF6B35] text-white'
                      : 'text-white/70 hover:bg-white/10 hover:text-white'
                  }`}
                >
                  <span className="text-[18px]">{item.icon}</span>
                  <span>{item.label}</span>
                </Link>
              )
            })}
          </nav>

          <button
            type="button"
            onClick={logout}
            className="mt-8 w-full rounded-lg border border-white/20 px-4 py-3 text-[14px] text-white/80 hover:bg-white/10"
          >
            Cerrar sesion
          </button>
        </aside>

        <section className="bg-[#F9FAFB] p-6 md:p-10">
          <Outlet />
        </section>
      </div>
    </AppShell>
  )
}
