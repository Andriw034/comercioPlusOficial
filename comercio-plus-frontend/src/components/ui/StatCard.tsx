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
    <GlassCard className={`flex items-center gap-4 rounded-xl ${className}`.trim()} {...props}>
      {icon && (
        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-[#F3F4F6] text-slate-600">
          {icon}
        </div>
      )}
      <div className="space-y-1">
        <p className="text-[12px] uppercase tracking-wide text-slate-500">{label}</p>
        <p className="text-[28px] font-bold leading-none text-[#1A1A2E]">{value}</p>
        {hint && <p className="text-[12px] text-slate-500">{hint}</p>}
      </div>
    </GlassCard>
  )
}
