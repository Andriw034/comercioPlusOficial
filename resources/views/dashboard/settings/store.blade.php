@extends('layouts.app')

@section('title', 'Ajustes de la tienda')

@section('content')
<div class="container py-8 max-w-3xl">
  <h1 class="text-2xl font-bold mb-6">Ajustes de la tienda</h1>

  <form method="POST" action="{{ route('dashboard.settings.store.save') }}" class="space-y-5">
    @csrf
    <div>
      <label class="text-sm font-medium">Nombre</label>
      <input name="name" class="mt-1 w-full h-10 rounded-md border border-input bg-background px-3" required value="{{ old('name', $store['name']) }}"/>
      @error('name')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
      <label class="text-sm font-medium">Dirección</label>
      <input name="address" class="mt-1 w-full h-10 rounded-md border border-input bg-background px-3" required value="{{ old('address', $store['address']) }}"/>
      @error('address')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
      <label class="text-sm font-medium">Logo (URL)</label>
      <input name="logo" class="mt-1 w-full h-10 rounded-md border border-input bg-background px-3" value="{{ old('logo', $store['logo']) }}"/>
    </div>

    <div>
      <label class="text-sm font-medium">Portada (URL)</label>
      <input name="cover" class="mt-1 w-full h-10 rounded-md border border-input bg-background px-3" value="{{ old('cover', $store['cover']) }}"/>
    </div>

    <div>
      <label class="text-sm font-medium">Descripción</label>
      <textarea name="description" class="mt-1 w-full min-h-[100px] rounded-md border border-input bg-background px-3 py-2">{{ old('description', $store['description']) }}</textarea>
    </div>

    <div class="pt-2">
      <button class="h-11 px-6 rounded-md bg-primary text-primary-foreground font-semibold">Guardar</button>
      <a href="{{ route('dashboard.index') }}" class="h-11 px-6 rounded-md border inline-flex items-center ml-2">Cancelar</a>
    </div>

    <div class="mt-8 rounded-lg border bg-card p-4">
      <div class="font-semibold mb-2">Previsualización</div>
      <div class="flex items-center gap-4">
        <img src="{{ old('logo', $store['logo']) }}" class="w-16 h-16 rounded-full object-cover border" alt="Logo">
        <div>
          <div class="text-lg font-bold">{{ old('name', $store['name']) }}</div>
          <div class="text-sm text-muted-foreground">{{ old('address', $store['address']) }}</div>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection
