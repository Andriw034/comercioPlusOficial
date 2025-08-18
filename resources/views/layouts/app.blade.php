<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ComercioPlus')</title>

    {{-- Tipografía --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Vite (Tailwind + JS) --}}
    @vite(['resources/css/app.css','resources/js/app.js'])

    <style>
        :root{
            --cp-primary: #FF6000;      /* Naranja principal */
            --cp-bg-50:  #f9f9f9;       /* Fondos */
            --cp-bg-100: #f1f1f1;
            --cp-bg-200: #e5e5e5;
            --cp-text-800:#333333;      /* Texto */
            --cp-text-700:#444444;
            --cp-text-900:#000000;
        }
        html, body { font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji','Segoe UI Emoji', 'Segoe UI Symbol' }
    </style>
</head>
<body class="min-h-full bg-[var(--cp-bg-50)] text-[var(--cp-text-800)]">
    <!-- NAVBAR -->
    <header class="sticky top-0 z-40 backdrop-blur bg-white/90 border-b border-[var(--cp-bg-200)]">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="h-16 flex items-center justify-between">
                <a href="{{ route('home') ?? url('/') }}" class="flex items-center gap-3 group">
                    {{-- Logo (usa tu imagen si existe en public/images) --}}
                    <div class="h-9 w-9 rounded-2xl bg-[var(--cp-primary)]/10 flex items-center justify-center ring-1 ring-[var(--cp-primary)]/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[var(--cp-primary)]" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7 4h10a2 2 0 012 2v2H5V6a2 2 0 012-2zm-2 6h14l-1.2 7.2A2 2 0 0115.83 19H8.17a2 2 0 01-1.97-1.8L5 10zm6 7a1 1 0 102 0 1 1 0 00-2 0z"/>
                        </svg>
                    </div>
                    <span class="text-lg font-extrabold tracking-tight text-[var(--cp-text-900)]">Comercio<span class="text-[var(--cp-primary)]">Plus</span></span>
                </a>

                {{-- Links desktop --}}
                <div class="hidden md:flex items-center gap-6">
                    <a href="{{ route('home') ?? url('/') }}" class="text-sm font-medium text-[var(--cp-text-800)] hover:text-[var(--cp-primary)] transition">
                        Inicio
                    </a>
                    @if(Route::has('public.store.show'))
                        {{-- Demo: ajusta el slug si tienes una tienda de demo --}}
                        <a href="{{ route('public.store.show', ['slug' => 'demo']) }}" class="text-sm font-medium text-[var(--cp-text-800)] hover:text-[var(--cp-primary)] transition">
                            Catálogo
                        </a>
                    @endif
                </div>

                {{-- Acciones (auth) --}}
                <div class="hidden md:flex items-center gap-3">
                    @auth
                        @role('comerciante')
                            <a href="{{ route('products.create') }}" class="px-4 py-2 rounded-xl text-sm font-semibold bg-[var(--cp-primary)] text-white hover:opacity-90 transition">
                                + Producto
                            </a>
                            <a href="{{ route('store.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium border border-[var(--cp-bg-200)] hover:bg-[var(--cp-bg-100)] transition">
                                Mi Tienda
                            </a>
                        @endrole

                        @role('admin')
                            @if(Route::has('admin.dashboard'))
                                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 rounded-xl text-sm font-medium border border-[var(--cp-bg-200)] hover:bg-[var(--cp-bg-100)] transition">
                                    Admin
                                </a>
                            @endif
                        @endrole

                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button class="px-4 py-2 rounded-xl text-sm font-medium border border-[var(--cp-bg-200)] hover:bg-[var(--cp-bg-100)] transition">
                                Cerrar sesión
                            </button>
                        </form>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl text-sm font-medium border border-[var(--cp-bg-200)] hover:bg-[var(--cp-bg-100)] transition">
                                Iniciar sesión
                            </a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-4 py-2 rounded-xl text-sm font-semibold bg-[var(--cp-primary)] text-white hover:opacity-90 transition">
                                Crear cuenta
                            </a>
                        @endif
                    @endauth
                </div>

                {{-- Botón mobile --}}
                <button id="cp-mobile-open" class="md:hidden inline-flex items-center justify-center rounded-xl p-2 hover:bg-[var(--cp-bg-100)]" aria-label="Abrir menú">
                    <svg class="h-6 w-6 text-[var(--cp-text-900)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </nav>

        {{-- Menú móvil --}}
        <div id="cp-mobile-menu" class="md:hidden hidden border-t border-[var(--cp-bg-200)] bg-white">
            <div class="px-4 py-3 space-y-2">
                <a href="{{ route('home') ?? url('/') }}" class="block px-3 py-2 rounded-lg text-[var(--cp-text-800)] hover:bg-[var(--cp-bg-100)]">Inicio</a>
                @if(Route::has('public.store.show'))
                    <a href="{{ route('public.store.show', ['slug' => 'demo']) }}" class="block px-3 py-2 rounded-lg text-[var(--cp-text-800)] hover:bg-[var(--cp-bg-100)]">Catálogo</a>
                @endif

                @auth
                    @role('comerciante')
                        <a href="{{ route('products.create') }}" class="block px-3 py-2 rounded-lg bg-[var(--cp-primary)] text-white">+ Producto</a>
                        <a href="{{ route('store.index') }}" class="block px-3 py-2 rounded-lg border border-[var(--cp-bg-200)]">Mi Tienda</a>
                    @endrole
                    @role('admin')
                        @if(Route::has('admin.dashboard'))
                            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg border border-[var(--cp-bg-200)]">Admin</a>
                        @endif
                    @endrole
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button class="w-full text-left px-3 py-2 rounded-lg border border-[var(--cp-bg-200)]">Cerrar sesión</button>
                    </form>
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="block px-3 py-2 rounded-lg border border-[var(--cp-bg-200)]">Iniciar sesión</a>
                    @endif
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="block px-3 py-2 rounded-lg bg-[var(--cp-primary)] text-white">Crear cuenta</a>
                    @endif
                @endauth
            </div>
        </div>
    </header>

    {{-- CONTENIDO --}}
    <main class="min-h-[calc(100vh-64px-200px)]">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="mt-16 border-t border-[var(--cp-bg-200)] bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid gap-8 md:grid-cols-3">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="h-8 w-8 rounded-xl bg-[var(--cp-primary)]/10 flex items-center justify-center ring-1 ring-[var(--cp-primary)]/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[var(--cp-primary)]" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 4h10a2 2 0 012 2v2H5V6a2 2 0 012-2zM5 10h14l-1.2 7.2A2 2 0 0115.83 19H8.17a2 2 0 01-1.97-1.8L5 10z"/>
                            </svg>
                        </div>
                        <span class="font-extrabold tracking-tight text-[var(--cp-text-900)]">Comercio<span class="text-[var(--cp-primary)]">Plus</span></span>
                    </div>
                    <p class="text-sm text-[var(--cp-text-700)]">
                        Plataforma minimalista para crear tu tienda y compartir tu catálogo en minutos.
                    </p>
                </div>
                <div class="space-y-2">
                    <h3 class="text-sm font-semibold text-[var(--cp-text-900)]">Enlaces</h3>
                    <div class="flex flex-col text-sm">
                        <a href="{{ route('home') ?? url('/') }}" class="hover:text-[var(--cp-primary)]">Inicio</a>
                        @if(Route::has('public.store.show'))
                            <a href="{{ route('public.store.show', ['slug' => 'demo']) }}" class="hover:text-[var(--cp-primary)]">Catálogo</a>
                        @endif
                        @auth
                            <a href="{{ route('products.index') }}" class="hover:text-[var(--cp-primary)]">Mis productos</a>
                        @endauth
                    </div>
                </div>
                <div class="space-y-2">
                    <h3 class="text-sm font-semibold text-[var(--cp-text-900)]">Soporte</h3>
                    <p class="text-sm text-[var(--cp-text-700)]">¿Dudas? Escríbenos y con gusto te ayudamos.</p>
                    <a href="mailto:soporte@comercioplus.app" class="inline-flex items-center gap-2 text-sm text-[var(--cp-primary)] font-semibold">
                        soporte@comercioplus.app
                    </a>
                </div>
            </div>
            <div class="mt-8 pt-6 border-t border-[var(--cp-bg-200)] text-sm text-[var(--cp-text-700)] flex items-center justify-between">
                <span>© {{ now()->year }} ComercioPlus. Todos los derechos reservados.</span>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-[var(--cp-primary)]">Términos</a>
                    <a href="#" class="hover:text-[var(--cp-primary)]">Privacidad</a>
                </div>
            </div>
        </div>
    </footer>

    {{-- Toggle menú móvil (vanilla) --}}
    <script>
        const btnOpen = document.getElementById('cp-mobile-open');
        const mobile = document.getElementById('cp-mobile-menu');
        btnOpen?.addEventListener('click', () => {
            mobile.classList.toggle('hidden');
        });
    </script>
</body>
</html>
