import { createContext, useCallback, useEffect, useLayoutEffect, useMemo, useState, type ReactNode } from 'react'

type Theme = 'light' | 'dark'

type ThemeContextValue = {
  theme: Theme
  isDark: boolean
  setTheme: (theme: Theme) => void
  toggleTheme: () => void
}

const THEME_STORAGE_KEY = 'cp-theme'
const FORCED_THEME = (() => {
  const raw = String(import.meta.env.VITE_FORCE_THEME || '').toLowerCase().trim()
  if (raw === 'light' || raw === 'dark') return raw as Theme
  return null
})()

export const ThemeContext = createContext<ThemeContextValue | null>(null)

const getSystemTheme = (): Theme => {
  if (typeof window === 'undefined') return 'light'
  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

const getStoredTheme = (): Theme | null => {
  if (FORCED_THEME) return FORCED_THEME
  if (typeof window === 'undefined') return null
  const savedTheme = localStorage.getItem(THEME_STORAGE_KEY)
  return savedTheme === 'light' || savedTheme === 'dark' ? savedTheme : null
}

const applyTheme = (theme: Theme) => {
  document.documentElement.classList.toggle('dark', theme === 'dark')
}

export default function ThemeProvider({ children }: { children: ReactNode }) {
  const [theme, setThemeState] = useState<Theme>(() => FORCED_THEME ?? getStoredTheme() ?? getSystemTheme())

  useLayoutEffect(() => {
    applyTheme(theme)
    if (!FORCED_THEME) {
      localStorage.setItem(THEME_STORAGE_KEY, theme)
    }
  }, [theme])

  useEffect(() => {
    if (FORCED_THEME) return
    const media = window.matchMedia('(prefers-color-scheme: dark)')
    const onChange = () => {
      const stored = getStoredTheme()
      if (!stored) {
        setThemeState(getSystemTheme())
      }
    }

    media.addEventListener('change', onChange)
    return () => media.removeEventListener('change', onChange)
  }, [])

  const setTheme = useCallback((nextTheme: Theme) => {
    if (FORCED_THEME) return
    setThemeState(nextTheme)
  }, [])

  const toggleTheme = useCallback(() => {
    if (FORCED_THEME) return
    setThemeState((prev) => (prev === 'dark' ? 'light' : 'dark'))
  }, [])

  const value = useMemo(
    () => ({
      theme,
      isDark: theme === 'dark',
      setTheme,
      toggleTheme,
    }),
    [theme, setTheme, toggleTheme],
  )

  return <ThemeContext.Provider value={value}>{children}</ThemeContext.Provider>
}
