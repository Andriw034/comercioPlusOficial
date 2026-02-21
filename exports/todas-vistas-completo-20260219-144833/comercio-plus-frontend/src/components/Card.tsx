import type { FC, ReactNode } from 'react'
import type { CardPadding } from '@/types'

interface CardProps {
  children: ReactNode
  className?: string
  variant?: 'default' | 'glass' | 'elevated' | 'bordered' | 'premium'
  hoverable?: boolean
  padding?: CardPadding
  onClick?: () => void
  gradient?: boolean
}

const Card: FC<CardProps> = ({
  children,
  className = '',
  variant = 'default',
  hoverable = false,
  padding = 'md',
  onClick,
  gradient = false,
}) => {
  const baseStyles = 'rounded-2xl transition-all duration-300 ease-premium relative overflow-hidden'
  const variantStyles = {
    default: 'bg-white shadow-premium',
    glass: 'bg-white/60 backdrop-blur-xl border border-white/20 shadow-glass',
    elevated: 'bg-white shadow-premium-lg',
    bordered: 'bg-white border-2 border-slate-200',
    premium: 'bg-gradient-to-br from-white to-slate-50 shadow-premium-xl border border-slate-100',
  }
  const hoverStyles = hoverable
    ? 'cursor-pointer hover:-translate-y-1 hover:shadow-premium-xl hover:scale-[1.01] active:translate-y-0 active:scale-100'
    : ''

  const paddingStyles: Record<CardPadding, string> = {
    none: '',
    sm: 'p-4',
    md: 'p-6',
    lg: 'p-8',
    xl: 'p-10',
  }

  return (
    <div
      className={`${baseStyles} ${variantStyles[variant]} ${hoverStyles} ${paddingStyles[padding]} ${className}`}
      onClick={onClick}
    >
      {gradient ? (
        <div className="pointer-events-none absolute inset-0 bg-gradient-to-br from-brand-500/5 to-transparent" />
      ) : null}
      {children}
    </div>
  )
}

export default Card
