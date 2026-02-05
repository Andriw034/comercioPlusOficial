import type { HTMLAttributes, ReactNode } from 'react'

type Props = HTMLAttributes<HTMLSpanElement> & {
  children: ReactNode
  variant?: 'brand' | 'neutral' | 'success' | 'warning' | 'danger'
}

const variants = {
  brand: 'bg-brand-500/15 text-brand-200 border-brand-500/20',
  neutral: 'bg-white/5 text-white/80 border-white/10',
  success: 'bg-green-500/15 text-green-200 border-green-500/20',
  warning: 'bg-amber-500/15 text-amber-200 border-amber-500/20',
  danger: 'bg-red-500/15 text-red-200 border-red-500/20',
} as const

export default function Badge({ children, variant = 'neutral', className = '', ...props }: Props) {
  return (
    <span
      className={`inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold ${variants[variant]} ${className}`.trim()}
      {...props}
    >
      {children}
    </span>
  )
}
