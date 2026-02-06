import { useEffect, useMemo, useState } from 'react'
import { Link, Outlet, useLocation, useNavigate } from 'react-router-dom'
import API from '@/lib/api'
import { buttonVariants } from '@/components/ui/button'
import Badge from '@/components/ui/Badge'
import AppShell from './AppShell'

export default function PublicLayout() {
  const navigate = useNavigate()
  const location = useLocation()
  const [isLogged, setIsLogged] = useState(!!localStorage.getItem('token'))
  const [menuOpen, setMenuOpen] = useState(false)

  useEffect(() => {
    setIsLogged(!!localStorage.getItem('token'))
    setMenuOpen(false)
  }, [location.pathname])

  const logout = async () => {
    try {
      await API.post('/logout')
    } catch (error) {
      console.warn('Logout error', error)
    } finally {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      setIsLogged(false)
      navigate('/login')
    }
  }

  const navLinks = useMemo(
    () => [
      { label: 'Inicio', to: '/' },
      { label: 'Productos', to: '/products' },
      { label: 'Tiendas', to: '/stores' },
      { label: 'Cómo funciona', to: '/how-it-works' },
    ],
    [],
  )

  const header = (
    <header className="sticky top-0 z-30 px-4 pt-4">
      <nav className="mx-auto max-w-7xl glass rounded-2xl px-4 py-3 flex items-center justify-between gap-4">
        <Link to="/" className="flex items-center gap-3">
          <span className="flex h-10 w-10 items-center justify-center rounded-2xl bg-brand-500 text-base font-bold text-white shadow-soft">CP</span>
          <div className="leading-tight">
            <p className="font-semibold text-white">ComercioPlus</p>
            <p className="text-xs text-white/60">Repuestos y tiendas confiables</p>
          </div>
        </Link>

        <div className="hidden md:flex items-center gap-5 text-sm font-medium text-white/70">
          {navLinks.map((link) => (
            <Link key={link.to} className="hover:text-white" to={link.to}>
              {link.label}
            </Link>
          ))}
        </div>

        <div className="hidden md:flex items-center gap-3">
          {isLogged ? (
            <Link to="/dashboard" className={buttonVariants('ghost')}>Panel</Link>
          ) : (
            <Link to="/login" className={buttonVariants('ghost')}>Entrar</Link>
          )}
          {isLogged ? (
            <button onClick={logout} className={buttonVariants('secondary')}>Cerrar sesión</button>
          ) : (
            <Link to="/register" className={buttonVariants('primary')}>Vender en ComercioPlus</Link>
          )}
        </div>

        <button
          type="button"
          onClick={() => setMenuOpen((prev) => !prev)}
          className="md:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-white"
          aria-label="Abrir menú"
        >
          <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </nav>

      {menuOpen && (
        <div className="mt-3 mx-auto max-w-7xl glass rounded-2xl px-4 py-4 flex flex-col gap-4 text-sm text-white/80 md:hidden">
          {navLinks.map((link) => (
            <Link key={link.to} className="hover:text-white" to={link.to}>
              {link.label}
            </Link>
          ))}
          <div className="flex flex-col gap-2 pt-2 border-t border-white/10">
            {isLogged ? (
              <Link to="/dashboard" className={buttonVariants('ghost')}>Panel</Link>
            ) : (
              <Link to="/login" className={buttonVariants('ghost')}>Entrar</Link>
            )}
            {isLogged ? (
              <button onClick={logout} className={buttonVariants('secondary')}>Cerrar sesión</button>
            ) : (
              <Link to="/register" className={buttonVariants('primary')}>Vender en ComercioPlus</Link>
            )}
          </div>
        </div>
      )}
    </header>
  )

  const footer = (
    <footer className="border-t border-white/10 bg-white/5 backdrop-blur-xl">
      <div className="max-w-7xl mx-auto px-4 py-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 text-sm text-white/70">
        <div className="space-y-3">
          <div className="flex items-center gap-2 text-white">
            <span className="font-semibold">ComercioPlus</span>
          </div>
          <p>Plataforma de repuestos y tiendas confiables para motociclistas.</p>
        </div>
        <div className="space-y-3">
          <p className="text-white font-semibold">Explora</p>
          <div className="flex flex-col gap-2">
            <Link to="/products" className="hover:text-white">Productos</Link>
            <Link to="/stores" className="hover:text-white">Tiendas</Link>
            <Link to="/how-it-works" className="hover:text-white">Cómo funciona</Link>
          </div>
        </div>
        <div className="space-y-3">
          <p className="text-white font-semibold">Legal</p>
          <div className="flex flex-col gap-2">
            <Link to="/register" className="hover:text-white">Ser comerciante</Link>
            <Link to="/privacy" className="hover:text-white">Políticas de privacidad</Link>
            <Link to="/terms" className="hover:text-white">Términos y condiciones</Link>
          </div>
        </div>
        <div className="space-y-3">
          <p className="text-white font-semibold">Contacto</p>
          <div className="flex flex-col gap-2">
            <span>soporte@comercioplus.co</span>
            <span>WhatsApp: +57 300 000 0000</span>
            <span>Horario: 8:00 - 18:00</span>
            <Badge variant="brand">Atención rápida</Badge>
          </div>
        </div>
      </div>
      <div className="border-t border-white/10">
        <div className="max-w-7xl mx-auto px-4 py-4 text-xs text-white/50 flex flex-col sm:flex-row items-center justify-between gap-2">
          <span>© {new Date().getFullYear()} ComercioPlus. Todos los derechos reservados.</span>
          <span>Hecho para motociclistas.</span>
        </div>
      </div>
    </footer>
  )

  return (
    <AppShell header={header} footer={footer}>
      <Outlet />
    </AppShell>
  )
}
