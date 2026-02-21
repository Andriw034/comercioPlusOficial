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

const TOKEN_KEY = 'token'
const USER_KEY = 'user'

function getSessionToken(): string | null {
  const token = sessionStorage.getItem(TOKEN_KEY)
  if (!token) return null
  return token.trim().length > 0 ? token : null
}

function getLocalToken(): string | null {
  const token = localStorage.getItem(TOKEN_KEY)
  if (!token) return null
  return token.trim().length > 0 ? token : null
}

export function getStoredToken(): string | null {
  const sessionToken = getSessionToken()
  if (sessionToken) return sessionToken

  // If the browser/tab session ended, stale local credentials are invalid.
  if (getLocalToken()) {
    clearSession()
  }

  return null
}

export function getStoredUserRaw(): string | null {
  const userRaw = sessionStorage.getItem(USER_KEY)
  if (userRaw) return userRaw

  // Mirror stale user cleanup when token does not exist in current session.
  if (localStorage.getItem(USER_KEY)) {
    clearSession()
  }

  return null
}

export async function hydrateSession(token: string): Promise<AuthUser> {
  localStorage.setItem(TOKEN_KEY, token)
  sessionStorage.setItem(TOKEN_KEY, token)
  API.defaults.headers.common.Authorization = `Bearer ${token}`

  const { data } = await API.get('/me')
  const user: AuthUser = data
  const serializedUser = JSON.stringify(user)
  localStorage.setItem(USER_KEY, serializedUser)
  sessionStorage.setItem(USER_KEY, serializedUser)
  if (!user.has_store && !user.store_id) {
    localStorage.removeItem('store')
  }

  return user
}

export function clearSession(): void {
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(USER_KEY)
  sessionStorage.removeItem(TOKEN_KEY)
  sessionStorage.removeItem(USER_KEY)
  delete API.defaults.headers.common.Authorization
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
