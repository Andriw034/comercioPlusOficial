@extends('layouts.app')

@section('title', 'Comercio Plus - Plataforma de e-commerce')

@section('content')
<div class="flex flex-col min-h-[calc(100vh-4rem)] bg-background">
    <!-- Hero Section -->
    <section class="relative w-full h-[70vh] md:h-[85vh] flex items-center justify-center text-center text-foreground overflow-hidden">
        <div class="absolute inset-0 bg-background z-0">
            <img
                src="https://picsum.photos/1920/1080"
                alt="Repuestos de moto"
                class="w-full h-full object-cover opacity-5"
            />
        </div>
        <div class="relative z-10 container px-4 md:px-6 space-y-8">
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold">
                La Plataforma Para Tu Tienda de Repuestos
            </h1>
            <p class="max-w-3xl mx-auto text-lg md:text-xl text-muted-foreground">
                Crea tu catálogo online, llega a más clientes y gestiona tu negocio de motos. Todo en un solo lugar.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-10 py-6 text-lg font-semibold bg-primary text-primary-foreground rounded hover:bg-primary/90">
                        Ir al Panel
                    </a>
                @else
                    <a href="{{ route('register') }}" class="px-10 py-6 text-lg font-semibold bg-primary text-primary-foreground rounded hover:bg-primary/90">
                        Crea tu Tienda Gratis
                    </a>
                    <a href="{{ route('dashboard') }}" class="px-10 py-6 text-lg font-semibold border border-primary text-primary rounded hover:bg-primary hover:text-primary-foreground">
                        Ir al Panel
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="w-full py-16 md:py-24 lg:py-32 bg-card border-y">
        <div class="container px-4 md:px-6">
            <div class="flex flex-col items-center justify-center space-y-4 text-center mb-16">
                <div class="inline-block rounded-full bg-secondary px-4 py-2 text-sm font-semibold text-secondary-foreground">Nuestra Promesa</div>
                <h2 class="text-3xl font-bold sm:text-5xl">Tu Negocio, a Toda Velocidad</h2>
                <p class="max-w-[900px] text-muted-foreground md:text-xl/relaxed">
                    Diseñado para que gestionar tu tienda de repuestos sea más fácil que nunca.
                </p>
            </div>
            <div class="mx-auto grid max-w-5xl items-start gap-10 lg:grid-cols-3 lg:gap-12">
                <div class="grid gap-2 text-center p-6">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-secondary mb-4 text-primary">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold">Publicación Rápida</h3>
                    <p class="text-muted-foreground">Crea tu tienda y sube tu catálogo de productos en cuestión de minutos.</p>
                </div>
                <div class="grid gap-2 text-center p-6">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-secondary mb-4 text-primary">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold">Gestión Simple</h3>
                    <p class="text-muted-foreground">Administra tu inventario y pedidos desde un panel de control intuitivo.</p>
                </div>
                <div class="grid gap-2 text-center p-6">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-secondary mb-4 text-primary">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path>
                            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-1-1h-3z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold">Catálogo Atractivo</h3>
                    <p class="text-muted-foreground">Llega a más clientes con una tienda online profesional y fácil de compartir.</p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
