@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">{{ $product->name }}</h1>

        <div class="flex flex-col md:flex-row md:space-x-8">
            {{-- Imagen --}}
            <div class="w-full md:w-1/2">
                @if ($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                        class="w-full h-72 object-cover rounded-lg border shadow">
                @else
                    <div class="w-full h-72 bg-gray-100 flex items-center justify-center text-gray-500 rounded-lg">
                        Sin imagen
                    </div>
                @endif
            </div>

            {{-- Detalles --}}
            <div class="w-full md:w-1/2 mt-6 md:mt-0 space-y-4 text-gray-700">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><span class="font-semibold">ID:</span> {{ $product->id }}</div>
                    <div><span class="font-semibold">Stock:</span> {{ $product->stock }}</div>
                    <div><span class="font-semibold">Precio:</span> <span class="text-green-600 font-bold">${{ number_format($product->price, 2) }}</span></div>
                    <div>
                        <span class="font-semibold">Oferta:</span>
                        <span class="{{ $product->offer ? 'text-red-500' : 'text-gray-500' }}">
                            {{ $product->offer ? 'Sí' : 'No' }}
                        </span>
                    </div>
                    <div><span class="font-semibold">Categoría:</span> {{ $product->category->name ?? 'Sin categoría' }}</div>
                    <div><span class="font-semibold">Publicado por:</span> {{ $product->user->name ?? 'Desconocido' }}</div>
                    <div><span class="font-semibold">Rating promedio:</span> ⭐ {{ number_format($product->average_rating, 1) }}</div>
                    <div><span class="font-semibold">Creado:</span> {{ $product->created_at?->format('d/m/Y H:i') }}</div>
                    <div><span class="font-semibold">Actualizado:</span> {{ $product->updated_at?->format('d/m/Y H:i') }}</div>
                </div>

                {{-- Descripción --}}
                <div>
                    <h2 class="text-md font-semibold mt-4 mb-2">Descripción</h2>
                    <p class="bg-gray-50 p-3 border rounded-md text-sm leading-relaxed">
                        {{ $product->description ?? 'No disponible' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('products.index') }}"
                class="inline-flex items-center bg-primary text-black font-bold px-4 py-2 rounded hover:bg-primary-light transition">
                ← Volver a la lista
            </a>
        </div>
    </div>
</div>
@endsection
