@extends('layouts.dashboard')

@section('title', 'Dashboard — Comerciante')

@section('content')
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Welcome + status card -->
        <div class="card p-6 col-span-2">
            <h2 class="text-2xl font-bold text-white mb-2">¡Bienvenido a tu panel!</h2>
            <p class="text-gray-300 mb-4">Gestiona tu catálogo, personaliza tu tienda y empieza a vender.</p>

            @if(auth()->user()->stores->isEmpty())
                <p class="text-yellow-400 mb-4">No tienes una tienda configurada aún.</p>
            @else
                <p class="text-green-400 mb-4">Tu tienda está lista para usar.</p>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('admin.products.create') }}" class="p-4 rounded-lg bg-white/5 smooth hover:bg-white/6">
                    <div class="text-sm text-gray-200">Catálogo</div>
                    <div class="mt-2 font-semibold text-white">Agregar producto</div>
                </a>

                <a href="{{ route('admin.store.appearance') }}" class="p-4 rounded-lg bg-white/5 smooth hover:bg-white/6">
                    <div class="text-sm text-gray-200">Tienda</div>
                    <div class="mt-2 font-semibold text-white">Logo y portada</div>
                </a>

                <a href="{{ route('admin.categories.index') }}" class="p-4 rounded-lg bg-white/5 smooth hover:bg-white/6">
                    <div class="text-sm text-gray-200">Catálogo</div>
                    <div class="mt-2 font-semibold text-white">Categorías</div>
                </a>
            </div>
        </div>

        <aside class="card p-6">
            <div class="text-xs text-gray-400">Estado</div>
            <h3 class="text-lg font-semibold text-white mt-2">
                @if(auth()->user()->stores->isEmpty())
                    Tienda sin publicar
                @else
                    Tienda publicada
                @endif
            </h3>
            <p class="text-gray-300 mt-2">
                @if(auth()->user()->stores->isEmpty())
                    Configura logo, portada y datos básicos para publicar.
                @else
                    Tu tienda está activa y lista para recibir pedidos.
                @endif
            </p>
            <a href="{{ route('store.wizard') }}" class="inline-block mt-4 px-4 py-2 rounded-full bg-orange-500 text-white smooth hover:brightness-95">
                @if(auth()->user()->stores->isEmpty())
                    Completar configuración
                @else
                    Ver tienda
                @endif
            </a>
        </aside>
    </section>

    <!-- KPI cards -->
    <section class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="card p-4 smooth">
            <div class="text-xs text-gray-400">Ventas hoy</div>
            <div class="text-xl font-bold text-white">$0</div>
            <div class="text-xs text-gray-400">0 órdenes</div>
        </div>
        <div class="card p-4 smooth">
            <div class="text-xs text-gray-400">Productos</div>
            <div class="text-xl font-bold text-white">{{ \App\Models\Product::where('store_id', auth()->user()->stores->first()?->id ?? 0)->count() ?? 0 }}</div>
            <div class="text-xs text-gray-400">En catálogo</div>
        </div>
        <div class="card p-4 smooth">
            <div class="text-xs text-gray-400">Visitas</div>
            <div class="text-xl font-bold text-white">0</div>
            <div class="text-xs text-gray-400">Últimas 24h</div>
        </div>
    </section>

    <!-- Recent activity / empty state placeholder -->
    <section class="card p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-lg font-semibold text-white">Actividad reciente</h4>
            <a href="#" class="text-sm text-gray-300">Ver todas</a>
        </div>
        <div class="text-gray-400">Aún no hay actividad para mostrar. Agrega productos o configura tu tienda para empezar.</div>
    </section>
@endsection
