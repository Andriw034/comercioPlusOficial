import type { FC, ReactNode } from 'react'
import type { BadgeVariant } from '@/types'

interface BadgeProps {
  children: ReactNode
  variant?: BadgeVariant
  className?: string
}

const Badge: FC<BadgeProps> = ({ children, variant = 'info', className = '' }) => {
  const baseStyles = 'inline-block px-3 py-1 rounded-full text-xs font-medium'
  const variantStyles: Record<BadgeVariant, string> = {
    success: 'bg-success/10 text-success',
    warning: 'bg-accent/10 text-accent',
    danger: 'bg-danger/10 text-danger',
    info: 'bg-secondary/10 text-secondary',
  }

  return (
    <span className={`${baseStyles} ${variantStyles[variant]} ${className}`}>
      {children}
    </span>
  )
}

export default Badge
