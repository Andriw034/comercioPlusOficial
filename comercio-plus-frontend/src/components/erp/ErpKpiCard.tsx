import EmojiIcon from '@/components/ui/EmojiIcon'
import type { IconName } from '@/components/Icon'

type Trend = {
  value: string
  up: boolean
}

type ErpKpiCardProps = {
  label: string
  value: string | number
  hint?: string
  icon: IconName
  iconBg: string
  iconColor: string
  trend?: Trend
}

export function ErpKpiCard({ label, value, hint, icon, iconBg, iconColor, trend }: ErpKpiCardProps) {
  return (
    <div
      className="relative flex flex-col justify-between overflow-hidden rounded-2xl p-5"
      style={{
        background: 'linear-gradient(160deg,#FFFFFF 0%,#F8FAFC 58%,#EEF4FF 100%)',
        border: '1px solid rgba(148,163,184,0.26)',
        boxShadow: '0 16px 34px rgba(15,23,42,0.10), inset 0 1px 0 rgba(255,255,255,0.95)',
      }}
    >
      <div className="pointer-events-none absolute inset-0 rounded-2xl bg-[radial-gradient(115%_75%_at_0%_0%,rgba(255,255,255,0.9)_0%,rgba(255,255,255,0)_65%)]" />
      <div className="mb-3 flex items-start justify-between">
        <span className="text-[12px] font-semibold text-slate-500">{label}</span>
        <div
          className="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl"
          style={{ background: iconBg, color: iconColor }}
        >
          <EmojiIcon name={icon} size={16} />
        </div>
      </div>

      <div>
        <p className="text-[26px] font-black leading-none text-slate-900">{value}</p>
        {hint ? <p className="mt-1 text-[11px] text-slate-400">{hint}</p> : null}
        {trend ? (
          <p className="mt-2 text-[11px] font-semibold" style={{ color: trend.up ? '#10B981' : '#EF4444' }}>
            {trend.up ? 'up' : 'down'} {trend.value}
          </p>
        ) : null}
      </div>
    </div>
  )
}
