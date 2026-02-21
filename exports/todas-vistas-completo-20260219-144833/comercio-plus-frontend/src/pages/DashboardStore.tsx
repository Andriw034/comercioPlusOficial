import { useEffect, useState } from 'react'
import Badge from '@/components/Badge'
import Button from '@/components/Button'
import Card from '@/components/Card'
import Input from '@/components/Input'
import API from '@/lib/api'
import { resolveMediaUrl } from '@/lib/format'
import { uploadStoreCover, uploadStoreLogo } from '@/services/uploads'
import { generatePreview, validateImage } from '@/utils/imageUtils'

interface StoreData {
  id: number
  name: string
  description: string
  email: string
  phone: string
  address: string
  isVisible: boolean
  status: 'active' | 'inactive'
  logo: string | null
  cover: string | null
}

const fallbackStore: StoreData = {
  id: 0,
  name: 'Accesorios Biker Colombia',
  description: 'Accesorios, cascos y repuestos para motos en toda Colombia.',
  email: 'ventas@bikercolombia.com',
  phone: '+57 300 123 4567',
  address: 'Bogota, Colombia',
  isVisible: true,
  status: 'active',
  logo: null,
  cover: null,
}

const autoDraftValues = {
  name: ['Accesorios Biker Colombia', 'Artesanias del Valle'],
  description: [
    'Accesorios, cascos y repuestos para motos en toda Colombia.',
    'Productos artesanales hechos a mano con materiales naturales.',
  ],
  email: ['ventas@bikercolombia.com', 'contacto@artesanias.com'],
  phone: ['+57 300 123 4567', '+56 9 9999 9999'],
  address: ['Bogota, Colombia', 'Santiago, Chile'],
} as const

