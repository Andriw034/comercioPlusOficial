import type { FC, ReactNode } from 'react'
import type { BadgeVariant } from '@/types'

interface BadgeProps {
  children: ReactNode
  variant?: BadgeVariant
  className?: string
}

const Badge: FC<BadgeProps> = ({ children, variant = 'info', className = '' }) => {
  const baseStyles = 'inline-flex items-center rounded-full px-3 py-1 text-caption font-semibold'
  const variantStyles: Record<BadgeVariant, string> = {
    success: 'bg-success/15 text-success border border-success/30',
    warning: 'bg-warning/15 text-warning border border-warning/30',
    danger: 'bg-danger/15 text-danger border border-danger/30',
    info: 'bg-blue-500/15 text-blue-700 border border-blue-500/25',
    neutral: 'bg-slate-200 text-slate-700 border border-slate-300',
    brand: 'bg-brand-500/15 text-brand-700 border border-brand-500/25',
  }

  return (
    <span className={`${baseStyles} ${variantStyles[variant]} ${className}`}>
      {children}
    </span>
  )
}

export default Badge
