import axios from 'axios'

export const api = axios.create({
  baseURL: '/',
  headers: { 'X-Requested-With': 'XMLHttpRequest' },
})

api.interceptors.response.use(
  r => r,
  e => {
    console.error('[API ERROR]', e?.response?.status, e?.response?.data)
    throw e
  }
)

