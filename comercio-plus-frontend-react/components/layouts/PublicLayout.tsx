import { useEffect, useState } from 'react'
import { Link, Outlet, useLocation, useNavigate } from 'react-router-dom'
import API from '@/lib/api'

export default function PublicLayout() {
  const navigate = useNavigate()
  const location = useLocation()
  const [isLogged, setIsLogged] = useState(!!localStorage.getItem('token'))

  useEffect(() => {
    setIsLogged(!!localStorage.getItem('token'))
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

  return (
    <div className="min-h-screen bg-mesh text-slate-50 relative overflow-hidden">
      <div className="absolute inset-0 pointer-events-none">
        <div className="absolute -left-24 top-10 w-72 h-72 bg-brand-500/20 blur-3xl rounded-full" />
        <div className="absolute right-0 top-32 w-80 h-80 bg-cyan-400/10 blur-3xl rounded-full" />
        <div className="absolute -right-10 bottom-10 w-64 h-64 bg-purple-500/10 blur-3xl rounded-full" />
      </div>

      <header className="sticky top-0 z-20 px-4 pt-4">
        <nav className="max-w-7xl mx-auto flex items-center justify-between gap-6 bg-white/5 border border-white/10 backdrop-blur-xl rounded-full px-5 py-3 shadow-soft">
          <Link to="/" className="flex items-center gap-3">
            <span className="flex h-10 w-10 items-center justify-center rounded-2xl bg-brand-500 text-xl font-bold shadow-soft">CP</span>
            <div className="leading-tight">
              <p className="font-semibold text-white">ComercioPlus</p>
              <p className="text-xs text-muted">Repuestos y tiendas confiables</p>
            </div>
          </Link>

          <div className="hidden md:flex items-center gap-6 text-sm font-medium text-slate-200">
            <Link className="hover:text-white" to="/">Inicio</Link>
            <Link className="hover:text-white" to="/products">Productos</Link>
            <Link className="hover:text-white" to="/stores">Tiendas</Link>
            <Link className="hover:text-white" to="/how-it-works">Cómo funciona</Link>
          </div>

          <div className="flex items-center gap-3">
            {isLogged ? (
              <Link to="/dashboard" className="btn-ghost">Panel</Link>
            ) : (
              <Link to="/login" className="btn-ghost">Entrar</Link>
            )}
            {isLogged ? (
              <button onClick={logout} className="btn-primary">Cerrar sesión</button>
            ) : (
              <Link to="/register" className="btn-primary">Vender en ComercioPlus</Link>
            )}
          </div>
        </nav>
      </header>

      <main className="relative z-10 pt-10 pb-16">
        <Outlet />
      </main>

      <footer className="relative z-10 border-t border-white/10 bg-white/5 backdrop-blur-xl">
        <div className="max-w-7xl mx-auto px-4 py-6 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-muted">
          <div className="flex items-center gap-2 text-slate-200">
            <span className="font-semibold text-white">ComercioPlus</span>
            <span className="text-muted">| Plataforma de repuestos y tiendas</span>
          </div>
          <div className="flex items-center gap-4">
            <Link to="/products" className="hover:text-white">Productos</Link>
            <Link to="/stores" className="hover:text-white">Tiendas</Link>
            <Link to="/register" className="hover:text-white">Ser comerciante</Link>
          </div>
        </div>
      </footer>
    </div>
  )
}

