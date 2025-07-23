@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Configuración de mi tienda</h2>

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

    {{-- Formulario para crear o actualizar tienda --}}
    <form 
        action="{{ isset($store) ? route('store.update', $store->id) : route('store.store') }}" 
        method="POST" 
        enctype="multipart/form-data"
    >
        @csrf
        @if(isset($store))
            @method('PUT')
        @endif

        <div class="mb-3">
            <label for="name" class="form-label">Nombre de la tienda</label>
            <input type="text" name="name" id="name" class="form-control"
                   value="{{ old('name', $store->name ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label for="slug" class="form-label">Slug (URL pública)</label>
            <input type="text" name="slug" id="slug" class="form-control"
                   value="{{ old('slug', $store->slug ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label for="logo" class="form-label">Logo de la tienda</label>
            <input type="file" name="logo" id="logo" class="form-control">
            @if(isset($store) && $store->logo)
                <img src="{{ asset('storage/' . $store->logo) }}" alt="Logo actual" width="100" class="mt-2">
            @endif
        </div>

        <div class="mb-3">
            <label for="primary_color" class="form-label">Color principal</label>
            <input type="color" name="primary_color" id="primary_color" class="form-control form-control-color"
                   value="{{ old('primary_color', $store->primary_color ?? '#FFA14F') }}">
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Descripción de la tienda</label>
            <textarea name="description" id="description" class="form-control" rows="4">{{ old('description', $store->description ?? '') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">
            {{ isset($store) ? 'Actualizar tienda' : 'Crear tienda' }}
        </button>
    </form>
</div>
@endsection
