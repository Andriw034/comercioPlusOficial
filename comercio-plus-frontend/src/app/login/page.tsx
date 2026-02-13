import { useState, type FormEvent } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router-dom'
import API from '@/lib/api'
import Button from '@/components/ui/button'
import Input from '@/components/ui/Input'
import { hydrateSession, resolvePostAuthRoute } from '@/services/auth-session'

export default function Login() {
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [form, setForm] = useState({ email: '', password: '', remember: false })

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault()
    setLoading(true)
    setError('')

    try {
      const { data } = await API.post('/login', {
        email: form.email,
        password: form.password,
        remember: form.remember,
      })

      if (data?.token) {
        const user = await hydrateSession(data.token)
        const redirectParam = searchParams.get('redirect')
        if (redirectParam) navigate(redirectParam)
        else navigate(resolvePostAuthRoute(user))
      } else {
        setError('No se recibio token de autenticacion.')
      }
    } catch (err: any) {
      const status = err?.response?.status
      const message = err?.response?.data?.message || 'Error al iniciar sesion. Verifica tus credenciales.'
      setError(status ? `${message} (HTTP ${status})` : message)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div>
      <h1 className="text-[32px]">Iniciar Sesion</h1>
      <p className="mb-8 mt-2 text-[15px] text-[#4B5563]">
        ¿No tienes cuenta? <Link to="/register" className="font-semibold text-[#FF6B35]">Registrate aqui</Link>
      </p>

      <form className="space-y-5" onSubmit={handleSubmit}>
        <Input
          label="Correo Electronico"
          id="email"
          name="email"
          type="email"
          autoComplete="email"
          required
          placeholder="tu@email.com"
          value={form.email}
          onChange={(e) => setForm((prev) => ({ ...prev, email: e.target.value }))}
        />

        <Input
          label="Contrasena"
          id="password"
          name="password"
          type="password"
          autoComplete="current-password"
          required
          placeholder="••••••••"
          value={form.password}
          onChange={(e) => setForm((prev) => ({ ...prev, password: e.target.value }))}
        />

        <div className="flex items-center justify-between">
          <label className="inline-flex items-center gap-2 text-[14px] text-[#4B5563]">
            <input
              id="remember-me"
              name="remember-me"
              type="checkbox"
              className="h-4 w-4 rounded border-[#D1D5DB]"
              checked={form.remember}
              onChange={(e) => setForm((prev) => ({ ...prev, remember: e.target.checked }))}
            />
            Recordarme
          </label>
          <span className="text-[14px] font-medium text-[#FF6B35]">¿Olvidaste tu contrasena?</span>
        </div>

        {error && <div className="text-sm text-red-600">{error}</div>}

        <Button type="submit" className="w-full" loading={loading}>
          {loading ? 'Iniciando...' : 'Iniciar Sesion'}
        </Button>
      </form>
    </div>
  )
}