export default function DashboardStore() {
  const [isEditing, setIsEditing] = useState(false)
  const [originalData, setOriginalData] = useState<StoreData>(fallbackStore)
  const [storeData, setStoreData] = useState<StoreData>(fallbackStore)
  const [loading, setLoading] = useState(true)
  const [loadError, setLoadError] = useState('')
  const [storeNotFound, setStoreNotFound] = useState(false)
  const [files, setFiles] = useState<{ logo: File | null; cover: File | null }>({ logo: null, cover: null })
  const [errors, setErrors] = useState<{ logo?: string; cover?: string }>({})

  const stats = {
    revenue: 24600000,
    orders: 382,
    customers: 284,
    totalProducts: 156,
    activeProducts: 142,
    avgRating: 4.8,
  }

  useEffect(() => {
    const loadStore = async () => {
      try {
        setLoadError('')
        setStoreNotFound(false)
        const { data } = await API.get('/my/store')
        if (data) {
          const mapped: StoreData = {
            id: data.id || 0,
            name: data.name || '',
            description: data.description || '',
            email: data.support_email || '',
            phone: data.phone || '',
            address: data.address || '',
            isVisible: Boolean(data.is_visible),
            status: data.is_visible ? 'active' : 'inactive',
            logo: resolveMediaUrl(data.logo_url || data.logo_path || data.logo) || null,
            cover: resolveMediaUrl(data.cover_url || data.cover_path || data.background_url || data.cover) || null,
          }
          setOriginalData(mapped)
          setStoreData(mapped)
          localStorage.setItem('store', JSON.stringify(data))
        }
      } catch (error: any) {
        if (error?.response?.status === 404) {
          setStoreNotFound(true)
          setLoadError('')
          setOriginalData(fallbackStore)
          setStoreData(fallbackStore)
          setIsEditing(true)
          return
        }

        const message =
          error?.response?.data?.message ||
          error?.message ||
          'No se pudo conectar con la API para cargar la tienda.'
        setLoadError(message)
      } finally {
        setLoading(false)
      }
    }

    loadStore()
  }, [])

  const handleEdit = () => setIsEditing(true)

  const handleCancel = () => {
    setStoreData({ ...originalData })
    setFiles({ logo: null, cover: null })
    setErrors({})
    setIsEditing(false)
  }

  const onImageChange = async (event: React.ChangeEvent<HTMLInputElement>, type: 'logo' | 'cover') => {
    const file = event.target.files?.[0] || null

    if (!file) {
      setFiles((prev) => ({ ...prev, [type]: null }))
      setErrors((prev) => ({ ...prev, [type]: '' }))
      return
    }

    const validation = await validateImage(file, type)
    if (!validation.valid) {
      setFiles((prev) => ({ ...prev, [type]: null }))
      setErrors((prev) => ({ ...prev, [type]: validation.error || 'Imagen invalida.' }))
      return
    }

    const preview = await generatePreview(file)
    setFiles((prev) => ({ ...prev, [type]: file }))
    setStoreData((prev) => ({ ...prev, [type]: preview }))
    setErrors((prev) => ({ ...prev, [type]: '' }))
  }

  const handleSave = async () => {
    try {
      let logoUrl = storeData.logo || ''
      let coverUrl = storeData.cover || ''

      if (files.logo) {
        const upload = await uploadStoreLogo(files.logo)
        logoUrl = upload.url
      }

      if (files.cover) {
        const upload = await uploadStoreCover(files.cover)
        coverUrl = upload.url
      }

      const payload: Record<string, string | boolean> = {
        name: storeData.name,
        description: storeData.description,
        support_email: storeData.email,
        phone: storeData.phone,
        address: storeData.address,
        is_visible: storeData.isVisible,
      }

      const isHttpUrl = (value: string) => /^https?:\/\//i.test(value.trim())
      if (logoUrl && isHttpUrl(logoUrl)) payload.logo_url = logoUrl
      if (coverUrl && isHttpUrl(coverUrl)) payload.cover_url = coverUrl

      let response
      if (storeData.id) {
        response = await API.put(`/stores/${storeData.id}`, payload)
      } else {
        response = await API.post('/stores', payload)
      }

      const responseData = response?.data || {}
      const merged: StoreData = {
        ...storeData,
        id: responseData.id || storeData.id,
        status: storeData.isVisible ? 'active' : 'inactive',
        logo: resolveMediaUrl(responseData.logo_url || responseData.logo_path || responseData.logo) || logoUrl || storeData.logo,
        cover: resolveMediaUrl(responseData.cover_url || responseData.cover_path || responseData.background_url || responseData.cover) || coverUrl || storeData.cover,
      }

      setStoreData(merged)
      setOriginalData(merged)
      setStoreNotFound(false)
      setFiles({ logo: null, cover: null })
      setIsEditing(false)
      localStorage.setItem('store', JSON.stringify(responseData || merged))
      window.dispatchEvent(new CustomEvent('store:updated', { detail: responseData || merged }))
      alert('Cambios guardados exitosamente')
    } catch (error: any) {
      const firstFieldError = error?.response?.data?.errors
        ? Object.values(error.response.data.errors).flat?.()[0]
        : ''
      alert(firstFieldError || error?.response?.data?.message || error?.message || 'Error al guardar cambios')
    }
  }

  const clearDraftValueOnFocus = (
    field: 'name' | 'description' | 'email' | 'phone' | 'address',
    currentValue: string,
  ) => {
    if (!isEditing) return false

    const normalizedCurrent = currentValue.trim().toLowerCase()
    if (!normalizedCurrent) return false

    const shouldClear = autoDraftValues[field].some(
      (value) => value.trim().toLowerCase() === normalizedCurrent,
    )

    if (!shouldClear) return false

    setStoreData((prev) => ({ ...prev, [field]: '' }))
    return true
  }

  return (
    <div className="relative">
      {storeData.cover && (
        <div className="pointer-events-none fixed inset-0 z-0 lg:left-[260px]">
          <div
            className="absolute inset-0 scale-[1.02] bg-cover bg-center bg-no-repeat saturate-125"
            style={{ backgroundImage: `url(${storeData.cover})` }}
          />
          <div className="absolute inset-0 bg-gradient-to-b from-slate-900/18 via-white/20 to-white/45" />
        </div>
      )}

      <div className="relative z-10 space-y-6">
        {storeNotFound && (
          <div className="rounded-xl border border-sky-300 bg-sky-50 px-4 py-3 text-[13px] text-sky-900">
            Aun no tienes una tienda creada. Completa los datos y presiona guardar para crearla.
          </div>
        )}

        {loadError && !storeNotFound && (
          <div className="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-[13px] text-amber-900">
            No se pudo cargar la tienda desde la API. {loadError}
          </div>
        )}

        <section className="relative overflow-hidden rounded-3xl border border-slate-200/70 bg-gradient-to-br from-white/88 via-slate-50/82 to-brand-50/38 p-8 shadow-premium backdrop-blur-sm lg:p-10">
          <div className="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-brand-500/10 blur-3xl" />
          <div className="relative z-10 flex flex-wrap items-end justify-between gap-4">
            <div>
              <h1 className="mb-2 text-display-sm text-slate-950">Panel de tienda</h1>
              <p className="text-body-lg text-slate-600">Controla tu marca, catalogo y rendimiento desde un solo lugar.</p>
            </div>
            <div className="flex gap-3">
              {!isEditing ? (
                <Button variant="primary" onClick={handleEdit} disabled={loading}>
                  {storeData.id ? 'Editar tienda' : 'Crear tienda'}
                </Button>
              ) : (
                <>
                  <Button variant="outline" onClick={handleCancel}>
                    Cancelar
                  </Button>
                  <Button variant="primary" onClick={handleSave}>
                    Guardar cambios
                  </Button>
                </>
              )}
            </div>
          </div>
        </section>

        <section className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
          <Card variant="premium" padding="md" className="bg-white/88 backdrop-blur-sm">
            <p className="text-caption uppercase tracking-wide text-slate-500">Ingresos</p>
            <p className="mt-2 text-h2 text-slate-950">${(stats.revenue / 1000000).toFixed(1)}M</p>
          </Card>
          <Card variant="glass" padding="md">
            <p className="text-caption uppercase tracking-wide text-slate-500">Pedidos</p>
            <p className="mt-2 text-h2 text-slate-950">{stats.orders}</p>
          </Card>
          <Card variant="glass" padding="md">
            <p className="text-caption uppercase tracking-wide text-slate-500">Clientes</p>
            <p className="mt-2 text-h2 text-slate-950">{stats.customers}</p>
          </Card>
          <Card variant="glass" padding="md">
            <p className="text-caption uppercase tracking-wide text-slate-500">Rating</p>
            <p className="mt-2 text-h2 text-slate-950">{stats.avgRating}</p>
          </Card>
        </section>

        <section className="grid grid-cols-1 gap-6 xl:grid-cols-[1fr_360px]">
          <Card variant="glass" padding="lg" className="bg-white/88 backdrop-blur-sm">
            <h2 className="mb-6 text-h2">Informacion general</h2>

            <div className="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label className="mb-2 block text-body-sm font-semibold text-slate-900">Logo de la tienda</label>
                <div className="relative">
                  <div className="flex h-32 items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50">
                    {storeData.logo ? (
                      <img src={storeData.logo} alt="Logo" className="h-full w-full object-contain p-2" style={{ maxHeight: '112px' }} />
                    ) : (
                      <p className="text-body-sm text-slate-500">Sube tu logo</p>
                    )}
                  </div>
                  {isEditing && (
                    <input
                      type="file"
                      accept="image/png,image/jpeg,image/jpg,image/webp,image/avif"
                      onChange={(event) => onImageChange(event, 'logo')}
                      className="absolute inset-0 cursor-pointer opacity-0"
                    />
                  )}
                </div>
                <p className="mt-1 text-caption text-slate-500">Recomendado: 512x512px, maximo 2MB.</p>
                {errors.logo && <p className="mt-1 text-caption text-danger">{errors.logo}</p>}
              </div>

              <div>
                <label className="mb-2 block text-body-sm font-semibold text-slate-900">Portada de la tienda</label>
                <div className="relative">
                  <div className="flex h-32 items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50">
                    {storeData.cover ? (
                      <img src={storeData.cover} alt="Portada" className="h-full w-full object-cover" />
                    ) : (
                      <p className="text-body-sm text-slate-500">Sube tu portada</p>
                    )}
                  </div>
                  {isEditing && (
                    <input
                      type="file"
                      accept="image/png,image/jpeg,image/jpg,image/webp,image/avif"
                      onChange={(event) => onImageChange(event, 'cover')}
                      className="absolute inset-0 cursor-pointer opacity-0"
                    />
                  )}
                </div>
                <p className="mt-1 text-caption text-slate-500">Recomendado: 1920x400px, maximo 5MB.</p>
                {errors.cover && <p className="mt-1 text-caption text-danger">{errors.cover}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
              <Input
                label="Nombre de la tienda"
                value={storeData.name}
                onChange={(event) => setStoreData({ ...storeData, name: event.target.value })}
                onFocus={(event) => {
                  const cleared = clearDraftValueOnFocus('name', event.target.value)
                  if (isEditing && !cleared) event.target.select()
                }}
                readOnly={!isEditing}
                spellCheck={false}
                fullWidth
              />
              <Input
                label="Email"
                value={storeData.email}
                onChange={(event) => setStoreData({ ...storeData, email: event.target.value })}
                onFocus={(event) => {
                  const cleared = clearDraftValueOnFocus('email', event.target.value)
                  if (isEditing && !cleared) event.target.select()
                }}
                readOnly={!isEditing}
                spellCheck={false}
                fullWidth
              />
              <Input
                label="Telefono"
                value={storeData.phone}
                onChange={(event) => setStoreData({ ...storeData, phone: event.target.value })}
                onFocus={(event) => {
                  const cleared = clearDraftValueOnFocus('phone', event.target.value)
                  if (isEditing && !cleared) event.target.select()
                }}
                readOnly={!isEditing}
                spellCheck={false}
                fullWidth
              />
              <Input
                label="Direccion"
                value={storeData.address}
                onChange={(event) => setStoreData({ ...storeData, address: event.target.value })}
                onFocus={(event) => {
                  const cleared = clearDraftValueOnFocus('address', event.target.value)
                  if (isEditing && !cleared) event.target.select()
                }}
                readOnly={!isEditing}
                spellCheck={false}
                fullWidth
              />
            </div>

            <div className="mt-4">
              <label className="mb-2 block text-body-sm font-semibold text-slate-900">Descripcion</label>
              <textarea
                className="textarea-dark w-full"
                rows={4}
                value={storeData.description}
                onChange={(event) => setStoreData({ ...storeData, description: event.target.value })}
                onFocus={(event) => {
                  const cleared = clearDraftValueOnFocus('description', event.target.value)
                  if (isEditing && !cleared) event.target.select()
                }}
                readOnly={!isEditing}
                spellCheck={false}
              />
            </div>
          </Card>

          <div className="space-y-6">
            <Card variant="premium" padding="lg">
              <h3 className="mb-4 text-h3">Estado de la tienda</h3>
              <div className="space-y-3">
                <div className="flex items-center justify-between">
                  <span className="text-body text-slate-700">Visibilidad</span>
                  <Badge variant={storeData.isVisible ? 'success' : 'warning'}>
                    {storeData.isVisible ? 'Visible' : 'Oculta'}
                  </Badge>
                </div>
                <div className="flex items-center justify-between">
                  <span className="text-body text-slate-700">Estado</span>
                  <Badge variant={storeData.status === 'active' ? 'success' : 'danger'}>
                    {storeData.status === 'active' ? 'Activa' : 'Inactiva'}
                  </Badge>
                </div>
              </div>
            </Card>

            <Card variant="glass" padding="lg">
              <h3 className="mb-4 text-h3">Inventario</h3>
              <div className="space-y-2 text-body text-slate-700">
                <p>Productos activos: <strong>{stats.activeProducts}</strong></p>
                <p>Total productos: <strong>{stats.totalProducts}</strong></p>
              </div>
            </Card>
          </div>
        </section>
      </div>
    </div>
  )
}
