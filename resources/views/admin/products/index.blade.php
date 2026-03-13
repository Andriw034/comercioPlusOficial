@extends('layouts.dashboard')

@section('title', 'Productos')

@section('content')
<div class="mx-auto max-w-7xl p-6">

    {{-- Header --}}
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-white">Productos</h1>

        <a href="{{ route('admin.products.create') }}"
           class="rounded-lg bg-orange-600 px-5 py-2.5 text-sm font-medium text-white shadow transition hover:bg-orange-700 focus:outline-none focus:ring-4 focus:ring-orange-800">
            Nuevo producto
        </a>
    </div>

    {{-- Grid de productos --}}
    @if($products->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-700 p-12 text-center text-gray-400">
            <h3 class="text-lg font-semibold">Aún no tienes productos</h3>
            <p class="mt-2">¡Crea tu primer producto para empezar a vender!</p>
        </div>
    @else
        <div class="grid gap-x-6 gap-y-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($products as $product)
                <div class="bg-white rounded-xl shadow-lg flex flex-col">
                    <div class="aspect-[4/3] rounded-t-lg overflow-hidden bg-gray-100">
                        <img src="{{ $product->image_path ? asset('storage/' . $product->image_path) : asset('images/no-image.png') }}"
                             alt="Imagen de {{ $product->name }}"
                             class="w-full h-full object-cover transition-transform duration-300 hover:scale-110">
                    </div>

                    <div class="p-4 flex flex-col flex-1">
                        <h3 class="font-semibold text-gray-900 text-lg leading-tight">
                            {{ $product->name }}
                        </h3>

                        <p class="mt-1 text-xs text-gray-500 uppercase tracking-wide">
                            {{ $product->category->name ?? 'Sin categoría' }}
                        </p>

                        <div class="flex justify-between items-center mt-4">
                            <span class="text-orange-500 font-semibold text-xl">
                                ${{ number_format($product->price, 0, ',', '.') }}
                            </span>
                            <span class="text-sm text-gray-600">
                                Stock: <span class="font-bold">{{ $product->stock }}</span>
                            </span>
                        </div>

                        <div class="grid grid-cols-3 gap-2 mt-5 pt-4 border-t border-gray-200">
                            {{-- Botón Editar --}}
                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="text-center px-3 py-2 rounded-md bg-orange-500 text-white text-sm font-semibold hover:bg-orange-600 transition-all">
                               Editar
                            </a>
                        
                            {{-- Botón Toggle Promoción --}}
                            <form action="{{ route('admin.products.toggle-promotion', $product) }}" method="POST">
                                @csrf
                                <button class="w-full px-3 py-2 rounded-md text-white text-sm font-semibold transition-all {{ $product->is_promo ? 'bg-gray-500 hover:bg-gray-600' : 'bg-blue-500 hover:bg-blue-600' }}">
                                    {{ $product->is_promo ? 'Quitar Promo' : 'Poner en Promo' }}
                                </button>
                            </form>
                        
                            {{-- Botón Eliminar --}}
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este producto?')">
                                @csrf
                                @method('DELETE')
                                <button class="w-full px-3 py-2 bg-red-600 text-white text-sm rounded-md font-semibold hover:bg-red-700 transition-all">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Paginación --}}
        @if ($products->hasPages())
            <div class="mt-10">
                {{ $products->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
