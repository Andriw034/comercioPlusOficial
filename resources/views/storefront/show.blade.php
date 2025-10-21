@extends('layouts.storefront')
@section('title', $product->name . ' - ' . $store->name)
@if($store->cover_url) @section('og_image', $store->cover_url) @endif

@section('content')
<div class="mx-auto max-w-5xl p-4">
  <a href="{{ route('storefront.public.home', $store->slug) }}" class="text-sm text-gray-400 hover:underline">← Volver a la tienda</a>

  <div class="mt-4 grid gap-6 md:grid-cols-2">
    <div class="overflow-hidden rounded-2xl border border-gray-700 bg-gray-800">
      @php
        $img = $product->image_url ?? asset('img/placeholder-product.png');
      @endphp
      <img src="{{ $img }}" alt="{{ $product->name }}" class="w-full h-full object-cover" loading="lazy"/>
    </div>

    <div>
      <h1 class="text-2xl font-semibold text-gray-100">{{ $product->name }}</h1>
      <p class="mt-1 text-gray-400">{{ optional($product->category)->name ?? 'Sin categoría' }}</p>
      <p class="mt-4 text-3xl font-semibold text-gray-100">
        {{ $product->price_formatted ?? '$'.number_format($product->price, 0, ',', '.') }}
      </p>
      <p class="mt-2 text-gray-300">Stock: <span class="font-medium">{{ $product->stock }}</span></p>

      @if($product->description)
        <div class="prose prose-invert mt-6 max-w-none">
          {!! nl2br(e($product->description)) !!}
        </div>
      @endif

      {{-- futuro: botón añadir al carrito/checkout --}}
    </div>
  </div>
</div>
@endsection
