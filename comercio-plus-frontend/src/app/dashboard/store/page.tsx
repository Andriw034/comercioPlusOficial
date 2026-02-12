import { useEffect, useState } from 'react'
import API from '@/lib/api'
import Button, { buttonVariants } from '@/components/ui/button'
import Input from '@/components/ui/Input'
import Textarea from '@/components/ui/Textarea'
import GlassCard from '@/components/ui/GlassCard'
import Badge from '@/components/ui/Badge'
import { resolveMediaUrl } from '@/lib/format'
import type { Store } from '@/types/api'
import { uploadStoreCover, uploadStoreLogo } from '@/services/uploads'

const MAX_IMAGE_SIZE_BYTES = 5 * 1024 * 1024
const ALLOWED_IMAGE_MIME_TYPES = new Set([
  'image/jpeg',
  'image/png',
  'image/webp',
  'image/avif',
])

const emptyStore: Store = {
  id: 0,
  name: '',
  slug: '',
  description: '',
  is_visible: true,
}

export default function ManageStore() {
  const [store, setStore] = useState<Store>(emptyStore)
  const [files, setFiles] = useState<{ logo: File | null; cover: File | null }>({ logo: null, cover: null })
  const [previews, setPreviews] = useState<{ logo: string; cover: string }>({ logo: '', cover: '' })
  const [submitting, setSubmitting] = useState(false)
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')

  const loadStore = async () => {
    try {
      const { data } = await API.get('/my/store')
      if (data) {
        setStore(data)
        setPreviews({
          logo: resolveMediaUrl(data.logo_url || data.logo_path || data.logo) || '',
          cover: resolveMediaUrl(data.cover_url || data.cover_path || data.background_url || data.cover) || '',
        })
      }
    } catch (err) {
      console.error('Load store', err)
      setStore(emptyStore)
    }
  }

  const onFileSelect = (event: React.ChangeEvent<HTMLInputElement>, type: 'logo' | 'cover') => {
    const file = event.target.files?.[0] || null
    setError('')

    if (file && !ALLOWED_IMAGE_MIME_TYPES.has(file.type)) {
      setFiles((prev) => ({ ...prev, [type]: null }))
      setError('Formato no permitido. Usa JPG, PNG, WEBP o AVIF.')
      return
    }

    if (file && file.size > MAX_IMAGE_SIZE_BYTES) {
      setFiles((prev) => ({ ...prev, [type]: null }))
      setError('La imagen supera 5MB. Selecciona un archivo mas liviano.')
      return
    }

    setFiles((prev) => ({ ...prev, [type]: file }))
    if (file) {
      const url = URL.createObjectURL(file)
      setPreviews((prev) => ({ ...prev, [type]: url }))
    }
  }

  const submit = async (event: React.FormEvent) => {
    event.preventDefault()
    setSubmitting(true)
    setMessage('')
    setError('')

    try {
      let logoUrl = store.logo_url || ''
      let coverUrl = store.cover_url || store.background_url || ''

      if (files.logo) {
        const upload = await uploadStoreLogo(files.logo)
        logoUrl = upload.url
      }

      if (files.cover) {
        const upload = await uploadStoreCover(files.cover)
        coverUrl = upload.url
      }

      const payload: Record<string, string | boolean> = {
        name: store.name,
        description: store.description || '',
        is_visible: !!store.is_visible,
      }
      if (store.slug) payload.slug = store.slug
      if (store.phone) payload.phone = store.phone
      if (store.whatsapp) payload.whatsapp = store.whatsapp
      if (store.support_email) payload.support_email = store.support_email
      if (store.facebook) payload.facebook = store.facebook
      if (store.instagram) payload.instagram = store.instagram
      if (store.address) payload.address = store.address
      if (logoUrl) payload.logo_url = logoUrl
      if (coverUrl) payload.cover_url = coverUrl

      let response
      if (store.id) {
        response = await API.put(`/stores/${store.id}`, payload)
      } else {
        response = await API.post('/stores', payload)
      }

      const cacheBuster = Date.now()
      const updatedStore = {
        ...response.data,
        logo_url: response.data.logo_url
          ? `${response.data.logo_url}${response.data.logo_url.includes('?') ? '&' : '?'}v=${cacheBuster}`
          : response.data.logo_url,
        cover_url: response.data.cover_url
          ? `${response.data.cover_url}${response.data.cover_url.includes('?') ? '&' : '?'}v=${cacheBuster}`
          : response.data.cover_url,
      }

      setStore(updatedStore)
      setPreviews((prev) => ({
        logo: resolveMediaUrl(updatedStore.logo_url || updatedStore.logo_path || updatedStore.logo) || prev.logo,
        cover: resolveMediaUrl(updatedStore.cover_url || updatedStore.cover_path || updatedStore.background_url || updatedStore.cover) || prev.cover,
      }))
      localStorage.setItem('store', JSON.stringify(updatedStore))
      window.dispatchEvent(new CustomEvent('store:updated', { detail: updatedStore }))
      setMessage('Guardado correctamente')
    } catch (err: any) {
      console.error('Store save', err)
      setError(err.response?.data?.message || 'Error al guardar la tienda')
    } finally {
      setSubmitting(false)
    }
  }

  useEffect(() => {
    loadStore()
  }, [])

  const previewName = store.name || 'Nombre de tu tienda'
  const previewDescription = store.description || 'Descripcion breve de tu tienda.'

  return (
    <div className="space-y-6 pb-24 md:pb-6">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div>
          <p className="text-[13px] text-slate-600 dark:text-white/60">Centro de configuracion</p>
          <h1 className="text-[22px] font-semibold text-slate-900 dark:text-white sm:text-[26px]">
            {store.id ? 'Editar tienda' : 'Crear tienda'}
          </h1>
        </div>
        <Badge variant={store.is_visible ? 'success' : 'neutral'}>{store.is_visible ? 'Visible' : 'Oculta'}</Badge>
      </div>

      <div className="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
        <GlassCard className="space-y-6 border-0 bg-transparent p-0 shadow-none dark:bg-transparent dark:shadow-none">
          <form id="store-settings-form" className="space-y-6" onSubmit={submit}>
            <section className="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-white/5 sm:p-5">
              <div>
                <h2 className="text-[18px] font-semibold text-slate-900 dark:text-white">Identidad de la tienda</h2>
                <p className="text-[13px] text-slate-600 dark:text-white/60">
                  Define nombre, portada y logo que veran tus clientes.
                </p>
              </div>

              <div className="grid gap-4 md:grid-cols-2">
                <Input
                  label="Nombre"
                  value={store.name}
                  required
                  onChange={(event) => setStore((prev) => ({ ...prev, name: event.target.value }))}
                />
                <Input
                  label="Slug (opcional)"
                  value={store.slug || ''}
                  onChange={(event) => setStore((prev) => ({ ...prev, slug: event.target.value }))}
                />
              </div>

              <Textarea
                label="Descripcion"
                rows={3}
                value={store.description || ''}
                onChange={(event) => setStore((prev) => ({ ...prev, description: event.target.value }))}
              />

              <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-slate-950/40">
                  <div>
                    <p className="text-[13px] font-semibold text-slate-900 dark:text-white">Logo</p>
                    <p className="text-[12px] text-slate-500 dark:text-white/60">Logo recomendado: 512x512 px, fondo transparente.</p>
                  </div>

                  <label className={buttonVariants('secondary', 'inline-flex cursor-pointer')}>
                    Subir logo
                    <input type="file" accept="image/jpeg,image/png,image/webp,image/avif" onChange={(event) => onFileSelect(event, 'logo')} className="hidden" />
                  </label>

                  {previews.logo && (
                    <div className="h-28 w-28 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-white/10">
                      <img src={previews.logo} alt="Preview logo" className="h-full w-full object-contain p-1" />
                    </div>
                  )}
                  {!previews.logo && (
                    <div className="flex h-28 w-28 items-center justify-center rounded-2xl border border-dashed border-slate-300 text-[12px] text-slate-500 dark:border-white/20 dark:text-white/60">
                      Sin logo
                    </div>
                  )}
                </div>

                <div className="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-slate-950/40">
                  <div>
                    <p className="text-[13px] font-semibold text-slate-900 dark:text-white">Portada</p>
                    <p className="text-[12px] text-slate-500 dark:text-white/60">Portada recomendada: 1600x600 px para el hero publico.</p>
                  </div>

                  <label className={buttonVariants('secondary', 'inline-flex cursor-pointer')}>
                    Subir portada
                    <input type="file" accept="image/jpeg,image/png,image/webp,image/avif" onChange={(event) => onFileSelect(event, 'cover')} className="hidden" />
                  </label>

                  {previews.cover && (
                    <div className="h-36 w-full overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-white/10">
                      <img src={previews.cover} alt="Preview portada" className="h-full w-full object-cover" />
                    </div>
                  )}
                  {!previews.cover && (
                    <div className="flex h-36 w-full items-center justify-center rounded-2xl border border-dashed border-slate-300 text-[12px] text-slate-500 dark:border-white/20 dark:text-white/60">
                      Sin portada
                    </div>
                  )}
                </div>
              </div>
            </section>

            <section className="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-white/5 sm:p-5">
              <div>
                <h3 className="text-[16px] font-semibold text-slate-900 dark:text-white">Contacto</h3>
                <p className="text-[13px] text-slate-600 dark:text-white/60">Datos principales para clientes.</p>
              </div>

              <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <Input
                  label="Telefono"
                  value={store.phone || ''}
                  onChange={(event) => setStore((prev) => ({ ...prev, phone: event.target.value }))}
                  placeholder="Ej: 3001234567"
                />
                <Input
                  label="WhatsApp"
                  value={store.whatsapp || ''}
                  onChange={(event) => setStore((prev) => ({ ...prev, whatsapp: event.target.value }))}
                  placeholder="Ej: 3001234567"
                />
                <Input
                  type="email"
                  label="Correo soporte"
                  value={store.support_email || ''}
                  onChange={(event) => setStore((prev) => ({ ...prev, support_email: event.target.value }))}
                  placeholder="correo@tienda.com"
                />
              </div>
            </section>

            <section className="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-white/5 sm:p-5">
              <div>
                <h3 className="text-[16px] font-semibold text-slate-900 dark:text-white">Redes</h3>
                <p className="text-[13px] text-slate-600 dark:text-white/60">Conecta canales sociales para dar confianza.</p>
              </div>

              <div className="grid gap-4 md:grid-cols-2">
                <Input
                  label="Facebook (URL)"
                  value={store.facebook || ''}
                  onChange={(event) => setStore((prev) => ({ ...prev, facebook: event.target.value }))}
                  placeholder="https://facebook.com/tu-tienda"
                />
                <Input
                  label="Instagram (URL)"
                  value={store.instagram || ''}
                  onChange={(event) => setStore((prev) => ({ ...prev, instagram: event.target.value }))}
                  placeholder="https://instagram.com/tu-tienda"
                />
              </div>
            </section>

            <section className="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-white/5 sm:p-5">
              <div>
                <h3 className="text-[16px] font-semibold text-slate-900 dark:text-white">Ubicacion</h3>
                <p className="text-[13px] text-slate-600 dark:text-white/60">Direccion para contacto y referencias de entrega.</p>
              </div>

              <Input
                label="Direccion"
                value={store.address || ''}
                onChange={(event) => setStore((prev) => ({ ...prev, address: event.target.value }))}
                placeholder="Calle 123 #45-67"
              />
            </section>

            <section className="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-white/5 sm:p-5">
              <div>
                <h3 className="text-[16px] font-semibold text-slate-900 dark:text-white">Visibilidad</h3>
                <p className="text-[13px] text-slate-600 dark:text-white/60">Controla si tu tienda aparece en el listado publico.</p>
              </div>

              <label className="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/10 dark:bg-slate-950/40">
                <div>
                  <p className="text-[14px] font-medium text-slate-900 dark:text-white">Visible al publico</p>
                  <p className="text-[12px] text-slate-500 dark:text-white/60">Si lo desactivas, tu tienda no aparecera en la vista publica.</p>
                </div>
                <input
                  type="checkbox"
                  checked={!!store.is_visible}
                  onChange={(event) => setStore((prev) => ({ ...prev, is_visible: event.target.checked }))}
                  className="h-5 w-5 rounded border-slate-300 text-brand-500 focus:ring-brand-500/60 dark:border-white/20 dark:bg-white/10"
                />
              </label>
            </section>

            <div className="flex flex-wrap items-center gap-3 border-t border-slate-200 pt-1 dark:border-white/10">
              <Button type="submit" className="w-full md:w-auto" loading={submitting}>
                {submitting ? 'Guardando...' : store.id ? 'Actualizar tienda' : 'Crear tienda'}
              </Button>
              {message && <span className="text-[12px] text-green-600 dark:text-green-300">{message}</span>}
              {error && <span className="text-[12px] text-red-600 dark:text-red-300">{error}</span>}
            </div>
          </form>
        </GlassCard>

        <div className="space-y-6 xl:sticky xl:top-[102px] xl:self-start">
          <GlassCard className="space-y-4">
            <div>
              <h2 className="text-[18px] font-semibold text-slate-900 dark:text-white">Vista previa</h2>
              <p className="text-[13px] text-slate-600 dark:text-white/60">
                Asi se vera tu hero publico en /store/:id
              </p>
            </div>

            <div className="relative overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-white/5">
              {previews.cover ? (
                <img src={previews.cover} alt="Preview hero" className="absolute inset-0 h-full w-full object-cover" />
              ) : (
                <div className="absolute inset-0 bg-slate-300 dark:bg-slate-900" />
              )}

              <div className="absolute inset-0 bg-gradient-to-r from-slate-950/70 via-slate-950/35 to-transparent dark:from-black/75 dark:via-black/40" />

              <div className="relative flex h-[240px] flex-col justify-end p-4">
                <div className="flex items-center gap-3">
                  {previews.logo ? (
                    <img src={previews.logo} alt="Preview logo" className="h-12 w-12 rounded-xl border border-white/20 bg-white/90 object-contain p-1" />
                  ) : (
                    <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-white/15 text-white/80">
                      {previewName.slice(0, 1).toUpperCase()}
                    </div>
                  )}

                  <div>
                    <p className="text-[18px] font-semibold text-white">{previewName}</p>
                    <p className="line-clamp-2 text-[12px] text-white/75">{previewDescription}</p>
                  </div>
                </div>

                <div className="mt-3 inline-flex w-fit items-center rounded-full bg-brand-500/20 px-3 py-1 text-[12px] font-semibold text-white ring-1 ring-white/15">
                  {store.is_visible ? 'Visible al publico' : 'Oculta temporalmente'}
                </div>
              </div>
            </div>
          </GlassCard>

          <GlassCard className="space-y-4">
            <div>
              <h3 className="text-[16px] font-semibold text-slate-900 dark:text-white">Apariencia</h3>
              <p className="text-[13px] text-slate-600 dark:text-white/60">Proximamente podras personalizar estilo y color de acento.</p>
            </div>

            <div className="space-y-3">
              <div className="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-3 dark:border-white/20 dark:bg-white/5">
                <p className="text-[13px] font-medium text-slate-900 dark:text-white">Color de acento</p>
                <p className="text-[12px] text-slate-500 dark:text-white/60">Proximamente</p>
              </div>
              <div className="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-3 dark:border-white/20 dark:bg-white/5">
                <p className="text-[13px] font-medium text-slate-900 dark:text-white">Estilo de botones</p>
                <p className="text-[12px] text-slate-500 dark:text-white/60">Proximamente</p>
              </div>
            </div>
          </GlassCard>
        </div>
      </div>

      <div className="fixed inset-x-4 bottom-4 z-20 rounded-2xl border border-slate-200 bg-white/95 p-3 shadow-lg backdrop-blur-sm dark:border-white/10 dark:bg-slate-950/90 md:hidden">
        <Button type="submit" form="store-settings-form" className="w-full" loading={submitting}>
          {submitting ? 'Guardando...' : 'Guardar cambios'}
        </Button>
      </div>
    </div>
  )
}
