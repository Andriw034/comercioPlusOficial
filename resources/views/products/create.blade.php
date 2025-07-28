@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-12">
    <div class="bg-white shadow-xl rounded-3xl p-10 border border-gray-100">
        <h2 class="text-3xl font-extrabold text-orange-500 mb-8">Agregar Producto</h2>

        {{-- Mensajes de error --}}
        @if ($errors->any())
            <div class="bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc pl-5 space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Formulario --}}
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Nombre --}}
            <div>
                <label for="name" class="block text-gray-700 font-medium mb-1">Nombre del Producto</label>
                <input type="text" name="name" id="name" placeholder="Ej: Casco deportivo"
                    class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-400 focus:outline-none"
                    value="{{ old('name') }}" required>
            </div>

            {{-- Descripción --}}
            <div>
                <label for="description" class="block text-gray-700 font-medium mb-1">Descripción</label>
                <textarea name="description" id="description" rows="4"
                    placeholder="Escribe una breve descripción del producto"
                    class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-400 focus:outline-none"
                    required>{{ old('description') }}</textarea>
            </div>

            {{-- Precio y Stock --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="price" class="block text-gray-700 font-medium mb-1">Precio ($)</label>
                    <input type="number" step="0.01" name="price" id="price"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-400 focus:outline-none"
                        value="{{ old('price') }}" placeholder="0.00" required>
                </div>
                <div>
                    <label for="stock" class="block text-gray-700 font-medium mb-1">Stock</label>
                    <input type="number" name="stock" id="stock"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-400 focus:outline-none"
                        value="{{ old('stock') }}" placeholder="Cantidad disponible" required>
                </div>
            </div>

            {{-- Categoría --}}
            <div>
                <label for="category_id" class="block text-gray-700 font-medium mb-1">Categoría</label>
                <select name="category_id" id="category_id"
                    class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-400 focus:outline-none"
                    required>
                    <option value="">Seleccione una categoría</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Imagen --}}
            <div>
                <label for="image" class="block text-gray-700 font-medium mb-1">Imagen del producto</label>
                <input type="file" name="image" id="image" accept="image/*"
                    class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-400 focus:outline-none">
            </div>

            {{-- Botón --}}
            <div class="pt-4">
                <button type="submit"
                    class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-3 rounded-xl shadow transition duration-300">
                    Guardar Producto
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
