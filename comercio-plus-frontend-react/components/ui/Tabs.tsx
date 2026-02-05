import type { ButtonHTMLAttributes } from 'react'

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
    <div className={`inline-flex rounded-2xl border border-white/10 bg-white/5 p-1 ${className}`.trim()}>
      {items.map((item) => {
        const active = item.value === value
        const classes = active
          ? 'bg-white/10 text-white shadow-sm'
          : 'text-white/60 hover:text-white'
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
