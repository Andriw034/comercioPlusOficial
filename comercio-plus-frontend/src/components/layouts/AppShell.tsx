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
    <div className="min-h-screen bg-mesh text-white relative overflow-hidden flex flex-col">
      <div className="absolute inset-0 pointer-events-none">
        <div className="absolute -left-28 top-6 h-72 w-72 rounded-full bg-brand-500/20 blur-3xl" />
        <div className="absolute right-[-10%] top-10 h-80 w-80 rounded-full bg-sky-500/20 blur-3xl" />
        <div className="absolute bottom-0 left-1/2 h-80 w-80 -translate-x-1/2 rounded-full bg-white/5 blur-3xl" />
      </div>

      {header && !isAuth && <div className="relative z-20">{header}</div>}

      <main
        className={`relative z-10 ${isAuth ? 'flex-1 flex items-center justify-center px-4 py-6 sm:py-10' : 'flex-1 px-4 py-8'} ${mainClassName}`.trim()}
      >
        <div
          className={`mx-auto w-full ${isAuth ? 'max-w-lg' : 'max-w-7xl'} ${containerClassName}`.trim()}
        >
          {children}
        </div>
      </main>

      {footer && !isAuth && <div className="relative z-10 mt-auto">{footer}</div>}
    </div>
  )
}
