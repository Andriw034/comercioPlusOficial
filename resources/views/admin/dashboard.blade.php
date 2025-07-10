@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Panel de Administración</h1>
        @if($logo)
            <img src="{{ asset('storage/' . $logo) }}" alt="Logo" class="h-16">
        @endif
    </div>

    <p class="mb-6 text-gray-700">Bienvenido al panel de administración. Aquí puedes gestionar tu tienda y productos.</p>

    <section>
        <h2 class="text-2xl font-semibold mb-4">Productos Destacados</h2>
        @if($products->count() > 0)
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
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $product->name }}</h3>
                    <p class="text-gray-700 mb-4">{{ $product->category->name ?? 'Sin categoría' }}</p>
                    <a href="{{ route('products.edit', $product) }}" class="mt-auto bg-primary text-white py-2 rounded hover:bg-primary-light transition-colors duration-300 text-center font-semibold">Editar Producto</a>
                </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        @else
            <p class="text-gray-600">No hay productos disponibles.</p>
        @endif
    </section>
</div>
@endsection
