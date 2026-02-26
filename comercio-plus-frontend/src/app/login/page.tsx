import { useState, type FormEvent } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router-dom'
import API from '@/services/api'
import Button from '@/components/ui/button'
import Input from '@/components/ui/Input'
import { Icon } from '@/components/Icon'
import { hydrateSession, resolvePostAuthRoute } from '@/services/auth-session'

const isSafeInternalRedirect = (value: string) => value.startsWith('/') && !value.startsWith('//')

export default function Login() {
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [form, setForm] = useState({ email: '', password: '', remember: false })
  const [showPassword, setShowPassword] = useState(false)

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault()
    setLoading(true)
    setError('')

    try {
      const email = form.email.trim().toLowerCase()
      const password = form.password
      const { data } = await API.post('/login', {
        email,
        password,
        remember: form.remember,
      })

      if (data?.token) {
        const user = await hydrateSession(data.token, form.remember)
        const nextParam = searchParams.get('next')
        if (nextParam && isSafeInternalRedirect(nextParam)) {
          navigate(nextParam, { replace: true })
          return
        }

        // Legacy support for existing auth wrappers that still send ?redirect=
        const redirectParam = searchParams.get('redirect')
        if (redirectParam && isSafeInternalRedirect(redirectParam)) {
          navigate(redirectParam, { replace: true })
          return
        }

        navigate(resolvePostAuthRoute(user), { replace: true })
      } else {
        setError('No se recibio token de autenticacion.')
      }
    } catch (err: any) {
      const status = err?.response?.status
      const fieldErrors = err?.response?.data?.errors as Record<string, string[] | string> | undefined
      const firstErrorValue = fieldErrors ? Object.values(fieldErrors)[0] : undefined
      const firstFieldError = Array.isArray(firstErrorValue)
        ? (firstErrorValue[0] ?? '')
        : (typeof firstErrorValue === 'string' ? firstErrorValue : '')
      const backendMessage = err?.response?.data?.message as string | undefined
      const normalizedBackendMessage = (backendMessage || '').toLowerCase()

      if (
        status === 503 ||
        normalizedBackendMessage.includes('base de datos') ||
        normalizedBackendMessage.includes('db/migrations')
      ) {
        setError('Servidor temporalmente no disponible. Intenta de nuevo en 1-2 minutos.')
        return
      }

      const message =
        firstFieldError ||
        backendMessage ||
        'Error al iniciar sesion. Verifica tus credenciales.'
      setError(status ? `${message} (HTTP ${status})` : message)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div>
      <h1 className="text-[28px] leading-tight">Iniciar Sesion</h1>
      <p className="mb-3 mt-1 text-[14px] text-[#4B5563]">
        ¿No tienes cuenta? <Link to="/register" className="font-semibold text-[#FF6B35]">Registrate aqui</Link>
      </p>

      <form className="space-y-2.5" onSubmit={handleSubmit}>
        <Input
          label="Correo Electronico"
          className="h-10 py-2"
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
          className="h-10 py-2"
          id="password"
          name="password"
          type={showPassword ? 'text' : 'password'}
          autoComplete="current-password"
          required
          placeholder="••••••••"
          value={form.password}
          onChange={(e) => setForm((prev) => ({ ...prev, password: e.target.value }))}
          rightIcon={<Icon name={showPassword ? 'eye-off' : 'eye'} size={16} />}
          rightIconButton
          rightIconAriaLabel={showPassword ? 'Ocultar contrasena' : 'Mostrar contrasena'}
          onRightIconClick={() => setShowPassword((previous) => !previous)}
        />

        <div className="flex items-center justify-between pt-0.5">
          <label className="inline-flex items-center gap-2 text-[13px] text-[#4B5563]">
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
          <Link to="/forgot-password" className="text-[13px] font-medium text-[#FF6B35] hover:underline">
            ¿Olvidaste tu contrasena?
          </Link>
        </div>

        {error && <div className="text-sm text-red-600">{error}</div>}

        <Button type="submit" className="h-10 w-full" loading={loading}>
          {loading ? 'Iniciando...' : 'Iniciar Sesion'}
        </Button>
      </form>
    </div>
  )
}
