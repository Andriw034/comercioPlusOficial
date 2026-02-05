import { useEffect, useState } from 'react'
import API from '@/lib/api'
import Button from '@/components/ui/Button'
import Input from '@/components/ui/Input'
import Textarea from '@/components/ui/Textarea'
import type { Store } from '@/types/api'

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
          logo: data.logo_url || '',
          cover: data.cover_url || '',
        })
      }
    } catch (err) {
      console.error('Load store', err)
      setStore(emptyStore)
    }
  }

  const onFileSelect = (event: React.ChangeEvent<HTMLInputElement>, type: 'logo' | 'cover') => {
    const file = event.target.files?.[0] || null
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
      const form = new FormData()
      form.append('name', store.name)
      if (store.slug) form.append('slug', store.slug)
      form.append('description', store.description || '')
      form.append('is_visible', store.is_visible ? '1' : '0')
      if (files.logo) form.append('logo', files.logo)
      if (files.cover) form.append('cover', files.cover)

      let response
      if (store.id) {
        form.append('_method', 'PUT')
        response = await API.post(`/stores/${store.id}`, form, { headers: { 'Content-Type': 'multipart/form-data' } })
      } else {
        response = await API.post('/stores', form, { headers: { 'Content-Type': 'multipart/form-data' } })
      }

      setStore(response.data)
      setPreviews((prev) => ({
        logo: response.data.logo_url || prev.logo,
        cover: response.data.cover_url || prev.cover,
      }))
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

  return (
    <div className="space-y-8">
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm text-muted">Configura tu tienda</p>
          <h1 className="text-2xl font-semibold text-white">{store.id ? 'Editar tienda' : 'Crear tienda'}</h1>
        </div>
        <span className={`chip ${store.is_visible ? 'border-green-400/60 text-green-200' : ''}`}>
          {store.is_visible ? 'Visible' : 'Oculta'}
        </span>
      </div>

      <form className="glass rounded-3xl p-6 space-y-6" onSubmit={submit}>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div className="space-y-2">
            <label className="text-sm text-muted">Nombre</label>
            <Input value={store.name} required onChange={(e) => setStore((prev) => ({ ...prev, name: e.target.value }))} />
          </div>
          <div className="space-y-2">
            <label className="text-sm text-muted">Slug (opcional)</label>
            <Input value={store.slug || ''} onChange={(e) => setStore((prev) => ({ ...prev, slug: e.target.value }))} />
          </div>
        </div>

        <div className="space-y-2">
          <label className="text-sm text-muted">Descripción</label>
          <Textarea
            rows={3}
            value={store.description || ''}
            onChange={(e) => setStore((prev) => ({ ...prev, description: e.target.value }))}
          />
        </div>

        <div className="flex items-center gap-3">
          <label className="text-sm text-muted">Visible al público</label>
          <input
            type="checkbox"
            checked={!!store.is_visible}
            onChange={(e) => setStore((prev) => ({ ...prev, is_visible: e.target.checked }))}
            className="h-4 w-4 rounded border-white/20 bg-white/5 text-brand-500 focus:ring-brand-500/60"
          />
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div className="space-y-3">
            <p className="text-sm text-muted">Logo</p>
            <div className="rounded-2xl border border-dashed border-white/15 bg-white/5 p-4 flex flex-col items-center gap-3">
              <label className="btn-secondary cursor-pointer w-full text-center">
                Subir logo
                <input type="file" accept="image/*" onChange={(e) => onFileSelect(e, 'logo')} className="hidden" />
              </label>
              {previews.logo && (
                <div className="w-24 h-24 rounded-2xl overflow-hidden border border-white/10">
                  <img src={previews.logo} className="w-full h-full object-cover" />
                </div>
              )}
            </div>
          </div>
          <div className="space-y-3">
            <p className="text-sm text-muted">Portada</p>
            <div className="rounded-2xl border border-dashed border-white/15 bg-white/5 p-4 flex flex-col items-center gap-3">
              <label className="btn-secondary cursor-pointer w-full text-center">
                Subir portada
                <input type="file" accept="image/*" onChange={(e) => onFileSelect(e, 'cover')} className="hidden" />
              </label>
              {previews.cover && (
                <div className="w-full h-28 rounded-2xl overflow-hidden border border-white/10">
                  <img src={previews.cover} className="w-full h-full object-cover" />
                </div>
              )}
            </div>
          </div>
        </div>

        <div className="flex flex-wrap items-center gap-3">
          <Button type="submit" className="w-full md:w-auto" loading={submitting}>
            {submitting ? 'Guardando...' : store.id ? 'Actualizar tienda' : 'Crear tienda'}
          </Button>
          {message && <span className="text-sm text-green-200">{message}</span>}
          {error && <span className="text-sm text-red-200">{error}</span>}
        </div>
      </form>
    </div>
  )
}

