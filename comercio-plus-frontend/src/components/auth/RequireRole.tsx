import type { ReactNode } from 'react'
import { Navigate, useLocation } from 'react-router-dom'
import { clearSession, getStoredUserRaw } from '@/services/auth-session'

type Role = 'merchant' | 'client'

type Props = {
  role: Role | Role[]
  children: ReactNode
}

export default function RequireRole({ role, children }: Props) {
  const location = useLocation()
  const userData = getStoredUserRaw()

  if (!userData) {
    const redirect = encodeURIComponent(`${location.pathname}${location.search}`)
    return <Navigate to={`/login?redirect=${redirect}`} replace />
  }

  let userRole: Role | undefined
  try {
    userRole = JSON.parse(userData)?.role
  } catch {
    userRole = undefined
  }

  const allowed = Array.isArray(role) ? role : [role]
  if (!userRole) {
    clearSession()
    const redirect = encodeURIComponent(`${location.pathname}${location.search}`)
    return <Navigate to={`/login?redirect=${redirect}`} replace />
  }

  if (!allowed.includes(userRole)) {
    return <Navigate to="/" replace />
  }

  return <>{children}</>
}
