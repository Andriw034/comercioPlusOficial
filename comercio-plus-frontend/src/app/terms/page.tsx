import GlassCard from '@/components/ui/GlassCard'

export default function Terms() {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-[30px] font-semibold leading-[1.12] text-slate-900 dark:text-white sm:text-[34px]">Terminos y condiciones</h1>
        <p className="text-[14px] text-slate-600 dark:text-white/70">
          Este es un texto base de ejemplo. Aqui se describen las reglas de uso de la plataforma.
        </p>
      </div>

      <GlassCard className="space-y-4 text-[14px] text-slate-700 dark:text-white/70">
        <p>- El comerciante es responsable de la informacion y los productos.</p>
        <p>- El cliente debe verificar datos antes de comprar.</p>
        <p>- ComercioPlus actua como intermediario tecnologico.</p>
      </GlassCard>
    </div>
  )
}