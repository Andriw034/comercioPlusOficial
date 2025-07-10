@extends('layouts.dashboard')
@section('title','Branding con IA')
@section('content')
<div class="rounded-lg border bg-card text-card-foreground shadow-sm">
  <div class="flex flex-col space-y-1.5 p-6">
    <h3 class="text-2xl font-semibold leading-none tracking-tight">Branding con IA</h3>
    <p class="text-sm text-muted-foreground">Genera una paleta de colores para tu tienda usando IA, basada en tu logo y portada.</p>
  </div>
  <div class="p-6 pt-0 space-y-6">
    {{-- FORM --}}
    <form action="{{ route('dashboard.branding.generate') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
      @csrf
      <div>
        <label for="shopNameForAI" class="text-sm font-medium leading-none">Nombre de la Tienda</label>
        <input id="shopNameForAI" name="shopName" type="text" value="{{ old('shopName', $store->name ?? '') }}" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 mt-1" />
        @error('shopName')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label for="logoForAI" class="text-sm font-medium leading-none">Logo</label>
        <input id="logoForAI" name="logo" type="file" accept="image/*" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 mt-1" />
        @error('logo')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label for="coverForAI" class="text-sm font-medium leading-none">Imagen de Portada</label>
        <input id="coverForAI" name="cover" type="file" accept="image/*" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 mt-1" />
        @error('cover')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>
      <button class="inline-flex items-center justify-center gap-2 rounded-md bg-primary text-primary-foreground h-10 px-4 py-2 w-full">
        <span class="mr-2">✨</span>
        {{ __('Generar Tema con IA') }}
      </button>
    </form>

    @php
      $sessionColors = session('colors');
      $c = $sessionColors ?: $colors;
    @endphp

    {{-- PALETA DE COLORES (edición manual, como en el original) --}}
    <div class="space-y-4 pt-4 border-t">
      <h4 class="font-semibold">Paleta de Colores</h4>
      <form method="POST" action="{{ route('dashboard.branding.generate') }}" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @csrf
        {{-- Mantener nombre para no romper flujo (si el usuario re-envía sin imágenes) --}}
        <input type="hidden" name="shopName" value="{{ old('shopName', $store->name ?? '') }}" />
        <div class="space-y-2">
          <label class="text-sm font-medium leading-none">Primary Color</label>
          <div class="flex items-center gap-2">
            <input type="color" name="primaryColor" value="{{ $c['primaryColor'] }}" class="p-1 h-10 w-12" readonly />
            <input type="text" value="{{ $c['primaryColor'] }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2" readonly />
          </div>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium leading-none">Background Color</label>
          <div class="flex items-center gap-2">
            <input type="color" name="backgroundColor" value="{{ $c['backgroundColor'] }}" class="p-1 h-10 w-12" readonly />
            <input type="text" value="{{ $c['backgroundColor'] }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2" readonly />
          </div>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium leading-none">Text Color</label>
          <div class="flex items-center gap-2">
            <input type="color" name="textColor" value="{{ $c['textColor'] }}" class="p-1 h-10 w-12" readonly />
            <input type="text" value="{{ $c['textColor'] }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2" readonly />
          </div>
        </div>
      </form>
    </div>

    {{-- VISTA PREVIA --}}
    <div class="space-y-4 pt-4 border-t">
      <h4 class="font-semibold">Vista Previa</h4>
      <div class="rounded-lg p-4 border" style="background-color: {{ $c['backgroundColor'] }}; color: {{ $c['textColor'] }};">
        <h5 class="font-bold text-lg">Producto de Muestra</h5>
        <p class="text-sm opacity-80">Una descripción breve del producto.</p>
        <button class="mt-4 rounded-md px-4 py-2 font-semibold" style="background-color: {{ $c['primaryColor'] }}; color: {{ $c['textColor'] }};">Botón Principal</button>
      </div>
    </div>
  </div>
</div>
@endsection
