import { useEffect, useRef, useState, type CSSProperties } from 'react'
import { Link, useLocation, useNavigate } from 'react-router-dom'

type AuthUser = {
  name?: string
  email?: string
}

const CATEGORIES = ['motos', 'repuestos', 'accesorios', 'herramientas', 'lubricantes', 'ofertas']

const SearchIcon = () => (
  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round">
    <circle cx="11" cy="11" r="8" />
    <line x1="21" y1="21" x2="16.65" y2="16.65" />
  </svg>
)

const ChevronDown = ({ size = 13 }: { size?: number }) => (
  <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
    <polyline points="6 9 12 15 18 9" />
  </svg>
)

const MenuIcon = () => (
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
    <line x1="3" y1="6" x2="21" y2="6" />
    <line x1="3" y1="12" x2="21" y2="12" />
    <line x1="3" y1="18" x2="21" y2="18" />
  </svg>
)

const XIcon = () => (
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
    <line x1="18" y1="6" x2="6" y2="18" />
    <line x1="6" y1="6" x2="18" y2="18" />
  </svg>
)

const LogoIcon = () => (
  <svg width="32" height="32" viewBox="0 0 38 38" fill="none" aria-hidden="true">
    <rect width="38" height="38" rx="9" fill="#FF6A00" />
    <path d="M11 16h16l-2 10H13L11 16z" fill="white" fillOpacity="0.95" />
    <path d="M15 16v-2a4 4 0 0 1 8 0v2" stroke="white" strokeWidth="2" strokeLinecap="round" fill="none" />
  </svg>
)

