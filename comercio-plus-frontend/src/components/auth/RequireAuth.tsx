import { useEffect, useMemo, useState, type ReactNode } from 'react'
import { Navigate, useLocation } from 'react-router-dom'
import { ensureStoredSession, getStoredToken, getStoredUserRaw } from '@/services/auth-session'

export default function RequireAuth({ children }: { children: ReactNode }) {
  const location = useLocation()
  const token = getStoredToken()
  const userRaw = getStoredUserRaw()
  const [recoveryResult, setRecoveryResult] = useState<'unknown' | 'ok' | 'fail'>('unknown')

  const hasValidUser = useMemo(() => {
    if (!userRaw) return false
    try {
      const parsed = JSON.parse(userRaw)
      return Boolean(parsed && typeof parsed === 'object')
    } catch {
      return false
    }
  }, [userRaw])

  useEffect(() => {
    if (!token || hasValidUser || recoveryResult !== 'unknown') return

    let isMounted = true
    void ensureStoredSession().then((user) => {
      if (!isMounted) return
      setRecoveryResult(user ? 'ok' : 'fail')
    })

    return () => {
      isMounted = false
    }
  }, [token, hasValidUser, recoveryResult])

  if (!token) {
    const redirect = encodeURIComponent(`${location.pathname}${location.search}`)
    return <Navigate to={`/login?redirect=${redirect}`} replace />
  }

  if (hasValidUser || recoveryResult === 'ok') {
    return <>{children}</>
  }

  if (recoveryResult === 'unknown') {
    return null
  }

  const redirect = encodeURIComponent(`${location.pathname}${location.search}`)
  return <Navigate to={`/login?redirect=${redirect}`} replace />
}
