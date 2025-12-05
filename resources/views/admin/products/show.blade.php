@extends('layouts.dashboard')

@section('title', 'Detalle del producto — ComercioPlus')

@section('content')
<div class="mx-auto max-w-6xl p-6">
    {{-- Header + acciones --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-100">Detalle del Producto</h1>
            <p class="text-sm text-gray-400">ID #{{ $product->id }}</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.products.index') }}"
               class="rounded-lg border border-gray-600 px-4 py-2 text-gray-200 hover:bg-gray-700 transition">
                ← Volver
            </a>

            <a href="{{ route('admin.products.edit', $product) }}"
               class="rounded-lg border border-orange-600/70 px-4 py-2 text-orange-400 hover:bg-orange-600 hover:text-white transition">
                Editar
            </a>

            <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                  onsubmit="return confirm('¿Eliminar este producto? Esta acción no se puede deshacer.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="rounded-lg border border-red-600/60 px-4 py-2 text-red-300 hover:bg-red-600 hover:text-white transition">
                    Eliminar
                </button>
            </form>
        </div>
    </div>

    {{-- Contenido --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Imagen principal --}}
        <div class="rounded-2xl border border-gray-700 bg-gray-900/40 p-4">
            <div class="relative aspect-square w-full overflow-hidden rounded-xl bg-gray-800">
                <img
                    src="{{ $product->image_url }}"
                    alt="Imagen de {{ $product->name }}"
                    class="h-full w-full object-cover"
                    onerror="this.onerror=null;this.src='{{ asset('images/no-image.png') }}';"
                    loading="lazy"
                >
                @if ((int) $product->status !== 1)
                    <span class="absolute left-3 top-3 rounded-md bg-red-700/90 px-2 py-1 text-xs font-semibold text-red-50">
                        Inactivo
                    </span>
                @endif
            </div>
            <p class="mt-2 text-xs text-gray-400">
                Ruta: <span class="font-mono">{{ $product->image_path ?? '—' }}</span>
            </p>
        </div>

        {{-- Datos del producto --}}
        <div class="rounded-2xl border border-gray-700 bg-gray-900/40 p-6">
            <div class="mb-5">
                <h2 class="text-xl font-semibold text-gray-100">{{ $product->name }}</h2>
                <p class="mt-1 text-sm text-gray-400">
                    {{ $product->description ?: 'Sin descripción' }}
                </p>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-400">Precio</dt>
                    <dd class="text-lg font-semibold text-gray-100">
                        {{ $product->price_formatted ?? '$'.number_format((float)$product->price, 0, ',', '.') }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-400">Stock</dt>
                    <dd class="text-base font-medium text-gray-100">{{ (int) $product->stock }}</dd>
                </div>

                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-400">Categoría</dt>
                    <dd class="text-base text-gray-100">
                        {{ optional($product->category)->name ?? 'Sin categoría' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-400">Estado</dt>
                    <dd class="text-base">
                        @if ((int) $product->status === 1)
                            <span class="inline-flex items-center rounded-md bg-emerald-700/30 px-2 py-1 text-sm font-medium text-emerald-200 border border-emerald-600/40">
                                Activo
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-md bg-red-800/30 px-2 py-1 text-sm font-medium text-red-200 border border-red-700/40">
                                Inactivo
                            </span>
                        @endif
                    </dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-xs uppercase tracking-wide text-gray-400">Slug</dt>
                    <dd class="font-mono text-sm text-gray-200">{{ $product->slug }}</dd>
                </div>

                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-400">Creado</dt>
                    <dd class="text-sm text-gray-300">
                        {{ optional($product->created_at)->format('Y-m-d H:i') ?? '—' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-400">Actualizado</dt>
                    <dd class="text-sm text-gray-300">
                        {{ optional($product->updated_at)->format('Y-m-d H:i') ?? '—' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Relación rápida (opcional): categoría/otros productos de la categoría --}}
    @if(isset($related) && $related->count())
        <div class="mt-10">
            <h3 class="mb-4 text-lg font-semibold text-gray-100">Otros productos de “{{ optional($product->category)->name }}”</h3>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($related as $r)
                    <a href="{{ route('admin.products.edit', $r) }}"
                       class="group overflow-hidden rounded-xl border border-gray-700 bg-gray-900/40 hover:border-orange-500/70 transition">
                        <div class="relative aspect-square w-full overflow-hidden bg-gray-800">
                            <img src="{{ $r->image_url }}" alt="{{ $r->name }}"
                                 class="h-full w-full object-cover group-hover:scale-105 transition"
                                 loading="lazy"
                                 onerror="this.onerror=null;this.src='{{ asset('images/no-image.png') }}';">
                        </div>
                        <div class="p-3">
                            <p class="line-clamp-1 text-sm font-medium text-gray-100">{{ $r->name }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $r->price_formatted ?? '$'.number_format((float)$r->price, 0, ',', '.') }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
