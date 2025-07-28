@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Banner de fondo --}}
    @if($store->background)
        <div class="w-full mb-6 rounded-lg overflow-hidden shadow">
            <img src="{{ asset('storage/' . $store->background) }}" alt="Fondo de la tienda" class="w-full h-64 object-cover">
        </div>
    @endif

    {{-- Encabezado: nombre + color primario --}}
    <h1 
        class="text-4xl font-bold text-center mb-4" 
        @if($store->primary_color) style="color: {{ $store->primary_color }}" @endif
    >
        {{ $store->name }}
    </h1>

    {{-- Logo --}}
    @if($store->logo)
        <div class="flex justify-center mb-4">
            <img src="{{ asset('storage/' . $store->logo) }}" alt="Logo de la tienda" class="h-28 object-contain rounded-lg shadow">
        </div>
    @endif

    {{-- Descripción --}}
    <p class="text-center text-gray-600 text-lg mb-8">
        {{ $store->description }}
    </p>

    {{-- Productos del comerciante --}}
    <h2 class="text-2xl font-semibold mb-6 text-center text-gray-800">Productos del comerciante</h2>

    @if($store->user->products->isEmpty())
        <p class="text-center text-gray-500">Este comerciante aún no ha agregado productos.</p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($store->user->products as $product)
                <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-48 object-cover">
                    @endif
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-1">{{ $product->name }}</h3>
                            <p class="text-gray-700 text-sm mb-2">{{ $product->description }}</p>
                        </div>

                        <div>
                            <p class="text-green-600 font-bold text-lg mb-3">${{ number_format($product->price, 0, ',', '.') }}</p>

                            {{-- WhatsApp --}}
                            <a 
                                href="https://wa.me/{{ $store->user->phone }}" 
                                target="_blank"
                                class="block text-center bg-green-500 hover:bg-green-600 text-white font-semibold py-2 rounded mb-2 transition"
                            >
                                Contactar por WhatsApp
                            </a>

                            {{-- Agregar al carrito --}}
                            <form action="{{ route('cart.add', $product->id) }}" method="POST">
                                @csrf
                                <button class="w-full bg-orange-500 hover:bg-orange-600 text-white py-2 rounded font-semibold transition">
                                    Agregar al carrito
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection





