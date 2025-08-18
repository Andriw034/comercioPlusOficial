@extends('layouts.app')

@section('title', 'Configuración de la Tienda')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-12">
    <div class="bg-white rounded-3xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-[#FF6000] to-[#FF8A3D] px-8 py-6 text-white">
            <h1 class="text-3xl font-bold">🛠️ Personaliza tu Tienda</h1>
            <p class="opacity-90">Cambia el look de tu tienda en línea</p>
        </div>

        <form action="{{ route('store.config.update') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-8">
            @csrf
            @method('PUT')

            <!-- Nombre y Descripción -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Nombre de la Tienda</label>
                    <input type="text" name="name" value="{{ old('name', $store->name) }}"
                           class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-1">Descripción</label>
                    <textarea name="description" rows="1"
                              class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none">{{ old('description', $store->description) }}</textarea>
                </div>
            </div>

            <!-- Logo -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Logo</label>
                @if($store->logo)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $store->logo) }}" alt="Logo actual" class="h-16 object-contain">
                    </div>
                @endif
                <input type="file" name="logo" accept="image/*"
                       class="w-full text-sm file:py-2 file:px-4 file:rounded-md file:bg-gray-100 hover:file:bg-gray-200">
            </div>

            <!-- Portada -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Portada</label>
                @if($store->cover_image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $store->cover_image) }}" alt="Portada actual" class="w-full h-32 object-cover rounded-lg">
                    </div>
                @endif
                <input type="file" name="cover" accept="image/*"
                       class="w-full text-sm file:py-2 file:px-4 file:rounded-md file:bg-gray-100 hover:file:bg-gray-200">
            </div>

            <!-- Colores -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Color Principal</label>
                    <input type="color" name="primary_color" value="{{ old('primary_color', $store->primary_color ?? '#FF6000') }}"
                           class="w-full h-12 rounded-lg border border-gray-300 cursor-pointer">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-1">Color de Fondo</label>
                    <input type="color" name="background_color" value="{{ old('background_color', $store->background_color ?? '#f9f9f9') }}"
                           class="w-full h-12 rounded-lg border border-gray-300 cursor-pointer">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-1">Color de Texto</label>
                    <input type="color" name="text_color" value="{{ old('text_color', $store->text_color ?? '#333333') }}"
                           class="w-full h-12 rounded-lg border border-gray-300 cursor-pointer">
                </div>
            </div>

            <!-- Botón -->
            <div class="pt-4">
                <button type="submit" class="bg-[#FF6000] hover:bg-orange-600 text-white font-bold px-6 py-3 rounded-xl shadow transition">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection