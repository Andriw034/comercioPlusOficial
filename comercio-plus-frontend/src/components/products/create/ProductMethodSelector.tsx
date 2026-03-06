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
  iconName: 'edit' | 'camera' | 'grid'
}> = [
  {
    id: 'keyboard',
    label: 'Escaner USB',
    description: 'Lector fisico',
    iconName: 'grid',
  },
  {
    id: 'camera',
    label: 'Camara',
    description: 'Escaneo movil',
    iconName: 'camera',
  },
  {
    id: 'manual',
    label: 'Manual',
    description: 'Escritura asistida',
    iconName: 'edit',
  },
]

export default function ProductMethodSelector({ active, onChange, disabled = false }: Props) {
  return (
    <div
      role="group"
      aria-label="Elige como ingresar el producto"
      className="rounded-[11px] border border-[#E2E8F0] bg-white p-1"
    >
      <div className="grid grid-cols-1 gap-1 sm:grid-cols-3">
      {METHODS.map((method) => {
        const isActive = active === method.id

        return (
          <button
            key={method.id}
            type="button"
            disabled={disabled}
            aria-pressed={isActive}
            onClick={() => onChange(method.id)}
            className={`relative flex items-center justify-center gap-2 rounded-[8px] border px-3 py-2.5 text-center transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-400 disabled:cursor-not-allowed disabled:opacity-50 ${
              isActive
                ? 'border-[#FF6A00] bg-[#FF6A00] text-white shadow-[0_3px_10px_rgba(255,106,0,0.28)]'
                : 'border-transparent bg-transparent text-[#64748B] hover:bg-[#F8FAFC]'
            }`}
          >
            <div
              className={`flex h-8 w-8 items-center justify-center rounded-lg transition-all duration-200 ${
                isActive
                  ? 'bg-white/20 text-white'
                  : 'bg-[#F1F5F9] text-[#94A3B8]'
              }`}
            >
              <Icon name={method.iconName} size={16} />
            </div>

            <span className={`text-[12px] font-bold leading-none ${isActive ? 'text-white' : 'text-[#475569]'}`}>
              {method.label}
            </span>
            <span className={`hidden text-[10px] leading-none sm:block ${isActive ? 'text-white/90' : 'text-[#94A3B8]'}`}>
              {method.description}
            </span>
          </button>
        )
      })}
      </div>
    </div>
  )
}
