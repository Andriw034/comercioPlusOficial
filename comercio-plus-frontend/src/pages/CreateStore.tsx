import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import API from '@/lib/api'
import Button from '@/components/Button'
import Card from '@/components/Card'
import Input from '@/components/Input'
import Header from '@/components/Header'
import { uploadStoreCover, uploadStoreLogo } from '@/services/uploads'
import { generatePreview, validateImage } from '@/utils/imageUtils'

export default function CreateStore() {
  const navigate = useNavigate()
  const [isSubmitting, setIsSubmitting] = useState(false)

  const [formData, setFormData] = useState({
    name: '',
    description: '',
    email: '',
    phone: '',
    address: '',
  })

  const [logo, setLogo] = useState<File | null>(null)
  const [logoPreview, setLogoPreview] = useState<string | null>(null)
  const [cover, setCover] = useState<File | null>(null)
  const [coverPreview, setCoverPreview] = useState<string | null>(null)
  const [errors, setErrors] = useState<Record<string, string>>({})

  const handleLogoChange = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0] || null
    if (!file) {
      setLogo(null)
      setLogoPreview(null)
      setErrors((prev) => ({ ...prev, logo: '' }))
      return
    }

    const result = await validateImage(file, 'logo')
    if (!result.valid) {
      setLogo(null)
      setLogoPreview(null)
      setErrors((prev) => ({ ...prev, logo: result.error || 'Logo invalido.' }))
      return
    }

    setLogo(file)
    setLogoPreview(await generatePreview(file))
    setErrors((prev) => ({ ...prev, logo: '' }))
  }

  const handleCoverChange = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0] || null
    if (!file) {
      setCover(null)
      setCoverPreview(null)
      setErrors((prev) => ({ ...prev, cover: '' }))
      return
    }

    const result = await validateImage(file, 'cover')
    if (!result.valid) {
      setCover(null)
      setCoverPreview(null)
      setErrors((prev) => ({ ...prev, cover: result.error || 'Portada invalida.' }))
      return
    }

    setCover(file)
    setCoverPreview(await generatePreview(file))
    setErrors((prev) => ({ ...prev, cover: '' }))
  }

  const validateForm = () => {
    const nextErrors: Record<string, string> = {}
    if (!formData.name.trim()) nextErrors.name = 'El nombre es obligatorio.'
    if (!formData.description.trim()) nextErrors.description = 'La descripcion es obligatoria.'
    if (!formData.email.trim()) nextErrors.email = 'El email es obligatorio.'
    if (!formData.phone.trim()) nextErrors.phone = 'El telefono es obligatorio.'
    if (!formData.address.trim()) nextErrors.address = 'La direccion es obligatoria.'
    if (!logo) nextErrors.logo = 'El logo es obligatorio.'
    if (!cover) nextErrors.cover = 'La portada es obligatoria.'

    setErrors(nextErrors)
    return Object.keys(nextErrors).length === 0
  }

  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault()
    if (!validateForm()) return
    setIsSubmitting(true)

    try {
      let logoUrl = ''
      let coverUrl = ''

      if (logo) {
        const upload = await uploadStoreLogo(logo)
        logoUrl = upload.url
      }

      if (cover) {
        const upload = await uploadStoreCover(cover)
        coverUrl = upload.url
      }

      const payload: Record<string, string | boolean> = {
        name: formData.name,
        description: formData.description,
        support_email: formData.email,
        phone: formData.phone,
        address: formData.address,
        is_visible: true,
      }

      if (logoUrl) payload.logo_url = logoUrl
      if (coverUrl) payload.cover_url = coverUrl

      const { data } = await API.post('/stores', payload)
      const userRaw = localStorage.getItem('user')
      if (userRaw) {
        try {
          const user = JSON.parse(userRaw)
          localStorage.setItem('user', JSON.stringify({ ...user, storeId: data?.id || user.storeId }))
        } catch {
          // ignore malformed local user payload
        }
      }

      localStorage.setItem('store', JSON.stringify(data))
      window.dispatchEvent(new CustomEvent('store:updated', { detail: data }))
      navigate('/dashboard/store')
    } catch (error: any) {
      console.error('Error al crear tienda:', error)
      alert(error?.response?.data?.message || error?.message || 'Error al crear la tienda. Intentalo de nuevo.')
    } finally {
      setIsSubmitting(false)
    }
  }

  return (
    <div className="min-h-screen bg-slate-50">
      <Header showAuth={false} />

      <main className="mx-auto max-w-4xl px-6 py-10 lg:px-10 lg:py-14">
        <section className="mb-10 text-center">
          <h1 className="mb-3 text-display-sm text-slate-950">Crea tu tienda</h1>
          <p className="text-body-lg text-slate-600">
            Completa la informacion para activar tu espacio de comercio en ComercioPlus
          </p>
        </section>

        <form onSubmit={handleSubmit}>
          <div className="space-y-6">
            <Card variant="glass" padding="lg">
              <h2 className="mb-6 text-h2">Identidad visual</h2>

              <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                  <label className="mb-2 block text-body-sm font-semibold text-slate-900">Logo de la tienda</label>
                  <div className="relative">
                    <div
                      className={`flex h-48 items-center justify-center overflow-hidden rounded-xl border-2 border-dashed bg-slate-50 transition-colors ${
                        errors.logo ? 'border-danger' : 'border-slate-300 hover:border-comercioplus-400'
                      }`}
                    >
                      {logoPreview ? (
                        <img src={logoPreview} alt="Vista previa logo" className="h-full w-full object-contain p-4" />
                      ) : (
                        <div className="text-center">
                          <p className="text-body-sm text-slate-500">
                            Sube tu logo
                            <br />
                            <span className="text-caption text-slate-400">PNG, JPG, WEBP, AVIF</span>
                          </p>
                        </div>
                      )}
                    </div>
                    <input
                      type="file"
                      accept="image/png,image/jpeg,image/jpg,image/webp,image/avif"
                      onChange={handleLogoChange}
                      className="absolute inset-0 cursor-pointer opacity-0"
                    />
                  </div>
                  <p className="mt-1 text-caption text-slate-500">Recomendado: 512x512px, maximo 2MB.</p>
                  {errors.logo && <p className="mt-1 text-caption text-danger">{errors.logo}</p>}
                </div>

                <div>
                  <label className="mb-2 block text-body-sm font-semibold text-slate-900">Portada de la tienda</label>
                  <div className="relative">
                    <div
                      className={`flex h-48 items-center justify-center overflow-hidden rounded-xl border-2 border-dashed bg-slate-50 transition-colors ${
                        errors.cover ? 'border-danger' : 'border-slate-300 hover:border-comercioplus-400'
                      }`}
                    >
                      {coverPreview ? (
                        <img src={coverPreview} alt="Vista previa portada" className="h-full w-full object-cover" />
                      ) : (
                        <div className="text-center">
                          <p className="text-body-sm text-slate-500">
                            Sube tu portada
                            <br />
                            <span className="text-caption text-slate-400">PNG, JPG, WEBP, AVIF</span>
                          </p>
                        </div>
                      )}
                    </div>
                    <input
                      type="file"
                      accept="image/png,image/jpeg,image/jpg,image/webp,image/avif"
                      onChange={handleCoverChange}
                      className="absolute inset-0 cursor-pointer opacity-0"
                    />
                  </div>
                  <p className="mt-1 text-caption text-slate-500">Recomendado: 1920x400px, maximo 5MB.</p>
                  {errors.cover && <p className="mt-1 text-caption text-danger">{errors.cover}</p>}
                </div>
              </div>
            </Card>

            <Card variant="glass" padding="lg">
              <h2 className="mb-6 text-h2">Informacion basica</h2>
              <div className="space-y-4">
                <Input
                  label="Nombre de la tienda"
                  placeholder="Ej: Artesanias del Valle"
                  value={formData.name}
                  onChange={(event) => setFormData({ ...formData, name: event.target.value })}
                  spellCheck={false}
                  fullWidth
                  required
                />
                {errors.name && <p className="-mt-2 text-caption text-danger">{errors.name}</p>}

                <div>
                  <label className="mb-2 block text-body-sm font-semibold text-slate-900">Descripcion</label>
                  <textarea
                    className="textarea-dark w-full"
                    rows={4}
                    placeholder="Describe tu tienda y los productos que ofreces..."
                    value={formData.description}
                    onChange={(event) => setFormData({ ...formData, description: event.target.value })}
                    spellCheck={false}
                    required
                  />
                  {errors.description && <p className="mt-1 text-caption text-danger">{errors.description}</p>}
                </div>
              </div>
            </Card>

            <Card variant="glass" padding="lg">
              <h2 className="mb-6 text-h2">Datos de contacto</h2>

              <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                <Input
                  label="Email"
                  type="email"
                  placeholder="contacto@tutienda.com"
                  value={formData.email}
                  onChange={(event) => setFormData({ ...formData, email: event.target.value })}
                  spellCheck={false}
                  fullWidth
                  required
                />
                {errors.email && <p className="-mt-2 text-caption text-danger">{errors.email}</p>}
                <Input
                  label="Telefono"
                  type="tel"
                  placeholder="+56 9 9999 9999"
                  value={formData.phone}
                  onChange={(event) => setFormData({ ...formData, phone: event.target.value })}
                  spellCheck={false}
                  fullWidth
                  required
                />
                {errors.phone && <p className="-mt-2 text-caption text-danger">{errors.phone}</p>}
              </div>

              <div className="mt-4">
                <Input
                  label="Direccion"
                  placeholder="Santiago, Chile"
                  value={formData.address}
                  onChange={(event) => setFormData({ ...formData, address: event.target.value })}
                  spellCheck={false}
                  fullWidth
                  required
                />
                {errors.address && <p className="mt-1 text-caption text-danger">{errors.address}</p>}
              </div>
            </Card>

            <div className="flex justify-end gap-4">
              <Button type="button" variant="outline" onClick={() => navigate('/')} disabled={isSubmitting}>
                Cancelar
              </Button>
              <Button type="submit" variant="primary" size="lg" loading={isSubmitting} disabled={isSubmitting}>
                {isSubmitting ? 'Creando tienda...' : 'Crear tienda'}
              </Button>
            </div>
          </div>
        </form>
      </main>
    </div>
  )
}
