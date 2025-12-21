import Link from 'next/link'
import { Logo } from './logo'

export function Footer() {
  return (
    <footer className="border-t bg-card">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8">
        <div className="py-8 text-sm text-muted-foreground flex flex-col sm:flex-row items-center justify-between gap-4">
          <div className="flex items-center gap-2">
            <Logo />
            <p>&copy; {new Date().getFullYear()} ComercioPlus. Todos los derechos reservados.</p>
          </div>
          <div className="flex gap-4">
            <Link href="/" className="hover:text-primary transition-colors">Términos</Link>
            <Link href="/" className="hover:text-primary transition-colors">Privacidad</Link>
            <Link href="/" className="hover:text-primary transition-colors">Contacto</Link>
          </div>
        </div>
      </div>
    </footer>
  )
}
