@extends('layouts.dashboard')

@section('title', 'Apariencia de la Tienda')

@section('content')
<div class="max-w-4xl mx-auto p-6">
  <h1 class="text-2xl font-semibold mb-6">Apariencia de la tienda</h1>

  @if(session('status'))
    <div class="mb-4 rounded-lg bg-green-100 p-3 text-green-800">
      {{ session('status') }}
    </div>
  @endif

  <div class="grid gap-6 md:grid-cols-2">
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
      <h2 class="mb-3 text-sm font-medium text-gray-700">Logo actual</h2>
      <div class="aspect-square w-40 overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
        <img
          src="{{ $store->logo_url ?? 'https://placehold.co/400x400?text=Logo' }}"
          alt="Logo"
          class="h-full w-full object-contain"
        />
      </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
      <h2 class="mb-3 text-sm font-medium text-gray-700">Portada actual</h2>
      <div class="aspect-video overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
        <img
          src="{{ $store->cover_url ?? 'https://placehold.co/1200x600?text=Portada' }}"
          alt="Portada"
          class="h-full w-full object-cover"
        />
      </div>
    </div>
  </div>

  <form
    action="{{ route('admin.store.update_appearance') }}"
    method="POST"
    enctype="multipart/form-data"
    class="mt-8 space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm"
  >
    @csrf
    @method('PUT')

    <div>
      <label class="mb-2 block text-sm font-medium text-gray-700" for="logo">Nuevo logo (PNG/JPG/SVG)</label>
      <input
        type="file"
        id="logo"
        name="logo"
        accept="image/*"
        class="w-full rounded-lg border border-gray-300 bg-white p-2 text-gray-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
      />
      @error('logo')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label class="mb-2 block text-sm font-medium text-gray-700" for="cover">Nueva portada (PNG/JPG)</label>
      <input
        type="file"
        id="cover"
        name="cover"
        accept="image/*"
        class="w-full rounded-lg border border-gray-300 bg-white p-2 text-gray-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
      />
      @error('cover')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
      @enderror
    </div>

    <div class="flex flex-wrap items-center gap-3">
      <button
        type="submit"
        class="rounded-xl bg-orange-600 px-5 py-2.5 font-medium text-white transition hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 focus:ring-offset-white"
      >
        Guardar cambios
      </button>
      <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-600 hover:text-gray-800 hover:underline">
        Cancelar
      </a>
    </div>
  </form>

  {{-- Delete Store Section --}}
  <div class="mt-8 rounded-2xl border border-red-500 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-red-700">Eliminar tienda</h2>
    <p class="mt-2 text-sm text-gray-600">
      Esta acción es irreversible. Se eliminarán todos los datos de tu tienda, incluyendo productos, categorías y pedidos.
    </p>
    <form
      action="{{ route('admin.store.destroy') }}"
      method="POST"
      class="mt-4"
      onsubmit="return confirm('¿Estás seguro de que quieres eliminar tu tienda? Esta acción no se puede deshacer.');"
    >
      @csrf
      @method('DELETE')
      <button
        type="submit"
        class="rounded-xl bg-red-600 px-5 py-2.5 font-medium text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 focus:ring-offset-white"
      >
        Eliminar mi tienda permanentemente
      </button>
    </form>
  </div>
</div>
@endsection
