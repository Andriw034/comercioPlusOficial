@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-8 bg-[#0e0f12]">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-white mb-8 drop-shadow-md">Editar Producto</h1>

        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="bg-gray-800/50 backdrop-blur-sm border border-gray-600 p-8 rounded-xl shadow-xl">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="name" class="block text-sm font-semibold text-gray-200 mb-3">Nombre del Producto</label>
                <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" placeholder="Ingrese el nombre del producto"
                       class="w-full px-4 py-3 bg-gray-700/80 border border-gray-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent text-gray-100 placeholder-gray-400 @error('name') border-red-400 ring-red-400 @enderror transition-all"
                       required>
                @error('name')
                    <p class="text-red-300 text-sm mt-2 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="description" class="block text-sm font-semibold text-gray-200 mb-3">Descripción</label>
                <textarea name="description" id="description" rows="5" placeholder="Ingrese la descripción detallada del producto"
                          class="w-full px-4 py-3 bg-gray-700/80 border border-gray-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent text-gray-100 placeholder-gray-400 @error('description') border-red-400 ring-red-400 @enderror transition-all resize-vertical"
                          required>{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <p class="text-red-300 text-sm mt-2 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="price" class="block text-sm font-semibold text-gray-200 mb-3">Precio (S/.)</label>
                    <input type="number" name="price" id="price" step="0.01" min="0" value="{{ old('price', $product->price) }}" placeholder="0.00"
                           class="w-full px-4 py-3 bg-gray-700/80 border border-gray-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent text-gray-100 placeholder-gray-400 @error('price') border-red-400 ring-red-400 @enderror transition-all"
                           required>
                    @error('price')
                        <p class="text-red-300 text-sm mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="stock" class="block text-sm font-semibold text-gray-200 mb-3">Stock</label>
                    <input type="number" name="stock" id="stock" min="0" value="{{ old('stock', $product->stock) }}" placeholder="0"
                           class="w-full px-4 py-3 bg-gray-700/80 border border-gray-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent text-gray-100 placeholder-gray-400 @error('stock') border-red-400 ring-red-400 @enderror transition-all"
                           required>
                    @error('stock')
                        <p class="text-red-300 text-sm mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label for="category_id" class="block text-sm font-semibold text-gray-200 mb-3">Categoría</label>
                <select name="category_id" id="category_id"
                        class="w-full px-4 py-3 bg-gray-700/80 border border-gray-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent text-gray-100 @error('category_id') border-red-400 ring-red-400 @enderror transition-all"
                        required>
                    <option value="" class="text-gray-400">Seleccionar categoría</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }} class="text-gray-100">
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-red-300 text-sm mt-2 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-200 mb-3">Imagen Actual</label>
                @if($product->image)
                    <div class="mb-3">
                        <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-32 h-32 object-cover rounded-lg ring-1 ring-white/10">
                    </div>
                @else
                    <p class="text-gray-400 text-sm">No hay imagen actual</p>
                @endif
            </div>

            <div class="mb-8">
                <label for="image" class="block text-sm font-semibold text-gray-200 mb-3">Cambiar Imagen (opcional)</label>
                <input type="file" name="image" id="image" accept="image/*"
                       class="w-full px-4 py-3 bg-gray-700/80 border border-gray-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent text-gray-100 file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-500 file:text-white hover:file:bg-orange-600 @error('image') border-red-400 ring-red-400 @enderror transition-all">
                <p class="text-sm text-gray-400 mt-2">Deja vacío para mantener la imagen actual. Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB.</p>
                @error('image')
                    <p class="text-red-300 text-sm mt-2 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-4 pt-4 border-t border-gray-600">
                <a href="{{ route('admin.products.index') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-gray-100 px-6 py-3 rounded-lg transition-all duration-200 shadow-md">
                    Cancelar
                </a>
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg transition-all duration-200 shadow-md font-semibold">
                    Actualizar Producto
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
