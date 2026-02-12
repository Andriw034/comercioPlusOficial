import type { ButtonHTMLAttributes, ReactNode } from 'react'

const variants = {
  primary:
    'border border-brand-500 bg-brand-500 text-white shadow-lg shadow-brand-500/30 hover:border-brand-600 hover:bg-brand-600',
  secondary:
    'border border-slate-200 bg-white text-slate-900 shadow-sm hover:border-slate-300 hover:bg-slate-50 dark:border-white/15 dark:bg-white/10 dark:text-white dark:hover:border-white/25 dark:hover:bg-white/15',
  outline:
    'border border-slate-300 bg-transparent text-slate-800 hover:border-slate-400 hover:bg-slate-900/5 dark:border-white/20 dark:text-slate-100 dark:hover:border-white/35 dark:hover:bg-white/10',
  ghost:
    'border border-transparent bg-transparent text-slate-700 hover:bg-slate-900/5 hover:text-slate-900 dark:text-slate-100 dark:hover:bg-white/10 dark:hover:text-white',
  danger:
    'border border-red-500 bg-red-500 text-white shadow-lg shadow-red-500/25 hover:border-red-600 hover:bg-red-600',
} as const

export type ButtonVariant = keyof typeof variants

const baseButtonClasses =
  'inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-[13px] font-semibold transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-transparent sm:text-[14px]'

type Props = ButtonHTMLAttributes<HTMLButtonElement> & {
  variant?: ButtonVariant
  loading?: boolean
  children: ReactNode
}

export const buttonVariants = (variant: ButtonVariant = 'primary', className = '') =>
  `${baseButtonClasses} ${variants[variant]} disabled:cursor-not-allowed disabled:opacity-50 ${className}`.trim()

export default function Button({
  variant = 'primary',
  loading = false,
  className = '',
  children,
  ...props
}: Props) {
  return (
    <button
      className={buttonVariants(variant, className)}
      disabled={loading || props.disabled}
      {...props}
    >
      {children}
    </button>
  )
}
