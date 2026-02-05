import { Link, Outlet } from 'react-router-dom'

export default function DashboardLayout() {
  return (
    <div className="min-h-screen bg-ink text-slate-50 relative">
      <div className="absolute inset-0 pointer-events-none">
        <div className="absolute -left-10 top-10 w-72 h-72 bg-brand-500/15 blur-3xl rounded-full" />
        <div className="absolute right-0 top-32 w-64 h-64 bg-cyan-400/10 blur-3xl rounded-full" />
      </div>

      <header className="sticky top-0 z-20 border-b border-white/5 bg-panel/80 backdrop-blur-xl">
        <div className="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
          <Link to="/" className="flex items-center gap-3">
            <span className="flex h-10 w-10 items-center justify-center rounded-2xl bg-brand-500 text-xl font-bold">CP</span>
            <div className="leading-tight">
              <p className="font-semibold text-white">ComercioPlus</p>
              <p className="text-xs text-muted">Panel del comerciante</p>
            </div>
          </Link>
          <div className="flex items-center gap-3 text-sm">
            <Link to="/stores" className="btn-ghost hidden sm:inline-flex">Ver tiendas</Link>
            <Link to="/products" className="btn-ghost hidden sm:inline-flex">Productos</Link>
          </div>
        </div>
      </header>

      <main className="relative z-10 max-w-7xl mx-auto px-4 py-10">
        <Outlet />
      </main>
    </div>
  )
}
