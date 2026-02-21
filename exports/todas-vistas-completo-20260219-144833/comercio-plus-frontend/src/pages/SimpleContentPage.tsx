import { Link } from 'react-router-dom'
import { Icon } from '@/components/Icon'

interface SimpleContentPageProps {
  title: string
  description: string
}

export default function SimpleContentPage({ title, description }: SimpleContentPageProps) {
  return (
    <div className="min-h-screen bg-slate-50">
      <main className="mx-auto max-w-4xl px-6 py-16">
        <div className="mb-8">
          <Link
            to="/"
            className="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900"
          >
            <Icon name="arrow-left" size={16} />
            Volver al inicio
          </Link>
        </div>

        <section className="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
          <h1 className="mb-4 text-3xl font-bold text-slate-900">{title}</h1>
          <p className="text-base leading-relaxed text-slate-600">{description}</p>

          <div className="mt-8 rounded-xl bg-slate-50 p-4 text-sm text-slate-500">
            Esta pagina ya esta disponible y puede ampliarse con contenido definitivo cuando lo
            definas.
          </div>
        </section>
      </main>
    </div>
  )
}
