import { forwardRef, type SelectHTMLAttributes } from 'react'

type Props = SelectHTMLAttributes<HTMLSelectElement> & {
  label?: string
  hint?: string
  error?: string
  containerClassName?: string
}

const Select = forwardRef<HTMLSelectElement, Props>(
  ({ className = '', label, hint, error, containerClassName = '', ...props }, ref) => {
    const select = <select ref={ref} className={`select-dark ${className}`.trim()} {...props} />

    if (!label && !hint && !error) {
      return select
    }

    return (
      <label className={`block space-y-2 ${containerClassName}`.trim()}>
        {label && <span className="text-sm font-medium text-white/80">{label}</span>}
        {select}
        {hint && !error && <span className="text-xs text-white/50">{hint}</span>}
        {error && <span className="text-xs text-red-300">{error}</span>}
      </label>
    )
  },
)

Select.displayName = 'Select'

export default Select
