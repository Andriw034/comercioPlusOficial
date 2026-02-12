@extends('layouts.dashboard')

@section('title', 'Productos Dashboard')
@section('content')
@php
  $store = \App\Models\Store::where('user_id', auth()->id())->first();
@endphp

<div class="min-h-screen bg-[#0e0f12] text-white p-6">
  <header class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-4">
      @if($store && $store->logo_path)
        <img src="{{ asset('storage/'.$store->logo_path) }}" alt="Logo" class="h-10 w-10 rounded-2xl object-cover ring-1 ring-white/10" />
      @endif
      <div>
        <h1 class="text-4xl font-bold mb-6 flex items-center gap-2">
          {{ $store?->name ?? 'Mi Tienda' }}
          @if($store && $store->cover_path)
            <span class="text-xs text-white/50">· portada activa</span>
          @endif
        </h1>
        @if($store && $store->description)
          <p class="text-white/60 text-sm">{{ $store->description }}</p>
        @endif
      </div>
    </div>

    <div class="flex items-center gap-3">
      {{-- Botón "Crear tienda" eliminado --}}
      {{-- <button class="btn-create-store">Crear tienda</button> --}}
      @if($store)
        <a href="{{ route('store.edit', $store) }}" class="rounded-xl bg-white/10 text-white px-4 py-2 hover:bg-white/15">Editar tienda</a>
      @endif
    </div>
  </header>

  @if($store && $store->cover_path)
    <div class="relative mb-6 overflow-hidden rounded-3xl">
      <img src="{{ asset('storage/'.$store->cover_path) }}" alt="Portada"
           class="w-full h-40 sm:h-52 object-cover opacity-90" />
      <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
    </div>
  @endif

  <div id="toolbar" class="sticky top-0 bg-[#0e0f12] p-4 rounded-xl shadow-lg flex flex-wrap gap-4 items-center z-10">
    <button id="btnNewProduct" class="bg-[#FF6000] text-black px-5 py-2 rounded-full font-semibold hover:bg-[#ff7a2e] transition">
      Nuevo producto
    </button>

    <input id="searchInput" type="search" placeholder="Buscar..." aria-label="Buscar productos"
      class="bg-neutral-900/60 ring-1 ring-white/10 text-white placeholder-white/40 rounded-lg px-4 py-2 flex-grow max-w-xs focus:ring-[#FF6000] outline-none" />

    <select id="categoryFilter" aria-label="Filtrar por categoría"
      class="bg-neutral-900/60 ring-1 ring-white/10 text-white rounded-lg px-4 py-2 focus:ring-[#FF6000] outline-none">
      <option value="">Todas las categorías</option>
      <option value="Frenos">Frenos</option>
      <option value="Iluminación">Iluminación</option>
      <option value="Transmisión">Transmisión</option>
      <option value="Accesorios">Accesorios</option>
      <option value="Lubricantes">Lubricantes</option>
      <option value="Llantas">Llantas</option>
    </select>

    <label class="flex items-center gap-2 cursor-pointer select-none">
      <input id="activeOnlyToggle" type="checkbox" class="accent-[#FF6000]" />
      Solo activos
    </label>

    <select id="sortSelect" aria-label="Ordenar productos"
      class="bg-neutral-900/60 ring-1 ring-white/10 text-white rounded-lg px-4 py-2 focus:ring-[#FF6000] outline-none">
      <option value="name_asc">Nombre A-Z</option>
      <option value="name_desc">Nombre Z-A</option>
      <option value="price_asc">Precio ascendente</option>
      <option value="price_desc">Precio descendente</option>
    </select>
  </div>

  <div id="productsGrid" class="mt-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"></div>

  <!-- Modal -->
  <div id="productModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" tabindex="-1"
    class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 hidden">
    <div class="bg-neutral-900 ring-1 ring-white/10 rounded-2xl p-6 max-w-xl w-full relative">
      <button id="modalCloseBtn" aria-label="Cerrar modal"
        class="absolute top-4 right-4 text-white hover:text-[#FF6000] text-2xl font-bold">&times;</button>
      <h2 id="modalTitle" class="text-2xl font-bold mb-4">Crear producto</h2>
      <form id="productForm" class="space-y-4" novalidate>
        <div>
          <label for="name" class="block mb-1">Nombre</label>
          <input type="text" id="name" name="name" required
            class="w-full bg-neutral-900/60 ring-1 ring-white/10 text-white placeholder-white/40 rounded-lg px-4 py-2 focus:ring-[#FF6000] outline-none" />
          <p class="text-red-500 text-sm mt-1 hidden" id="nameError">Este campo es obligatorio.</p>
        </div>

        <div>
          <label for="category" class="block mb-1">Categoría</label>
          <select id="category" name="category" required
            class="w-full bg-neutral-900/60 ring-1 ring-white/10 text-white rounded-lg px-4 py-2 focus:ring-[#FF6000] outline-none">
            <option value="">Seleccione una categoría</option>
            <option value="Frenos">Frenos</option>
            <option value="Iluminación">Iluminación</option>
            <option value="Transmisión">Transmisión</option>
            <option value="Accesorios">Accesorios</option>
            <option value="Lubricantes">Lubricantes</option>
            <option value="Llantas">Llantas</option>
          </select>
          <p class="text-red-500 text-sm mt-1 hidden" id="categoryError">Seleccione una categoría.</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="price" class="block mb-1">Precio</label>
            <input type="number" id="price" name="price" min="0" step="0.01" required
              class="w-full bg-neutral-900/60 ring-1 ring-white/10 text-white placeholder-white/40 rounded-lg px-4 py-2 focus:ring-[#FF6000] outline-none" />
            <p class="text-red-500 text-sm mt-1 hidden" id="priceError">Ingrese un precio válido.</p>
          </div>
          <div>
            <label for="stock" class="block mb-1">Existencias</label>
            <input type="number" id="stock" name="stock" min="0" step="1" required
              class="w-full bg-neutral-900/60 ring-1 ring-white/10 text-white placeholder-white/40 rounded-lg px-4 py-2 focus:ring-[#FF6000] outline-none" />
            <p class="text-red-500 text-sm mt-1 hidden" id="stockError">Ingrese un stock válido.</p>
          </div>
        </div>

        <div>
          <label for="imageUrl" class="block mb-1">URL de imagen</label>
          <input type="url" id="imageUrl" name="imageUrl" placeholder="https://example.com/imagen.jpg"
            class="w-full bg-neutral-900/60 ring-1 ring-white/10 text-white placeholder-white/40 rounded-lg px-4 py-2 focus:ring-[#FF6000] outline-none" />
          <p class="text-red-500 text-sm mt-1 hidden" id="imageUrlError">Ingrese una URL válida.</p>
          <div class="mt-3">
            <img id="imagePreview" src="" alt="Vista previa de imagen" class="w-full max-h-48 object-contain rounded-lg" />
          </div>
        </div>

        <div>
          <label for="description" class="block mb-1">Descripción</label>
          <textarea id="description" name="description" rows="3"
            class="w-full bg-neutral-900/60 ring-1 ring-white/10 text-white placeholder-white/40 rounded-lg px-4 py-2 focus:ring-[#FF6000] outline-none"></textarea>
        </div>

        <div class="flex items-center gap-2">
          <input type="checkbox" id="status" name="status" checked class="accent-[#FF6000]" />
          <label for="status">Activo</label>
        </div>

        <div class="flex justify-end gap-4 mt-6">
          <button type="submit" id="saveBtn" class="bg-[#FF6000] text-black px-6 py-2 rounded-full font-semibold hover:bg-[#ff7a2e] transition">
            Guardar
          </button>
          <button type="button" id="cancelBtn" class="bg-neutral-700 text-white px-6 py-2 rounded-full font-semibold hover:bg-neutral-600 transition">
            Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
