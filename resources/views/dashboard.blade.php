<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - ComercioPlus</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .card {
            @apply bg-gray-800 border border-gray-700 rounded-lg;
        }
        .smooth {
            @apply transition-all duration-200;
        }
    </style>
</head>
<body class="h-full bg-gray-900">
    <div class="min-h-full flex">
        <!-- Sidebar -->
        <nav class="w-64 bg-gray-800 border-r border-gray-700">
            <div class="p-6 border-b border-gray-700">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-sm">C</span>
                    </div>
                    <span class="text-xl font-bold text-white">
                        Comercio<span class="text-orange-500">Plus</span>
                    </span>
                </div>
            </div>

            <div class="p-4 space-y-2">
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition {{ request()->routeIs('dashboard') ? 'bg-orange-500 text-white' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('dashboard.products.index') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition {{ request()->routeIs('dashboard.products.*') ? 'bg-orange-500 text-white' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span>Productos</span>
                </a>

                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span>Categorías</span>
                </a>

                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span>Pedidos</span>
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1">
            <!-- Header -->
            <header class="bg-gray-800 border-b border-gray-700 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-white">@yield('title', 'Dashboard')</h1>
                        <p class="text-sm text-gray-400">{{ auth()->user()->stores->first()?->name ?? 'Mi Tienda' }}</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-300">{{ auth()->user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-red-400 hover:text-red-300 font-medium">
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="p-6">
                @if(session('success'))
                    <div class="mb-4 bg-green-900/50 border border-green-700 text-green-300 px-4 py-3 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-900/50 border border-red-700 text-red-300 px-4 py-3 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>