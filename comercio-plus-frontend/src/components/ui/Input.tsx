import { forwardRef, type InputHTMLAttributes, type ReactNode } from 'react'

type Props = InputHTMLAttributes<HTMLInputElement> & {
  label?: string
  hint?: string
  error?: string
  leftIcon?: ReactNode
  rightIcon?: ReactNode
  containerClassName?: string
}

const Input = forwardRef<HTMLInputElement, Props>(
  (
    { className = '', label, hint, error, leftIcon, rightIcon, containerClassName = '', ...props },
    ref,
  ) => {
    const input = leftIcon || rightIcon ? (
      <div className="relative">
        {leftIcon && (
          <span className="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
            {leftIcon}
          </span>
        )}
        <input
          ref={ref}
          className={`input-dark ${leftIcon ? 'pl-11' : ''} ${rightIcon ? 'pr-11' : ''} ${className}`.trim()}
          {...props}
        />
        {rightIcon && (
          <span className="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
            {rightIcon}
          </span>
        )}
      </div>
    ) : (
      <input ref={ref} className={`input-dark ${className}`.trim()} {...props} />
    )

    if (!label && !hint && !error && !leftIcon && !rightIcon) {
      return input
    }

    return (
      <label className={`block space-y-2 ${containerClassName}`.trim()}>
        {label && (
          <span className="text-[15px] font-semibold !text-slate-900" style={{ color: '#111827' }}>
            {label}
          </span>
        )}
        {input}
        {hint && !error && <span className="text-xs text-slate-500">{hint}</span>}
        {error && <span className="text-xs text-red-600">{error}</span>}
      </label>
    )
  },
)

Input.displayName = 'Input'

export default Input
