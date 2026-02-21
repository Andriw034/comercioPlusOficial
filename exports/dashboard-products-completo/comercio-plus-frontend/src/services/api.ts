import axios, { AxiosError } from 'axios'
import { API_BASE_URL } from '@/lib/runtime'

if (!API_BASE_URL) {
  console.error('[api] Missing API base URL. Requests will fallback to /api.')
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

    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
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
      localStorage.removeItem('user')
      localStorage.removeItem('token')
      window.location.href = '/login'
    }

    return Promise.reject(error)
  },
)

export default API
