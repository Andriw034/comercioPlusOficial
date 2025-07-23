@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center">Crear tienda</h1>

    <form action="{{ route('store.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Nombre --}}
        <div class="mb-3">
            <label for="name" class="form-label">Nombre de la tienda</label>
            <input type="text" class="form-control" name="name" required>
        </div>

        {{-- Slug --}}
        <div class="mb-3">
            <label for="slug" class="form-label">Slug público (ej: mi-tienda)</label>
            <input type="text" class="form-control" name="slug" required>
        </div>

        {{-- Logo --}}
        <div class="mb-3">
            <label for="logo" class="form-label">Logo</label>
            <input type="file" class="form-control" name="logo">
        </div>

        {{-- Fondo/banner (opcional) --}}
        <div class="mb-3">
            <label for="background" class="form-label">Fondo (opcional)</label>
            <input type="file" class="form-control" name="background">
        </div>

        {{-- Color principal --}}
        <div class="mb-3">
            <label for="primary_color" class="form-label">Color principal (#HEX)</label>
            <input type="text" class="form-control" name="primary_color" value="#FFA14F">
        </div>

        {{-- Descripción --}}
        <div class="mb-3">
            <label for="description" class="form-label">Descripción</label>
            <textarea class="form-control" name="description" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Crear tienda</button>
    </form>
</div>
@endsection
