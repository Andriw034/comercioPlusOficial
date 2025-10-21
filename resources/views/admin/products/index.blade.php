@extends('layouts.dashboard')

@section('title', 'Productos')

@section('content')
<div class="mx-auto max-w-7xl p-6">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-100">Productos</h1>
        <a href="{{ route('admin.products.create') }}"
           class="rounded-lg bg-orange-600 px-4 py-2 text-white shadow hover:bg-orange-700 transition">
            Nuevo producto
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg border border-emerald-700/60 bg-emerald-900/40 px-4 py-3 text-emerald-100">
            {{ session('success') }}
        </div>
    @endif

    @if($products->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-700 p-10 text-center text-gray-400">
            No hay productos
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($products as $product)
                <div class="group flex flex-col overflow-hidden rounded-2xl border border-gray-700 bg-gray-900/40 shadow-sm transition hover:-translate-y-0.5 hover:border-orange-500/70 hover:shadow-xl">
                    {{-- Imagen --}}
                    <a href="{{ route('admin.products.edit', $product) }}" class="block">
                        <div class="relative aspect-square w-full overflow-hidden bg-gray-800">
                            <img
                                src="{{ $product->image_url }}"
                                alt="{{ $product->name }}"
                                loading="lazy"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                onerror="this.onerror=null;this.src='{{ asset('images/no-image.png') }}';"
                            >
                            @if((int)$product->status !== 1)
                                <span class="absolute left-2 top-2 rounded-md bg-red-700/90 px-2 py-1 text-xs font-semibold text-red-50">
                                    Inactivo
                                </span>
                            @endif
                        </div>
                    </a>

                    {{-- Info --}}
                    <div class="flex flex-1 flex-col p-4">
                        <h3 class="line-clamp-2 text-base font-medium text-gray-100">{{ $product->name }}</h3>
                        <p class="mt-1 text-xs uppercase tracking-wide text-gray-400">
                            {{ optional($product->category)->name ?? 'Sin categoría' }}
                        </p>

                        <div class="mt-3 flex items-center justify-between">
                            <p class="text-lg font-semibold text-gray-100">
                                ${{ number_format((float)$product->price, 0, ',', '.') }}
                            </p>
                            <p class="text-sm text-gray-300">
                                Stock: <span class="font-medium">{{ $product->stock }}</span>
                            </p>
                        </div>

                        {{-- Acciones --}}
                        <div class="mt-4 flex items-center justify-between">
                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="inline-flex items-center rounded-lg border border-orange-600/70 px-3 py-2 text-sm font-medium text-orange-400 hover:bg-orange-600 hover:text-white transition">
                                Editar
                            </a>

                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                  onsubmit="return confirm('¿Eliminar producto?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center rounded-lg border border-red-600/60 px-3 py-2 text-sm font-medium text-red-300 hover:bg-red-600 hover:text-white transition">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Paginación --}}
        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
