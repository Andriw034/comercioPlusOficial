type TabItem = {
  value: string
  label: string
}

type Props = {
  items: TabItem[]
  value: string
  onChange: (value: string) => void
  className?: string
}

export default function Tabs({ items, value, onChange, className = '' }: Props) {
  return (
    <div className={`inline-flex rounded-2xl border border-slate-200 bg-white p-1 dark:border-white/10 dark:bg-white/5 ${className}`.trim()}>
      {items.map((item) => {
        const active = item.value === value
        const classes = active
          ? 'bg-slate-900/5 text-slate-900 shadow-sm dark:bg-white/10 dark:text-white'
          : 'text-slate-600 hover:text-slate-900 dark:text-white/60 dark:hover:text-white'
        return (
          <button
            key={item.value}
            type="button"
            onClick={() => onChange(item.value)}
            className={`px-4 py-2 text-sm font-medium rounded-xl transition ${classes}`.trim()}
          >
            {item.label}
          </button>
        )
      })}
    </div>
  )
}
