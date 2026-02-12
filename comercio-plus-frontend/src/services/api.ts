import axios, { AxiosError } from 'axios'
import { API_BASE_URL } from '@/lib/runtime'

if (!API_BASE_URL) {
  console.error('[api] Missing VITE_API_BASE_URL. Set an absolute backend URL (e.g. https://xxxx.up.railway.app/api).')
}

const API = axios.create({
  baseURL: API_BASE_URL,
  timeout: 30000,
  headers: {
    Accept: 'application/json',
  },
  withCredentials: false,
})

API.interceptors.request.use(
  (config) => {
    if (!API_BASE_URL) {
      return Promise.reject(new Error('Missing VITE_API_BASE_URL configuration in production.'))
    }

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

    if (status === 401) {
      localStorage.removeItem('user')
      localStorage.removeItem('token')
      window.location.href = '/login'
    }

    return Promise.reject(error)
  },
)

export default API

