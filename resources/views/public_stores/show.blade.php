@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-6 bg-white rounded-xl shadow-md mt-6">

    {{-- Imagen del logo --}}
    <div class="flex justify-center mb-4">
        <img src="{{ $store->logo ? asset('storage/' . $store->logo) : asset('images/default-store.png') }}"
             alt="Logo de {{ $store->nombre_tienda }}"
             class="w-32 h-32 object-contain border rounded-full shadow-sm">
    </div>

    {{-- Nombre de la tienda --}}
    <h2 class="text-3xl font-bold text-center text-orange-500 mb-2">{{ $store->nombre_tienda }}</h2>

    {{-- Descripción --}}
    <p class="text-gray-700 text-center mb-2">{{ $store->descripcion }}</p>

    {{-- Dirección u ubicación --}}
    <p class="text-gray-500 text-sm text-center mb-4">
        <strong>Ubicación:</strong> {{ $store->direccion ?? 'No definida' }}
    </p>

    {{-- Botones de acción para el dueño --}}
    @if(auth()->check() && auth()->id() === $store->user_id)
    <div class="flex justify-center space-x-4 mt-6">
        {{-- Botón Editar --}}
        <a href="{{ route('public_stores.edit', $store->id) }}"
           class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded shadow">
            Editar
        </a>

        {{-- Botón Eliminar --}}
        <form action="{{ route('public_stores.destroy', $store->id) }}" method="POST"
              onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta tienda?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded shadow">
                Eliminar
            </button>
        </form>
    </div>
    @endif

    {{-- Botón de regreso --}}
    <div class="text-center mt-6">
        <a href="{{ route('public_stores.index') }}"
           class="text-orange-500 hover:underline font-semibold text-sm">
            ← Volver al listado
        </a>
    </div>
</div>
@endsection
