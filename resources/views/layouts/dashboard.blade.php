<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Comercio') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 text-gray-100">

    <div class="min-h-screen flex">

        {{-- Sidebar --}}
        <aside class="w-64 bg-gray-800 border-r border-gray-700 p-4">
            <div class="mb-6">
                <div class="flex items-center gap-3">
                    <span class="text-xl font-bold text-white">
                        Comercio<span class="text-orange-500">Plus</span>
                    </span>
                </div>
            </div>

            <nav class="space-y-1">
                {{-- Dashboard (nota: existe 'dashboard' y 'admin.dashboard' en rutas; dejamos el link al admin dashboard) --}}
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition {{ request()->routeIs('admin.dashboard') ? 'bg-orange-500 text-white' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                {{-- Productos: apuntar a admin.products.index --}}
                <a href="{{ route('admin.products.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition {{ request()->routeIs('admin.products.*') ? 'bg-orange-500 text-white' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span>Productos</span>
                </a>

                {{-- Categorías: apuntar a admin.categories.index --}}
                <a href="{{ route('admin.categories.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition {{ request()->routeIs('admin.categories.*') ? 'bg-orange-500 text-white' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span>Categorías</span>
                </a>

                {{-- Otros enlaces (ajusta nombres de ruta si cambian) --}}
                <a href="{{ route('admin.store.appearance') ?? '#' }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5v14"/></svg>
                    <span>Configuración</span>
                </a>
            </nav>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 min-h-screen">
            {{-- Topbar --}}
            <header class="bg-gray-900 border-b border-gray-800 p-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button id="menu-toggle" class="text-gray-300 md:hidden">☰</button>
                    <h1 class="text-lg font-semibold">{{ $title ?? 'Panel de control' }}</h1>
                </div>

                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-300">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-300 hover:text-white">Cerrar sesión</button>
                    </form>
                </div>
            </header>

            {{-- Content area --}}
            <main class="p-6">
                {{-- Mensajes de sesión --}}
                @if(session('success'))
                    <div class="mb-4 p-3 bg-emerald-800 text-emerald-100 rounded">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="mb-4 p-3 bg-rose-800 text-rose-100 rounded">{{ session('error') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    {{-- Optional scripts --}}
    @stack('scripts')
</body>
</html>
