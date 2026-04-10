import type { UsageInfo } from '@/types/ai'

interface UsageCounterProps {
  usage: UsageInfo
  onUpgrade: () => void
}

export default function UsageCounter({ usage, onUpgrade }: UsageCounterProps) {
  const { used, limit, plan } = usage
  const remaining = limit - used

  let colorClasses: string
  if (remaining <= 0) {
    colorClasses = 'bg-red-100 text-red-700 border-red-200'
  } else if (remaining <= 2) {
    colorClasses = 'bg-amber-50 text-amber-700 border-amber-200'
  } else {
    colorClasses = 'bg-emerald-50 text-emerald-700 border-emerald-200'
  }

  return (
    <div className="flex items-center gap-2">
      <span className={`inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold ${colorClasses}`}>
        <span className="tabular-nums">{used}/{limit}</span>
        <span className="text-[10px] font-bold uppercase tracking-wide opacity-70">{plan}</span>
      </span>

      {remaining <= 0 && (
        <button
          onClick={onUpgrade}
          className="inline-flex items-center gap-1 rounded-full bg-comercioplus-600 px-3 py-1 text-xs font-semibold text-white transition-colors hover:bg-comercioplus-700"
        >
          Upgrade a PRO
        </button>
      )}
    </div>
  )
}
