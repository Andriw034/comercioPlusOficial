import { useCallback, useEffect, useState } from 'react'
import { Link, Outlet } from 'react-router-dom'
import API from '@/lib/api'
import type { Store } from '@/types/api'
import { buttonVariants } from '@/components/ui/button'
import { resolveMediaUrl } from '@/lib/format'
import ThemeToggle from '@/components/theme/ThemeToggle'
import AppShell from './AppShell'

export default function DashboardLayout() {
  type StoreMedia = Store & {
    logo?: string
    cover?: string
    logo_path?: string
    cover_path?: string
    background_url?: string
    background_path?: string
  }

  const [store, setStore] = useState<Store | null>(() => {
    try {
      const cached = localStorage.getItem('store')
      return cached ? (JSON.parse(cached) as Store) : null
    } catch {
      return null
    }
  })

  const withCacheBust = useCallback((data: StoreMedia): Store => {
    const cacheBuster = Date.now().toString()
    const addBust = (url?: string) => {
      if (!url) return url
      const [base, query] = url.split('?')
      const params = new URLSearchParams(query || '')
      params.set('v', cacheBuster)
      return `${base}?${params.toString()}`
    }

    const resolvedLogo = resolveMediaUrl(data.logo_url || data.logo_path || data.logo)
    const resolvedCover = resolveMediaUrl(
      data.cover_url || data.cover_path || data.background_url || data.background_path || data.cover,
    )

    return {
      ...data,
      logo_url: addBust(resolvedLogo),
      cover_url: addBust(resolvedCover),
    }
  }, [])

  useEffect(() => {
    let active = true

    const loadStore = async () => {
      try {
        const { data } = await API.get('/my/store')
        if (!active || !data) return
        const normalized = withCacheBust(data)
        setStore(normalized)
        localStorage.setItem('store', JSON.stringify(normalized))
      } catch (err: any) {
        if (err?.response?.status === 404) {
          setStore(null)
          localStorage.removeItem('store')
          return
        }
        console.error('Load store header', err)
      }
    }

    const handleStoreUpdate = (event: Event) => {
      const detail = (event as CustomEvent<Store>).detail
      if (!detail) return
      const normalized = withCacheBust(detail)
      setStore(normalized)
      localStorage.setItem('store', JSON.stringify(normalized))
    }

    loadStore()
    window.addEventListener('store:updated', handleStoreUpdate as EventListener)

    return () => {
      active = false
      window.removeEventListener('store:updated', handleStoreUpdate as EventListener)
    }
  }, [withCacheBust])

  const storeName = store?.name || 'ComercioPlus'
  const storeLogo = store?.logo_url
  const storeInitial = storeName.trim().charAt(0).toUpperCase() || 'C'

  const header = (
    <header className="sticky top-0 z-30 px-4 pt-4">
      <div className="mx-auto max-w-7xl rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm dark:border-white/10 dark:bg-slate-950/40">
        <div className="flex items-center justify-between gap-3">
          <Link to="/" className="flex min-w-0 items-center gap-3">
            {storeLogo ? (
              <img
                src={storeLogo}
                alt={`Logo ${storeName}`}
                className="h-11 w-11 rounded-2xl border border-slate-200 bg-white object-cover dark:border-white/10"
              />
            ) : (
              <span className="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-500 text-base font-bold text-white">
                {storeInitial}
              </span>
            )}

            <div className="min-w-0 leading-tight">
              <p className="truncate text-[15px] font-semibold text-slate-900 dark:text-white">{storeName}</p>
              <p className="text-[12px] text-slate-600 dark:text-white/60">Panel del comerciante</p>
            </div>
          </Link>

          <ThemeToggle />
        </div>

        <div className="mt-3 grid grid-cols-2 gap-2 sm:mt-0 sm:flex sm:items-center sm:justify-end">
          <Link
            to="/dashboard/store"
            className={buttonVariants('ghost', 'h-10 w-full justify-center whitespace-nowrap px-3 sm:w-auto sm:px-4')}
          >
            Configuracion
          </Link>
          <Link
            to="/dashboard/products"
            className={buttonVariants('ghost', 'h-10 w-full justify-center whitespace-nowrap px-3 sm:w-auto sm:px-4')}
          >
            Productos
          </Link>
        </div>
      </div>
    </header>
  )

  return (
    <AppShell variant="dashboard" header={header}>
      <Outlet />
    </AppShell>
  )
}
