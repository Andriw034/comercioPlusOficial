import { forwardRef } from 'react'

type Props = React.SelectHTMLAttributes<HTMLSelectElement>

const Select = forwardRef<HTMLSelectElement, Props>(({ className = '', ...props }, ref) => (
  <select ref={ref} className={`select-dark ${className}`.trim()} {...props} />
))

Select.displayName = 'Select'

export default Select
