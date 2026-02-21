import type { ReactNode } from 'react'
import { Navigate, useLocation } from 'react-router-dom'
import { clearSession, getStoredToken, getStoredUserRaw } from '@/services/auth-session'

export default function RequireAuth({ children }: { children: ReactNode }) {
  const location = useLocation()
  const token = getStoredToken()
  const userRaw = getStoredUserRaw()

  if (!token || !userRaw) {
    const redirect = encodeURIComponent(`${location.pathname}${location.search}`)
    return <Navigate to={`/login?redirect=${redirect}`} replace />
  }

  try {
    JSON.parse(userRaw)
  } catch {
    clearSession()
    const redirect = encodeURIComponent(`${location.pathname}${location.search}`)
    return <Navigate to={`/login?redirect=${redirect}`} replace />
  }

  return <>{children}</>
}
