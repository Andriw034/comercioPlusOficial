@extends('layouts.app')

@section('content')
<div class="container max-w-xl mx-auto mt-5 bg-white p-5 rounded shadow">

    <h2 class="text-center text-2xl font-bold mb-4 text-orange-500">
        {{ isset($store) ? 'Editar Tienda' : 'Crear Nueva Tienda' }}
    </h2>

    {{-- Mostrar errores --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form 
        action="{{ isset($store) ? route('store.update', $store->id) : route('store.store') }}" 
        method="POST" 
        enctype="multipart/form-data"
    >
        @csrf
        @if(isset($store))
            @method('PUT')
        @endif

        {{-- Nombre --}}
        <div class="mb-4">
            <label for="name" class="form-label fw-bold">Nombre de la tienda</label>
            <input type="text" name="name" id="name" class="form-control"
                value="{{ old('name', $store->name ?? '') }}" required>
            @error('name')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        {{-- Slug --}}
        <div class="mb-4">
            <label for="slug" class="form-label fw-bold">Slug público (ej: mi-tienda)</label>
            <input type="text" name="slug" id="slug" class="form-control"
                value="{{ old('slug', $store->slug ?? '') }}" required>
            @error('slug')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        {{-- Logo --}}
        <div class="mb-4">
            <label for="logo" class="form-label fw-bold">Logo</label>
            <input type="file" name="logo" id="logo" class="form-control" accept="image/*">
            @if(isset($store) && $store->logo)
                <div class="mt-2">
                    <img src="{{ asset('storage/' . $store->logo) }}" alt="Logo actual" width="100">
                </div>
            @endif
            @error('logo')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        {{-- Color --}}
        <div class="mb-4">
            <label for="primary_color" class="form-label fw-bold">Color principal</label>
            <input type="color" name="primary_color" id="primary_color" class="form-control form-control-color"
                value="{{ old('primary_color', $store->primary_color ?? '#FFA14F') }}">
            @error('primary_color')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        {{-- Descripción --}}
        <div class="mb-4">
            <label for="description" class="form-label fw-bold">Descripción</label>
            <textarea name="description" id="description" class="form-control" rows="4">{{ old('description', $store->description ?? '') }}</textarea>
            @error('description')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        {{-- Botón --}}
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-success px-4">
                {{ isset($store) ? 'Actualizar Tienda' : 'Crear Tienda' }}
            </button>
        </div>
    </form>
</div>
@endsection
