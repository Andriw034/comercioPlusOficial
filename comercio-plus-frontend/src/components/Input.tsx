import { useState, type FC, type InputHTMLAttributes, type ReactNode } from 'react'

interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
  label?: string
  error?: string
  success?: string
  hint?: string
  fullWidth?: boolean
  icon?: ReactNode
  iconPosition?: 'left' | 'right'
}

const Input: FC<InputProps> = ({
  label,
  error,
  success,
  hint,
  fullWidth = false,
  icon,
  iconPosition = 'left',
  className = '',
  onFocus,
  onBlur,
  ...props
}) => {
  const [isFocused, setIsFocused] = useState(false)

  const baseStyles = 'px-4 py-3.5 border-2 rounded-xl text-body font-sans transition-all duration-300 ease-premium bg-white'
  const focusStyles = `focus:outline-none ${
    error
      ? 'focus:border-danger focus:ring-4 focus:ring-danger/10'
      : success
        ? 'focus:border-success focus:ring-4 focus:ring-success/10'
        : 'focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10'
  }`
  const stateStyles = error
    ? 'border-danger'
    : success
      ? 'border-success'
      : isFocused
        ? 'border-brand-500'
        : 'border-slate-200'
  const iconPaddingStyles = icon ? (iconPosition === 'left' ? 'pl-12' : 'pr-12') : ''

  return (
    <div className={fullWidth ? 'w-full' : ''}>
      {label && (
        <label className="mb-2 block text-body-sm font-semibold text-slate-900">
          {label}
        </label>
      )}
      <div className="relative">
        {icon && iconPosition === 'left' ? (
          <div className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">{icon}</div>
        ) : null}
        <input
          className={`${baseStyles} ${focusStyles} ${stateStyles} ${fullWidth ? 'w-full' : ''} ${iconPaddingStyles} ${className}`}
          onFocus={(event) => {
            setIsFocused(true)
            onFocus?.(event)
          }}
          onBlur={(event) => {
            setIsFocused(false)
            onBlur?.(event)
          }}
          {...props}
        />
        {icon && iconPosition === 'right' ? (
          <div className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">{icon}</div>
        ) : null}
      </div>
      {!error && !success && hint ? <p className="mt-2 text-caption text-slate-600">{hint}</p> : null}
      {error ? <p className="mt-2 text-caption text-danger">{error}</p> : null}
      {success ? <p className="mt-2 text-caption text-success">{success}</p> : null}
    </div>
  )
}

export default Input
