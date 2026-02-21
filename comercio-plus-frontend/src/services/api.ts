import axios, { AxiosError } from 'axios'
import { API_BASE_URL } from '@/lib/runtime'

if (!API_BASE_URL) {
  console.error('[api] Missing API base URL. Requests will fallback to /api.')
}

const TOKEN_KEY = 'token'
const USER_KEY = 'user'

const clearStoredSession = () => {
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(USER_KEY)
  sessionStorage.removeItem(TOKEN_KEY)
  sessionStorage.removeItem(USER_KEY)
}

const readToken = (): string | null => {
  const sessionToken = sessionStorage.getItem(TOKEN_KEY)
  if (sessionToken && sessionToken.trim().length > 0) return sessionToken

  const localToken = localStorage.getItem(TOKEN_KEY)
  if (localToken && localToken.trim().length > 0) {
    clearStoredSession()
  }

  return null
}

const API = axios.create({
  baseURL: API_BASE_URL || '/api',
  timeout: 30000,
  headers: {
    Accept: 'application/json',
  },
  withCredentials: false,
})

API.interceptors.request.use(
  (config) => {
    config.withCredentials = false

    const token = readToken()
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    } else if (config.headers && 'Authorization' in config.headers) {
      delete config.headers.Authorization
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
    const isAuthFlowRequest =
      requestUrl.includes('/login') ||
      requestUrl.includes('/register') ||
      requestUrl.includes('/me')

    if (status === 401 && !isAuthFlowRequest) {
      clearStoredSession()
      delete API.defaults.headers.common.Authorization
      window.location.href = '/login'
    }

    return Promise.reject(error)
  },
)

export default API
