import { useEffect, useState } from 'react'
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

export default function DashboardLayout() {
  const [store, setStore] = useState<Store | null>(null)
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    loadStoreData()
  }, [])

  const loadStoreData = async () => {
    try {
      const { data } = await API.get('/my/store')
      const mapped: Store = {
        id: data?.id || '',
        name: data?.name || 'ComercioPlus',
        logo: resolveMediaUrl(data?.logo_url || data?.logo_path || data?.logo) || '',
        cover: resolveMediaUrl(data?.cover_url || data?.cover_path || data?.background_url || data?.cover) || '',
      }
      setStore(mapped)
      localStorage.setItem('store', JSON.stringify(data || mapped))
    } catch (error: any) {
      if (error?.response?.status === 404) {
        localStorage.removeItem('store')
        setStore({ id: '', name: 'ComercioPlus', logo: '', cover: '' })
        return
      }

      try {
        const cached = localStorage.getItem('store')
        const parsed = cached ? JSON.parse(cached) : null
        if (parsed) {
          setStore({
            id: parsed.id || '',
            name: parsed.name || 'ComercioPlus',
            logo: resolveMediaUrl(parsed.logo_url || parsed.logo_path || parsed.logo) || '',
            cover: resolveMediaUrl(parsed.cover_url || parsed.cover_path || parsed.background_url || parsed.cover) || '',
          })
        } else {
          setStore({ id: '', name: 'ComercioPlus', logo: '', cover: '' })
        }
      } catch {
        setStore({ id: '', name: 'ComercioPlus', logo: '', cover: '' })
      }
    } finally {
      setIsLoading(false)
    }
  }

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

  return (
    <div className="flex min-h-screen bg-[#f0f2f7]">
      <Sidebar />

      <main className="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 md:p-8">
        <Outlet context={{ store }} />
      </main>
    </div>
  )
}
