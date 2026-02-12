import { useEffect, useMemo, useState } from 'react'
import { Link, Outlet, useLocation, useNavigate } from 'react-router-dom'
import API from '@/lib/api'
import { buttonVariants } from '@/components/ui/button'
import Badge from '@/components/ui/Badge'
import ThemeToggle from '@/components/theme/ThemeToggle'
import AppShell from './AppShell'

type PublicStoreBrand = {
  name?: string
  logoUrl?: string | null
} | null

export default function PublicLayout() {
  const navigate = useNavigate()
  const location = useLocation()
  const [isLogged, setIsLogged] = useState(!!localStorage.getItem('token'))
  const [menuOpen, setMenuOpen] = useState(false)
  const [brandLogoError, setBrandLogoError] = useState(false)
  const [publicStoreBrand, setPublicStoreBrand] = useState<PublicStoreBrand>(null)

  useEffect(() => {
    setIsLogged(!!localStorage.getItem('token'))
    setMenuOpen(false)
  }, [location.pathname])

  useEffect(() => {
    const handlePublicStoreChanged = (event: Event) => {
      const detail = (event as CustomEvent<PublicStoreBrand>).detail

      if (detail && detail.name) {
        setPublicStoreBrand({
          name: detail.name,
          logoUrl: detail.logoUrl || null,
        })
      } else {
        setPublicStoreBrand(null)
      }

      setBrandLogoError(false)
    }

    window.addEventListener('publicStore:changed', handlePublicStoreChanged as EventListener)
    return () => {
      window.removeEventListener('publicStore:changed', handlePublicStoreChanged as EventListener)
    }
  }, [])

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

  const brandName = publicStoreBrand?.name?.trim() || 'ComercioPlus'
  const brandLogo = publicStoreBrand?.logoUrl || '/brand/logo-comercioplus.png'
  const brandSubtitle = publicStoreBrand ? 'Tienda verificada' : 'Repuestos y tiendas confiables'
  const brandInitial = brandName.charAt(0).toUpperCase() || 'C'

  const header = (
    <header className="sticky top-0 z-30 px-4 pt-4">
      <nav className="mx-auto flex max-w-7xl items-center justify-between gap-4 rounded-2xl glass px-4 py-3">
        <Link to="/" className="flex items-center gap-3">
          {!brandLogoError && brandLogo ? (
            <img
              src={brandLogo}
              alt={brandName}
              className="h-10 w-10 rounded-2xl border border-slate-200 bg-white object-cover shadow-sm sm:h-11 sm:w-11 dark:border-white/15 dark:bg-white/10"
              onError={() => setBrandLogoError(true)}
            />
          ) : (
            <span className="flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white text-base font-bold text-slate-900 shadow-sm sm:h-11 sm:w-11 dark:border-white/15 dark:bg-white/10 dark:text-white">
              {brandInitial}
            </span>
          )}

          <div className="leading-tight">
            <p className="text-[14px] font-semibold text-slate-900 dark:text-white">{brandName}</p>
            <p className="text-[12px] text-slate-600 dark:text-white/60">{brandSubtitle}</p>
          </div>
        </Link>

        <div className="hidden items-center gap-5 text-sm font-medium text-slate-700 dark:text-white/70 md:flex">
          {navLinks.map((link) => (
            <Link key={link.to} className="hover:text-slate-900 dark:hover:text-white" to={link.to}>
              {link.label}
            </Link>
          ))}
        </div>

        <div className="hidden items-center gap-3 md:flex">
          <ThemeToggle />

          {isLogged ? (
            <Link to="/dashboard" className={buttonVariants('ghost')}>
              Panel
            </Link>
          ) : (
            <Link to="/login" className={buttonVariants('ghost')}>
              Entrar
            </Link>
          )}

          {isLogged ? (
            <button onClick={logout} className={buttonVariants('secondary')}>
              Cerrar sesión
            </button>
          ) : (
            <Link to="/register" className={buttonVariants('primary')}>
              Vender en ComercioPlus
            </Link>
          )}
        </div>

        <div className="flex items-center gap-2 md:hidden">
          <ThemeToggle />
          <button
            type="button"
            onClick={() => setMenuOpen((prev) => !prev)}
            className="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white/80 text-slate-900 transition-colors hover:bg-slate-900/5 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:bg-white/10"
            aria-label="Abrir menú"
          >
            <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </nav>

      {menuOpen && (
        <div className="mx-auto mt-3 flex max-w-7xl flex-col gap-4 rounded-2xl glass px-4 py-4 text-sm text-slate-700 dark:text-white/80 md:hidden">
          {navLinks.map((link) => (
            <Link key={link.to} className="hover:text-slate-900 dark:hover:text-white" to={link.to}>
              {link.label}
            </Link>
          ))}

          <div className="flex flex-col gap-2 border-t border-slate-200 pt-2 dark:border-white/10">
            {isLogged ? (
              <Link to="/dashboard" className={buttonVariants('ghost')}>
                Panel
              </Link>
            ) : (
              <Link to="/login" className={buttonVariants('ghost')}>
                Entrar
              </Link>
            )}

            {isLogged ? (
              <button onClick={logout} className={buttonVariants('secondary')}>
                Cerrar sesión
              </button>
            ) : (
              <Link to="/register" className={buttonVariants('primary')}>
                Vender en ComercioPlus
              </Link>
            )}
          </div>
        </div>
      )}
    </header>
  )

  const footer = (
    <footer className="border-t border-slate-200/80 bg-white/70 backdrop-blur-xl dark:border-white/10 dark:bg-white/5">
      <div className="mx-auto grid max-w-7xl grid-cols-1 gap-8 px-4 py-10 text-sm text-slate-600 dark:text-white/70 sm:grid-cols-2 lg:grid-cols-4">
        <div className="space-y-3">
          <div className="flex items-center gap-2 text-slate-900 dark:text-white">
            <span className="font-semibold">ComercioPlus</span>
          </div>
          <p>Plataforma de repuestos y tiendas confiables para motociclistas.</p>
        </div>

        <div className="space-y-3">
          <p className="font-semibold text-slate-900 dark:text-white">Explora</p>
          <div className="flex flex-col gap-2">
            <Link to="/products" className="hover:text-slate-900 dark:hover:text-white">
              Productos
            </Link>
            <Link to="/stores" className="hover:text-slate-900 dark:hover:text-white">
              Tiendas
            </Link>
            <Link to="/how-it-works" className="hover:text-slate-900 dark:hover:text-white">
              Cómo funciona
            </Link>
          </div>
        </div>

        <div className="space-y-3">
          <p className="font-semibold text-slate-900 dark:text-white">Legal</p>
          <div className="flex flex-col gap-2">
            <Link to="/register" className="hover:text-slate-900 dark:hover:text-white">
              Ser comerciante
            </Link>
            <Link to="/privacy" className="hover:text-slate-900 dark:hover:text-white">
              Políticas de privacidad
            </Link>
            <Link to="/terms" className="hover:text-slate-900 dark:hover:text-white">
              Términos y condiciones
            </Link>
          </div>
        </div>

        <div className="space-y-3">
          <p className="font-semibold text-slate-900 dark:text-white">Contacto</p>
          <div className="flex flex-col gap-2">
            <span>soporte@comercioplus.co</span>
            <span>WhatsApp: +57 300 000 0000</span>
            <span>Horario: 8:00 - 18:00</span>
            <Badge variant="brand">Atención rápida</Badge>
          </div>
        </div>
      </div>

      <div className="border-t border-slate-200/80 dark:border-white/10">
        <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-4 text-xs text-slate-500 dark:text-white/50 sm:flex-row">
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
