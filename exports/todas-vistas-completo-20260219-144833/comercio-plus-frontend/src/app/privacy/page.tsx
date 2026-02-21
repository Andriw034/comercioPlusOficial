import GlassCard from '@/components/ui/GlassCard'

export default function Privacy() {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-[30px] font-semibold leading-[1.12] text-slate-900 dark:text-white sm:text-[34px]">Politicas de privacidad</h1>
        <p className="text-[14px] text-slate-600 dark:text-white/70">
          Este es un texto base de ejemplo. Aqui se describe como ComercioPlus gestiona los datos personales.
        </p>
      </div>

      <GlassCard className="space-y-4 text-[14px] text-slate-700 dark:text-white/70">
        <p>- Solo usamos la informacion necesaria para operar la plataforma.</p>
        <p>- No vendemos tus datos a terceros.</p>
        <p>- Puedes solicitar la eliminacion de tu cuenta cuando lo necesites.</p>
      </GlassCard>
    </div>
  )
}