import { Button } from '@/components/ui/button';
import { Truck, Store, Zap } from 'lucide-react';
import Image from 'next/image';
import Link from 'next/link';

export default function Home() {
  return (
    <div className="flex flex-col min-h-[calc(100vh-4rem)] bg-background">
      <section className="relative w-full h-[60vh] md:h-[80vh] flex items-center justify-center text-center text-white overflow-hidden">
        <Image
          src="https://picsum.photos/1920/1080"
          layout="fill"
          objectFit="cover"
          alt="Motorcycle parts hero image"
          data-ai-hint="motorcycle engine"
          className="absolute inset-0 z-0 opacity-40"
        />
        <div className="relative z-10 container px-4 md:px-6 space-y-6">
          <h1 className="text-4xl md:text-6xl lg:text-7xl font-extrabold tracking-tighter drop-shadow-2xl font-headline">
            Potencia Tu Pasión. Encuentra Tus Repuestos.
          </h1>
          <p className="max-w-3xl mx-auto text-lg md:text-xl text-primary-foreground/80 drop-shadow-lg">
            La plataforma definitiva para conectar a los amantes de las motos con las mejores tiendas de repuestos.
          </p>
          <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
             <Button asChild size="lg" className="shadow-lg hover:shadow-xl transition-shadow bg-primary text-primary-foreground hover:bg-primary/90 rounded-full px-10 py-6 text-lg font-bold">
                  <Link href="/register">
                    CREA TU TIENDA AHORA
                  </Link>
                </Button>
                <Button asChild variant="outline" size="lg" className="border-2 border-primary-foreground/50 text-primary-foreground bg-transparent hover:bg-primary-foreground hover:text-background rounded-full px-10 py-6 text-lg font-bold">
                  <Link href="/login">
                    Iniciar Sesión
                  </Link>
                </Button>
          </div>
        </div>
      </section>
      
      <section className="w-full py-16 md:py-24 lg:py-32 bg-secondary/30">
        <div className="container px-4 md:px-6">
          <div className="flex flex-col items-center justify-center space-y-4 text-center mb-12">
            <div className="inline-block rounded-full bg-primary/10 px-4 py-2 text-sm font-semibold text-primary">Nuestra Promesa</div>
            <h2 className="text-3xl font-bold tracking-tighter sm:text-5xl font-headline text-primary-foreground">Tu Negocio, a Toda Velocidad</h2>
            <p className="max-w-[900px] text-muted-foreground md:text-xl/relaxed">
              Diseñado para que gestionar tu tienda de repuestos sea más fácil que nunca.
            </p>
          </div>
          <div className="mx-auto grid max-w-5xl items-start gap-10 lg:grid-cols-3 lg:gap-12">
            <div className="grid gap-2 text-center p-6 rounded-2xl transition-all hover:bg-muted/50 hover:scale-105">
              <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary/20 mb-4 text-primary">
                <Zap className="w-8 h-8" />
              </div>
              <h3 className="text-xl font-bold font-headline">Publicación Rápida</h3>
              <p className="text-muted-foreground">Crea tu tienda y sube tu catálogo de productos en cuestión de minutos.</p>
            </div>
            <div className="grid gap-2 text-center p-6 rounded-2xl transition-all hover:bg-muted/50 hover:scale-105">
               <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary/20 mb-4 text-primary">
                <Store className="w-8 h-8" />
              </div>
              <h3 className="text-xl font-bold font-headline">Gestión Simple</h3>
              <p className="text-muted-foreground">Administra tu inventario y pedidos desde un panel de control intuitivo.</p>
            </div>
            <div className="grid gap-2 text-center p-6 rounded-2xl transition-all hover:bg-muted/50 hover:scale-105">
              <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary/20 mb-4 text-primary">
                <Truck className="w-8 h-8" />
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
