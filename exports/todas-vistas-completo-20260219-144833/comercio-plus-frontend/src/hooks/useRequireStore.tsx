import { useEffect, useState, type ReactNode } from 'react'
import { Navigate, useLocation, useNavigate } from 'react-router-dom'
import API from '@/lib/api'

type AppUser = {
  role?: string
  storeId?: string | number | null
  store_id?: string | number | null
  store?: { id?: string | number | null } | null
}

const userHasStore = (user: AppUser | null | undefined): boolean => {
  if (!user) return false
  return Boolean(user.storeId || user.store_id || user.store?.id)
}

export function useRequireStore() {
  const navigate = useNavigate()
  const location = useLocation()
  const [isLoading, setIsLoading] = useState(true)
  const [hasStore, setHasStore] = useState(false)

  useEffect(() => {
    const checkStoreStatus = async () => {
      try {
        const token = localStorage.getItem('token')
        const userRaw = localStorage.getItem('user')

        if (!token || !userRaw) {
          const redirect = encodeURIComponent(`${location.pathname}${location.search}`)
          navigate(`/login?redirect=${redirect}`, { replace: true })
          return
        }

        const parsedUser: AppUser = JSON.parse(userRaw)
        const role = parsedUser.role

        if (role !== 'merchant') {
          setHasStore(true)
          return
        }

        if (userHasStore(parsedUser)) {
          setHasStore(true)
          return
        }

        try {
          const { data } = await API.get('/my/store')
          if (data?.id) {
            setHasStore(true)
            localStorage.setItem(
              'user',
              JSON.stringify({
                ...parsedUser,
                storeId: data.id,
              }),
            )
            return
          }
        } catch {
          // If /my/store fails with 404, the merchant has no store yet.
        }

        navigate('/crear-tienda', { replace: true })
      } catch {
        const redirect = encodeURIComponent(`${location.pathname}${location.search}`)
        navigate(`/login?redirect=${redirect}`, { replace: true })
      } finally {
        setIsLoading(false)
      }
    }

    checkStoreStatus()
  }, [location.pathname, location.search, navigate])

  return { isLoading, hasStore }
}

export function ProtectedRoute({ children }: { children: ReactNode }) {
  const { isLoading, hasStore } = useRequireStore()

  if (isLoading) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-slate-50">
        <div className="text-center">
          <div className="mb-4 inline-block h-12 w-12 animate-spin rounded-full border-4 border-comercioplus-600 border-t-transparent" />
          <p className="text-body text-slate-600">Cargando...</p>
        </div>
      </div>
    )
  }

  if (!hasStore) {
    return <Navigate to="/crear-tienda" replace />
  }

  return <>{children}</>
}
