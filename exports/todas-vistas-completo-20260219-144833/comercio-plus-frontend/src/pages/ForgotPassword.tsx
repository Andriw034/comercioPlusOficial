import { useState } from 'react'
import { Link } from 'react-router-dom'
import Button from '@/components/Button'
import { Icon } from '@/components/Icon'

export default function ForgotPassword() {
  const [email, setEmail] = useState('')
  const [submitted, setSubmitted] = useState(false)

  const onSubmit = (event: React.FormEvent) => {
    event.preventDefault()
    if (!email.trim()) return
    setSubmitted(true)
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-slate-50 px-4">
      <div className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <h1 className="mb-2 text-2xl font-bold text-slate-900">Recuperar contrasena</h1>
        <p className="mb-6 text-sm text-slate-600">
          Ingresa tu correo y te enviaremos instrucciones para recuperar el acceso.
        </p>

        {submitted ? (
          <div className="rounded-lg bg-green-50 p-4 text-sm text-green-700">
            Revisamos la solicitud para <strong>{email}</strong>. Si la cuenta existe, recibiras un
            correo con los pasos.
          </div>
        ) : (
          <form onSubmit={onSubmit} className="space-y-4">
            <div>
              <label className="mb-2 block text-sm font-semibold text-slate-800">Correo</label>
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="tu@email.com"
                className="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-comercioplus-500 focus:outline-none"
              />
            </div>
            <Button type="submit" variant="primary" fullWidth>
              Enviar instrucciones
            </Button>
          </form>
        )}

        <div className="mt-6">
          <Link
            to="/login"
            className="inline-flex items-center gap-2 text-sm font-semibold text-comercioplus-600 hover:text-comercioplus-700"
          >
            <Icon name="arrow-left" size={16} />
            Volver a iniciar sesion
          </Link>
        </div>
      </div>
    </div>
  )
}
