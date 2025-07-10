@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-4 md:mb-0">Productos</h1>
        <a href="{{ route('products.create') }}" class="bg-primary text-white px-5 py-3 rounded font-semibold hover:bg-primary-light transition-colors duration-300">Agregar Producto</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded mb-6 shadow">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($products as $product)
        <div class="bg-white rounded shadow p-4 flex flex-col">
            <div class="h-48 w-full mb-4 overflow-hidden rounded">
                @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="object-cover h-full w-full transition-transform duration-300 hover:scale-105">
                @else
                <div class="flex items-center justify-center h-full bg-gray-200 text-gray-500">Sin imagen</div>
                @endif
            </div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ $product->name }}</h2>
            <p class="text-gray-700 mb-4">{{ $product->category->name ?? 'Sin categoría' }}</p>
            <div class="mt-auto flex space-x-3">
                <a href="{{ route('products.edit', $product) }}" class="flex-1 bg-primary text-white text-center py-2 rounded hover:bg-primary-light transition-colors duration-300">Editar</a>
                <form action="{{ route('products.destroy', $product) }}" method="POST" class="flex-1" onsubmit="return confirm('¿Está seguro de eliminar este producto?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 transition-colors duration-300">Eliminar</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $products->links() }}
    </div>
</div>
@endsection
