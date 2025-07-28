@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto mt-10 bg-white p-6 shadow-md rounded-lg border border-gray-200">
    <h2 class="text-2xl font-bold text-orange-500 mb-6 text-center">Editar Tienda Pública</h2>

    {{-- Errores de validación --}}
    @if ($errors->any())
        <div class="mb-4 bg-red-100 border border-red-300 text-red-700 p-4 rounded">
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('public_stores.update', $store->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Nombre de la tienda --}}
        <div>
            <label for="nombre_tienda" class="block text-sm font-semibold text-gray-700 mb-1">Nombre de la tienda</label>
            <input type="text" name="nombre_tienda" id="nombre_tienda"
                   value="{{ old('nombre_tienda', $store->nombre_tienda) }}"
                   class="w-full border border-gray-300 rounded-md px-4 py-2 shadow-sm focus:ring-orange-500 focus:border-orange-500"
                   placeholder="Ingresa el nombre de la tienda" required>
        </div>

        {{-- Descripción --}}
        <div>
            <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-1">Descripción</label>
            <textarea name="descripcion" id="descripcion" rows="4"
                      class="w-full border border-gray-300 rounded-md px-4 py-2 shadow-sm focus:ring-orange-500 focus:border-orange-500"
                      placeholder="Agrega una descripción corta de la tienda" required>{{ old('descripcion', $store->descripcion) }}</textarea>
        </div>

        {{-- Logo --}}
        <div>
            <label for="logo" class="block text-sm font-semibold text-gray-700 mb-1">Logo de la tienda (opcional)</label>
            <input type="file" name="logo" id="logo"
                   class="w-full border border-gray-300 rounded-md px-4 py-2 file:bg-orange-100 file:text-orange-700 file:border-0 file:rounded file:px-4 file:py-2 cursor-pointer">

            @if($store->logo)
                <div class="mt-3">
                    <p class="text-sm text-gray-600 mb-1">Logo actual:</p>
                    <img src="{{ asset('storage/' . $store->logo) }}" alt="Logo actual" class="h-20 rounded shadow">
                </div>
            @endif
        </div>

        {{-- Botón de envío --}}
        <div class="text-right">
            <button type="submit"
                    class="bg-orange-500 text-white font-semibold px-6 py-2 rounded-lg shadow hover:bg-orange-600 transition duration-200">
                Actualizar Tienda
            </button>
        </div>
    </form>
</div>
@endsection
