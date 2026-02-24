import { useEffect, useMemo, useState } from 'react'
import { Outlet } from 'react-router-dom'
import Sidebar from '@/components/dashboard/Sidebar'
import API from '@/lib/api'
import { resolveMediaUrl } from '@/lib/format'

interface Store {
  id: string | number
  name: string
  logo?: string
  cover?: string
}

const STORE_CACHE_KEY = 'store'

function safeParseStore(raw: string | null): any | null {
  if (!raw) return null
  try {
    return JSON.parse(raw)
  } catch {
    return null
  }
}

function mapStore(data: any): Store {
  return {
    id: data?.id || '',
    name: String(data?.name || '').trim() || 'Mi tienda',
    logo: resolveMediaUrl(data?.logo_url || data?.logo_path || data?.logo) || '',
    cover: resolveMediaUrl(data?.cover_url || data?.cover_path || data?.background_url || data?.cover) || '',
  }
}

export default function DashboardLayout() {
  const cachedRaw = useMemo(() => localStorage.getItem(STORE_CACHE_KEY), [])
  const cachedParsed = useMemo(() => safeParseStore(cachedRaw), [cachedRaw])

  const [store, setStore] = useState<Store | null>(() => (cachedParsed ? mapStore(cachedParsed) : null))
  const [isLoading, setIsLoading] = useState(() => !cachedParsed)

  useEffect(() => {
    let isMounted = true

    const loadStoreData = async () => {
      try {
        const { data } = await API.get('/my/store')
        const mapped = mapStore(data)
        if (!isMounted) return
        setStore(mapped)
        localStorage.setItem(STORE_CACHE_KEY, JSON.stringify(data || mapped))
      } catch (error: any) {
        if (!isMounted) return

        if (error?.response?.status === 404) {
          localStorage.removeItem(STORE_CACHE_KEY)
          setStore(null)
          return
        }

        if (!cachedParsed) {
          setStore(null)
        }
      } finally {
        if (isMounted) {
          setIsLoading(false)
        }
      }
    }

    loadStoreData()

    return () => {
      isMounted = false
    }
  }, [cachedParsed])

  useEffect(() => {
    const onStoreUpdated = (event: Event) => {
      const custom = event as CustomEvent<any>
      const detail = custom.detail
      if (!detail) return

      const mapped = mapStore(detail)
      setStore(mapped)
      localStorage.setItem(STORE_CACHE_KEY, JSON.stringify(detail))
    }

    window.addEventListener('store:updated', onStoreUpdated as EventListener)

    return () => {
      window.removeEventListener('store:updated', onStoreUpdated as EventListener)
    }
  }, [])

  if (isLoading) {
    return (
      <div className="flex h-screen items-center justify-center bg-slate-50 dark:bg-slate-950">
        <div className="text-center">
          <div className="mb-4 inline-block h-12 w-12 animate-spin rounded-full border-4 border-comercioplus-600 border-t-transparent" />
          <p className="text-body text-slate-600 dark:text-slate-300">Cargando...</p>
        </div>
      </div>
    )
  }

  return (
    <div className="flex h-screen overflow-hidden bg-[#f0f2f7] text-slate-900 dark:bg-slate-950 dark:text-slate-100">
      <Sidebar store={store} />

      <main className="flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6 md:p-8">
        <Outlet context={{ store }} />
      </main>
    </div>
  )
}
