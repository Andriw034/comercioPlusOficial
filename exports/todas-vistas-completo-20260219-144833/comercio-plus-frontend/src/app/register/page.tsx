import { useState, type FormEvent } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import API from '@/lib/api'
import Button from '@/components/ui/button'
import Input from '@/components/ui/Input'
import Select from '@/components/ui/Select'
import { hydrateSession, resolvePostAuthRoute } from '@/services/auth-session'

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

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault()
    setLoading(true)
    setError('')

    try {
      const { data } = await API.post('/register', form)
      if (data?.token) {
        const user = await hydrateSession(data.token)
        navigate(resolvePostAuthRoute(user))
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
      const message =
        firstFieldError ||
        err?.response?.data?.message ||
        'Error al crear la cuenta. Verifica tus datos.'
      setError(status ? `${message} (HTTP ${status})` : message)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div>
      <h1 className="text-[28px] leading-tight">Crear Cuenta</h1>
      <p className="mb-3 mt-1 text-[14px] text-[#4B5563]">
        ¿Ya tienes cuenta? <Link to="/login" className="font-semibold text-[#FF6B35]">Inicia sesion</Link>
      </p>

      <form className="space-y-2.5" onSubmit={handleSubmit}>
        <div>
          <p className="mb-1.5 text-[13px] font-medium text-[#1F2937]">Tipo de cuenta</p>
          <div className="grid grid-cols-2 gap-4">
            <button
              type="button"
              onClick={() => setForm((prev) => ({ ...prev, role: 'merchant' }))}
              className={`rounded-xl border-2 p-2.5 text-center transition-all ${
                form.role === 'merchant'
                  ? 'border-[#FF6B35] bg-[rgba(255,107,53,0.1)]'
                  : 'border-[#E5E7EB] bg-white'
              }`}
            >
              <div className="text-xl">🏪</div>
              <div className="mt-0.5 text-[13px] font-semibold text-[#1F2937]">Comerciante</div>
            </button>

            <button
              type="button"
              onClick={() => setForm((prev) => ({ ...prev, role: 'client' }))}
              className={`rounded-xl border-2 p-2.5 text-center transition-all ${
                form.role === 'client'
                  ? 'border-[#FF6B35] bg-[rgba(255,107,53,0.1)]'
                  : 'border-[#E5E7EB] bg-white'
              }`}
            >
              <div className="text-xl">🛍️</div>
              <div className="mt-0.5 text-[13px] font-semibold text-[#1F2937]">Cliente</div>
            </button>
          </div>

          <Select
            id="role"
            name="role"
            value={form.role}
            onChange={(e) => setForm((prev) => ({ ...prev, role: e.target.value }))}
            className="sr-only"
          >
            <option value="merchant">Comerciante</option>
            <option value="client">Cliente</option>
          </Select>
        </div>

        <Input
          label="Nombre Completo"
          className="h-10 py-2"
          id="name"
          name="name"
          type="text"
          autoComplete="name"
          required
          placeholder="Juan Perez"
          value={form.name}
          onChange={(e) => setForm((prev) => ({ ...prev, name: e.target.value }))}
        />

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
          type="password"
          autoComplete="new-password"
          required
          placeholder="••••••••"
          value={form.password}
          onChange={(e) => setForm((prev) => ({ ...prev, password: e.target.value }))}
        />

        <Input
          label="Confirmar Contrasena"
          className="h-10 py-2"
          id="password_confirmation"
          name="password_confirmation"
          type="password"
          autoComplete="new-password"
          required
          placeholder="••••••••"
          value={form.password_confirmation}
          onChange={(e) => setForm((prev) => ({ ...prev, password_confirmation: e.target.value }))}
        />

        {error && <div className="text-sm text-red-600">{error}</div>}

        <Button type="submit" className="h-10 w-full" loading={loading}>
          {loading ? 'Creando...' : 'Crear Cuenta'}
        </Button>
      </form>
    </div>
  )
}
