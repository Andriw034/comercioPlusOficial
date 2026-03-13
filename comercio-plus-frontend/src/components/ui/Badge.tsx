import type { HTMLAttributes, ReactNode } from 'react'

type Props = HTMLAttributes<HTMLSpanElement> & {
  children: ReactNode
  variant?: 'brand' | 'neutral' | 'success' | 'warning' | 'danger'
}

const variants = {
  brand: 'border-brand-500/25 bg-brand-500/15 text-brand-700 dark:text-brand-200',
  neutral: 'border-slate-200 bg-slate-100 text-slate-700 dark:border-white/10 dark:bg-white/5 dark:text-white/80',
  success: 'border-green-500/25 bg-green-500/15 text-green-700 dark:text-green-200',
  warning: 'border-amber-500/25 bg-amber-500/15 text-amber-700 dark:text-amber-200',
  danger: 'border-red-500/25 bg-red-500/15 text-red-700 dark:text-red-200',
} as const

export default function Badge({ children, variant = 'neutral', className = '', ...props }: Props) {
  return (
    <span
      className={`inline-flex items-center rounded-full border px-3 py-1 text-[12px] font-semibold ${variants[variant]} ${className}`.trim()}
      {...props}
    >
      {children}
    </span>
  )
}
