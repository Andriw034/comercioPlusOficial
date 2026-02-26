import type { ButtonHTMLAttributes } from 'react'

type SwitchSize = 'sm' | 'md'

export interface SwitchProps
  extends Omit<ButtonHTMLAttributes<HTMLButtonElement>, 'onChange' | 'role' | 'aria-checked'> {
  checked: boolean
  onCheckedChange: (next: boolean) => void
  size?: SwitchSize
}

const sizeClasses: Record<SwitchSize, { track: string; thumb: string; on: string }> = {
  sm: {
    track: 'h-5 w-9',
    thumb: 'h-4 w-4',
    on: 'translate-x-4',
  },
  md: {
    track: 'h-6 w-11',
    thumb: 'h-5 w-5',
    on: 'translate-x-5',
  },
}

export default function Switch({
  checked,
  onCheckedChange,
  size = 'md',
  className = '',
  disabled = false,
  ...props
}: SwitchProps) {
  const classes = sizeClasses[size]

  return (
    <button
      type="button"
      role="switch"
      aria-checked={checked}
      disabled={disabled}
      onClick={() => {
        if (!disabled) onCheckedChange(!checked)
      }}
      className={`relative inline-flex ${classes.track} items-center rounded-full p-0.5 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500/40 disabled:cursor-not-allowed disabled:opacity-60 ${
        checked ? 'bg-brand-500' : 'bg-slate-300 dark:bg-white/20'
      } ${className}`.trim()}
      {...props}
    >
      <span
        className={`inline-block ${classes.thumb} transform rounded-full bg-white shadow-sm transition-transform ${
          checked ? classes.on : 'translate-x-0'
        }`}
      />
    </button>
  )
}
