@extends('layouts.dashboard')

@section('title', 'Crear tienda — ComercioPlus')

@section('content')
<div class="w-full bg-gray-50 min-h-screen">
  <div class="mx-auto max-w-4xl p-6">

    {{-- Encabezado --}}
    <header class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Crear tienda</h1>
      <p class="text-gray-600">Completa los datos de tu tienda. Al finalizar verás el logo y la portada aplicados en tu panel de productos.</p>
    </header>

    {{-- Formulario de creación --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-md p-8">
      <form method="POST" action="{{ route('store.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Nombre de la tienda --}}
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nombre de la tienda *</label>
          <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Ej: Mi Tienda Online"
                 class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('name') border-red-500 @enderror" required>
          @error('name')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
          @enderror
        </div>

        {{-- Descripción --}}
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
          <textarea name="description" id="description" rows="4" placeholder="Describe brevemente tu tienda..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-vertical">{{ old('description') }}</textarea>
          @error('description')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
          @enderror
        </div>

        {{-- Color primario --}}
        <div>
          <label for="primary_color" class="block text-sm font-medium text-gray-700 mb-2">Color primario</label>
          <input type="color" name="primary_color" id="primary_color" value="{{ old('primary_color', '#ff6600') }}"
                 class="w-16 h-10 border border-gray-300 rounded-lg cursor-pointer">
          <span class="ml-3 text-sm text-gray-600">Selecciona el color principal de tu tienda (opcional)</span>
          @error('primary_color')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
          @enderror
        </div>

        {{-- Botones --}}
        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
          <a href="{{ route('dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg transition-colors">
            Cancelar
          </a>
          <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg transition-colors font-medium">
            Crear tienda
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
