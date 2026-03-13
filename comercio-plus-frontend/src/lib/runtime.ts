const stripTrailingSlash = (value: string) => value.replace(/\/+$/, '')

const withUrlScheme = (value: string) => {
  if (!value) return ''
  if (value.startsWith('/')) return value
  if (/^[a-z][a-z0-9+.-]*:\/\//i.test(value)) return value

  const lower = value.toLowerCase()
  const isLocalHost = lower.startsWith('localhost') || lower.startsWith('127.0.0.1')
  const isHostLike = /^[a-z0-9.-]+(?::\d+)?(\/.*)?$/i.test(value)
  if (!isHostLike) return value

  return `${isLocalHost ? 'http' : 'https'}://${value}`
}

const normalizeApiBaseUrl = (value: string) => {
  const trimmed = stripTrailingSlash(value.trim())
  if (!trimmed) return ''

  const candidate = stripTrailingSlash(withUrlScheme(trimmed))

  if (/\/api$/i.test(candidate)) {
    return candidate
  }

  try {
    if (candidate.startsWith('/')) {
      return candidate
    }

    const parsed = new URL(candidate)
    if (parsed.pathname === '/' || parsed.pathname === '') {
      parsed.pathname = '/api'
      return stripTrailingSlash(parsed.toString())
    }
    return stripTrailingSlash(parsed.toString())
  } catch {
    return candidate
  }
}

const rawApiBaseUrl = normalizeApiBaseUrl(
  import.meta.env.VITE_API_BASE_URL ||
  import.meta.env.VITE_API_URL ||
  '',
)

const fallbackApiBaseUrl = (() => {
  if (rawApiBaseUrl) return rawApiBaseUrl

  // Safe default for production: rely on same-origin /api + Vercel rewrites.
  return '/api'
})()

export const API_BASE_URL = fallbackApiBaseUrl
export const API_CONFIG_OK = true
export const API_CONFIG_ERROR_MESSAGE = 'Falta configurar VITE_API_BASE_URL en produccion.'

const inferredOrigin = API_BASE_URL.startsWith('http')
  ? stripTrailingSlash(API_BASE_URL.replace(/\/api\/?$/, ''))
  : (typeof window !== 'undefined' ? stripTrailingSlash(window.location.origin) : '')

export const API_ORIGIN = inferredOrigin

if (import.meta.env.PROD && !rawApiBaseUrl && typeof window !== 'undefined') {
  console.warn('[runtime] Missing VITE_API_BASE_URL in production. Using fallback /api (Vercel rewrites).')
}

if (import.meta.env.DEV && !rawApiBaseUrl && typeof window !== 'undefined') {
  console.warn('[runtime] Missing VITE_API_BASE_URL in development. Using fallback /api (Vite proxy).')
}
