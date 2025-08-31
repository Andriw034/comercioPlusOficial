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
          <div className="grid gap-6 lg:grid-cols-[1fr_400px] lg:gap-12 xl:grid-cols-[1fr_600px]">
            <div className="flex flex-col justify-center space-y-4">
              <div className="space-y-2">
                <h1 className="text-3xl font-extrabold tracking-tighter sm:text-5xl xl:text-6xl/none font-headline">
                  Empieza gratis y comparte tu <span className="text-primary">catálogo</span> hoy.
                </h1>
                <p className="max-w-[600px] text-muted-foreground md:text-xl">
                  Publica tus productos, gestiona pedidos y comparte tu tienda en minutos. La plataforma definitiva para comerciantes de motos y repuestos.
                </p>
              </div>
              <div className="flex flex-col gap-2 min-[400px]:flex-row">
                <Button asChild size="lg" className="bg-gradient-to-r from-primary to-accent text-primary-foreground shadow-lg hover:shadow-xl transition-shadow">
                  <Link href="/register">
                    Crear cuenta
                  </Link>
                </Button>
                <Button asChild variant="outline" size="lg">
                  <Link href="/login">
                    Iniciar sesión
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
              className="mx-auto aspect-video overflow-hidden rounded-3xl object-cover sm:w-full lg:order-last"
            />
          </div>
        </div>
      </section>
      <section className="w-full py-12 md:py-24 lg:py-32 bg-secondary">
        <div className="container px-4 md:px-6">
          <div className="flex flex-col items-center justify-center space-y-4 text-center">
            <div className="space-y-2">
              <div className="inline-block rounded-lg bg-muted px-3 py-1 text-sm">Beneficios Clave</div>
              <h2 className="text-3xl font-bold tracking-tighter sm:text-5xl font-headline">Tu negocio de motos, online y sin complicaciones</h2>
              <p className="max-w-[900px] text-muted-foreground md:text-xl/relaxed lg:text-base/relaxed xl:text-xl/relaxed">
                Te damos las herramientas para que tu tienda de repuestos destaque en el mundo digital.
              </p>
            </div>
          </div>
          <div className="mx-auto grid max-w-5xl items-center gap-6 py-12 lg:grid-cols-3 lg:gap-12">
            <Card className="h-full">
              <CardContent className="flex flex-col items-center justify-center p-6 text-center space-y-4">
                <div className="bg-primary/10 p-4 rounded-full">
                  <Zap className="w-8 h-8 text-primary" />
                </div>
                <h3 className="text-xl font-bold font-headline">Publica rápido</h3>
                <p className="text-muted-foreground">Crea tu tienda y sube tu catálogo de productos en cuestión de minutos.</p>
              </CardContent>
            </Card>
            <Card className="h-full">
              <CardContent className="flex flex-col items-center justify-center p-6 text-center space-y-4">
                <div className="bg-primary/10 p-4 rounded-full">
                  <Store className="w-8 h-8 text-primary" />
                </div>
                <h3 className="text-xl font-bold font-headline">Gestión simple</h3>
                <p className="text-muted-foreground">Administra tu inventario, precios y pedidos desde un panel de control intuitivo.</p>
              </CardContent>
            </Card>
            <Card className="h-full">
              <CardContent className="flex flex-col items-center justify-center p-6 text-center space-y-4">
                <div className="bg-primary/10 p-4 rounded-full">
                  <Truck className="w-8 h-8 text-primary" />
                </div>
                <h3 className="text-xl font-bold font-headline">Catálogo online</h3>
                <p className="text-muted-foreground">Llega a más clientes con una tienda online profesional y atractiva.</p>
              </CardContent>
            </Card>
          </div>
        </div>
      </section>
    </div>
  );
}