export default function Navbar() {
  const location = useLocation()
  const navigate = useNavigate()

  const [user, setUser] = useState<AuthUser | null>(null)
  const [hasToken, setHasToken] = useState(false)
  const [catOpen, setCatOpen] = useState(false)
  const [userOpen, setUserOpen] = useState(false)
  const [mobileOpen, setMobileOpen] = useState(false)
  const [searchVal, setSearchVal] = useState('')

  const catRef = useRef<HTMLDivElement | null>(null)
  const userRef = useRef<HTMLDivElement | null>(null)
  const searchInputRef = useRef<HTMLInputElement | null>(null)

  const isAuthenticated = hasToken

  useEffect(() => {
    const token = localStorage.getItem('token')
    const hasValidToken = Boolean(token && token.trim().length > 0)
    setHasToken(hasValidToken)

    if (!hasValidToken) {
      setUser(null)
      return
    }

    const raw = localStorage.getItem('user')
    if (!raw) {
      setUser({})
      return
    }

    try {
      setUser(JSON.parse(raw))
    } catch {
      setUser({})
    }
  }, [location.pathname])

  useEffect(() => {
    const onKey = (event: KeyboardEvent) => {
      if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault()
        searchInputRef.current?.focus()
      }

      if (event.key === 'Escape') {
        setCatOpen(false)
        setUserOpen(false)
        setMobileOpen(false)
      }
    }

    window.addEventListener('keydown', onKey)
    return () => window.removeEventListener('keydown', onKey)
  }, [])

  useEffect(() => {
    const onMouseDown = (event: MouseEvent) => {
      const target = event.target as Node
      if (catRef.current && !catRef.current.contains(target)) setCatOpen(false)
      if (userRef.current && !userRef.current.contains(target)) setUserOpen(false)
    }

    document.addEventListener('mousedown', onMouseDown)
    return () => document.removeEventListener('mousedown', onMouseDown)
  }, [])

  useEffect(() => {
    setMobileOpen(false)
    setCatOpen(false)
    setUserOpen(false)
  }, [location.pathname])

  const handleVender = () => {
    if (isAuthenticated) {
      navigate('/dashboard/store')
      return
    }

    navigate('/login?redirect=/dashboard/store')
  }

  const handleLogout = () => {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    setUser(null)
    navigate('/login')
  }

  const navLinkStyle = (active: boolean): CSSProperties => ({
    border: 'none',
    cursor: 'pointer',
    fontFamily: 'inherit',
    fontSize: '14px',
    fontWeight: 500,
    color: active ? '#FF6A00' : '#374151',
    padding: '6px 10px',
    borderRadius: '6px',
    textDecoration: 'none',
    display: 'flex',
    alignItems: 'center',
    gap: '4px',
    transition: 'color 0.15s, background 0.15s',
    whiteSpace: 'nowrap',
    background: active ? '#FFF7ED' : 'transparent',
  })

  return (
    <>
      <nav
        style={{
          position: 'sticky',
          top: 0,
          zIndex: 50,
          background: '#ffffff',
          borderBottom: '1px solid #f3f4f6',
          boxShadow: '0 1px 3px rgba(0,0,0,0.07)',
          fontFamily: 'Inter, system-ui, sans-serif',
        }}
      >
        <div
          style={{
            maxWidth: '1280px',
            margin: '0 auto',
            padding: '0 20px',
            height: '54px',
            display: 'flex',
            alignItems: 'center',
            gap: '12px',
          }}
        >
          <Link
            to="/"
            style={{
              display: 'flex',
              alignItems: 'center',
              gap: '10px',
              textDecoration: 'none',
              flexShrink: 0,
            }}
          >
            <LogoIcon />
            <div style={{ lineHeight: 1.2 }}>
              <div style={{ fontWeight: 700, fontSize: '16px', color: '#111827', letterSpacing: '-0.2px' }}>ComercioPlus</div>
              <div style={{ fontSize: '10.5px', color: '#9ca3af', fontWeight: 400 }}>Tu tienda en linea</div>
            </div>
          </Link>

          <div
            style={{
              flex: 1,
              maxWidth: '340px',
              position: 'relative',
              display: 'flex',
              alignItems: 'center',
            }}
          >
            <span
              style={{
                position: 'absolute',
                left: '12px',
                color: '#9ca3af',
                display: 'flex',
                pointerEvents: 'none',
              }}
            >
              <SearchIcon />
            </span>
            <input
              ref={searchInputRef}
              id="cp-search"
              type="text"
              placeholder="Buscar productos, tiendas..."
              value={searchVal}
              onChange={(event) => setSearchVal(event.target.value)}
              style={{
                width: '100%',
                height: '32px',
                paddingLeft: '36px',
                paddingRight: '60px',
                border: '1.5px solid #e5e7eb',
                borderRadius: '8px',
                fontSize: '13.5px',
                color: '#374151',
                background: '#f9fafb',
                outline: 'none',
                fontFamily: 'inherit',
              }}
            />
            <span
              style={{
                position: 'absolute',
                right: '10px',
                display: 'flex',
                gap: '2px',
                alignItems: 'center',
                pointerEvents: 'none',
              }}
            >
              <kbd
                style={{
                  fontSize: '10px',
                  color: '#9ca3af',
                  background: '#f3f4f6',
                  border: '1px solid #e5e7eb',
                  borderRadius: '4px',
                  padding: '1px 5px',
                  lineHeight: '16px',
                }}
              >
                Ctrl
              </kbd>
              <kbd
                style={{
                  fontSize: '10px',
                  color: '#9ca3af',
                  background: '#f3f4f6',
                  border: '1px solid #e5e7eb',
                  borderRadius: '4px',
                  padding: '1px 5px',
                  lineHeight: '16px',
                }}
              >
                K
              </kbd>
            </span>
          </div>

          <div
            style={{
              display: 'flex',
              alignItems: 'center',
              gap: '2px',
              marginLeft: 'auto',
            }}
            className="cp-desktop-nav"
          >
            <Link
              to="/stores"
              style={navLinkStyle(location.pathname === '/stores')}
              onMouseEnter={(event) => {
                event.currentTarget.style.color = '#FF6A00'
                event.currentTarget.style.background = '#fff7ed'
              }}
              onMouseLeave={(event) => {
                if (location.pathname !== '/stores') {
                  event.currentTarget.style.color = '#374151'
                  event.currentTarget.style.background = 'transparent'
                }
              }}
            >
              Tiendas
            </Link>

            <Link
              to="/products"
              style={navLinkStyle(location.pathname.startsWith('/products') || location.pathname.startsWith('/product'))}
              onMouseEnter={(event) => {
                event.currentTarget.style.color = '#FF6A00'
                event.currentTarget.style.background = '#fff7ed'
              }}
              onMouseLeave={(event) => {
                if (!location.pathname.startsWith('/products') && !location.pathname.startsWith('/product')) {
                  event.currentTarget.style.color = '#374151'
                  event.currentTarget.style.background = 'transparent'
                }
              }}
            >
              Productos
            </Link>

            <div ref={catRef} style={{ position: 'relative' }}>
              <button
                onClick={() => setCatOpen((prev) => !prev)}
                style={{
                  ...navLinkStyle(catOpen),
                  background: catOpen ? '#fff7ed' : 'transparent',
                }}
                onMouseEnter={(event) => {
                  event.currentTarget.style.color = '#FF6A00'
                  event.currentTarget.style.background = '#fff7ed'
                }}
                onMouseLeave={(event) => {
                  if (!catOpen) {
                    event.currentTarget.style.color = '#374151'
                    event.currentTarget.style.background = 'transparent'
                  }
                }}
              >
                Categorias
                <span style={{ transform: catOpen ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform 0.2s ease', color: '#9ca3af' }}>
                  <ChevronDown />
                </span>
              </button>

              {catOpen && (
                <div
                  style={{
                    position: 'absolute',
                    top: 'calc(100% + 8px)',
                    left: '50%',
                    transform: 'translateX(-50%)',
                    background: '#ffffff',
                    border: '1px solid #f3f4f6',
                    borderRadius: '10px',
                    boxShadow: '0 10px 30px rgba(0,0,0,0.12)',
                    minWidth: '190px',
                    padding: '6px',
                    zIndex: 60,
                  }}
                >
                  {CATEGORIES.map((category) => (
                    <Link
                      key={category}
                      to={`/products?category=${encodeURIComponent(category)}`}
                      style={{
                        display: 'block',
                        padding: '8px 14px',
                        borderRadius: '7px',
                        color: '#374151',
                        textDecoration: 'none',
                        fontSize: '13.5px',
                        fontWeight: 500,
                      }}
                      onClick={() => setCatOpen(false)}
                      onMouseEnter={(event) => {
                        event.currentTarget.style.background = '#fff7ed'
                        event.currentTarget.style.color = '#FF6A00'
                      }}
                      onMouseLeave={(event) => {
                        event.currentTarget.style.background = 'transparent'
                        event.currentTarget.style.color = '#374151'
                      }}
                    >
                      {category[0].toUpperCase() + category.slice(1)}
                    </Link>
                  ))}
                </div>
              )}
            </div>

            <div style={{ width: '1px', height: '20px', background: '#e5e7eb', margin: '0 6px' }} />

            <button
              onClick={handleVender}
              style={{
                background: '#FF6A00',
                color: '#ffffff',
                border: 'none',
                borderRadius: '8px',
                padding: '0 16px',
                height: '30px',
                fontSize: '13.5px',
                fontWeight: 600,
                cursor: 'pointer',
              }}
            >
              Vender
            </button>

            {!isAuthenticated && (
              <Link
                to="/login"
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  padding: '0 14px',
                  height: '30px',
                  borderRadius: '8px',
                  border: '1.5px solid #e5e7eb',
                  color: '#374151',
                  textDecoration: 'none',
                  fontSize: '13.5px',
                  fontWeight: 600,
                }}
              >
                Iniciar sesion
              </Link>
            )}

            {isAuthenticated && (
              <div ref={userRef} style={{ position: 'relative' }}>
                <button
                  onClick={() => setUserOpen((prev) => !prev)}
                  style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: '6px',
                    background: 'none',
                    border: 'none',
                    cursor: 'pointer',
                    padding: '4px 6px',
                    borderRadius: '8px',
                  }}
                >
                  <div
                    style={{
                      width: '32px',
                      height: '32px',
                      borderRadius: '50%',
                      background: '#FF6A00',
                      color: '#fff',
                      fontWeight: 700,
                      fontSize: '14px',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                    }}
                  >
                    {user?.name?.trim()?.charAt(0)?.toUpperCase() || 'U'}
                  </div>
                  <span style={{ color: '#9ca3af' }}>
                    <ChevronDown />
                  </span>
                </button>

                {userOpen && (
                  <div
                    style={{
                      position: 'absolute',
                      top: 'calc(100% + 8px)',
                      right: 0,
                      background: '#ffffff',
                      border: '1px solid #f3f4f6',
                      borderRadius: '10px',
                      boxShadow: '0 10px 30px rgba(0,0,0,0.12)',
                      minWidth: '220px',
                      padding: '6px',
                      zIndex: 60,
                    }}
                  >
                    {[{ label: 'Mi panel', href: '/dashboard' }, { label: 'Productos', href: '/dashboard/products' }, { label: 'Tienda', href: '/dashboard/store' }].map((item) => (
                      <Link
                        key={item.label}
                        to={item.href}
                        style={{
                          display: 'block',
                          padding: '8px 14px',
                          borderRadius: '7px',
                          color: '#374151',
                          textDecoration: 'none',
                          fontSize: '13.5px',
                          fontWeight: 500,
                        }}
                        onClick={() => setUserOpen(false)}
                        onMouseEnter={(event) => {
                          event.currentTarget.style.background = '#fff7ed'
                          event.currentTarget.style.color = '#FF6A00'
                        }}
                        onMouseLeave={(event) => {
                          event.currentTarget.style.background = 'transparent'
                          event.currentTarget.style.color = '#374151'
                        }}
                      >
                        {item.label}
                      </Link>
                    ))}
                    <div style={{ height: '1px', background: '#f3f4f6', margin: '4px 0' }} />
                    <button
                      onClick={handleLogout}
                      style={{
                        display: 'block',
                        width: '100%',
                        textAlign: 'left',
                        padding: '8px 14px',
                        borderRadius: '7px',
                        color: '#ef4444',
                        background: 'none',
                        border: 'none',
                        fontSize: '13.5px',
                        fontWeight: 500,
                        cursor: 'pointer',
                      }}
                    >
                      Cerrar sesion
                    </button>
                  </div>
                )}
              </div>
            )}
          </div>

          <button
            onClick={() => setMobileOpen((prev) => !prev)}
            aria-label={mobileOpen ? 'Cerrar menu' : 'Abrir menu'}
            className="cp-hamburger"
            style={{
              display: 'none',
              alignItems: 'center',
              justifyContent: 'center',
              width: '36px',
              height: '36px',
              borderRadius: '8px',
              border: '1.5px solid #e5e7eb',
              background: 'none',
              color: '#374151',
              cursor: 'pointer',
              flexShrink: 0,
            }}
          >
            {mobileOpen ? <XIcon /> : <MenuIcon />}
          </button>
        </div>

        <div
          style={{
            overflow: 'hidden',
            maxHeight: mobileOpen ? '420px' : '0',
            transition: 'max-height 0.3s cubic-bezier(0.4,0,0.2,1)',
            borderTop: mobileOpen ? '1px solid #f3f4f6' : 'none',
            background: '#ffffff',
          }}
        >
          <div style={{ padding: '12px 20px 20px', display: 'flex', flexDirection: 'column', gap: '2px' }}>
            {[{ label: 'Tiendas', href: '/stores' }, { label: 'Productos', href: '/products' }].map((item) => (
              <Link
                key={item.label}
                to={item.href}
                style={{
                  padding: '11px 14px',
                  borderRadius: '8px',
                  color: '#374151',
                  textDecoration: 'none',
                  fontSize: '14px',
                  fontWeight: 500,
                }}
              >
                {item.label}
              </Link>
            ))}
            <div style={{ height: '1px', background: '#f3f4f6', margin: '6px 0' }} />
            <button
              onClick={handleVender}
              style={{
                padding: '12px 14px',
                borderRadius: '8px',
                background: '#FF6A00',
                color: '#fff',
                border: 'none',
                fontSize: '14px',
                fontWeight: 600,
                cursor: 'pointer',
                textAlign: 'center',
              }}
            >
              Vender
            </button>
            {!isAuthenticated && (
              <Link
                to="/login"
                style={{
                  padding: '12px 14px',
                  borderRadius: '8px',
                  border: '1.5px solid #e5e7eb',
                  color: '#374151',
                  textDecoration: 'none',
                  fontSize: '14px',
                  fontWeight: 600,
                  textAlign: 'center',
                }}
              >
                Iniciar sesion
              </Link>
            )}
          </div>
        </div>
      </nav>

      <style>{`
        @media (max-width: 960px) {
          .cp-desktop-nav { display: none !important; }
          .cp-hamburger { display: flex !important; }
        }
        @media (min-width: 961px) {
          .cp-hamburger { display: none !important; }
        }
      `}</style>

    </>
  )
}

