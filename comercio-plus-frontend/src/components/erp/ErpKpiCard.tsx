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
        background: '#FFFFFF',
        border: '1px solid rgba(0,0,0,0.07)',
        boxShadow: '0 2px 8px rgba(0,0,0,0.04)',
      }}
    >
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
