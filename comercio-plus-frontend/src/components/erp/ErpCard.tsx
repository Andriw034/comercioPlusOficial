import type { ReactNode } from 'react'

interface ErpCardProps {
  children: ReactNode
  className?: string
  padding?: 'sm' | 'md' | 'lg'
}

export function ErpCard({ children, className = '', padding = 'md' }: ErpCardProps) {
  const paddings = { sm: 'p-4', md: 'p-6', lg: 'p-8' }
  return (
    <div
      className={`rounded-2xl bg-white ${paddings[padding]} ${className}`}
      style={{
        boxShadow: '0 2px 12px 0 rgba(0,0,0,0.07), 0 1px 3px 0 rgba(0,0,0,0.05)',
        border: '1px solid #e5e7eb',
      }}
    >
      {children}
    </div>
  )
}
