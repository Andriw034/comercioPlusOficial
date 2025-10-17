
import Link from "next/link";
import { Logo } from "./logo";
import { AuthWidget } from "./auth-widget";

export function Header() {
  return (
    <header className="sticky top-0 z-50 w-full border-b bg-background/80 backdrop-blur-sm">
      <div className="container h-16 flex items-center justify-between">
        <div className="flex items-center gap-6">
          <Link href="/" className="flex items-center gap-2">
            <Logo />
          </Link>
        </div>

        <nav className="hidden md:flex items-center gap-4">
            <Link href="/dashboard" className="text-sm font-medium text-muted-foreground hover:text-primary transition-colors">Panel</Link>
            <Link href="/#stores" className="text-sm font-medium text-muted-foreground hover:text-primary transition-colors">Tiendas</Link>
        </nav>

        <div className="flex items-center">
          <AuthWidget />
        </div>
      </div>
    </header>
  );
}
