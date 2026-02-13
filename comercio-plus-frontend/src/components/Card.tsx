import type { FC, ReactNode } from 'react'
import type { CardPadding } from '@/types'

interface CardProps {
  children: ReactNode
  className?: string
  hoverable?: boolean
  padding?: CardPadding
  onClick?: () => void
}

const Card: FC<CardProps> = ({
  children,
  className = '',
  hoverable = false,
  padding = 'md',
  onClick,
}) => {
  const baseStyles = 'bg-white rounded-md border border-dark-200'
  const hoverStyles = hoverable
    ? 'transition-all duration-300 cursor-pointer hover:-translate-y-2 hover:shadow-lg'
    : ''

  const paddingStyles: Record<CardPadding, string> = {
    none: '',
    sm: 'p-4',
    md: 'p-6',
    lg: 'p-8',
  }

  return (
    <div
      className={`${baseStyles} ${hoverStyles} ${paddingStyles[padding]} ${className}`}
      onClick={onClick}
    >
      {children}
    </div>
  )
}

export default Card
