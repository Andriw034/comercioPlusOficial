import CoverImage from '@/ui/images/CoverImage'
import LogoImage from '@/ui/images/LogoImage'

type StoreHeaderPreviewProps = {
  name?: string
  category?: string
  logoUrl?: string | null
  coverUrl?: string | null
  className?: string
  badgeLabel?: string
}

const placeholders = ['🪖', '🔄', '🧤', '⛓️']

export default function StoreHeaderPreview({
  name,
  category,
  logoUrl,
  coverUrl,
  className = '',
  badgeLabel = 'Vista previa de cabecera',
}: StoreHeaderPreviewProps) {
  const title = name?.trim() || 'Nombre de tu tienda'
  const subtitle = category?.trim() || 'Categoria · ★ 4.8'

  return (
    <div className={`overflow-hidden rounded-2xl border border-brand-200 bg-white shadow-premium ${className}`.trim()}>
      <div className="flex items-center gap-2 bg-brand-500 px-3 py-2">
        <span className="h-1.5 w-1.5 rounded-full bg-white/60" />
        <p className="text-[10px] font-bold uppercase tracking-[0.14em] text-white">{badgeLabel}</p>
      </div>

      <CoverImage
        src={coverUrl || ''}
        ratio="free"
        overlay
        overlayMode="header"
        className="h-[170px]"
      >
        <div className="flex h-full items-end p-4">
          <div className="flex items-center gap-3">
            <LogoImage
              src={logoUrl || ''}
              alt={`Logo de ${title}`}
              className="h-14 w-14 rounded-[14px] border-[2.5px] border-white/60 bg-white/10 p-0 shadow-lg"
              imageClassName="h-full w-full object-cover"
              fallbackClassName="rounded-[11px]"
            />
            <div>
              <h3 className="text-[17px] font-black leading-tight text-white [text-shadow:0_2px_8px_rgba(0,0,0,0.55)]">
                {title}
              </h3>
              <p className="text-[10px] text-white/85 [text-shadow:0_1px_4px_rgba(0,0,0,0.45)]">{subtitle}</p>
            </div>
          </div>
        </div>
      </CoverImage>

      <div className="bg-slate-50 px-4 py-3">
        <p className="mb-2 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400">Catalogo</p>
        <div className="grid grid-cols-4 gap-1.5">
          {placeholders.map((icon) => (
            <div
              key={icon}
              className="rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-center"
            >
              <p className="text-base">{icon}</p>
              <div className="mx-auto mt-1 h-1 w-full rounded bg-slate-100" />
              <div className="mx-auto mt-1 h-1 w-2/3 rounded bg-slate-100" />
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}
