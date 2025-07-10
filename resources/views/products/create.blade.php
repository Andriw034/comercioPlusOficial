@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow-md">
    <h2 class="text-3xl font-bold mb-6 text-gray-900">Agregar Producto</h2>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-4 rounded mb-6 shadow">
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
            <label for="name" class="block font-semibold mb-2 text-gray-700">Nombre</label>
            <input type="text" name="name" id="name" placeholder="Nombre del producto" class="w-full border border-gray-300 rounded px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary" value="{{ old('name') }}" required>
        </div>
        <div>
            <label for="description" class="block font-semibold mb-2 text-gray-700">Descripción</label>
            <textarea name="description" id="description" rows="5" placeholder="Descripción del producto" class="w-full border border-gray-300 rounded px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary" required>{{ old('description') }}</textarea>
        </div>
        <div>
            <label for="category_id" class="block font-semibold mb-2 text-gray-700">Categoría</label>
            <select name="category_id" id="category_id" class="w-full border border-gray-300 rounded px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary" required>
                <option value="">Seleccione una categoría</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="image" class="block font-semibold mb-2 text-gray-700">Imagen</label>
            <input type="file" name="image" id="image" accept="image/*" class="w-full">
        </div>
        <button type="submit" class="bg-primary text-white px-6 py-3 rounded font-semibold hover:bg-primary-light transition-colors duration-300">Guardar Producto</button>
    </form>
</div>
@endsection
