import { Link, Outlet } from 'react-router-dom'

export default function AuthLayout() {
  return (
    <div className="min-h-screen bg-mesh text-slate-50 relative flex items-center justify-center px-4 py-16 overflow-hidden">
      <div className="absolute inset-0 pointer-events-none">
        <div className="absolute left-10 top-16 w-64 h-64 bg-brand-500/15 blur-3xl rounded-full" />
        <div className="absolute right-8 bottom-10 w-72 h-72 bg-cyan-400/12 blur-3xl rounded-full" />
      </div>

      <div className="relative w-full max-w-lg glass rounded-3xl p-8 shadow-card">
        <div className="mb-6 flex items-center justify-between">
          <Link to="/" className="flex items-center gap-3">
            <span className="flex h-10 w-10 items-center justify-center rounded-2xl bg-brand-500 text-xl font-bold shadow-soft">CP</span>
            <div className="leading-tight">
              <p className="font-semibold text-white">ComercioPlus</p>
              <p className="text-xs text-muted">Regresa a comprar o vender</p>
            </div>
          </Link>
          <Link to="/" className="text-sm text-slate-200 hover:text-white">Volver al inicio</Link>
        </div>
        <Outlet />
      </div>
    </div>
  )
}
