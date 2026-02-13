import type { FC, InputHTMLAttributes } from 'react'

interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
  label?: string
  error?: string
  fullWidth?: boolean
}

const Input: FC<InputProps> = ({
  label,
  error,
  fullWidth = false,
  className = '',
  ...props
}) => {
  const baseStyles = 'px-4 py-3.5 border-2 border-dark-200 rounded-sm text-body font-sans transition-all duration-300'
  const focusStyles = 'focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10'
  const errorStyles = error ? 'border-danger focus:border-danger focus:ring-danger/10' : ''

  return (
    <div className={fullWidth ? 'w-full' : ''}>
      {label && (
        <label className="block text-body-sm font-medium text-dark-800 mb-2">
          {label}
        </label>
      )}
      <input
        className={`${baseStyles} ${focusStyles} ${errorStyles} ${fullWidth ? 'w-full' : ''} ${className}`}
        {...props}
      />
      {error && <p className="mt-2 text-body-sm text-danger">{error}</p>}
    </div>
  )
}

export default Input
