@extends('layouts.dashboard')

@section('title', 'Crear Producto Admin — ComercioPlus')

@section('content')
<div class="p-6 space-y-6">
    <div class="bg-white/10 ring-1 ring-white/15 rounded-3xl p-6">
        <h2 class="text-2xl font-bold mb-6 text-white">Agregar Producto</h2>

        @if ($errors->any())
            <div class="bg-red-500/20 border border-red-500/30 text-red-200 px-4 py-3 rounded-xl mb-6">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div>
                <label for="name" class="block font-semibold mb-2 text-white/90">Nombre</label>
                <input type="text" name="name" id="name" placeholder="Nombre del producto" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-xl text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-500 smooth" value="{{ old('name') }}" required>
            </div>
            <div>
                <label for="description" class="block font-semibold mb-2 text-white/90">Descripción</label>
                <textarea name="description" id="description" rows="5" placeholder="Descripción del producto" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-xl text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-500 smooth resize-vertical" required>{{ old('description') }}</textarea>
            </div>
            <div>
                <label for="price" class="block font-semibold mb-2 text-white/90">Precio</label>
                <input type="number" step="0.01" name="price" id="price" placeholder="Precio del producto" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-xl text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-500 smooth" value="{{ old('price') }}" required>
            </div>
            <div>
                <label for="store_id" class="block font-semibold mb-2 text-white/90">Tienda</label>
                <select name="store_id" id="store_id" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 smooth" required>
                    <option value="">Seleccione una tienda</option>
                    @foreach($stores as $store)
                    <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                        {{ $store->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="category_id" class="block font-semibold mb-2 text-white/90">Categoría</label>
                <select name="category_id" id="category_id" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 smooth" required>
                    <option value="">Seleccione una categoría</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="image" class="block font-semibold mb-2 text-white/90">Imagen</label>
                <input type="file" name="image" id="image" accept="image/*" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 smooth">
            </div>
            <button type="submit" class="px-6 py-2 text-black bg-orange-500 rounded-xl hover:bg-orange-600 font-semibold shadow smooth">Guardar Producto</button>
        </form>
    </div>
</div>
@endsection
