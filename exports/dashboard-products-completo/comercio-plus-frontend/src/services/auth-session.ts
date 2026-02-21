import API from '@/lib/api'

export type AuthUser = {
  id: number
  name: string
  email: string
  phone?: string | null
  role: 'merchant' | 'client' | 'admin' | string
  has_store?: boolean
  store_id?: number | null
}

export async function hydrateSession(token: string): Promise<AuthUser> {
  localStorage.setItem('token', token)
  API.defaults.headers.common.Authorization = `Bearer ${token}`

  const { data } = await API.get('/me')
  const user: AuthUser = data
  localStorage.setItem('user', JSON.stringify(user))

  return user
}

export function resolvePostAuthRoute(user: AuthUser): string {
  if (user.role === 'merchant') {
    return user.has_store ? '/dashboard/products' : '/dashboard/store'
  }

  if (user.role === 'admin') {
    return '/dashboard'
  }

  return '/'
}

