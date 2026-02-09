import axios from 'axios'
import { API_BASE_URL, SANCTUM_BASE_URL } from './runtime'

const resolveCsrfUrl = () => {
  if (SANCTUM_BASE_URL) return `${SANCTUM_BASE_URL}/sanctum/csrf-cookie`
  if (typeof window !== 'undefined') return `${window.location.origin}/sanctum/csrf-cookie`
  return '/sanctum/csrf-cookie'
}

axios.defaults.baseURL = API_BASE_URL
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.withCredentials = true

const API = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  withCredentials: true,
})

let csrfFetched = false

API.interceptors.request.use(
  async (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    const method = (config.method || '').toLowerCase()
    if (!csrfFetched && ['post', 'put', 'patch', 'delete'].includes(method)) {
      try {
        await axios.get(resolveCsrfUrl(), { withCredentials: true })
        csrfFetched = true
      } catch (error) {
        console.warn('No se pudo obtener CSRF cookie:', error)
      }
    }
    return config
  },
  (error) => Promise.reject(error),
)

API.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('user')
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  },
)

export default API
