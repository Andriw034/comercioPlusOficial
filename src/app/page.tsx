import { Button } from '@/components/ui/button'
import { Truck, Store, Zap } from 'lucide-react'
import Image from 'next/image'
import Link from 'next/link'

export default function Home() {
  return (
    <div className="flex flex-col min-h-[calc(100vh-4rem)] bg-background">
      <section className="relative w-full h-[70vh] md:h-[85vh] flex items-center justify-center text-center text-foreground overflow-hidden">
        <div className="absolute inset-0 bg-background z-0">
          <Image
            src="https://picsum.photos/1920/1080"
            fill
            alt="Motorcycle parts hero image"
            data-ai-hint="motorcycle engine"
            className="opacity-5"
            priority
          />
        </div>
        <div className="relative z-10 container px-4 md:px-6 space-y-8">
          <h1 className="text-4xl md:text-6xl lg:text-7xl font-bold">
            La Plataforma Para Tu Tienda de Repuestos
          </h1>
          <p className="max-w-3xl mx-auto text-lg md:text-xl text-muted-foreground">
            Crea tu catálogo online, llega a más clientes y gestiona tu negocio de motos. Todo en un solo lugar.
          </p>
          <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
            <Button asChild size="lg" className="px-10 py-6 text-lg font-semibold">
              <Link href="/register">
                Crea tu Tienda Gratis
              </Link>
            </Button>
            <Button asChild variant="outline" size="lg" className="px-10 py-6 text-lg font-semibold">
              <Link href="/dashboard">
                Ir al Panel
              </Link>
            </Button>
          </div>
        </div>
      </section>

      <section className="w-full py-16 md:py-24 lg:py-32 bg-card border-y">
        <div className="container px-4 md:px-6">
          <div className="flex flex-col items-center justify-center space-y-4 text-center mb-16">
            <div className="inline-block rounded-full bg-secondary px-4 py-2 text-sm font-semibold text-secondary-foreground">Nuestra Promesa</div>
            <h2 className="text-3xl font-bold sm:text-5xl">Tu Negocio, a Toda Velocidad</h2>
            <p className="max-w-[900px] text-muted-foreground md:text-xl/relaxed">
              Diseñado para que gestionar tu tienda de repuestos sea más fácil que nunca.
            </p>
          </div>
          <div className="mx-auto grid max-w-5xl items-start gap-10 lg:grid-cols-3 lg:gap-12">
            <div className="grid gap-2 text-center p-6">
              <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-secondary mb-4 text-primary">
                <Zap className="w-8 h-8" />
              </div>
              <h3 className="text-xl font-bold">Publicación Rápida</h3>
              <p className="text-muted-foreground">Crea tu tienda y sube tu catálogo de productos en cuestión de minutos.</p>
            </div>
            <div className="grid gap-2 text-center p-6">
              <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-secondary mb-4 text-primary">
                <Store className="w-8 h-8" />
              </div>
              <h3 className="text-xl font-bold">Gestión Simple</h3>
              <p className="text-muted-foreground">Administra tu inventario y pedidos desde un panel de control intuitivo.</p>
            </div>
            <div className="grid gap-2 text-center p-6">
              <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-secondary mb-4 text-primary">
                <Truck className="w-8 h-8" />
              </div>
              <h3 className="text-xl font-bold">Catálogo Atractivo</h3>
              <p className="text-muted-foreground">Llega a más clientes con una tienda online profesional y fácil de compartir.</p>
            </div>
          </div>
        </div>
      </section>
    </div>
  )
}
