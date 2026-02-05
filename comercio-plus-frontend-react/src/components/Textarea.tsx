import { forwardRef } from 'react'

type Props = React.TextareaHTMLAttributes<HTMLTextAreaElement>

const Textarea = forwardRef<HTMLTextAreaElement, Props>(({ className = '', ...props }, ref) => (
  <textarea ref={ref} className={`input-dark ${className}`.trim()} {...props} />
))

Textarea.displayName = 'Textarea'

export default Textarea
