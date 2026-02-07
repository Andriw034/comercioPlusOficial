import { useState } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router-dom'
import API from '@/lib/api'
import Button from '@/components/ui/button'
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
    <div className="rounded-[28px] border border-white/25 bg-white/10 px-6 py-7 sm:px-8 sm:py-8 shadow-[0_30px_80px_rgba(0,0,0,0.35)] backdrop-blur-xl">
      <div className="flex flex-col items-center text-center">
        <div className="relative flex h-16 w-16 items-center justify-center rounded-full border border-white/40">
          <div className="flex h-10 w-10 items-center justify-center rounded-full border border-white/60 text-white">
            <svg className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.6" d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5z" />
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.6" d="M4 20c0-3.314 3.134-6 8-6s8 2.686 8 6" />
            </svg>
          </div>
        </div>

        <div className="mt-4 flex w-full items-center gap-4 text-white/70">
          <span className="h-px flex-1 bg-white/30" />
          <span className="text-[11px] uppercase tracking-[0.4em]">ComercioPlus</span>
          <span className="h-px flex-1 bg-white/30" />
        </div>
      </div>

      <div className="mt-6 text-center">
        <h1 className="text-2xl font-semibold text-white">Iniciar sesión</h1>
        <p className="text-sm text-white/70">Accede para comprar o gestionar tu tienda</p>
      </div>

      <form className="mt-6 space-y-4" onSubmit={handleSubmit}>
        <div className="space-y-4">
          <div className="flex items-center overflow-hidden rounded-xl border border-white/40 bg-white/90 text-slate-900 shadow-sm">
            <span className="flex h-11 w-11 items-center justify-center bg-panel text-white/80">
              <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.6" d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.6" d="M4 20c0-3.314 3.134-6 8-6s8 2.686 8 6" />
              </svg>
            </span>
            <label htmlFor="email" className="sr-only">Correo electrónico</label>
            <Input
              id="email"
              name="email"
              type="email"
              autoComplete="email"
              required
              placeholder="Correo electrónico"
              className="!border-0 !bg-transparent !text-slate-900 !placeholder:text-slate-400 !py-2.5 !px-4 focus:!border-0 focus:!ring-0"
              value={form.email}
              onChange={(e) => setForm((prev) => ({ ...prev, email: e.target.value }))}
            />
          </div>

          <div className="flex items-center overflow-hidden rounded-xl border border-white/40 bg-white/90 text-slate-900 shadow-sm">
            <span className="flex h-11 w-11 items-center justify-center bg-panel text-white/80">
              <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.6" d="M16 10V8a4 4 0 00-8 0v2" />
                <rect x="5" y="10" width="14" height="10" rx="2" ry="2" strokeWidth="1.6" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.6" d="M12 14v3" />
              </svg>
            </span>
            <label htmlFor="password" className="sr-only">Contraseña</label>
            <Input
              id="password"
              name="password"
              type="password"
              autoComplete="current-password"
              required
              placeholder="Contraseña"
              className="!border-0 !bg-transparent !text-slate-900 !placeholder:text-slate-400 !py-2.5 !px-4 focus:!border-0 focus:!ring-0"
              value={form.password}
              onChange={(e) => setForm((prev) => ({ ...prev, password: e.target.value }))}
            />
          </div>
        </div>

        <div className="flex items-center justify-between text-sm text-white/75">
          <label className="inline-flex items-center gap-2">
            <input
              id="remember-me"
              name="remember-me"
              type="checkbox"
              className="h-4 w-4 rounded border-white/40 bg-white/10 text-brand-500 focus:ring-brand-400"
              checked={form.remember}
              onChange={(e) => setForm((prev) => ({ ...prev, remember: e.target.checked }))}
            />
            Recordarme
          </label>
          <span className="text-white/60 select-none">¿Olvidaste tu contraseña?</span>
        </div>

        {error && (
          <div className="text-red-200 text-sm">
            {error}
          </div>
        )}

        <Button
          type="submit"
          className="w-full inline-flex items-center justify-center rounded-xl !bg-gradient-to-r from-brand-500 to-brand-600 px-4 py-2.5 text-sm font-semibold !text-white shadow-lg shadow-brand-600/30 hover:from-brand-600 hover:to-brand-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70 focus-visible:ring-offset-0 transition"
          loading={loading}
        >
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

        <p className="text-center text-sm text-white/70">
          ¿No tienes cuenta?
          <Link to="/register" className="text-white font-semibold hover:text-white/90"> Crear cuenta</Link>
        </p>
      </form>
    </div>
  )
}





