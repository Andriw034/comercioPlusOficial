import { useEffect, useState } from 'react'

type Theme = 'light' | 'dark'

const THEME_STORAGE_KEY = 'cp-theme'

const getSystemTheme = (): Theme => {
  if (typeof window === 'undefined') return 'light'
  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

const getInitialTheme = (): Theme => {
  if (typeof window === 'undefined') return 'light'

  const savedTheme = localStorage.getItem(THEME_STORAGE_KEY)
  if (savedTheme === 'light' || savedTheme === 'dark') {
    return savedTheme
  }

  return getSystemTheme()
}

const applyTheme = (theme: Theme) => {
  document.documentElement.classList.toggle('dark', theme === 'dark')
}

export default function ThemeToggle() {
  const [theme, setTheme] = useState<Theme>(getInitialTheme)
  const isDark = theme === 'dark'

  useEffect(() => {
    applyTheme(theme)
    localStorage.setItem(THEME_STORAGE_KEY, theme)
  }, [theme])

  return (
    <button
      type="button"
      onClick={() => setTheme((prev) => (prev === 'dark' ? 'light' : 'dark'))}
      className="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white/80 text-slate-700 transition-colors hover:bg-slate-900/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-transparent dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:bg-white/10"
      aria-label={isDark ? 'Activar modo claro' : 'Activar modo oscuro'}
      title={isDark ? 'Activar modo claro' : 'Activar modo oscuro'}
    >
      {isDark ? (
        <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
          <circle cx="12" cy="12" r="4" />
          <path strokeLinecap="round" d="M12 3v2.2M12 18.8V21M3 12h2.2M18.8 12H21M5.64 5.64l1.56 1.56M16.8 16.8l1.56 1.56M5.64 18.36l1.56-1.56M16.8 7.2l1.56-1.56" />
        </svg>
      ) : (
        <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            d="M21 12.8A8.8 8.8 0 1 1 11.2 3a7.1 7.1 0 0 0 9.8 9.8"
          />
        </svg>
      )}
    </button>
  )
}
