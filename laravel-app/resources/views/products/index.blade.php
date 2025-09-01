@extends('layouts.app')

@section('title', 'Productos - Comercio Plus')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Productos</h1>
        @auth
            <a href="{{ route('products.create') }}" class="bg-primary text-primary-foreground px-4 py-2 rounded hover:bg-primary/90">
                Crear Producto
            </a>
        @endauth
    </div>

    <!-- Filtros -->
    <div class="bg-card p-4 rounded-lg shadow mb-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium mb-1">Buscar</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       class="w-full px-3 py-2 border border-input rounded-md" placeholder="Buscar productos...">
            </div>
            <div class="min-w-48">
                <label for="category" class="block text-sm font-medium mb-1">Categoría</label>
                <select name="category" id="category" class="w-full px-3 py-2 border border-input rounded-md">
                    <option value="">Todas las categorías</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-32">
                <label for="sort" class="block text-sm font-medium mb-1">Ordenar por</label>
                <select name="sort" id="sort" class="w-full px-3 py-2 border border-input rounded-md">
                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Fecha</option>
                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nombre</option>
                    <option value="price" {{ request('sort') == 'price' ? 'selected' : '' }}>Precio</option>
                </select>
            </div>
            <div class="min-w-32">
                <label for="direction" class="block text-sm font-medium mb-1">Dirección</label>
                <select name="direction" id="direction" class="w-full px-3 py-2 border border-input rounded-md">
                    <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>Descendente</option>
                    <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Ascendente</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-primary text-primary-foreground px-4 py-2 rounded hover:bg-primary/90">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Productos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($products as $product)
            <div class="bg-card rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow">
                <div class="aspect-square bg-muted">
                    @if($product->image_path)
                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-muted-foreground">
                            <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-lg mb-2">{{ $product->name }}</h3>
                    <p class="text-muted-foreground text-sm mb-2">{{ Str::limit($product->description, 100) }}</p>
                    <div class="flex justify-between items-center">
                        <span class="text-2xl font-bold text-primary">${{ number_format($product->price, 0, ',', '.') }}</span>
                        <span class="text-sm text-muted-foreground">Stock: {{ $product->stock }}</span>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('products.show', $product) }}"
                           class="flex-1 bg-secondary text-secondary-foreground px-3 py-2 rounded text-center hover:bg-secondary/80">
                            Ver
                        </a>
                        @auth
                            @if($product->user_id == auth()->id())
                                <a href="{{ route('products.edit', $product) }}"
                                   class="bg-primary text-primary-foreground px-3 py-2 rounded hover:bg-primary/90">
                                    Editar
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-muted-foreground text-lg">No se encontraron productos.</p>
            </div>
        @endforelse
    </div>

    <!-- Paginación -->
    <div class="mt-8">
        {{ $products->appends(request()->query())->links() }}
    </div>
</div>
@endsection
