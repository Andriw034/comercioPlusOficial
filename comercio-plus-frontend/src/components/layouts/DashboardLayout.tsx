import { useEffect, useState } from 'react'
import { Link, Outlet } from 'react-router-dom'
import API from '@/lib/api'
import type { Store } from '@/types/api'
import { buttonVariants } from '@/components/ui/button'
import AppShell from './AppShell'

export default function DashboardLayout() {
  const [store, setStore] = useState<Store | null>(() => {
    try {
      const cached = localStorage.getItem('store')
      return cached ? (JSON.parse(cached) as Store) : null
    } catch {
      return null
    }
  })

  const withCacheBust = (data: Store) => {
    const cacheBuster = Date.now().toString()
    const addBust = (url?: string) => {
      if (!url) return url
      const [base, query] = url.split('?')
      const params = new URLSearchParams(query || '')
      params.set('v', cacheBuster)
      return `${base}?${params.toString()}`
    }

    return {
      ...data,
      logo_url: addBust(data.logo_url),
      cover_url: addBust(data.cover_url),
    }
  }

  useEffect(() => {
    let active = true
    const loadStore = async () => {
      try {
        const { data } = await API.get('/my/store')
        if (!active || !data) return
        const normalized = withCacheBust(data)
        setStore(normalized)
        localStorage.setItem('store', JSON.stringify(normalized))
      } catch (err) {
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
  }, [])

  const storeName = store?.name || 'ComercioPlus'
  const storeLogo = store?.logo_url
  const storeCover = store?.cover_url

  const header = (
    <header className="sticky top-0 z-30 px-4 pt-4">
      <div className="mx-auto max-w-7xl glass rounded-2xl px-4 py-3 flex items-center justify-between gap-4 relative overflow-hidden">
        {storeCover && (
          <div className="absolute inset-0">
            <img src={storeCover} alt="" className="h-full w-full object-cover opacity-25" />
            <div className="absolute inset-0 bg-slate-900/70" />
          </div>
        )}
        <Link to="/" className="relative flex items-center gap-3">
          {storeLogo ? (
            <img
              src={storeLogo}
              alt={`Logo ${storeName}`}
              className="h-10 w-10 rounded-2xl object-cover border border-white/10 bg-white"
            />
          ) : (
            <span className="flex h-10 w-10 items-center justify-center rounded-2xl bg-brand-500 text-base font-bold text-white">CP</span>
          )}
          <div className="leading-tight">
            <p className="font-semibold text-white">{storeName}</p>
            <p className="text-xs text-white/60">Panel del comerciante</p>
          </div>
        </Link>
        <div className="relative flex items-center gap-3 text-sm">
          <Link to="/stores" className={buttonVariants('ghost')}>Ver tiendas</Link>
          <Link to="/products" className={buttonVariants('ghost')}>Productos</Link>
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
