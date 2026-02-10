import axios from 'axios'
import { API_BASE_URL } from './runtime'

axios.defaults.baseURL = API_BASE_URL
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.withCredentials = false

if (!API_BASE_URL) {
  console.error('[api] Missing VITE_API_BASE_URL. Set an absolute backend URL (e.g. https://xxxx.up.railway.app/api).')
}

const API = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    Accept: 'application/json',
  },
  withCredentials: false,
})

API.interceptors.request.use(
  async (config) => {
    if (!API_BASE_URL) {
      return Promise.reject(new Error('Missing VITE_API_BASE_URL configuration in production.'))
    }

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
