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
    <GlassCard className={`flex items-center gap-4 rounded-2xl ${className}`.trim()} {...props}>
      {icon && (
        <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900/5 text-slate-600 dark:bg-white/10 dark:text-white/80">
          {icon}
        </div>
      )}
      <div className="space-y-1">
        <p className="text-[12px] uppercase tracking-wide text-slate-500 dark:text-white/60">{label}</p>
        <p className="text-[20px] font-extrabold leading-none text-slate-900 dark:text-white sm:text-[22px]">{value}</p>
        {hint && <p className="text-[12px] text-slate-500 dark:text-white/60">{hint}</p>}
      </div>
    </GlassCard>
  )
}
