import type { ButtonHTMLAttributes, ReactNode } from 'react'

const variants = {
  primary: 'btn-primary',
  secondary: 'btn-secondary',
  ghost: 'btn-ghost',
  danger: 'btn-danger',
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
