import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Truck, Store, Zap } from 'lucide-react';
import Image from 'next/image';
import Link from 'next/link';

export default function Home() {
  return (
    <div className="flex flex-col min-h-[calc(100vh-4rem)]">
      <section className="w-full py-12 md:py-24 lg:py-32 xl:py-48 bg-background">
        <div className="container px-4 md:px-6">
          <div className="grid gap-6 lg:grid-cols-2 lg:gap-16 items-center">
            <div className="flex flex-col justify-center space-y-4">
              <div className="space-y-2">
                <h1 className="text-4xl font-bold tracking-tighter sm:text-5xl xl:text-6xl/none font-headline">
                  La plataforma para tu tienda de motos
                </h1>
                <p className="max-w-[600px] text-muted-foreground md:text-xl">
                  Crea tu catálogo, gestiona tu inventario y llega a más clientes. Todo en un solo lugar.
                </p>
              </div>
              <div className="flex flex-col gap-2 min-[400px]:flex-row">
                <Button asChild size="lg" className="shadow-lg hover:shadow-xl transition-shadow">
                  <Link href="/register">
                    Crear Tienda Gratis
                  </Link>
                </Button>
                <Button asChild variant="outline" size="lg">
                  <Link href="/login">
                    Iniciar Sesión
                  </Link>
                </Button>
              </div>
            </div>
            <Image
              src="https://picsum.photos/600/400"
              width="600"
              height="400"
              alt="Hero"
              data-ai-hint="motorcycle parts"
              className="mx-auto aspect-video overflow-hidden rounded-xl object-cover sm:w-full"
            />
          </div>
        </div>
      </section>
      <section className="w-full py-12 md:py-24 lg:py-32 bg-muted/40">
        <div className="container px-4 md:px-6">
          <div className="flex flex-col items-center justify-center space-y-4 text-center">
            <div className="space-y-2">
              <div className="inline-block rounded-lg bg-secondary px-3 py-1 text-sm">Beneficios Clave</div>
              <h2 className="text-3xl font-bold tracking-tighter sm:text-5xl font-headline">Tu negocio, a toda velocidad</h2>
              <p className="max-w-[900px] text-muted-foreground md:text-xl/relaxed lg:text-base/relaxed xl:text-xl/relaxed">
                Diseñado para que gestionar tu tienda de repuestos sea más fácil que nunca.
              </p>
            </div>
          </div>
          <div className="mx-auto grid max-w-5xl items-start gap-8 py-12 lg:grid-cols-3 lg:gap-12">
            <div className="grid gap-1 text-center">
              <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 mb-4">
                <Zap className="w-8 h-8 text-primary" />
              </div>
              <h3 className="text-xl font-bold font-headline">Publicación Rápida</h3>
              <p className="text-muted-foreground">Crea tu tienda y sube tu catálogo de productos en cuestión de minutos.</p>
            </div>
            <div className="grid gap-1 text-center">
               <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 mb-4">
                <Store className="w-8 h-8 text-primary" />
              </div>
              <h3 className="text-xl font-bold font-headline">Gestión Simple</h3>
              <p className="text-muted-foreground">Administra tu inventario y pedidos desde un panel de control intuitivo.</p>
            </div>
            <div className="grid gap-1 text-center">
              <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 mb-4">
                <Truck className="w-8 h-8 text-primary" />
              </div>
              <h3 className="text-xl font-bold font-headline">Catálogo Atractivo</h3>
              <p className="text-muted-foreground">Llega a más clientes con una tienda online profesional y fácil de compartir.</p>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
