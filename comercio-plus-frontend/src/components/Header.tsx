import { useEffect, useState, type FC } from 'react'
import { Link } from 'react-router-dom'
import Button from '@/components/Button'
import { Icon } from '@/components/Icon'
import type { NavLink } from '@/types'

interface HeaderProps {
  links?: NavLink[]
  showAuth?: boolean
  cartCount?: number
  showCart?: boolean
  showBell?: boolean
  notificationsCount?: number
}

const Header: FC<HeaderProps> = ({
  links = [],
  showAuth = true,
  cartCount = 0,
  showCart = false,
  showBell = false,
  notificationsCount = 0,
}) => {
  const [isScrolled, setIsScrolled] = useState(false)

  useEffect(() => {
    const onScroll = () => setIsScrolled(window.scrollY > 10)
    window.addEventListener('scroll', onScroll)
    onScroll()
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  return (
    <header
      className={`sticky top-0 z-50 transition-all duration-300 ${
        isScrolled ? 'glass border-b border-white/40 shadow-premium' : 'border-b border-slate-200 bg-white'
      }`}
    >
      <div className="mx-auto max-w-7xl px-6 lg:px-10">
        <div className="flex h-20 items-center justify-between">
          <Link to="/" className="flex-shrink-0">
            <h1 className="bg-gradient-to-r from-brand-600 to-brand-500 bg-clip-text text-2xl font-display font-bold text-transparent">
              ComercioPlus
            </h1>
          </Link>

          <nav className="hidden items-center gap-8 md:flex">
            {links.map((link, index) => (
              <Link
                key={`${link.href}-${index}`}
                to={link.href}
                className={`relative text-body font-medium transition-colors duration-200 ${
                  link.active
                    ? 'text-brand-600 after:absolute after:bottom-[-8px] after:left-0 after:right-0 after:h-0.5 after:bg-brand-500'
                    : 'text-slate-600 hover:text-brand-600'
                }`}
              >
                {link.label}
              </Link>
            ))}
          </nav>

          <div className="flex items-center gap-4">
            {showBell ? (
              <button
                type="button"
                aria-label="Notificaciones"
                className="relative flex h-10 w-10 items-center justify-center rounded-lg text-slate-600 transition-colors hover:bg-slate-100 hover:text-brand-600"
              >
                <Icon name="bell" size={20} />
                {notificationsCount > 0 ? (
                  <span className="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-danger text-[10px] font-bold text-white">
                    {notificationsCount}
                  </span>
                ) : null}
              </button>
            ) : null}

            {showCart || cartCount > 0 ? (
              <Link
                to="/cart"
                className="relative flex h-10 w-10 items-center justify-center rounded-lg text-slate-600 transition-colors hover:bg-slate-100 hover:text-brand-600"
                aria-label="Carrito"
              >
                <Icon name="cart" size={20} />
                {cartCount > 0 ? (
                  <span className="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-brand-500 text-[10px] font-bold text-white">
                    {cartCount}
                  </span>
                ) : null}
              </Link>
            ) : null}

            {showAuth ? (
              <Link to="/login">
                <Button variant="outline" size="sm">
                  Iniciar sesión
                </Button>
              </Link>
            ) : null}
          </div>
        </div>
      </div>
    </header>
  )
}

export default Header
