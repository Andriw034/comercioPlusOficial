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

  return (
    <div className="relative flex min-h-screen flex-col overflow-x-hidden bg-gradient-to-b from-slate-50 via-white to-slate-50 text-slate-900 dark:bg-gradient-to-b dark:from-slate-950 dark:via-slate-950 dark:to-slate-900 dark:text-slate-100">

      {header && !isAuth && <div className="relative z-20">{header}</div>}

      <main
        className={`relative z-10 ${
          isAuth ? 'flex-1 flex items-center justify-center px-4 py-6 sm:py-10' : 'flex-1 px-4 py-8'
        } ${mainClassName}`.trim()}
      >
        <div className={`mx-auto w-full ${isAuth ? 'max-w-lg' : 'max-w-7xl'} ${containerClassName}`.trim()}>
          {children}
        </div>
      </main>

      {footer && !isAuth && <div className="relative z-10 mt-auto">{footer}</div>}
    </div>
  )
}
