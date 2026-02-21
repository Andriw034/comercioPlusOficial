import Badge from '@/components/Badge'
import Button from '@/components/Button'
import Card from '@/components/Card'
import Header from '@/components/Header'

const merchantSteps = [
  {
    code: '01',
    title: 'Crea tu cuenta',
    description: 'Registro rapido para abrir tu panel de comercio en minutos.',
  },
  {
    code: '02',
    title: 'Configura tu tienda',
    description: 'Define identidad visual, metodos de contacto y catalogo base.',
  },
  {
    code: '03',
    title: 'Publica productos',
    description: 'Sube fotos, precios y stock con control total por categoria.',
  },
  {
    code: '04',
    title: 'Escala tus ventas',
    description: 'Activa promociones y monitorea conversiones en tiempo real.',
  },
]

const benefits = [
  { title: 'Sin costos ocultos', description: 'Modelo transparente con foco en crecimiento sostenible.' },
  { title: 'Panel rapido', description: 'Flujos optimizados para gestionar pedidos y productos.' },
  { title: 'Mobile first', description: 'Experiencia fluida en desktop, tablet y telefono.' },
  { title: 'Seguridad', description: 'Buenas practicas para proteger datos y operaciones.' },
  { title: 'Analiticas', description: 'Mide ingresos, ordenes y comportamiento de clientes.' },
  { title: 'Soporte', description: 'Acompanamiento tecnico para lanzamientos y mejoras.' },
]

export default function HowItWorks() {
  return (
    <div className="min-h-screen bg-slate-50">
      <Header
        links={[
          { label: 'Tiendas', href: '/' },
          { label: 'Productos', href: '/products' },
          { label: 'Como Funciona', href: '/how-it-works', active: true },
        ]}
      />

      <section className="relative overflow-hidden bg-slate-950 py-24 text-white">
        <div className="absolute inset-0 bg-mesh opacity-70" />
        <div className="absolute -left-16 top-20 h-56 w-56 rounded-full bg-brand-500/25 blur-3xl" />
        <div className="absolute -right-20 top-8 h-72 w-72 rounded-full bg-cyan-400/20 blur-3xl" />

        <div className="relative mx-auto max-w-7xl px-6 text-center lg:px-10">
          <Badge variant="brand" className="mb-4">ComercioPlus Method</Badge>
          <h1 className="mb-6 text-display-md text-white">Como funciona ComercioPlus</h1>
          <p className="mx-auto mb-10 max-w-3xl text-body-lg text-slate-200">
            Una plataforma para lanzar, operar y escalar comercio digital con una experiencia premium.
          </p>
          <div className="flex flex-col justify-center gap-4 sm:flex-row">
            <Button variant="primary" size="lg">Crear tienda gratis</Button>
            <Button variant="glass" size="lg">Solicitar demo</Button>
          </div>
        </div>
      </section>

      <main className="mx-auto max-w-7xl px-6 py-20 lg:px-10">
        <section className="mb-24">
          <div className="mb-12 text-center">
            <h2 className="mb-3 text-h1">Ruta para comerciantes</h2>
            <p className="mx-auto max-w-2xl text-body-lg text-slate-600">
              Cuatro pasos para pasar de idea a tienda activa con procesos claros.
            </p>
          </div>

          <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            {merchantSteps.map((step) => (
              <Card key={step.title} variant="premium" className="group" padding="lg" gradient>
                <p className="mb-4 text-display-sm text-brand-600/50 transition-colors group-hover:text-brand-600">{step.code}</p>
                <h3 className="mb-3 text-h3">{step.title}</h3>
                <p className="text-body text-slate-600">{step.description}</p>
              </Card>
            ))}
          </div>
        </section>

        <section className="mb-24">
          <div className="mb-12 text-center">
            <h2 className="mb-3 text-h1">Por que elegirnos</h2>
            <p className="mx-auto max-w-2xl text-body-lg text-slate-600">
              Herramientas comerciales con claridad operativa y diseno de alto contraste.
            </p>
          </div>

          <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            {benefits.map((benefit, index) => (
              <Card key={benefit.title} variant="glass" className="group" padding="lg">
                <div className="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-sm font-bold text-white shadow-glow">
                  {String(index + 1).padStart(2, '0')}
                </div>
                <h3 className="mb-2 text-h3">{benefit.title}</h3>
                <p className="text-body text-slate-600">{benefit.description}</p>
              </Card>
            ))}
          </div>
        </section>

        <section>
          <Card variant="premium" className="overflow-hidden" padding="xl">
            <div className="relative text-center">
              <div className="absolute left-1/2 top-0 h-40 w-40 -translate-x-1/2 rounded-full bg-brand-500/20 blur-3xl" />
              <div className="relative z-10">
                <Badge variant="info" className="mb-4">Production ready</Badge>
                <h2 className="mb-4 text-h1">Listo para empezar</h2>
                <p className="mx-auto mb-8 max-w-2xl text-body-lg text-slate-600">
                  Activa tu tienda y comienza a vender con una experiencia moderna para tu equipo y clientes.
                </p>
                <div className="flex flex-col justify-center gap-4 sm:flex-row">
                  <Button variant="primary" size="lg">Crear mi tienda</Button>
                  <Button variant="outline" size="lg">Hablar con ventas</Button>
                </div>
              </div>
            </div>
          </Card>
        </section>
      </main>
    </div>
  )
}