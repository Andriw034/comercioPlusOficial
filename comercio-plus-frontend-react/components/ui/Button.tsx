import type { ButtonHTMLAttributes, ReactNode } from 'react'

const variants = {
  primary: 'btn-primary',
  secondary: 'btn-secondary',
  ghost: 'btn-ghost',
  danger: 'btn-danger',
} as const

export type ButtonVariant = keyof typeof variants

type Props = ButtonHTMLAttributes<HTMLButtonElement> & {
  variant?: ButtonVariant
  loading?: boolean
  children: ReactNode
}

export const buttonVariants = (variant: ButtonVariant = 'primary', className = '') =>
  `${variants[variant]} disabled:opacity-60 disabled:cursor-not-allowed ${className}`.trim()

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
