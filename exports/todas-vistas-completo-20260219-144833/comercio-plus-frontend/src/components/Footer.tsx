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
                <a
                  href="https://facebook.com"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-800 text-slate-400 transition-all hover:bg-comercioplus-600 hover:text-white"
                  aria-label="Facebook"
                >
                  <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                  </svg>
                </a>
                <a
                  href="https://instagram.com"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-800 text-slate-400 transition-all hover:bg-gradient-to-br hover:from-purple-600 hover:to-pink-600 hover:text-white"
                  aria-label="Instagram"
                >
                  <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                  </svg>
                </a>
                <a
                  href="https://twitter.com"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-800 text-slate-400 transition-all hover:bg-sky-500 hover:text-white"
                  aria-label="Twitter"
                >
                  <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                  </svg>
                </a>
                <a
                  href="https://linkedin.com"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-800 text-slate-400 transition-all hover:bg-blue-600 hover:text-white"
                  aria-label="LinkedIn"
                >
                  <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                  </svg>
                </a>
                <a
                  href="https://youtube.com"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-800 text-slate-400 transition-all hover:bg-red-600 hover:text-white"
                  aria-label="YouTube"
                >
                  <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                  </svg>
                </a>
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
                <div className="flex h-10 w-16 items-center justify-center rounded-lg bg-white p-1.5">
                  <svg viewBox="0 0 48 32" className="h-full w-full">
                    <path fill="#1434CB" d="M18.5 11.7l-3.3 8.6h-2.4L11 13.9c-.1-.4-.3-.6-.5-.8-.5-.3-1.4-.6-2.2-.8l.1-.3h3.7c.5 0 .9.3 1 .8l.9 4.8 2.2-5.6h2.3zm9.2 5.8c0-2.3-3.2-2.4-3.2-3.4 0-.3.3-.6.9-.7.3-.1 1.2-.1 2.1.4l.4-1.8c-.5-.2-1.2-.4-2-.4-2.1 0-3.6 1.1-3.6 2.7 0 1.2 1.1 1.8 1.9 2.2.8.4 1.1.7 1.1 1 0 .5-.6.7-1.2.7-1 0-1.5-.1-2.3-.5l-.4 1.9c.5.2 1.5.4 2.5.4 2.3.1 3.8-1 3.8-2.5z" />
                  </svg>
                </div>
                <div className="flex h-10 w-16 items-center justify-center rounded-lg bg-white p-1.5">
                  <svg viewBox="0 0 48 32" className="h-full w-full">
                    <circle cx="15" cy="16" r="10" fill="#EB001B" />
                    <circle cx="25" cy="16" r="10" fill="#F79E1B" />
                    <path fill="#FF5F00" d="M20 7c-2.4 1.9-4 4.8-4 8s1.6 6.1 4 8c2.4-1.9 4-4.8 4-8s-1.6-6.1-4-8z" />
                  </svg>
                </div>
                <div className="flex h-10 items-center justify-center rounded-lg bg-white px-3">
                  <span className="text-sm font-bold text-blue-600">PSE</span>
                </div>
                <div className="flex h-10 items-center justify-center rounded-lg bg-purple-600 px-3">
                  <span className="text-sm font-bold text-white">nequi</span>
                </div>
                <div className="flex h-10 items-center justify-center rounded-lg bg-yellow-400 px-3">
                  <span className="text-xs font-bold text-slate-900">Bancolombia</span>
                </div>
                <div className="flex h-10 items-center justify-center rounded-lg bg-blue-500 px-3">
                  <span className="text-sm font-bold text-white">AMEX</span>
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
                  <svg className="h-6 w-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z" />
                  </svg>
                  <div className="text-left">
                    <p className="text-xs text-slate-400">Disponible en</p>
                    <p className="text-sm font-semibold text-white">App Store</p>
                  </div>
                </a>
                <a
                  href="#"
                  className="flex items-center gap-2 rounded-lg bg-slate-800 px-4 py-2 transition-colors hover:bg-slate-700"
                >
                  <svg className="h-6 w-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 20.5v-17c0-.59.34-1.11.84-1.35L13.69 12 3.84 21.85c-.5-.24-.84-.76-.84-1.35m13.81-5.38L6.05 21.34l8.49-8.49 2.27 2.27m3.35-4.31c.34.27.54.68.54 1.19 0 .51-.2.92-.54 1.19l-2.07 1.07-2.43-2.43 2.43-2.43 2.07 1.07M6.05 2.66l10.76 6.22-2.27 2.27-8.49-8.49z" />
                  </svg>
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

