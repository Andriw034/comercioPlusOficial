import type { ButtonHTMLAttributes, FC, ReactNode } from 'react'
import type { ButtonSize, ButtonVariant } from '@/types'

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: ButtonVariant
  size?: ButtonSize
  fullWidth?: boolean
  icon?: ReactNode
  iconPosition?: 'left' | 'right'
  loading?: boolean
  children: ReactNode
}

const Button: FC<ButtonProps> = ({
  variant = 'primary',
  size = 'md',
  fullWidth = false,
  icon,
  iconPosition = 'left',
  loading = false,
  children,
  className = '',
  disabled,
  ...props
}) => {
  const baseStyles =
    'font-medium rounded-xl transition-all duration-300 ease-premium inline-flex items-center justify-center gap-2 relative overflow-hidden group disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:scale-100'

  const variantStyles: Record<ButtonVariant, string> = {
    primary:
      'bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-glow hover:shadow-glow-lg hover:-translate-y-0.5 hover:scale-[1.02] active:translate-y-0 active:scale-100 before:absolute before:inset-0 before:bg-gradient-to-br before:from-white/20 before:to-transparent before:opacity-0 before:transition-opacity before:duration-300 hover:before:opacity-100',
    secondary:
      'bg-slate-900 text-white shadow-premium hover:bg-slate-800 hover:-translate-y-0.5 hover:shadow-premium-lg active:translate-y-0',
    outline:
      'bg-transparent border-2 border-slate-300 text-slate-900 hover:border-brand-500 hover:text-brand-500 hover:bg-brand-50 active:bg-brand-100',
    ghost:
      'bg-transparent text-slate-700 hover:bg-slate-100 hover:text-slate-900 active:bg-slate-200',
    glass:
      'bg-white/10 backdrop-blur-md border border-white/30 text-white shadow-glass hover:bg-white/20 hover:border-white/50 hover:-translate-y-0.5 active:translate-y-0',
    danger:
      'bg-gradient-to-br from-danger to-rose-600 text-white shadow-premium hover:shadow-premium-lg hover:-translate-y-0.5 hover:scale-[1.02] active:translate-y-0 active:scale-100',
  }

  const sizeStyles: Record<ButtonSize, string> = {
    sm: 'px-4 py-2 text-body-sm',
    md: 'px-6 py-3 text-body',
    lg: 'px-8 py-4 text-body-lg',
    xl: 'px-10 py-5 text-h3',
  }

  return (
    <button
      className={`${baseStyles} ${variantStyles[variant]} ${sizeStyles[size]} ${fullWidth ? 'w-full' : ''} ${className}`}
      disabled={disabled || loading}
      {...props}
    >
      {loading ? (
        <svg className="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
      ) : null}
      {!loading && icon && iconPosition === 'left' ? <span>{icon}</span> : null}
      {children}
      {!loading && icon && iconPosition === 'right' ? <span>{icon}</span> : null}
    </button>
  )
}

export default Button
