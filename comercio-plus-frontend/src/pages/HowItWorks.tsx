import Badge from '@/components/Badge'
import Button from '@/components/Button'
import Card from '@/components/Card'
import Header from '@/components/Header'

const merchantSteps = [
  {
    icon: '📝',
    title: 'Crea tu Cuenta',
    description: 'Regístrate gratis como comerciante en menos de 2 minutos.',
  },
  {
    icon: '🏪',
    title: 'Configura tu Tienda',
    description: 'Personaliza tu tienda con nombre, logo y descripción.',
  },
  {
    icon: '📦',
    title: 'Añade tus Productos',
    description: 'Sube fotos, precios e inventario de forma simple.',
  },
  {
    icon: '💰',
    title: 'Empieza a Vender',
    description: 'Recibe pedidos y gestiona todo desde tu dashboard.',
  },
]

const benefits = [
  { icon: '💼', title: 'Sin Comisiones Ocultas', description: 'Transparencia total en costos.' },
  { icon: '🚀', title: 'Fácil de Usar', description: 'Interfaz simple para empezar hoy.' },
  { icon: '📱', title: 'Responsive', description: 'Funciona en PC, tablet y móvil.' },
  { icon: '🔒', title: 'Seguro', description: 'Protección de datos y transacciones.' },
  { icon: '📊', title: 'Analíticas', description: 'Métricas claras de ventas y clientes.' },
  { icon: '💬', title: 'Soporte 24/7', description: 'Ayuda disponible cuando la necesites.' },
]

export default function HowItWorks() {
  return (
    <div className="min-h-screen bg-dark-50">
      <Header
        links={[
          { label: 'Tiendas', href: '/' },
          { label: 'Productos', href: '/products' },
          { label: 'Cómo Funciona', href: '/how-it-works', active: true },
        ]}
      />

      <div className="bg-gradient-to-r from-secondary to-primary text-white py-24">
        <div className="max-w-7xl mx-auto px-6 lg:px-10 text-center">
          <h1 className="text-hero mb-6 text-white animate-fade-in">
            ¿Cómo Funciona ComercioPlus?
          </h1>
          <p className="text-body-lg opacity-95 max-w-3xl mx-auto mb-10">
            Tu plataforma de e-commerce completa. Simple, poderosa y diseñada para ayudarte a vender más.
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Button variant="primary" size="lg" className="bg-white text-primary hover:bg-dark-50">
              🏪 Crear mi Tienda Gratis
            </Button>
            <Button variant="outline" size="lg" className="border-white text-white hover:bg-white/10">
              👁️ Ver Demo
            </Button>
          </div>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-6 lg:px-10 py-20">
        <section className="mb-24">
          <div className="text-center mb-16">
            <h2 className="text-h1 mb-4">Para Comerciantes</h2>
            <p className="text-body-lg text-dark-600 max-w-2xl mx-auto">
              En 4 simples pasos, tendrás tu tienda online lista para vender.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {merchantSteps.map((step, index) => (
              <Card key={step.title} className="text-center relative overflow-hidden group">
                <div className="absolute top-4 right-4 text-6xl font-bold text-dark-100 group-hover:text-dark-200 transition-colors">
                  {index + 1}
                </div>
                <div className="text-5xl mb-4 relative z-10">{step.icon}</div>
                <h3 className="text-h3 mb-3 relative z-10">{step.title}</h3>
                <p className="text-body text-dark-600 relative z-10">{step.description}</p>
              </Card>
            ))}
          </div>
        </section>

        <section className="mb-24">
          <div className="text-center mb-16">
            <h2 className="text-h1 mb-4">¿Por Qué ComercioPlus?</h2>
            <p className="text-body-lg text-dark-600 max-w-2xl mx-auto">
              Herramientas claras para hacer crecer tu negocio.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {benefits.map((benefit) => (
              <Card key={benefit.title} className="group hover:shadow-xl transition-all duration-300">
                <div className="w-16 h-16 bg-gradient-to-br from-secondary to-primary rounded-md flex items-center justify-center text-3xl mb-4">
                  {benefit.icon}
                </div>
                <h3 className="text-h3 mb-2">{benefit.title}</h3>
                <p className="text-body text-dark-600">{benefit.description}</p>
              </Card>
            ))}
          </div>
        </section>

        <section>
          <Card className="bg-gradient-to-br from-secondary via-primary to-accent text-white p-16 text-center">
            <Badge variant="info" className="mb-4">Producción Ready</Badge>
            <h2 className="text-h1 mb-6 text-white">¿Listo para Empezar?</h2>
            <p className="text-body-lg opacity-95 max-w-2xl mx-auto mb-10 text-white/95">
              Únete a cientos de emprendedores que ya venden en ComercioPlus.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Button variant="primary" size="lg" className="bg-white text-primary">
                🚀 Crear mi Tienda Ahora
              </Button>
              <Button variant="outline" size="lg" className="border-white text-white hover:bg-white/10">
                💬 Hablar con Ventas
              </Button>
            </div>
          </Card>
        </section>
      </div>
    </div>
  )
}
