import type { HTMLAttributes, ReactNode } from 'react'

type Props = HTMLAttributes<HTMLDivElement> & {
  children: ReactNode
}

export default function GlassCard({ className = '', children, ...props }: Props) {
  return (
    <div
      className={`
        rounded-xl p-5 sm:p-6
        border border-[#E2E8F0]
        bg-[linear-gradient(145deg,#FFFFFF_0%,#F8FAFC_100%)]
        text-[14px] leading-[1.55] text-[#1F2937]
        shadow-[0_14px_28px_rgba(15,23,42,0.08)]
        ${className}
      `.trim()}
      {...props}
    >
      {children}
    </div>
  )
}
