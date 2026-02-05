import type { HTMLAttributes, ReactNode } from 'react'

type Props = HTMLAttributes<HTMLDivElement> & {
  children: ReactNode
}

export default function Card({ className = '', children, ...props }: Props) {
  return (
    <div className={`glass rounded-2xl ${className}`.trim()} {...props}>
      {children}
    </div>
  )
}
