import type { HTMLAttributes, ReactNode } from 'react'

type Props = HTMLAttributes<HTMLDivElement> & {
  children: ReactNode
}

export default function GlassCard({ className = '', children, ...props }: Props) {
  return (
    <div
      className={`
        relative overflow-hidden rounded-xl p-5 sm:p-6
        border border-[#D7E3F3]
        bg-[linear-gradient(150deg,#FFFFFF_0%,#F8FAFC_52%,#EEF4FF_100%)]
        text-[14px] leading-[1.55] text-[#1F2937]
        shadow-[0_20px_46px_rgba(15,23,42,0.12),inset_0_1px_0_rgba(255,255,255,0.92)]
        ${className}
      `.trim()}
      {...props}
    >
      {children}
    </div>
  )
}
