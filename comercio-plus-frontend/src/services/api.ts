import axios, { AxiosError, type AxiosRequestConfig, type AxiosResponse } from 'axios'
import { API_BASE_URL, API_CONFIG_ERROR_MESSAGE, API_CONFIG_OK } from '@/lib/runtime'

if (import.meta.env.PROD && !API_CONFIG_OK) {
  console.error(`[api][AUTH] ${API_CONFIG_ERROR_MESSAGE}`)
}

const TOKEN_KEY = 'token'
const USER_KEY = 'user'
const GET_CACHE_TTL_MS = 45_000

type CachedGetResponse = {
  expiresAt: number
  response: AxiosResponse
}

const getCache = new Map<string, CachedGetResponse>()

const clearStoredSession = () => {
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(USER_KEY)
  sessionStorage.removeItem(TOKEN_KEY)
  sessionStorage.removeItem(USER_KEY)
}

const clearGetCache = () => {
  getCache.clear()
}

const readToken = (): string | null => {
  const sessionToken = sessionStorage.getItem(TOKEN_KEY)
  if (sessionToken && sessionToken.trim().length > 0) return sessionToken

  const localToken = localStorage.getItem(TOKEN_KEY)
  if (localToken && localToken.trim().length > 0) {
    sessionStorage.setItem(TOKEN_KEY, localToken)
    return localToken
  }

  return null
}

const stableSerialize = (value: unknown): string => {
  if (value === null || value === undefined) return ''
  if (typeof value !== 'object') return String(value)
  if (Array.isArray(value)) return `[${value.map(stableSerialize).join(',')}]`

  const obj = value as Record<string, unknown>
  return `{${Object.keys(obj).sort().map((key) => `${key}:${stableSerialize(obj[key])}`).join('|')}}`
}

const buildGetCacheKey = (url: string, config?: AxiosRequestConfig): string => {
  const params = stableSerialize(config?.params)
  const token = readToken() || 'guest'
  return [String(API.defaults.baseURL || ''), url, params, token].join('|')
}

const API = axios.create({
  baseURL: API_BASE_URL || (import.meta.env.DEV ? '/api' : ''),
  timeout: 30000,
  headers: {
    Accept: 'application/json',
  },
  withCredentials: false,
})

if (import.meta.env.DEV) {
  console.info(`[api] baseURL: ${String(API.defaults.baseURL || '')}`)
}

API.interceptors.request.use(
  (config) => {
    if (import.meta.env.PROD && !API_CONFIG_OK) {
      const configError = new Error(API_CONFIG_ERROR_MESSAGE) as Error & {
        code?: string
        isApiConfigError?: boolean
      }
      configError.code = 'AUTH_API_CONFIG_MISSING'
      configError.isApiConfigError = true
      return Promise.reject(configError)
    }

    config.withCredentials = false
    const method = (config.method || 'get').toLowerCase()

    const token = readToken()
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    } else if (config.headers && 'Authorization' in config.headers) {
      delete config.headers.Authorization
    }

    if (method !== 'get') {
      clearGetCache()
    }

    if (import.meta.env.DEV && ['login', 'register', 'me', 'logout'].some((segment) => String(config.url || '').includes(segment))) {
      console.debug(`[AUTH] request ${method.toUpperCase()} ${String(config.baseURL || '')}${String(config.url || '')}`)
    }

    return config
  },
  (error) => Promise.reject(error),
)

API.interceptors.response.use(
  (response) => response,
  (error: AxiosError) => {
    const status = error.response?.status
    const requestUrl = String(error.config?.url || '')
    const hasStoredToken = Boolean(readToken())
    const pathname = String(window.location?.pathname || '')
    const isLoginRoute = pathname.startsWith('/login')
    const isAuthFlowRequest =
      requestUrl.includes('/login') ||
      requestUrl.includes('/register') ||
      requestUrl.includes('/me')
    const isProtectedRoute = pathname.startsWith('/dashboard') || pathname.startsWith('/checkout')

    // Guest-first: clear session on 401, but only force /login when user is currently in a protected route.
    if (status === 401 && !isAuthFlowRequest && hasStoredToken) {
      clearStoredSession()
      clearGetCache()
      delete API.defaults.headers.common.Authorization
      if (isProtectedRoute && !isLoginRoute) {
        window.location.href = '/login'
      }
    }

    if (import.meta.env.DEV && isAuthFlowRequest) {
      console.debug(`[AUTH] response error ${status || 'n/a'} on ${requestUrl}`)
    }

    return Promise.reject(error)
  },
)

const originalGet = API.get.bind(API)
API.get = (async function cachedGet<T = any, R = AxiosResponse<T>, D = any>(
  url: string,
  config?: AxiosRequestConfig<D>,
): Promise<R> {
  const key = buildGetCacheKey(url, config)
  const now = Date.now()
  const cached = getCache.get(key)

  if (cached && cached.expiresAt > now) {
    return cached.response as R
  }

  const response = await originalGet<T, AxiosResponse<T>, D>(url, config)

  if (response.status >= 200 && response.status < 300) {
    getCache.set(key, {
      expiresAt: now + GET_CACHE_TTL_MS,
      response: response as AxiosResponse,
    })
  }

  return response as R
}) as typeof API.get

export default API
