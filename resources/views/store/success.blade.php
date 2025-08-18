@extends('layouts.app')

@section('title', 'Tienda Creada')

@section('content')
<div class="max-w-xl mx-auto mt-12 text-center">
    <div class="bg-white rounded-2xl shadow-lg p-8 border border-green-200">
        <h2 class="text-3xl font-bold text-green-600 mb-4">🎉 ¡Tienda creada con éxito!</h2>
        <p class="text-gray-700 mb-6">Tu tienda ya está lista. Ahora agrega tus primeros productos.</p>
        <a href="{{ route('products.create') }}" class="btn-primary inline-block">
            Ir a Crear Productos
        </a>
    </div>
</div>
@endsection