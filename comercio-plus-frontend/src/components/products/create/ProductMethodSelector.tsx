import { Icon } from '@/components/Icon'

export type InputMethod = 'manual' | 'camera' | 'keyboard'

type Props = {
  active: InputMethod
  onChange: (method: InputMethod) => void
  disabled?: boolean
}

const METHODS: Array<{
  id: InputMethod
  label: string
  description: string
  badge?: string
  iconName: 'edit' | 'camera' | 'grid'
}> = [
  {
    id: 'manual',
    label: 'Manual',
    description: 'Escribe los datos',
    iconName: 'edit',
  },
  {
    id: 'camera',
    label: 'Camara',
    description: 'Escanea con el celular',
    badge: 'Rapido',
    iconName: 'camera',
  },
  {
    id: 'keyboard',
    label: 'Lector fisico',
    description: 'USB / Bluetooth',
    iconName: 'grid',
  },
]

export default function ProductMethodSelector({ active, onChange, disabled = false }: Props) {
  return (
    <div
      role="group"
      aria-label="Elige como ingresar el producto"
      className="grid grid-cols-3 gap-1.5 rounded-2xl border border-slate-200 bg-slate-100 p-1.5 dark:border-white/10 dark:bg-white/5"
    >
      {METHODS.map((method) => {
        const isActive = active === method.id

        return (
          <button
            key={method.id}
            type="button"
            disabled={disabled}
            aria-pressed={isActive}
            onClick={() => onChange(method.id)}
            className={`relative flex flex-col items-center gap-1.5 rounded-xl border px-2 py-3 text-center transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-400 disabled:cursor-not-allowed disabled:opacity-50 ${
              isActive
                ? 'border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-800'
                : 'border-transparent bg-transparent hover:bg-white/60 dark:hover:bg-white/5'
            }`}
          >
            {method.badge && (
              <span className="absolute -top-1.5 right-0 rounded-full bg-gradient-to-r from-[#FF6B35] to-[#E65A2B] px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white">
                {method.badge}
              </span>
            )}

            <div
              className={`flex h-8 w-8 items-center justify-center rounded-lg transition-all duration-200 ${
                isActive
                  ? 'bg-gradient-to-br from-[#FF6B35] to-[#E65A2B] text-white shadow-[0_3px_8px_rgba(255,107,53,0.35)]'
                  : 'bg-slate-200/70 text-slate-400 dark:bg-white/10 dark:text-slate-500'
              }`}
            >
              <Icon name={method.iconName} size={16} />
            </div>

            <span
              className={`text-[11px] leading-none ${
                isActive ? 'font-bold text-slate-900 dark:text-white' : 'font-semibold text-slate-500 dark:text-slate-500'
              }`}
            >
              {method.label}
            </span>

            <span
              className={`hidden text-[10px] leading-tight sm:block ${
                isActive ? 'text-slate-500 dark:text-slate-400' : 'text-slate-400 dark:text-slate-600'
              }`}
            >
              {method.description}
            </span>

            {isActive && <span className="absolute bottom-1.5 left-1/2 h-0.5 w-5 -translate-x-1/2 rounded-full bg-orange-500" />}
          </button>
        )
      })}
    </div>
  )
}

