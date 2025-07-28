@extends('layouts.app') {{-- Usa tu layout base app.blade.php --}}

@section('content')
<div class="container max-w-3xl mx-auto bg-white p-6 rounded-lg shadow-md">

    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Editar Tienda</h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ isset($store) ? route('merchant.store.update', $store->id) : '#' }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Nombre --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Nombre de la tienda:</label>
            <input type="text" name="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange focus:border-orange" value="{{ old('name', $store->name) }}" required>
        </div>

        {{-- Descripción --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Descripción:</label>
            <textarea name="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange focus:border-orange" rows="3">{{ old('description', $store->description) }}</textarea>
        </div>

        {{-- Color principal --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Color principal:</label>
            <input type="color" name="primary_color" class="mt-1 block w-20 h-10 p-0 border-none focus:ring-2 focus:ring-orange" value="{{ old('primary_color', $store->primary_color) }}">
        </div>

        {{-- Logo actual --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Logo actual:</label>
            @if($store->logo)
                <img src="{{ asset('storage/' . $store->logo) }}" class="w-24 h-24 object-contain border rounded mt-2" alt="Logo actual">
            @else
                <p class="text-gray-500 italic">No hay logo</p>
            @endif
        </div>

        {{-- Subir nuevo logo --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700">Subir nuevo logo:</label>
            <input type="file" name="logo" class="mt-1 block w-full text-sm text-gray-700 file:bg-orange file:text-white file:border-none file:px-4 file:py-2 file:rounded hover:file:bg-orange-light">
        </div>

        {{-- Botón --}}
        <button type="submit" class="btn-primary px-6 py-2 rounded text-white font-semibold shadow-md hover:bg-orange-light transition">Guardar cambios</button>
    </form>
</div>
@endsection
