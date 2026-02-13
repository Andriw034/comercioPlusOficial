import { useEffect, useMemo, useState } from 'react'
import { Link, Outlet, useLocation, useNavigate } from 'react-router-dom'
import API from '@/lib/api'
import { buttonVariants } from '@/components/ui/button'
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
  const [publicStoreBrand, setPublicStoreBrand] = useState<PublicStoreBrand>(null)

  useEffect(() => {
    setIsLogged(!!localStorage.getItem('token'))
    setMenuOpen(false)
  }, [location.pathname])

  useEffect(() => {
    const handlePublicStoreChanged = (event: Event) => {
      const detail = (event as CustomEvent<PublicStoreBrand>).detail
      if (detail && detail.name) {
        setPublicStoreBrand({ name: detail.name, logoUrl: detail.logoUrl || null })
      } else {
        setPublicStoreBrand(null)
      }
    }

    window.addEventListener('publicStore:changed', handlePublicStoreChanged as EventListener)
    return () => window.removeEventListener('publicStore:changed', handlePublicStoreChanged as EventListener)
  }, [])

  const logout = async () => {
    try {
      await API.post('/logout')
    } catch {
      // ignore
    } finally {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      setIsLogged(false)
      navigate('/login')
    }
  }

  const navLinks = useMemo(
    () => [
      { label: 'Tiendas', to: '/' },
      { label: 'Productos', to: '/products' },
      { label: 'Categorias', to: '/category/1' },
    ],
    [],
  )

  const brandName = publicStoreBrand?.name?.trim() || 'ComercioPlus'

  const header = (
    <header className="sticky top-0 z-30 border-b border-[#E5E7EB] bg-white px-4 py-4">
      <nav className="mx-auto flex max-w-7xl items-center justify-between gap-4">
        <Link to="/" className="font-display text-[28px] font-bold leading-none">
          <span className="bg-gradient-to-br from-[#FF6B35] to-[#004E89] bg-clip-text text-transparent">{brandName}</span>
        </Link>

        <div className="hidden items-center gap-8 md:flex">
          {navLinks.map((link) => (
            <Link key={link.to} to={link.to} className="text-[15px] font-medium text-[#4B5563] hover:text-[#FF6B35]">
              {link.label}
            </Link>
          ))}

          {isLogged ? (
            <>
              <Link to="/dashboard/products" className={buttonVariants('outline')}>Panel</Link>
              <button onClick={logout} className={buttonVariants('ghost')}>Cerrar sesion</button>
            </>
          ) : (
            <>
              <Link to="/login" className={buttonVariants('outline')}>Iniciar sesion</Link>
              <Link to="/register" className={buttonVariants('primary')}>Vender</Link>
            </>
          )}
        </div>

        <button
          type="button"
          onClick={() => setMenuOpen((prev) => !prev)}
          className="inline-flex h-10 w-10 items-center justify-center rounded-lg border-2 border-[#E5E7EB] text-[#4B5563] md:hidden"
          aria-label="Abrir menu"
        >
          <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </nav>

      {menuOpen && (
        <div className="mx-auto mt-3 flex max-w-7xl flex-col gap-2 rounded-xl border border-[#E5E7EB] bg-white p-4 md:hidden">
          {navLinks.map((link) => (
            <Link key={link.to} to={link.to} className="text-[15px] font-medium text-[#4B5563]">
              {link.label}
            </Link>
          ))}
          <div className="mt-2 flex flex-col gap-2 border-t border-[#E5E7EB] pt-3">
            {isLogged ? (
              <>
                <Link to="/dashboard/products" className={buttonVariants('outline')}>Panel</Link>
                <button onClick={logout} className={buttonVariants('ghost')}>Cerrar sesion</button>
              </>
            ) : (
              <>
                <Link to="/login" className={buttonVariants('outline')}>Iniciar sesion</Link>
                <Link to="/register" className={buttonVariants('primary')}>Vender</Link>
              </>
            )}
          </div>
        </div>
      )}
    </header>
  )

  const footer = (
    <footer className="mt-12 border-t border-[#E5E7EB] bg-white">
      <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-6 text-[13px] text-[#4B5563]">
        <span>© {new Date().getFullYear()} ComercioPlus</span>
        <span>Diseño oficial</span>
      </div>
    </footer>
  )

  return (
    <AppShell header={header} footer={footer}>
      <Outlet />
    </AppShell>
  )
}
