import GlassCard from '@/components/ui/GlassCard'

export default function Terms() {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-semibold text-white">Términos y condiciones</h1>
        <p className="text-sm text-white/60">
          Este es un texto base de ejemplo. Aquí se describen las reglas de uso de la plataforma.
        </p>
      </div>

      <GlassCard className="space-y-4 text-sm text-white/70">
        <p>• El comerciante es responsable de la información y los productos.</p>
        <p>• El cliente debe verificar datos antes de comprar.</p>
        <p>• ComercioPlus actúa como intermediario tecnológico.</p>
      </GlassCard>
    </div>
  )
}
