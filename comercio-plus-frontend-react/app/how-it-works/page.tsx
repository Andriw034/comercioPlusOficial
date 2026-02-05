import GlassCard from '@/components/ui/GlassCard'
import Badge from '@/components/ui/Badge'

export default function HowItWorks() {
  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-semibold text-white">Como funciona</h1>
        <p className="text-sm text-white/60">Compra repuestos y conecta con tiendas verificadas en minutos.</p>
      </div>

      <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        {[
          {
            title: 'Explora tiendas',
            description: 'Descubre comerciantes verificados y compara precios en un solo lugar.',
          },
          {
            title: 'Elige tus repuestos',
            description: 'Filtra por categoria, revisa stock y selecciona lo que necesitas.',
          },
          {
            title: 'Contacta y compra',
            description: 'Habla directo con la tienda, coordina el pago y recibe rapidamente.',
          },
        ].map((step, index) => (
          <GlassCard key={step.title} className="space-y-3">
            <Badge variant="brand">Paso {index + 1}</Badge>
            <h2 className="text-xl font-semibold text-white">{step.title}</h2>
            <p className="text-sm text-white/60">{step.description}</p>
          </GlassCard>
        ))}
      </div>
    </div>
  )
}
