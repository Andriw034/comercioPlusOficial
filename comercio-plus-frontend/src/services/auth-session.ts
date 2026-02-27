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

const normalizeRole = (value: unknown): string => {
  const role = String(value || '').trim().toLowerCase()
  if (role === 'comerciante') return 'merchant'
  if (role === 'cliente') return 'client'
  return role || 'client'
}

const mapAuthUser = (value: unknown): AuthUser | null => {
  if (!value || typeof value !== 'object') return null
  const user = value as Record<string, unknown>
  const id = Number(user.id)
  const email = String(user.email || '').trim()
  const name = String(user.name || '').trim()

  if (!Number.isFinite(id) || id <= 0 || !email || !name) return null

  return {
    id,
    name,
    email,
    phone: user.phone ? String(user.phone) : null,
    role: normalizeRole(user.role),
    has_store: typeof user.has_store === 'boolean' ? user.has_store : undefined,
    store_id: user.store_id ? Number(user.store_id) : null,
  }
}

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

  const localToken = getLocalToken()
  if (localToken) {
    sessionStorage.setItem(TOKEN_KEY, localToken)
    return localToken
  }

  return null
}

export function getStoredUserRaw(): string | null {
  const userRaw = sessionStorage.getItem(USER_KEY)
  if (userRaw) return userRaw

  const localUser = localStorage.getItem(USER_KEY)
  if (localUser) {
    sessionStorage.setItem(USER_KEY, localUser)
    return localUser
  }

  return null
}

export async function ensureStoredSession(): Promise<AuthUser | null> {
  const token = getStoredToken()
  if (!token) return null

  const userRaw = getStoredUserRaw()
  if (userRaw) {
    try {
      const parsed = JSON.parse(userRaw)
      const mapped = mapAuthUser(parsed)
      if (mapped) return mapped
    } catch {
      // Continue with API re-hydration.
    }
  }

  try {
    API.defaults.headers.common.Authorization = `Bearer ${token}`
    const { data } = await API.get('/me')
    const user = mapAuthUser(data)
    if (!user) {
      clearSession()
      return null
    }

    const serializedUser = JSON.stringify(user)
    sessionStorage.setItem(USER_KEY, serializedUser)
    if (localStorage.getItem(TOKEN_KEY)) {
      localStorage.setItem(USER_KEY, serializedUser)
    }
    return user
  } catch {
    clearSession()
    return null
  }
}

export async function hydrateSession(token: string, persist = true, fallbackUser?: unknown): Promise<AuthUser> {
  sessionStorage.setItem(TOKEN_KEY, token)
  if (persist) {
    localStorage.setItem(TOKEN_KEY, token)
  } else {
    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem(USER_KEY)
  }
  API.defaults.headers.common.Authorization = `Bearer ${token}`

  let user: AuthUser | null = null
  try {
    const { data } = await API.get('/me')
    user = mapAuthUser(data)
  } catch {
    user = mapAuthUser(fallbackUser)
  }

  if (!user) {
    clearSession()
    throw new Error('No se pudo hidratar la sesion del usuario.')
  }

  const serializedUser = JSON.stringify(user)
  sessionStorage.setItem(USER_KEY, serializedUser)
  if (persist) {
    localStorage.setItem(USER_KEY, serializedUser)
  }
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
    if (!user.has_store && !user.store_id) {
      return '/dashboard/store'
    }
    return '/dashboard'
  }

  if (user.role === 'admin') {
    return '/dashboard'
  }

  return '/'
}
