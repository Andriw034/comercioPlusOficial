import { useState } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router-dom'
import API from '@/lib/api'
import Button from '@/components/ui/Button'
import Input from '@/components/ui/Input'

export default function Login() {
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [form, setForm] = useState({ email: '', password: '', remember: false })

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
      const { data } = await API.post('/login', {
        email: form.email,
        password: form.password,
        remember: form.remember,
      })

      if (data) {
        localStorage.setItem('user', JSON.stringify(data.user))
        if (data.token) {
          localStorage.setItem('token', data.token)
          API.defaults.headers.common.Authorization = `Bearer ${data.token}`
        }
        const redirectParam = searchParams.get('redirect')
        if (redirectParam) {
          navigate(redirectParam)
        } else {
          const route = await resolvePostAuthRoute(data.user?.role)
          navigate(route)
        }
      }
    } catch (err: any) {
      console.error('Login error:', err)
      setError(err.response?.data?.message || 'Error al iniciar sesión. Verifica tus credenciales.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="space-y-6">
      <div className="space-y-1">
        <p className="text-sm text-muted">Accede para comprar o gestionar tu tienda</p>
        <h1 className="text-3xl font-semibold text-white">Iniciar sesión</h1>
      </div>

      <form className="space-y-5" onSubmit={handleSubmit}>
        <div className="space-y-4">
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
          <div className="space-y-1">
            <label htmlFor="password" className="text-sm text-muted">Contraseña</label>
            <Input
              id="password"
              name="password"
              type="password"
              autoComplete="current-password"
              required
              placeholder="********"
              value={form.password}
              onChange={(e) => setForm((prev) => ({ ...prev, password: e.target.value }))}
            />
          </div>
        </div>

        <div className="flex items-center justify-between text-sm text-muted">
          <label className="inline-flex items-center gap-2">
            <input
              id="remember-me"
              name="remember-me"
              type="checkbox"
              className="h-4 w-4 rounded border-white/20 bg-white/5 text-brand-500 focus:ring-brand-500/60"
              checked={form.remember}
              onChange={(e) => setForm((prev) => ({ ...prev, remember: e.target.checked }))}
            />
            Recordarme
          </label>
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
              Iniciando...
            </span>
          ) : (
            <span>Entrar</span>
          )}
        </Button>

        <p className="text-center text-sm text-muted">
          ¿No tienes cuenta?
          <Link to="/register" className="text-brand-200 hover:text-white font-medium"> Crear cuenta</Link>
        </p>
      </form>
    </div>
  )
}


