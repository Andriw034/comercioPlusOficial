const stripTrailingSlash = (value: string) => value.replace(/\/+$/, '')

const rawApiBaseUrl = stripTrailingSlash(
  (
    import.meta.env.VITE_API_BASE_URL ||
    import.meta.env.VITE_API_URL ||
    ''
  ).trim(),
)

const fallbackApiBaseUrl = import.meta.env.DEV ? 'http://127.0.0.1:8000/api' : ''

export const API_BASE_URL = rawApiBaseUrl || fallbackApiBaseUrl

const inferredOrigin = API_BASE_URL.startsWith('http')
  ? stripTrailingSlash(API_BASE_URL.replace(/\/api\/?$/, ''))
  : (typeof window !== 'undefined' ? stripTrailingSlash(window.location.origin) : '')

export const API_ORIGIN = inferredOrigin

if (!import.meta.env.DEV && !rawApiBaseUrl && typeof window !== 'undefined') {
  console.error('[runtime] Missing VITE_API_BASE_URL in production. API calls may fail.')
}
