import type { ButtonHTMLAttributes, FC, ReactNode } from 'react'
import type { ButtonSize, ButtonVariant } from '@/types'

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: ButtonVariant
  size?: ButtonSize
  fullWidth?: boolean
  children: ReactNode
}

const Button: FC<ButtonProps> = ({
  variant = 'primary',
  size = 'md',
  fullWidth = false,
  children,
  className = '',
  disabled,
  ...props
}) => {
  const baseStyles =
    'font-medium rounded-sm transition-all duration-300 ease-in-out inline-flex items-center justify-center gap-2'

  const variantStyles: Record<ButtonVariant, string> = {
    primary:
      'bg-primary text-white hover:bg-primary-dark hover:-translate-y-0.5 hover:shadow-primary disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0',
    secondary:
      'bg-secondary text-white hover:bg-secondary-dark hover:-translate-y-0.5 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed',
    outline:
      'bg-transparent border-2 border-dark-300 text-dark-800 hover:border-primary hover:text-primary disabled:opacity-50 disabled:cursor-not-allowed',
    danger:
      'bg-danger text-white hover:bg-danger-dark hover:-translate-y-0.5 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed',
  }

  const sizeStyles: Record<ButtonSize, string> = {
    sm: 'px-4 py-2 text-sm',
    md: 'px-6 py-3 text-body',
    lg: 'px-8 py-4 text-body-lg',
  }

  return (
    <button
      className={`${baseStyles} ${variantStyles[variant]} ${sizeStyles[size]} ${fullWidth ? 'w-full' : ''} ${className}`}
      disabled={disabled}
      {...props}
    >
      {children}
    </button>
  )
}

export default Button
