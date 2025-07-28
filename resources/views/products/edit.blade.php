@extends('layouts.admin') {{-- Asegúrate que es el layout con el sidebar --}}

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
            <input type="text" name="name" id="name" class="w-full border px-4 py-3 rounded focus:ring-2 focus:ring-blue-500" value="{{ old('name', $product->name) }}" required>
        </div>

        <div>
            <label for="description" class="block font-semibold mb-2 text-gray-700">Descripción</label>
            <textarea name="description" id="description" class="w-full border px-4 py-3 rounded focus:ring-2 focus:ring-blue-500" rows="4" required>{{ old('description', $product->description) }}</textarea>
        </div>

        <div>
            <label for="category_id" class="block font-semibold mb-2 text-gray-700">Categoría</label>
            <select name="category_id" id="category_id" class="w-full border px-4 py-3 rounded focus:ring-2 focus:ring-blue-500" required>
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
            @if ($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-32 w-32 object-cover mb-2 rounded">
            @endif
            <input type="file" name="image" id="image" accept="image/*" class="w-full">
        </div>

        <div class="text-right">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                💾 Actualizar Producto
            </button>
        </div>
    </form>
</div>
@endsection
