import { useEffect, useRef, useState, type CSSProperties } from 'react'
import { Link, useLocation, useNavigate } from 'react-router-dom'
import { Icon } from '@/components/Icon'
import { clearSession, getStoredToken, getStoredUserRaw } from '@/services/auth-session'

type AuthUser = {
  name?: string
  email?: string
}

const CATEGORIES = ['motos', 'repuestos', 'accesorios', 'herramientas', 'lubricantes', 'ofertas']

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
    const token = getStoredToken()
    const hasValidToken = Boolean(token && token.trim().length > 0)
    setHasToken(hasValidToken)

    if (!hasValidToken) {
      setUser(null)
      return
    }

    const raw = getStoredUserRaw()
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

  const handleLogout = () => {
    clearSession()
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
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: '32px', height: '32px', borderRadius: '9px', background: '#FF6A00' }}><Icon name="store" size={18} className="text-white" /></div>
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
              <Icon name="search" size={15} strokeWidth={2.2} />
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
                  <Icon name="chevron-down" size={13} strokeWidth={2.5} />
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

            {!isAuthenticated && (
              <Link
                to="/login"
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  padding: '0 16px',
                  height: '30px',
                  borderRadius: '8px',
                  border: 'none',
                  background: '#FF6A00',
                  color: '#ffffff',
                  textDecoration: 'none',
                  fontSize: '13.5px',
                  fontWeight: 600,
                  cursor: 'pointer',
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
                    <Icon name="chevron-down" size={13} strokeWidth={2.5} />
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
            {mobileOpen ? <Icon name="x" size={20} /> : <Icon name="menu" size={20} />}
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
            {!isAuthenticated && (
              <Link
                to="/login"
                style={{
                  padding: '12px 14px',
                  borderRadius: '8px',
                  border: 'none',
                  background: '#FF6A00',
                  color: '#ffffff',
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


