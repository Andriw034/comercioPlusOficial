import { Link } from 'react-router-dom'
import { Icon } from '@/components/Icon'
import { useState } from 'react'

export default function Footer() {
  const [email, setEmail] = useState('')
  const [isSubmitting, setIsSubmitting] = useState(false)

  const handleNewsletterSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsSubmitting(true)
    
    // TODO: Conectar con API
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    alert('¡Gracias por suscribirte!')
    setEmail('')
    setIsSubmitting(false)
  }

  return (
    <footer className="bg-slate-900">
      {/* Sección principal */}
      <div className="mx-auto max-w-7xl px-6 py-16">
        <div className="grid grid-cols-1 gap-12 lg:grid-cols-5">
          {/* Columna 1: Logo + Descripción + Redes */}
          <div className="lg:col-span-2">
            <Link to="/" className="mb-6 flex items-center gap-3">
              <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-comercioplus-600 to-comercioplus-700 shadow-lg">
                <Icon name="store" size={24} className="text-white" />
              </div>
              <div>
                <span className="block text-xl font-bold text-white">ComercioPlus</span>
                <span className="block text-xs text-slate-400">Tu tienda en línea</span>
              </div>
            </Link>

            <p className="mb-6 text-sm leading-relaxed text-slate-400">
              La plataforma líder para crear tu tienda en línea y vender productos en Colombia.
              Más de 1,000 comerciantes confían en nosotros para hacer crecer su negocio.
            </p>

            {/* Redes sociales */}
            <div className="mb-6">
              <p className="mb-3 text-sm font-semibold text-slate-300">Síguenos</p>
              <div className="flex gap-2">
                {[
                  { href: 'https://facebook.com', label: 'Facebook', icon: 'facebook' as const, hover: 'hover:bg-comercioplus-600' },
                  { href: 'https://instagram.com', label: 'Instagram', icon: 'instagram' as const, hover: 'hover:bg-gradient-to-br hover:from-purple-600 hover:to-pink-600' },
                  { href: 'https://twitter.com', label: 'Twitter', icon: 'twitter' as const, hover: 'hover:bg-sky-500' },
                  { href: 'https://linkedin.com', label: 'LinkedIn', icon: 'linkedin' as const, hover: 'hover:bg-blue-600' },
                  { href: 'https://youtube.com', label: 'YouTube', icon: 'youtube' as const, hover: 'hover:bg-red-600' },
                ].map((item) => (
                  <a
                    key={item.label}
                    href={item.href}
                    target="_blank"
                    rel="noopener noreferrer"
                    className={`flex h-10 w-10 items-center justify-center rounded-lg bg-slate-800 text-slate-400 transition-all hover:text-white ${item.hover}`}
                    aria-label={item.label}
                  >
                    <Icon name={item.icon} size={18} />
                  </a>
                ))}
              </div>
            </div>

            {/* Trust badges */}
            <div className="flex flex-wrap gap-2">
              <div className="flex items-center gap-2 rounded-lg bg-slate-800 px-3 py-2">
                <Icon name="shield" size={16} className="text-green-400" />
                <span className="text-xs font-semibold text-slate-300">SSL Seguro</span>
              </div>
              <div className="flex items-center gap-2 rounded-lg bg-slate-800 px-3 py-2">
                <Icon name="check" size={16} className="text-green-400" />
                <span className="text-xs font-semibold text-slate-300">Verificado</span>
              </div>
            </div>
          </div>

          {/* Columna 2: Empresa */}
          <div>
            <h3 className="mb-4 text-sm font-bold text-white">Empresa</h3>
            <ul className="space-y-3">
              <li>
                <Link
                  to="/about"
                  className="text-sm text-slate-400 transition-colors hover:text-comercioplus-500"
                >
                  Quiénes somos
                </Link>
              </li>
              <li>
                <Link
                  to="/team"
                  className="text-sm text-slate-400 transition-colors hover:text-comercioplus-500"
                >
                  Nuestro equipo
                </Link>
              </li>
              <li>
                <Link
                  to="/careers"
                  className="text-sm text-slate-400 transition-colors hover:text-comercioplus-500"
                >
                  Carreras
                </Link>
              </li>
              <li>
                <Link
                  to="/blog"
                  className="text-sm text-slate-400 transition-colors hover:text-comercioplus-500"
                >
                  Blog
                </Link>
              </li>
              <li>
                <Link
                  to="/press"
                  className="text-sm text-slate-400 transition-colors hover:text-comercioplus-500"
                >
                  Prensa
                </Link>
              </li>
            </ul>
          </div>

          {/* Columna 3: Soporte */}
          <div>
            <h3 className="mb-4 text-sm font-bold text-white">Soporte</h3>
            <ul className="space-y-3">
              <li>
                <Link
                  to="/help"
                  className="text-sm text-slate-400 transition-colors hover:text-comercioplus-500"
                >
                  Centro de ayuda
                </Link>
              </li>
              <li>
                <Link
                  to="/contact"
                  className="text-sm text-slate-400 transition-colors hover:text-comercioplus-500"
                >
                  Contacto
                </Link>
              </li>
              <li>
                <Link
                  to="/faq"
                  className="text-sm text-slate-400 transition-colors hover:text-comercioplus-500"
                >
                  Preguntas frecuentes
                </Link>
              </li>
              <li>
                <Link
                  to="/status"
                  className="text-sm text-slate-400 transition-colors hover:text-comercioplus-500"
                >
                  Estado del servicio
                </Link>
              </li>
              <li>
                <Link
                  to="/report"
                  className="text-sm text-slate-400 transition-colors hover:text-comercioplus-500"
                >
                  Reportar problema
                </Link>
              </li>
            </ul>
          </div>

          {/* Columna 4: Legal */}
          <div>
            <h3 className="mb-4 text-sm font-bold text-white">Legal</h3>
            <ul className="space-y-3">
              <li>
                <Link
                  to="/terms"
                  className="text-sm text-slate-400 transition-colors hover:text-comercioplus-500"
                >
                  Términos y condiciones
                </Link>
              </li>
              <li>
                <Link
                  to="/privacy"
                  className="text-sm text-slate-400 transition-colors hover:text-comercioplus-500"
                >
                  Política de privacidad
                </Link>
              </li>
              <li>
                <Link
                  to="/cookies"
                  className="text-sm text-slate-400 transition-colors hover:text-comercioplus-500"
                >
                  Política de cookies
                </Link>
              </li>
              <li>
                <Link
                  to="/returns"
                  className="text-sm text-slate-400 transition-colors hover:text-comercioplus-500"
                >
                  Devoluciones
                </Link>
              </li>
              <li>
                <Link
                  to="/warranty"
                  className="text-sm text-slate-400 transition-colors hover:text-comercioplus-500"
                >
                  Garantía
                </Link>
              </li>
            </ul>
          </div>
        </div>

        {/* Newsletter (columna completa) */}
        <div className="mt-12 rounded-2xl border border-slate-800 bg-slate-800/50 p-8">
          <div className="mx-auto max-w-xl text-center">
            <Icon name="mail" size={32} className="mx-auto mb-4 text-comercioplus-500" />
            <h3 className="mb-2 text-xl font-bold text-white">Suscríbete a nuestro boletín</h3>
            <p className="mb-6 text-sm text-slate-400">
              Recibe ofertas exclusivas, novedades y consejos para tu negocio
            </p>
            <form onSubmit={handleNewsletterSubmit} className="flex gap-3">
              <input
                type="email"
                placeholder="tu@email.com"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                className="flex-1 rounded-xl border-2 border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 focus:border-comercioplus-500 focus:outline-none focus:ring-4 focus:ring-comercioplus-500/20"
              />
              <button
                type="submit"
                disabled={isSubmitting}
                className="rounded-xl bg-comercioplus-600 px-6 py-3 text-sm font-semibold text-white transition-all hover:bg-comercioplus-700 disabled:opacity-50"
              >
                {isSubmitting ? 'Enviando...' : 'Suscribirse'}
              </button>
            </form>
          </div>
        </div>
      </div>

      {/* Métodos de pago + Apps */}
      <div className="border-t border-slate-800">
        <div className="mx-auto max-w-7xl px-6 py-8">
          <div className="flex flex-wrap items-center justify-between gap-6">
            {/* Métodos de pago */}
            <div>
              <p className="mb-3 text-xs font-semibold text-slate-400">Métodos de pago aceptados:</p>
              <div className="flex flex-wrap items-center gap-3">
                <div className="flex h-10 w-16 items-center justify-center rounded-lg bg-white px-2 text-blue-700">
                  <Icon name="credit-card" size={14} />
                  <span className="ml-1 text-xs font-bold">VISA</span>
                </div>
                <div className="flex h-10 w-16 items-center justify-center rounded-lg bg-white px-2 text-rose-600">
                  <Icon name="credit-card" size={14} />
                  <span className="ml-1 text-xs font-bold">MC</span>
                </div>
                <div className="flex h-10 items-center justify-center rounded-lg bg-white px-3">
                  <Icon name="bank" size={14} className="text-blue-600" />
                  <span className="ml-1 text-sm font-bold text-blue-600">PSE</span>
                </div>
                <div className="flex h-10 items-center justify-center rounded-lg bg-purple-600 px-3">
                  <Icon name="smartphone" size={14} className="text-white" />
                  <span className="ml-1 text-sm font-bold text-white">nequi</span>
                </div>
                <div className="flex h-10 items-center justify-center rounded-lg bg-yellow-400 px-3">
                  <Icon name="bank" size={14} className="text-slate-900" />
                  <span className="ml-1 text-xs font-bold text-slate-900">Bancolombia</span>
                </div>
                <div className="flex h-10 items-center justify-center rounded-lg bg-blue-500 px-3">
                  <Icon name="credit-card" size={14} className="text-white" />
                  <span className="ml-1 text-sm font-bold text-white">AMEX</span>
                </div>
              </div>
            </div>

            {/* Apps */}
            <div>
              <p className="mb-3 text-xs font-semibold text-slate-400">Descarga la app:</p>
              <div className="flex gap-3">
                <a
                  href="#"
                  className="flex items-center gap-2 rounded-lg bg-slate-800 px-4 py-2 transition-colors hover:bg-slate-700"
                >
                  <Icon name="smartphone" size={24} className="text-white" />
                  <div className="text-left">
                    <p className="text-xs text-slate-400">Disponible en</p>
                    <p className="text-sm font-semibold text-white">App Store</p>
                  </div>
                </a>
                <a
                  href="#"
                  className="flex items-center gap-2 rounded-lg bg-slate-800 px-4 py-2 transition-colors hover:bg-slate-700"
                >
                  <Icon name="download" size={24} className="text-white" />
                  <div className="text-left">
                    <p className="text-xs text-slate-400">Disponible en</p>
                    <p className="text-sm font-semibold text-white">Google Play</p>
                  </div>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Copyright */}
      <div className="border-t border-slate-800">
        <div className="mx-auto max-w-7xl px-6 py-6">
          <div className="flex flex-wrap items-center justify-between gap-4 text-sm text-slate-400">
            <p>© 2024 ComercioPlus. Todos los derechos reservados.</p>
            <div className="flex gap-6">
              <Link to="/sitemap" className="hover:text-comercioplus-500">
                Mapa del sitio
              </Link>
              <Link to="/accessibility" className="hover:text-comercioplus-500">
                Accesibilidad
              </Link>
            </div>
          </div>
        </div>
      </div>
    </footer>
  )
}

