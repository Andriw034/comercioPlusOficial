const stripTrailingSlash = (value: string) => value.replace(/\/+$/, '')

const normalizeApiBaseUrl = (value: string) => {
  const trimmed = stripTrailingSlash(value.trim())
  if (!trimmed) return ''

  if (/\/api$/i.test(trimmed)) {
    return trimmed
  }

  try {
    const parsed = new URL(trimmed)
    if (parsed.pathname === '/' || parsed.pathname === '') {
      parsed.pathname = '/api'
      return stripTrailingSlash(parsed.toString())
    }
    return stripTrailingSlash(parsed.toString())
  } catch {
    return trimmed
  }
}

const rawApiBaseUrl = normalizeApiBaseUrl(
  import.meta.env.VITE_API_BASE_URL ||
  import.meta.env.VITE_API_URL ||
  '',
)

const fallbackApiBaseUrl = (() => {
  if (rawApiBaseUrl) return rawApiBaseUrl

  if (import.meta.env.DEV) {
    return 'http://127.0.0.1:8000/api'
  }

  if (typeof window !== 'undefined') {
    return normalizeApiBaseUrl(`${window.location.origin}/api`)
  }

  return ''
})()

export const API_BASE_URL = fallbackApiBaseUrl

const inferredOrigin = API_BASE_URL.startsWith('http')
  ? stripTrailingSlash(API_BASE_URL.replace(/\/api\/?$/, ''))
  : (typeof window !== 'undefined' ? stripTrailingSlash(window.location.origin) : '')

export const API_ORIGIN = inferredOrigin

if (!import.meta.env.DEV && !rawApiBaseUrl && typeof window !== 'undefined') {
  console.warn('[runtime] Missing VITE_API_BASE_URL in production. Using fallback /api on current origin.')
}

if (import.meta.env.DEV && !rawApiBaseUrl && typeof window !== 'undefined') {
  console.warn('[runtime] Missing VITE_API_BASE_URL in development. Using fallback local API http://127.0.0.1:8000/api.')
}
