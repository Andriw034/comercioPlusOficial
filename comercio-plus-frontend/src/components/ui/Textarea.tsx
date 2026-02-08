import { forwardRef, type TextareaHTMLAttributes } from 'react'

type Props = TextareaHTMLAttributes<HTMLTextAreaElement> & {
  label?: string
  hint?: string
  error?: string
  containerClassName?: string
}

const Textarea = forwardRef<HTMLTextAreaElement, Props>(
  ({ className = '', label, hint, error, containerClassName = '', ...props }, ref) => {
    const area = <textarea ref={ref} className={`textarea-dark ${className}`.trim()} {...props} />

    if (!label && !hint && !error) {
      return area
    }

    return (
      <label className={`block space-y-2 ${containerClassName}`.trim()}>
        {label && <span className="text-[13px] font-medium text-slate-700 dark:text-white/80">{label}</span>}
        {area}
        {hint && !error && <span className="text-xs text-slate-500 dark:text-white/50">{hint}</span>}
        {error && <span className="text-xs text-red-600 dark:text-red-300">{error}</span>}
      </label>
    )
  },
)

Textarea.displayName = 'Textarea'

export default Textarea
