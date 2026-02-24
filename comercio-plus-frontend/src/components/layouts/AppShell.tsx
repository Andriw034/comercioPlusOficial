import type { ReactNode } from 'react'

type Props = {
  children: ReactNode
  header?: ReactNode
  footer?: ReactNode
  variant?: 'public' | 'dashboard' | 'auth'
  containerClassName?: string
  mainClassName?: string
}

export default function AppShell({
  children,
  header,
  footer,
  variant = 'public',
  containerClassName = '',
  mainClassName = '',
}: Props) {
  const isAuth = variant === 'auth'
  const isDashboard = variant === 'dashboard'

  const baseBg = isDashboard ? 'bg-[#F3F4F6] dark:bg-slate-950' : 'bg-[#F9FAFB] dark:bg-slate-950'

  return (
    <div className={`relative flex min-h-screen flex-col overflow-x-hidden ${baseBg} text-slate-900 dark:text-slate-100`}>
      {header && !isAuth && <div className="relative z-20">{header}</div>}

      <main
        className={`relative z-10 ${
          isAuth ? 'flex-1 flex items-center justify-center px-4 py-8 sm:py-12' : 'flex-1 px-4 py-8'
        } ${mainClassName}`.trim()}
      >
        <div className={`mx-auto w-full ${isAuth ? 'max-w-6xl' : 'max-w-7xl'} ${containerClassName}`.trim()}>
          {children}
        </div>
      </main>

      {footer && !isAuth && <div className="relative z-10 mt-auto">{footer}</div>}
    </div>
  )
}
