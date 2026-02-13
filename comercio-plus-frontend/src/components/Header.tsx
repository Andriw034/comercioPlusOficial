import type { FC } from 'react'
import { Link } from 'react-router-dom'
import Button from '@/components/Button'
import type { NavLink } from '@/types'

interface HeaderProps {
  links?: NavLink[]
  showAuth?: boolean
  cartCount?: number
}

const Header: FC<HeaderProps> = ({ links = [], showAuth = true, cartCount = 0 }) => {
  return (
    <header className="bg-white border-b border-dark-200 sticky top-0 z-50">
      <div className="max-w-7xl mx-auto px-6 lg:px-10">
        <div className="flex items-center justify-between h-20">
          <Link to="/" className="flex-shrink-0">
            <h1 className="text-2xl font-display font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
              ComercioPlus
            </h1>
          </Link>

          <nav className="hidden md:flex items-center gap-8">
            {links.map((link, index) => (
              <Link
                key={`${link.href}-${index}`}
                to={link.href}
                className={`text-body font-medium transition-colors duration-200 relative ${
                  link.active
                    ? 'text-primary after:absolute after:bottom-[-8px] after:left-0 after:right-0 after:h-0.5 after:bg-primary'
                    : 'text-dark-600 hover:text-primary'
                }`}
              >
                {link.label}
              </Link>
            ))}
          </nav>

          <div className="flex items-center gap-4">
            {cartCount > 0 && (
              <Link to="/cart" className="relative text-body text-dark-600 hover:text-primary transition-colors">
                🛒 Carrito
                <span className="absolute -top-2 -right-2 bg-primary text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                  {cartCount}
                </span>
              </Link>
            )}

            {showAuth && (
              <Link to="/login">
                <Button variant="outline" size="sm">
                  Iniciar Sesión
                </Button>
              </Link>
            )}
          </div>
        </div>
      </div>
    </header>
  )
}

export default Header
