@extends('layouts.app') {{-- Usa tu layout base (app.blade.php) --}}

@section('content')
<div class="container">
    <h2>Editar Tienda</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ isset($store) ? route('store.update', $store->id) : '#' }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label>Nombre de la tienda:</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $store->name) }}" required>
        </div>

        <div class="form-group mb-3">
            <label>Descripción:</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $store->description) }}</textarea>
        </div>

        <div class="form-group mb-3">
            <label>Color principal:</label>
            <input type="color" name="primary_color" class="form-control" value="{{ old('primary_color', $store->primary_color) }}">
        </div>

        <div class="form-group mb-3">
            <label>Logo actual:</label><br>
            @if($store->logo)
                <img src="{{ asset('storage/' . $store->logo) }}" width="100" alt="Logo actual">
            @else
                <p>No hay logo</p>
            @endif
        </div>

        <div class="form-group mb-3">
            <label>Subir nuevo logo:</label>
            <input type="file" name="logo" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </form>
</div>
@endsection
