import { forwardRef } from 'react'

type Props = React.InputHTMLAttributes<HTMLInputElement>

const Input = forwardRef<HTMLInputElement, Props>(({ className = '', ...props }, ref) => (
  <input ref={ref} className={`input-dark ${className}`.trim()} {...props} />
))

Input.displayName = 'Input'

export default Input
