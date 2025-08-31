
import Link from "next/link";
import { Home, Package, Settings, ShoppingCart } from "lucide-react";
import { Logo } from "../logo";

export function DashboardSidebar() {
  return (
    <div className="hidden border-r bg-background md:block">
      <div className="flex h-full max-h-screen flex-col gap-2">
        <div className="flex h-16 items-center border-b px-6">
          <Link href="/dashboard" className="flex items-center gap-2 font-semibold">
            <Logo />
          </Link>
        </div>
        <div className="flex-1">
          <nav className="grid items-start px-4 text-sm font-medium">
            <Link
              href="/dashboard"
              className="flex items-center gap-3 rounded-lg px-3 py-2 text-muted-foreground transition-all hover:text-primary hover:bg-primary/10"
            >
              <Home className="h-4 w-4" />
              Inicio
            </Link>
            <Link
              href="#"
              className="flex items-center gap-3 rounded-lg px-3 py-2 text-muted-foreground transition-all hover:text-primary hover:bg-primary/10"
            >
              <ShoppingCart className="h-4 w-4" />
              Órdenes
            </Link>
            <Link
              href="/dashboard/products"
              className="flex items-center gap-3 rounded-lg px-3 py-2 text-muted-foreground transition-all hover:text-primary hover:bg-primary/10"
            >
              <Package className="h-4 w-4" />
              Productos
            </Link>
            <Link
              href="/dashboard/settings/store"
              className="flex items-center gap-3 rounded-lg px-3 py-2 text-muted-foreground transition-all hover:text-primary hover:bg-primary/10"
            >
              <Settings className="h-4 w-4" />
              Ajustes
            </Link>
          </nav>
        </div>
      </div>
    </div>
  );
}
