import { Outlet } from 'react-router-dom'
import AppShell from './AppShell'

export default function AuthLayout() {
  return (
    <AppShell variant="auth" containerClassName="max-w-6xl">
      <div className="overflow-hidden rounded-2xl border border-[#E5E7EB] bg-white shadow-[0_4px_24px_rgba(0,0,0,0.08)]">
        <div className="grid min-h-[620px] grid-cols-1 lg:grid-cols-2">
          <div className="hidden flex-col justify-center bg-[linear-gradient(135deg,#004E89_0%,#FF6B35_100%)] p-14 lg:flex">
            <h2 className="font-display text-[42px] font-bold text-white">Bienvenido a ComercioPlus</h2>
            <p className="mt-4 text-[18px] leading-[1.8] text-white/90">
              Conecta tu negocio con miles de clientes. Gestiona tu tienda online de manera simple y efectiva.
            </p>
          </div>

          <div className="flex items-center p-8 sm:p-12">
            <div className="w-full">
              <Outlet />
            </div>
          </div>
        </div>
      </div>
    </AppShell>
  )
}
