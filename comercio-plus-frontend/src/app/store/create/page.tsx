import { Link } from 'react-router-dom'
import AppShell from '@/components/layouts/AppShell'
import GlassCard from '@/components/ui/GlassCard'

export default function CreateStore() {
  return (
    <AppShell>
      <GlassCard className="mx-auto max-w-2xl space-y-4 p-6 text-center">
        <h1 className="text-h2 text-slate-900">Crear tienda</h1>
        <p className="text-body text-slate-600">
          Esta ruta es publica. Desde aqui puedes iniciar el proceso de creacion de tienda.
        </p>
        <div className="flex flex-wrap items-center justify-center gap-3">
          <Link
            to="/register"
            className="rounded-xl bg-comercioplus-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-comercioplus-700"
          >
            Registrarme y crear tienda
          </Link>
          <Link
            to="/login"
            className="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
          >
            Ya tengo cuenta
          </Link>
        </div>
      </GlassCard>
    </AppShell>
  )
}
