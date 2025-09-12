@extends('layouts.admin')

@section('title', 'Productos — ComercioPlus')
@section('header', 'Productos')

@php
  // Branding: primero intentamos desde sesión (StoreController@store),
  // si no hay, tratamos de inferirlo desde la tienda del usuario.
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Storage;

  $branding = session('store_branding') ?? null;

  if (!$branding && Auth::check()) {
      $store = \App\Models\Store::where('user_id', Auth::id())->first();
      if ($store) {
          $branding = [
              'store_id'   => $store->id,
              'store_name' => $store->name,
              'logo_url'   => $store->logo  ? Storage::disk('public')->url($store->logo)   : null,
              'cover_url'  => $store->cover ? Storage::disk('public')->url($store->cover) : null,
          ];
      }
  }
@endphp

@section('content')
  {{-- HERO / Branding --}}
  <div class="relative mb-8 overflow-hidden rounded-3xl ring-1 ring-white/15">
    <div class="h-48 md:h-64 w-full bg-center bg-cover"
         style="
          @if(!empty($branding['cover_url']))
            background-image:
              linear-gradient(to bottom, rgba(0,0,0,.35), rgba(0,0,0,.35)),
              url('{{ $branding['cover_url'] }}');
          @else
            background-image: linear-gradient(to bottom right, rgba(255,255,255,.08), rgba(255,255,255,.04));
          @endif
         ">
      <div class="h-full w-full"></div>
    </div>

    @if(!empty($branding['logo_url']))
      <img
        src="{{ $branding['logo_url'] }}"
        alt="Logo tienda"
        class="absolute -bottom-8 left-6 h-20 w-20 md:h-24 md:w-24 rounded-2xl ring-4 ring-white object-cover shadow-lg"
      >
    @endif
  </div>

  <div class="mx-auto max-w-7xl">
  {{-- Título + CTA --}}
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
      <div class="mt-8 md:mt-0">
        <h1 class="text-3xl font-extrabold tracking-tight">
          Productos
          @if(!empty($branding['store_name']))
            <span class="text-white/70 text-xl align-middle"> · {{ $branding['store_name'] }}</span>
          @endif
        </h1>
        <p class="text-white/70 mt-1">
          @if(isset($store) && $store)
            Administra tu catálogo de productos para {{ $store->name }}
          @else
            Administra tu catálogo. <a href="{{ route('store.create') }}" class="text-orange-400 hover:text-orange-300 underline">Crea tu tienda primero</a>
          @endif
        </p>
      </div>
      <div class="flex gap-3">
        @if(isset($store) && $store)
          <a href="{{ route('store.create') }}"
             class="inline-flex items-center justify-center h-11 rounded-full px-5 font-semibold bg-white/10 text-white hover:bg-white/20 shadow border border-white/20">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Editar tienda
          </a>
        @endif
        <a href="{{ route('admin.products.create') }}"
           class="inline-flex items-center justify-center h-11 rounded-full px-5 font-semibold bg-orange-500 text-black hover:bg-orange-600 shadow">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Agregar producto
        </a>
      </div>
    </div>

    {{-- Mensajes --}}
    @if(session('success'))
      <div class="mb-6 rounded-2xl border border-green-300/30 bg-green-400/10 text-green-200 px-4 py-3">
        {{ session('success') }}
      </div>
    @endif

    {{-- Estado vacío --}}
    @if($products->count() === 0)
      <div class="rounded-3xl bg-white/10 ring-1 ring-white/15 p-10 text-center">
        <div class="text-xl font-semibold">Aún no tienes productos</div>
        <p class="text-white/70 mt-1">Crea tu primer producto y empieza a construir tu catálogo.</p>
        <a href="{{ route('admin.products.create') }}"
           class="mt-5 inline-flex items-center justify-center h-11 rounded-full px-5 font-semibold bg-orange-500 text-black hover:bg-orange-600 shadow">
          + Agregar producto
        </a>
      </div>
    @else
      {{-- Grid de productos --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($products as $product)
          <div class="bg-white rounded-2xl shadow p-4 flex flex-col ring-1 ring-black/5">
            <div class="h-48 w-full mb-4 overflow-hidden rounded-xl">
              @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}"
                     alt="{{ $product->name }}"
                     class="object-cover h-full w-full transition-transform duration-300 hover:scale-105">
              @else
                <div class="flex items-center justify-center h-full bg-gray-100 text-gray-500">
                  Sin imagen
                </div>
              @endif
            </div>

            <h2 class="text-xl font-semibold text-gray-900 mb-1 line-clamp-1">{{ $product->name }}</h2>
            <p class="text-gray-600 mb-4 text-sm">{{ $product->category->name ?? 'Sin categoría' }}</p>

            <div class="mt-auto flex gap-3">
              <a href="{{ route('admin.products.edit', $product) }}"
                 class="flex-1 bg-neutral-900 text-white text-center py-2 rounded-xl hover:bg-neutral-800 transition-colors duration-300">
                Editar
              </a>
              <form action="{{ route('admin.products.destroy', $product) }}"
                    method="POST"
                    class="flex-1"
                    onsubmit="return confirm('¿Está seguro de eliminar este producto?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="w-full bg-red-600 text-white py-2 rounded-xl hover:bg-red-700 transition-colors duration-300">
                  Eliminar
                </button>
              </form>
            </div>
          </div>
        @endforeach
      </div>

      {{-- Paginación (Tailwind) --}}
      <div class="mt-8">
        {{ $products->onEachSide(1)->links('vendor.pagination.tailwind') }}
      </div>
    @endif
  </div>
@endsection
