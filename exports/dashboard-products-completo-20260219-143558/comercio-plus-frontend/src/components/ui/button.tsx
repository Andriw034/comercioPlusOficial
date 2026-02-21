import type { ButtonHTMLAttributes, ReactNode } from 'react'

const variants = {
  primary:
    'border border-transparent bg-[#FF6B35] text-white hover:-translate-y-[2px] hover:bg-[#E65A2B] hover:shadow-[0_8px_20px_rgba(255,107,53,0.3)]',
  secondary:
    'border border-transparent bg-[#004E89] text-white hover:bg-[#003f6f]',
  outline:
    'border-2 border-[#D1D5DB] bg-transparent text-[#1F2937] hover:border-[#FF6B35] hover:text-[#FF6B35]',
  ghost:
    'border border-transparent bg-transparent text-[#4B5563] hover:bg-[#F3F4F6] hover:text-[#1F2937]',
  danger:
    'border border-transparent bg-[#EF476F] text-white hover:bg-[#d83f65]',
} as const

export type ButtonVariant = keyof typeof variants

const baseButtonClasses =
  'inline-flex items-center justify-center gap-2 rounded-[8px] px-6 py-3 text-[15px] font-medium transition-all duration-300 ease-in-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B35]/30'

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
