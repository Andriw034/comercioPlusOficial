@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-6 rounded shadow-md">
    <h2 class="text-3xl font-bold mb-6 text-gray-900">Mi Tienda</h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded mb-6 shadow">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('store.create') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <div>
            <label for="name" class="block font-semibold mb-2 text-gray-700">Nombre de la Tienda</label>
            <input type="text" name="name" id="name" placeholder="Ejemplo: Tienda Plus" class="w-full border border-gray-300 rounded px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary" required value="{{ old('name', $store->name ?? '') }}">
        </div>
        <div>
            <label for="description" class="block font-semibold mb-2 text-gray-700">Descripción</label>
            <textarea name="description" id="description" rows="5" placeholder="Describe tu tienda" class="w-full border border-gray-300 rounded px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary" required>{{ old('description', $store->description ?? '') }}</textarea>
        </div>
        <div>
            <label for="logo" class="block font-semibold mb-2 text-gray-700">Logo de la Tienda</label>
            <input type="file" name="logo" id="logo" accept="image/*" class="w-full border border-gray-300 rounded px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
            @if(!empty($store->logo))
                <img src="{{ asset('storage/' . $store->logo) }}" alt="Logo actual" class="mt-4 h-20">
            @endif
        </div>
        <button type="submit" class="bg-primary text-white px-6 py-3 rounded font-semibold hover:bg-primary-light transition-colors duration-300">Guardar Tienda</button>
    </form>
</div>
@endsection
