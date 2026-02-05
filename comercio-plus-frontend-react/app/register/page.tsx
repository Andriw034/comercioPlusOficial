import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import API from '@/lib/api'
import Button from '@/components/ui/Button'
import Input from '@/components/ui/Input'
import Select from '@/components/ui/Select'

export default function Register() {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [form, setForm] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'merchant',
  })

  const resolvePostAuthRoute = async (role?: string) => {
    if (role === 'merchant') {
      try {
        await API.get('/my/store')
        return '/dashboard'
      } catch {
        return '/store/create'
      }
    }
    return '/stores'
  }

  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault()
    setLoading(true)
    setError('')

    try {
      const { data } = await API.post('/register', {
        name: form.name,
        email: form.email,
        password: form.password,
        password_confirmation: form.password_confirmation,
        role: form.role,
      })

      if (data) {
        localStorage.setItem('user', JSON.stringify(data.user))
        if (data.token) {
          localStorage.setItem('token', data.token)
          API.defaults.headers.common.Authorization = `Bearer ${data.token}`
        }
        const route = await resolvePostAuthRoute(data.user?.role)
        navigate(route)
      }
    } catch (err: any) {
      console.error('Register error:', err)
      setError(err.response?.data?.message || 'Error al crear la cuenta. Verifica tus datos.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="space-y-6">
      <div className="space-y-1">
        <p className="text-sm text-muted">Crea tu cuenta para vender o comprar</p>
        <h1 className="text-3xl font-semibold text-white">Crear cuenta</h1>
      </div>

      <form className="space-y-5" onSubmit={handleSubmit}>
        <div className="space-y-4">
          <div className="space-y-1">
            <label htmlFor="name" className="text-sm text-muted">Nombre</label>
            <Input
              id="name"
              name="name"
              type="text"
              autoComplete="name"
              required
              placeholder="Nombre completo"
              value={form.name}
              onChange={(e) => setForm((prev) => ({ ...prev, name: e.target.value }))}
            />
          </div>

          <div className="space-y-1">
            <label htmlFor="email" className="text-sm text-muted">Correo electrónico</label>
            <Input
              id="email"
              name="email"
              type="email"
              autoComplete="email"
              required
              placeholder="tu@correo.com"
              value={form.email}
              onChange={(e) => setForm((prev) => ({ ...prev, email: e.target.value }))}
            />
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-1">
              <label htmlFor="password" className="text-sm text-muted">Contraseña</label>
              <Input
                id="password"
                name="password"
                type="password"
                autoComplete="new-password"
                required
                placeholder="********"
                value={form.password}
                onChange={(e) => setForm((prev) => ({ ...prev, password: e.target.value }))}
              />
            </div>
            <div className="space-y-1">
              <label htmlFor="password_confirmation" className="text-sm text-muted">Confirmar contraseña</label>
              <Input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                autoComplete="new-password"
                required
                placeholder="Repite tu contraseña"
                value={form.password_confirmation}
                onChange={(e) => setForm((prev) => ({ ...prev, password_confirmation: e.target.value }))}
              />
            </div>
          </div>

          <div className="space-y-1">
            <label htmlFor="role" className="text-sm text-muted">Rol</label>
            <Select
              id="role"
              name="role"
              value={form.role}
              onChange={(e) => setForm((prev) => ({ ...prev, role: e.target.value }))}
            >
              <option value="merchant">Comerciante</option>
              <option value="client">Cliente</option>
            </Select>
          </div>
        </div>

        {error && (
          <div className="rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-100">
            {error}
          </div>
        )}

        <Button type="submit" className="w-full md:w-auto justify-center" loading={loading}>
          {loading ? (
            <span className="flex items-center gap-2">
              <svg className="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none">
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              Creando...
            </span>
          ) : (
            <span>Crear cuenta</span>
          )}
        </Button>

        <p className="text-center text-sm text-muted">
          ¿Ya tienes cuenta?
          <Link to="/login" className="text-brand-200 hover:text-white font-medium"> Inicia sesión</Link>
        </p>
      </form>
    </div>
  )
}


