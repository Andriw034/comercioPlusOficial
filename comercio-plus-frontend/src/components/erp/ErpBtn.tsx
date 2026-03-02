import type { ButtonHTMLAttributes, ReactNode } from 'react'

type Variant = 'primary' | 'secondary' | 'danger' | 'ghost' | 'success'
type Size = 'sm' | 'md'

type ErpBtnProps = ButtonHTMLAttributes<HTMLButtonElement> & {
  variant?: Variant
  size?: Size
  icon?: ReactNode
  children: ReactNode
}

const STYLES: Record<Variant, { bg: string; color: string; border: string; hoverBg: string }> = {
  primary: { bg: '#FFA14F', color: '#FFF', border: 'transparent', hoverBg: '#e8893a' },
  secondary: { bg: '#FFFFFF', color: '#374151', border: 'rgba(0,0,0,0.12)', hoverBg: '#F9FAFB' },
  danger: { bg: 'rgba(239,68,68,0.1)', color: '#EF4444', border: 'rgba(239,68,68,0.2)', hoverBg: 'rgba(239,68,68,0.15)' },
  ghost: { bg: 'transparent', color: '#64748B', border: 'transparent', hoverBg: 'rgba(0,0,0,0.04)' },
  success: { bg: 'rgba(16,185,129,0.1)', color: '#10B981', border: 'rgba(16,185,129,0.2)', hoverBg: 'rgba(16,185,129,0.15)' },
}

const SIZES: Record<Size, string> = {
  sm: 'px-3 py-1.5 text-[12px] rounded-lg',
  md: 'px-4 py-2 text-[13px] rounded-xl',
}

export function ErpBtn({ variant = 'secondary', size = 'md', icon, children, className = '', style, ...rest }: ErpBtnProps) {
  const s = STYLES[variant]
  return (
    <button
      type="button"
      className={`inline-flex items-center gap-2 font-semibold transition-all disabled:cursor-not-allowed disabled:opacity-50 ${SIZES[size]} ${className}`}
      style={{ background: s.bg, color: s.color, border: `1px solid ${s.border}`, ...style }}
      onMouseEnter={(event) => {
        if (!rest.disabled) {
          event.currentTarget.style.background = s.hoverBg
        }
      }}
      onMouseLeave={(event) => {
        if (!rest.disabled) {
          event.currentTarget.style.background = s.bg
        }
      }}
      {...rest}
    >
      {icon ? <span>{icon}</span> : null}
      {children}
    </button>
  )
}
