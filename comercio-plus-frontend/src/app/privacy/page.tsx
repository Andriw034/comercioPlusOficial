import GlassCard from '@/components/ui/GlassCard'

export default function Privacy() {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-semibold text-white">Políticas de privacidad</h1>
        <p className="text-sm text-white/60">
          Este es un texto base de ejemplo. Aquí se describe cómo ComercioPlus gestiona los datos personales.
        </p>
      </div>

      <GlassCard className="space-y-4 text-sm text-white/70">
        <p>• Solo usamos la información necesaria para operar la plataforma.</p>
        <p>• No vendemos tus datos a terceros.</p>
        <p>• Puedes solicitar la eliminación de tu cuenta cuando lo necesites.</p>
      </GlassCard>
    </div>
  )
}
