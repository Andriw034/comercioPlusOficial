@extends('layouts.public')

@section('title', 'Catálogo de Productos')

@section('content')
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-center mb-8">Catálogo de Productos</h1>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
      @for ($i = 0; $i < 6; $i++)
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <img src="https://via.placeholder.com/300x200" alt="Producto" class="w-full h-48 object-cover">
          <div class="p-4">
            <h3 class="text-lg font-semibold mb-2">Nombre del Producto</h3>
            <p class="text-gray-600 text-sm mb-4">Breve descripción del producto.</p>
            <div class="flex items-center justify-between">
              <span class="text-xl font-bold text-gray-900">$99.99</span>
              <a href="#" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                Ver más
              </a>
            </div>
          </div>
        </div>
      @endfor
    </div>
  </div>
@endsection
