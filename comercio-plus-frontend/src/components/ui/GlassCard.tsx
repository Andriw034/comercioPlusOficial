import type { HTMLAttributes, ReactNode } from 'react'

type Props = HTMLAttributes<HTMLDivElement> & {
  children: ReactNode
}

export default function GlassCard({ className = '', children, ...props }: Props) {
  return (
    <div
      className={`
        rounded-2xl p-5 sm:p-6
        border border-slate-200
        bg-white
        text-[14px] leading-[1.55] text-slate-900
        shadow-sm

        dark:border-white/10
        dark:bg-white/5
        dark:text-white
        dark:backdrop-blur-md
        dark:shadow-glass

        ${className}
      `.trim()}
      {...props}
    >
      {children}
    </div>
  )
}
