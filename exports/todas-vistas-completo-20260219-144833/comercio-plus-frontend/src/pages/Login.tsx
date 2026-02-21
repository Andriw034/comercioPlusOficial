import { useState, useEffect } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import Button from '@/components/Button'
import { Icon } from '@/components/Icon'

interface MotorcycleImage {
  id: string
  urls: {
    regular: string
  }
  alt_description: string
}

export default function Login() {
  const navigate = useNavigate()
  const [formData, setFormData] = useState({
    email: '',
    password: '',
    rememberMe: false,
  })
  const [errors, setErrors] = useState<Record<string, string>>({})
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [showPassword, setShowPassword] = useState(false)

  // Estado para imágenes de fondo
  const [backgroundImages, setBackgroundImages] = useState<MotorcycleImage[]>([])
  const [currentImageIndex, setCurrentImageIndex] = useState(0)

  // Cargar imágenes de motos
  useEffect(() => {
    // URLs directas de Unsplash (sin API key necesaria)
    const images: MotorcycleImage[] = [
      {
        id: '1',
        urls: {
          regular: 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=1200&q=80',
        },
        alt_description: 'Motociclista en carretera',
      },
      {
        id: '2',
        urls: {
          regular: 'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=1200&q=80',
        },
        alt_description: 'Moto deportiva',
      },
      {
        id: '3',
        urls: {
          regular: 'https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=1200&q=80',
        },
        alt_description: 'Motociclista en ruta',
      },
      {
        id: '4',
        urls: {
          regular: 'https://images.unsplash.com/photo-1580310614729-ccd69652491d?w=1200&q=80',
        },
        alt_description: 'Moto custom',
      },
      {
        id: '5',
        urls: {
          regular: 'https://images.unsplash.com/photo-1449426468159-d96dbf08f19f?w=1200&q=80',
        },
        alt_description: 'Moto clásica',
      },
    ]

    setBackgroundImages(images)
  }, [])

  // Cambiar imagen cada 5 segundos
  useEffect(() => {
    if (backgroundImages.length === 0) return

    const interval = setInterval(() => {
      setCurrentImageIndex((prev) => (prev + 1) % backgroundImages.length)
    }, 5000)

    return () => clearInterval(interval)
  }, [backgroundImages.length])

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {}

    if (!formData.email.trim()) {
      newErrors.email = 'El correo es obligatorio'
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      newErrors.email = 'Correo electrónico inválido'
    }

    if (!formData.password) {
      newErrors.password = 'La contraseña es obligatoria'
    } else if (formData.password.length < 6) {
      newErrors.password = 'Mínimo 6 caracteres'
    }

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault()

    if (!validateForm()) return

    setIsSubmitting(true)

    try {
      // TODO: Conectar con API real
      await new Promise((resolve) => setTimeout(resolve, 1500))

      const mockUser = {
        id: '1',
        email: formData.email,
        role: 'merchant',
        storeId: 'store-123',
      }

      localStorage.setItem('user', JSON.stringify(mockUser))
      navigate('/dashboard/products')
    } catch (error) {
      console.error('Error al iniciar sesión:', error)
      setErrors({ general: 'Error al iniciar sesión. Verifica tus credenciales.' })
    } finally {
      setIsSubmitting(false)
    }
  }

  return (
    <div className="flex min-h-screen bg-slate-50">
      {/* Panel izquierdo - Hero con animación */}
      <div className="relative hidden w-1/2 overflow-hidden bg-slate-900 lg:block">
        {/* Fondo animado con imágenes */}
        <AnimatePresence mode="sync" initial={false}>
          {backgroundImages.length > 0 && (
            <motion.div
              key={currentImageIndex}
              initial={{ opacity: 0, scale: 1.1 }}
              animate={{ opacity: 1, scale: 1 }}
              exit={{ opacity: 0, scale: 0.95 }}
              transition={{ duration: 1.5, ease: 'easeInOut' }}
              className="absolute inset-0"
            >
              <img
                src={backgroundImages[currentImageIndex].urls.regular}
                alt={backgroundImages[currentImageIndex].alt_description}
                className="h-full w-full object-cover"
                loading="eager"
                decoding="async"
              />
              {/* Overlay gradiente para legibilidad */}
              <div className="absolute inset-0 bg-gradient-to-br from-slate-900/90 via-slate-900/75 to-comercioplus-900/80" />
            </motion.div>
          )}
        </AnimatePresence>

        {/* Contenido del hero - SOBRE el fondo */}
        <div className="relative z-10 flex h-full flex-col justify-center p-16">
          <div className="max-w-xl">
            <motion.h1
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.2, duration: 0.8 }}
              className="mb-6 text-5xl font-bold leading-tight text-white"
            >
              Bienvenido a ComercioPlus
            </motion.h1>

            <motion.p
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.4, duration: 0.8 }}
              className="text-xl leading-relaxed text-slate-200"
            >
              Conecta tu negocio con miles de clientes. Gestiona tu tienda online de manera
              simple y efectiva.
            </motion.p>

            {/* Indicadores de imagen */}
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ delay: 0.6, duration: 0.8 }}
              className="mt-10 flex gap-2"
            >
              {backgroundImages.map((_, index) => (
                <button
                  key={index}
                  onClick={() => setCurrentImageIndex(index)}
                  className={`h-2 rounded-full transition-all duration-300 ${
                    index === currentImageIndex
                      ? 'w-8 bg-comercioplus-500'
                      : 'w-2 bg-white/30 hover:bg-white/50'
                  }`}
                  aria-label={`Ver imagen ${index + 1}`}
                />
              ))}
            </motion.div>
          </div>
        </div>
      </div>

      {/* Panel derecho - Formulario (INTACTO) */}
      <div className="flex w-full items-center justify-center p-4 lg:w-1/2 lg:p-8">
        <div className="w-full max-w-md">
          {/* Card del formulario */}
          <div className="min-h-[640px] rounded-3xl border border-white/45 bg-white/58 p-8 shadow-[0_24px_60px_rgba(15,23,42,0.34)] backdrop-blur-2xl">
            {/* Header */}
            <div className="mb-8">
              <h2 className="mb-2 text-3xl font-bold text-slate-950">Iniciar Sesión</h2>
              <p className="text-base text-slate-600">
                ¿No tienes cuenta?{' '}
                <Link
                  to="/register"
                  className="font-semibold text-comercioplus-600 hover:text-comercioplus-700"
                >
                  Regístrate aquí
                </Link>
              </p>
            </div>

            {/* Formulario */}
            <form onSubmit={handleSubmit} className="space-y-5">
              {/* Error general */}
              {errors.general && (
                <div className="flex items-start gap-3 rounded-lg bg-danger/10 p-4">
                  <Icon name="alert" size={20} className="mt-0.5 text-danger" />
                  <p className="text-sm text-danger">{errors.general}</p>
                </div>
              )}

              {/* Email */}
              <div>
                <label className="mb-2 block text-sm font-semibold text-slate-900">
                  Correo Electrónico
                </label>
                <input
                  type="email"
                  placeholder="tu@email.com"
                  value={formData.email}
                  onChange={(e) => {
                    setFormData({ ...formData, email: e.target.value })
                    setErrors({ ...errors, email: '' })
                  }}
                  className={`input-dark w-full ${errors.email ? 'border-danger' : ''}`}
                  autoComplete="email"
                  spellCheck={false}
                />
                {errors.email && <p className="mt-2 text-xs text-danger">{errors.email}</p>}
              </div>

              {/* Contraseña */}
              <div>
                <label className="mb-2 block text-sm font-semibold text-slate-900">
                  Contraseña
                </label>
                <div className="relative">
                  <input
                    type={showPassword ? 'text' : 'password'}
                    placeholder="••••••••"
                    value={formData.password}
                    onChange={(e) => {
                      setFormData({ ...formData, password: e.target.value })
                      setErrors({ ...errors, password: '' })
                    }}
                    className={`input-dark w-full pr-12 ${errors.password ? 'border-danger' : ''}`}
                    autoComplete="current-password"
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors hover:text-slate-600"
                    aria-label={showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'}
                  >
                    <Icon name={showPassword ? 'eye-off' : 'eye'} size={20} />
                  </button>
                </div>
                {errors.password && (
                  <p className="mt-2 text-xs text-danger">{errors.password}</p>
                )}
              </div>

              {/* Recordarme / Olvidé contraseña */}
              <div className="flex items-center justify-between">
                <label className="flex items-center gap-2">
                  <input
                    type="checkbox"
                    checked={formData.rememberMe}
                    onChange={(e) =>
                      setFormData({ ...formData, rememberMe: e.target.checked })
                    }
                    className="h-4 w-4 rounded border-slate-300 text-comercioplus-600 focus:ring-2 focus:ring-comercioplus-500/20"
                  />
                  <span className="text-sm text-slate-700">Recordarme</span>
                </label>

                <Link
                  to="/forgot-password"
                  className="text-sm font-medium text-comercioplus-600 hover:text-comercioplus-700"
                >
                  ¿Olvidaste tu contraseña?
                </Link>
              </div>

              {/* Botón submit */}
              <Button
                type="submit"
                variant="primary"
                size="lg"
                fullWidth
                loading={isSubmitting}
                disabled={isSubmitting}
                className="bg-comercioplus-600 hover:bg-comercioplus-700"
              >
                {isSubmitting ? 'Iniciando sesión...' : 'Iniciar Sesión'}
              </Button>
            </form>

          </div>

          {/* Footer */}
          <p className="mt-6 text-center text-xs text-slate-500">
            © 2024 ComercioPlus. Todos los derechos reservados.
          </p>
        </div>
      </div>
    </div>
  )
}
