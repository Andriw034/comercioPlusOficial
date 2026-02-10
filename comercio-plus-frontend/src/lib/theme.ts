export type Theme = 'light' | 'dark'

export const THEME_STORAGE_KEY = 'cp-theme'

export const getSystemTheme = (): Theme => {
  if (typeof window === 'undefined') return 'light'
  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

export const getStoredTheme = (): Theme | null => {
  if (typeof window === 'undefined') return null
  const savedTheme = localStorage.getItem(THEME_STORAGE_KEY)
  return savedTheme === 'light' || savedTheme === 'dark' ? savedTheme : null
}

export const getInitialTheme = (): Theme => getStoredTheme() || getSystemTheme()

export const applyTheme = (theme: Theme) => {
  if (typeof document === 'undefined') return
  const isDark = theme === 'dark'
  document.documentElement.classList.toggle('dark', isDark)
  document.documentElement.style.colorScheme = isDark ? 'dark' : 'light'
}

export const initializeTheme = () => {
  applyTheme(getInitialTheme())
}
