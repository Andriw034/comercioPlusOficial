@extends('layouts.storefront')
@section('title', $store->name)
@if($store->cover_url) @section('og_image', $store->cover_url) @endif

@section('content')
<div class="mx-auto max-w-6xl p-4">

  {{-- Filtros --}}
  <form method="GET" class="mb-6 grid grid-cols-1 gap-3 md:grid-cols-6">
    <input name="q" value="{{ $q }}" placeholder="Buscar productos..."
           class="md:col-span-3 w-full rounded-xl border border-gray-600 bg-gray-800 px-3 py-2 text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-600"/>

    <select name="category_id"
            class="md:col-span-2 w-full rounded-xl border border-gray-600 bg-gray-800 px-3 py-2 text-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-600">
      <option value="">Todas las categorías</option>
      @foreach($categories as $c)
        <option value="{{ $c->id }}" @selected((string)$catId === (string)$c->id)>{{ $c->name }}</option>
      @endforeach
    </select>

    <button class="rounded-xl bg-orange-600 px-4 py-2 text-white hover:bg-orange-700">Filtrar</button>
  </form>

  {{-- Grid de productos --}}
  @if($products->isEmpty())
    <div class="rounded-2xl border border-dashed border-gray-600 p-10 text-center text-gray-400">
      No hay productos disponibles.
    </div>
  @else
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
      @foreach($products as $p)
        <a href="{{ route('storefront.public.product.show', [$store->slug, $p->slug]) }}"
           class="group overflow-hidden rounded-2xl border border-gray-700 bg-gray-900/40 hover:border-gray-600 transition">
          <div class="aspect-square w-full bg-gray-800 overflow-hidden">
            @php
              $thumb = $p->image_url ?? asset('img/placeholder-product.png');
            @endphp
            <img src="{{ $thumb }}" alt="{{ $p->name }}"
                 class="h-full w-full object-cover group-hover:scale-105 transition" loading="lazy"/>
          </div>
          <div class="p-4">
            <h3 class="line-clamp-1 text-lg font-medium text-gray-100">{{ $p->name }}</h3>
            <p class="mt-1 text-sm text-gray-400">{{ optional($p->category)->name ?? 'Sin categoría' }}</p>
            <p class="mt-2 text-xl font-semibold text-gray-100">
              {{ $p->price_formatted ?? '$'.number_format($p->price, 0, ',', '.') }}
            </p>
          </div>
        </a>
      @endforeach
    </div>

    <div class="mt-6">
      {{ $products->links() }}
    </div>
  @endif
</div>
@endsection
