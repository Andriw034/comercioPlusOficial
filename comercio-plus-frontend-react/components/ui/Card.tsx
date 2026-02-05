import type { HTMLAttributes, ReactNode } from 'react'
import GlassCard from './GlassCard'

type Props = HTMLAttributes<HTMLDivElement> & {
  children: ReactNode
}

export default function Card({ className = '', children, ...props }: Props) {
  return (
    <GlassCard className={className} {...props}>
      {children}
    </GlassCard>
  )
}
