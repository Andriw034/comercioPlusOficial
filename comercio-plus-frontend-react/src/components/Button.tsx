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

export default function Button({
  variant = 'primary',
  loading = false,
  className = '',
  children,
  ...props
}: Props) {
  return (
    <button
      className={`${variants[variant]} ${className}`.trim()}
      disabled={loading || props.disabled}
      {...props}
    >
      {children}
    </button>
  )
}
