@extends('layouts.app')
@section('title','Crear tienda')

@section('content')
<div class="max-w-xl mx-auto px-4 py-10">
  <div class="bg-[var(--cp-card)] rounded-2xl shadow-xl p-6">
    <h1 class="text-2xl font-bold mb-1">Crear tu primera tienda</h1>
    <p class="text-sm text-[var(--cp-ink-2)] mb-6">Completa la información básica para comenzar</p>

    @if(session('error'))
      <div class="text-red-700 bg-red-50 border border-red-200 rounded p-3 mb-4">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('store.store') }}" enctype="multipart/form-data" class="space-y-4">
      @csrf

      <div><label class="text-sm">Nombre</label><input name="name" class="input" value="{{ old('name') }}" required></div>
      <div><label class="text-sm">Slug (URL)</label><input name="slug" class="input" placeholder="mi-tienda" value="{{ old('slug') }}"></div>
      <div><label class="text-sm">Descripción</label><textarea name="description" rows="3" class="input">{{ old('description') }}</textarea></div>

      <div class="grid grid-cols-2 gap-3">
        <div><label class="text-sm">Dirección</label><input name="direccion" class="input" value="{{ old('direccion') }}"></div>
        <div><label class="text-sm">Teléfono</label><input name="telefono" class="input" value="{{ old('telefono') }}"></div>
      </div>

      <div><label class="text-sm">Categoría principal</label><input name="categoria_principal" class="input" value="{{ old('categoria_principal') }}"></div>

      <div class="grid grid-cols-2 gap-3">
        <div><label class="text-sm">Color primario</label><input name="primary_color" class="input" value="{{ old('primary_color','#FF6A2E') }}"></div>
        <div><label class="text-sm">Texto</label><input name="text_color" class="input" value="{{ old('text_color','#333333') }}"></div>
        <div><label class="text-sm">Botón</label><input name="button_color" class="input" value="{{ old('button_color','#FF6A2E') }}"></div>
        <div><label class="text-sm">Fondo</label><input name="background_color" class="input" value="{{ old('background_color') }}"></div>
      </div>

      <div><label class="text-sm">Logo</label><input name="logo" type="file" accept="image/*" class="block w-full"></div>
      <div><label class="text-sm">Portada</label><input name="cover" type="file" accept="image/*" class="block w-full"></div>

      <button class="btn btn-primary w-full">Crear tienda</button>
      @if ($errors->any())
        <p class="text-sm text-red-600">No se pudo crear la tienda. Revisa los campos.</p>
      @endif
    </form>
  </div>
</div>
@endsection
