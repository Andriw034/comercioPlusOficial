@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md mt-6">

    <h2 class="text-3xl font-bold text-orange-600 mb-6">Configuración de Mi Tienda</h2>

    {{-- Mensaje de éxito --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    {{-- Mostrar errores de validación --}}
    @if($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded mb-6">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulario --}}
    <form 
        action="{{ isset($store) ? route('store.update', $store->id) : route('store.create') }}" 
        method="POST" 
        enctype="multipart/form-data" 
        class="space-y-6"
    >
        @csrf
        @if(isset($store))
            @method('PUT')
        @endif

        {{-- Nombre --}}
        <div>
            <label for="name" class="block font-medium text-gray-700 mb-2">Nombre de la Tienda</label>
            <input 
                type="text" 
                name="name" 
                id="name" 
                placeholder="Ej: Tienda Plus" 
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-400"
                required 
                value="{{ old('name', $store->name ?? '') }}"
            >
        </div>

        {{-- Descripción --}}
        <div>
            <label for="description" class="block font-medium text-gray-700 mb-2">Descripción</label>
            <textarea 
                name="description" 
                id="description" 
                rows="5" 
                placeholder="Describe tu tienda" 
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-400"
                required
            >{{ old('description', $store->description ?? '') }}</textarea>
        </div>

        {{-- Logo --}}
        <div>
            <label for="logo" class="block font-medium text-gray-700 mb-2">Logo</label>
            <input 
                type="file" 
                name="logo" 
                id="logo" 
                accept="image/*" 
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-400"
            >
            @if(!empty($store->logo))
                <div class="mt-4">
                    <img src="{{ asset('storage/' . $store->logo) }}" alt="Logo actual" class="h-20 rounded shadow">
                </div>
            @endif
        </div>

        {{-- Botón --}}
        <div class="pt-4">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-3 rounded-lg transition">
                {{ isset($store) ? 'Actualizar Tienda' : 'Guardar Tienda' }}
            </button>
        </div>
    </form>
</div>
@endsection
