import type { HTMLAttributes, ReactNode } from 'react'

type Props = HTMLAttributes<HTMLDivElement> & {
  children: ReactNode
}

export default function GlassCard({ className = '', children, ...props }: Props) {
  return (
    <div
      className={`
        rounded-xl p-5 sm:p-6
        border border-[#E5E7EB]
        bg-white
        text-[14px] leading-[1.55] text-[#1F2937]
        shadow-[0_2px_8px_rgba(0,0,0,0.05)]
        ${className}
      `.trim()}
      {...props}
    >
      {children}
    </div>
  )
}
