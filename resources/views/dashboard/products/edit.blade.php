@extends('layouts.app')

@section('title', 'Editar Producto')

@section('content')
<div class="container py-8">
  <div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
      <a href="{{ route('dashboard.products.index') }}" class="text-muted-foreground hover:text-foreground">
        ← Volver a productos
      </a>
      <h1 class="text-2xl font-bold">Editar Producto</h1>
    </div>

    <form action="{{ route('dashboard.products.update', $product['id']) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
      @csrf
      @method('PUT')

      <div>
        <label for="name" class="block text-sm font-medium mb-2">Nombre del producto</label>
        <input type="text" id="name" name="name" value="{{ old('name', $product['name']) }}" required
               class="w-full px-3 py-2 border border-input rounded-md focus:outline-none focus:ring-2 focus:ring-ring">
        @error('name')
          <p class="text-sm text-destructive mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label for="description" class="block text-sm font-medium mb-2">Descripción</label>
        <textarea id="description" name="description" rows="4"
                  class="w-full px-3 py-2 border border-input rounded-md focus:outline-none focus:ring-2 focus:ring-ring">{{ old('description', $product['description']) }}</textarea>
        @error('description')
          <p class="text-sm text-destructive mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label for="price" class="block text-sm font-medium mb-2">Precio (COP)</label>
          <input type="number" id="price" name="price" value="{{ old('price', $product['price']) }}" step="0.01" min="0" required
                 class="w-full px-3 py-2 border border-input rounded-md focus:outline-none focus:ring-2 focus:ring-ring">
          @error('price')
            <p class="text-sm text-destructive mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="stock" class="block text-sm font-medium mb-2">Stock</label>
          <input type="number" id="stock" name="stock" value="{{ old('stock', $product['stock']) }}" min="0" required
                 class="w-full px-3 py-2 border border-input rounded-md focus:outline-none focus:ring-2 focus:ring-ring">
          @error('stock')
            <p class="text-sm text-destructive mt-1">{{ $message }}</p>
          @enderror
        </div>
      </div>

      <div>
        <label for="category_id" class="block text-sm font-medium mb-2">Categoría</label>
        <select id="category_id" name="category_id" required
                class="w-full px-3 py-2 border border-input rounded-md focus:outline-none focus:ring-2 focus:ring-ring">
          <option value="">Seleccionar categoría</option>
          @foreach($categories as $category)
            <option value="{{ $category['id'] }}" {{ old('category_id', $product['category_id']) == $category['id'] ? 'selected' : '' }}>
              {{ $category['name'] }}
            </option>
          @endforeach
        </select>
        @error('category_id')
          <p class="text-sm text-destructive mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label for="image" class="block text-sm font-medium mb-2">Imagen del producto</label>
        <input type="file" id="image" name="image" accept="image/*"
               class="w-full px-3 py-2 border border-input rounded-md focus:outline-none focus:ring-2 focus:ring-ring">
        @if($product['image'])
          <div class="mt-2">
            <img src="{{ $product['image'] }}" alt="Imagen actual" class="w-32 h-32 object-cover rounded-md">
            <p class="text-sm text-muted-foreground mt-1">Imagen actual. Sube una nueva para reemplazarla.</p>
          </div>
        @endif
        @error('image')
          <p class="text-sm text-destructive mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="flex gap-4">
        <button type="submit" class="px-4 py-2 bg-primary text-primary-foreground rounded-md font-semibold hover:bg-primary/90">
          Actualizar Producto
        </button>
        <a href="{{ route('dashboard.products.index') }}" class="px-4 py-2 border border-input rounded-md font-semibold hover:bg-accent">
          Cancelar
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
