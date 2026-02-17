import { useEffect, useState } from 'react'
import { Link, useLocation, useNavigate } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import { Icon } from '@/components/Icon'

interface Category {
  name: string
  slug: string
  subcategories?: string[]
}

const CATEGORIES: Category[] = [
  { 
    name: 'Motos', 
    slug: 'motos',
    subcategories: ['Deportivas', 'Custom', 'Touring', 'Scooter', 'Cross']
  },
  { 
    name: 'Repuestos', 
    slug: 'repuestos',
    subcategories: ['Frenos', 'Suspensión', 'Motor', 'Eléctrico', 'Carrocería']
  },
  { 
    name: 'Accesorios', 
    slug: 'accesorios',
    subcategories: ['Cascos', 'Ropa', 'Guantes', 'Botas', 'Maletas']
  },
  { name: 'Servicios', slug: 'servicios' },
  { name: 'Ofertas', slug: 'ofertas' },
]

export default function Navbar() {
  const navigate = useNavigate()
  const location = useLocation()
  const [user, setUser] = useState<{ name?: string; email?: string } | null>(null)
  const isAuthenticated = !!user
  const [isScrolled, setIsScrolled] = useState(false)
  const [showSearch, setShowSearch] = useState(false)
  const [showCategories, setShowCategories] = useState(false)
  const [showUserMenu, setShowUserMenu] = useState(false)
  const [searchQuery, setSearchQuery] = useState('')

  useEffect(() => {
    const userData = localStorage.getItem('user')
    if (!userData) {
      setUser(null)
      return
    }
    try {
      setUser(JSON.parse(userData))
    } catch {
      setUser(null)
    }
  }, [location.pathname])

  // Detectar scroll para efecto glass
  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 20)
    }
    window.addEventListener('scroll', handleScroll)
    return () => window.removeEventListener('scroll', handleScroll)
  }, [])

  const isActive = (path: string) => location.pathname === path
  const handleVenderClick = () => {
    if (!isAuthenticated) {
      navigate('/login?redirect=/store/create')
      return
    }
    navigate('/store/create')
  }

  return (
    <>
      <nav
        className={`fixed top-0 left-0 right-0 z-header transition-all duration-300 ${
          isScrolled
            ? 'bg-white/95 backdrop-blur-lg shadow-premium'
            : 'bg-white'
        }`}
      >
        <div className="mx-auto max-w-7xl px-6">
          <div className="flex h-20 items-center justify-between">
            {/* Logo */}
            <Link to="/" className="flex items-center gap-3">
              <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-comercioplus-600 to-comercioplus-700 shadow-lg">
                <Icon name="store" size={24} className="text-white" />
              </div>
              <div>
                <span className="block text-xl font-bold text-slate-900">ComercioPlus</span>
                <span className="block text-xs text-slate-500">Tu tienda en línea</span>
              </div>
            </Link>

            {/* Búsqueda central */}
            <div className="hidden flex-1 max-w-2xl mx-12 lg:block">
              <div className="relative">
                <Icon
                  name="search"
                  size={20}
                  className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
                />
                <input
                  type="text"
                  placeholder="Buscar productos, tiendas..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full rounded-xl border-2 border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-sm transition-all focus:border-comercioplus-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-comercioplus-500/10"
                />
                <kbd className="absolute right-4 top-1/2 -translate-y-1/2 rounded bg-slate-200 px-2 py-1 text-xs text-slate-600">
                  Ctrl+K
                </kbd>
              </div>
            </div>

            {/* Búsqueda móvil (icono) */}
            <button
              onClick={() => setShowSearch(true)}
              className="flex h-10 w-10 items-center justify-center rounded-lg text-slate-600 transition-colors hover:bg-slate-100 lg:hidden"
            >
              <Icon name="search" size={20} />
            </button>

            {/* Navegación */}
            <div className="hidden items-center gap-1 lg:flex">
              <Link
                to="/stores"
                className={`rounded-lg px-4 py-2 text-sm font-semibold transition-colors ${
                  isActive('/stores')
                    ? 'bg-comercioplus-50 text-comercioplus-600'
                    : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'
                }`}
              >
                Tiendas
              </Link>

              <Link
                to="/products"
                className={`rounded-lg px-4 py-2 text-sm font-semibold transition-colors ${
                  isActive('/products')
                    ? 'bg-comercioplus-50 text-comercioplus-600'
                    : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'
                }`}
              >
                Productos
              </Link>

              {/* Mega-menu Categorías */}
              <div className="relative">
                <button
                  onClick={() => setShowCategories(!showCategories)}
                  className="flex items-center gap-1 rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-100 hover:text-slate-900"
                >
                  Categorías
                  <Icon name="chevron-down" size={16} />
                </button>

                <AnimatePresence>
                  {showCategories && (
                    <>
                      {/* Overlay */}
                      <div
                        className="fixed inset-0 z-dropdown"
                        onClick={() => setShowCategories(false)}
                      />

                      {/* Mega Menu */}
                      <motion.div
                        initial={{ opacity: 0, y: -10 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -10 }}
                        transition={{ duration: 0.2 }}
                        className="absolute left-0 top-full z-dropdown mt-2 w-96 rounded-2xl border border-slate-200 bg-white p-6 shadow-premium-xl"
                      >
                        <div className="space-y-4">
                          {CATEGORIES.map((category) => (
                            <div key={category.slug}>
                              <Link
                                to={`/products?category=${encodeURIComponent(category.slug)}`}
                                className="mb-2 block text-sm font-bold text-slate-900 hover:text-comercioplus-600"
                                onClick={() => setShowCategories(false)}
                              >
                                {category.name}
                              </Link>
                              {category.subcategories && (
                                <div className="ml-3 space-y-1">
                                  {category.subcategories.map((sub) => (
                                    <Link
                                      key={sub}
                                      to={`/products?category=${encodeURIComponent(sub.toLowerCase())}`}
                                      className="block text-sm text-slate-600 hover:text-comercioplus-600"
                                      onClick={() => setShowCategories(false)}
                                    >
                                      {sub}
                                    </Link>
                                  ))}
                                </div>
                              )}
                            </div>
                          ))}
                        </div>
                      </motion.div>
                    </>
                  )}
                </AnimatePresence>
              </div>

              {/* CTA Vender */}
              <button
                onClick={handleVenderClick}
                className="ml-2 rounded-lg bg-comercioplus-600 px-4 py-2 text-sm font-semibold text-white transition-all hover:bg-comercioplus-700 hover:shadow-lg hover:shadow-comercioplus-600/25"
              >
                Vender
              </button>
            </div>

            {/* Actions */}
            <div className="flex items-center gap-2">
              {/* User/Auth */}
              {isAuthenticated ? (
                <div className="relative">
                  <button
                    onClick={() => setShowUserMenu(!showUserMenu)}
                    className="flex items-center gap-2 rounded-lg py-2 pl-2 pr-3 transition-colors hover:bg-slate-100"
                  >
                    <div className="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-comercioplus-600 to-comercioplus-700 text-sm font-bold text-white">
                      {(user?.name?.trim()?.charAt(0) || 'U').toUpperCase()}
                    </div>
                    <Icon name="chevron-down" size={16} className="text-slate-600" />
                  </button>

                  <AnimatePresence>
                    {showUserMenu && (
                      <>
                        <div
                          className="fixed inset-0 z-dropdown"
                          onClick={() => setShowUserMenu(false)}
                        />
                        <motion.div
                          initial={{ opacity: 0, y: -10 }}
                          animate={{ opacity: 1, y: 0 }}
                          exit={{ opacity: 0, y: -10 }}
                          className="absolute right-0 top-full z-dropdown mt-2 w-64 rounded-2xl border border-slate-200 bg-white p-2 shadow-premium-xl"
                        >
                          <div className="border-b border-slate-100 p-3">
                            <p className="font-semibold text-slate-900">{user?.name || 'Mi cuenta'}</p>
                            <p className="text-sm text-slate-500">{user?.email || 'Usuario registrado'}</p>
                          </div>
                          <div className="py-2">
                            <Link
                              to="/dashboard"
                              className="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50"
                            >
                              <Icon name="grid" size={18} />
                              Mi panel
                            </Link>
                            <Link
                              to="/dashboard/products"
                              className="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50"
                            >
                              <Icon name="package" size={18} />
                              Mis Pedidos
                            </Link>
                            <Link
                              to="/products"
                              className="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50"
                            >
                              <Icon name="heart" size={18} />
                              Favoritos
                            </Link>
                            <Link
                              to="/dashboard/store"
                              className="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50"
                            >
                              <Icon name="settings" size={18} />
                              Configuración
                            </Link>
                          </div>
                          <div className="border-t border-slate-100 pt-2">
                            <button
                              onClick={() => {
                                localStorage.removeItem('token')
                                localStorage.removeItem('user')
                                setUser(null)
                                navigate('/login')
                              }}
                              className="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-danger transition-colors hover:bg-danger/5"
                            >
                              <Icon name="logout" size={18} />
                              Cerrar sesión
                            </button>
                          </div>
                        </motion.div>
                      </>
                    )}
                  </AnimatePresence>
                </div>
              ) : (
                <div className="hidden items-center gap-2 lg:flex">
                  <Link
                    to="/login"
                    className="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-100"
                  >
                    Iniciar sesión
                  </Link>
                  <Link
                    to="/register"
                    className="rounded-lg border-2 border-comercioplus-600 px-4 py-2 text-sm font-semibold text-comercioplus-600 transition-all hover:bg-comercioplus-50"
                  >
                    Registrarse
                  </Link>
                </div>
              )}
            </div>
          </div>
        </div>
      </nav>

      {/* Modal de búsqueda móvil */}
      <AnimatePresence>
        {showSearch && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-modal bg-slate-900/50 backdrop-blur-sm lg:hidden"
            onClick={() => setShowSearch(false)}
          >
            <motion.div
              initial={{ y: -100 }}
              animate={{ y: 0 }}
              exit={{ y: -100 }}
              className="bg-white p-6"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="relative">
                <Icon
                  name="search"
                  size={20}
                  className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
                />
                <input
                  type="text"
                  placeholder="Buscar productos, tiendas..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  autoFocus
                  className="w-full rounded-xl border-2 border-slate-200 bg-slate-50 py-4 pl-12 pr-4 text-base"
                />
                <button
                  onClick={() => setShowSearch(false)}
                  className="absolute right-4 top-1/2 -translate-y-1/2"
                >
                  <Icon name="x" size={20} className="text-slate-400" />
                </button>
              </div>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* Spacer para que el contenido no quede detrás del navbar */}
      <div className="h-20" />
    </>
  )
}


