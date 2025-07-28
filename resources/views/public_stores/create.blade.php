@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto mt-10 bg-white shadow-md rounded-xl p-6 border border-gray-200">
    <h2 class="text-2xl font-bold text-orange-500 mb-6 text-center">Crear Nueva Tienda Pública</h2>

    {{-- Errores --}}
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('public_stores.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf

        {{-- Nombre de la tienda --}}
        <div>
            <label for="store_name" class="block text-sm font-medium text-gray-700 mb-1">Nombre de la tienda</label>
            <input type="text" name="store_name" id="store_name" value="{{ old('store_name') }}"
                   class="w-full border border-gray-300 rounded-md px-4 py-2 shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                   placeholder="Ej. Tienda Rápida" required>
        </div>

        {{-- Descripción --}}
        <div>
            <label for="store_description" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
            <textarea name="store_description" id="store_description" rows="4"
                      class="w-full border border-gray-300 rounded-md px-4 py-2 shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                      placeholder="Escribe una descripción de tu tienda..." required>{{ old('store_description') }}</textarea>
        </div>

        {{-- Imagen/logo --}}
        <div>
            <label for="store_image" class="block text-sm font-medium text-gray-700 mb-1">Logo de la tienda</label>
            <input type="file" name="store_image" id="store_image"
                   class="w-full border border-gray-300 rounded-md px-4 py-2 file:bg-orange-100 file:text-orange-700 file:border-0 file:rounded file:px-4 file:py-2 cursor-pointer" required>
        </div>

        {{-- Botón --}}
        <div class="text-right">
            <button type="submit"
                    class="bg-orange-500 text-white font-semibold px-6 py-2 rounded-lg shadow hover:bg-orange-600 transition">
                Guardar Tienda
            </button>
        </div>
    </form>
</div>
@endsection
