@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow-md">
    <h2 class="text-3xl font-bold mb-6 text-gray-900">Editar Producto</h2>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-4 rounded mb-6 shadow">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        <div>
            <label for="name" class="block font-semibold mb-2 text-gray-700">Nombre</label>
            <input type="text" name="name" id="name" placeholder="Nombre del producto" class="w-full border border-gray-300 rounded px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary" value="{{ old('name', $product->name) }}" required>
        </div>
        <div>
            <label for="description" class="block font-semibold mb-2 text-gray-700">Descripción</label>
            <textarea name="description" id="description" rows="5" placeholder="Descripción del producto" class="w-full border border-gray-300 rounded px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary" required>{{ old('description', $product->description) }}</textarea>
        </div>
        <div>
            <label for="category_id" class="block font-semibold mb-2 text-gray-700">Categoría</label>
            <select name="category_id" id="category_id" class="w-full border border-gray-300 rounded px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary" required>
                <option value="">Seleccione una categoría</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="image" class="block font-semibold mb-2 text-gray-700">Imagen</label>
            @if($product->image)
            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-32 w-32 object-cover rounded mb-4">
            @endif
            <input type="file" name="image" id="image" accept="image/*" class="w-full">
        </div>
        <button type="submit" class="bg-primary text-white px-6 py-3 rounded font-semibold hover:bg-primary-light transition-colors duration-300">Actualizar Producto</button>
    </form>
</div>
@endsection
