import type { HTMLAttributes, ReactNode } from 'react'
import GlassCard from './GlassCard'

type Props = HTMLAttributes<HTMLDivElement> & {
  label: string
  value: string | number
  icon?: ReactNode
  hint?: string
}

export default function StatCard({ label, value, icon, hint, className = '', ...props }: Props) {
  return (
    <GlassCard className={`flex items-center gap-4 ${className}`.trim()} {...props}>
      {icon && (
        <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-white/80">
          {icon}
        </div>
      )}
      <div className="space-y-1">
        <p className="text-sm text-white/60">{label}</p>
        <p className="text-2xl font-semibold text-white">{value}</p>
        {hint && <p className="text-xs text-white/40">{hint}</p>}
      </div>
    </GlassCard>
  )
}
