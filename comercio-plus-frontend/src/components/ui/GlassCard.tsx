import type { HTMLAttributes, ReactNode } from 'react'

type Props = HTMLAttributes<HTMLDivElement> & {
  children: ReactNode
}

export default function GlassCard({ className = '', children, ...props }: Props) {
  return (
    <div
      className={`rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md shadow-glass p-5 sm:p-6 ${className}`.trim()}
      {...props}
    >
      {children}
    </div>
  )
}
