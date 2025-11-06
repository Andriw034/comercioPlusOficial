@props([
  'product',
  'href' => null, // opcional: si lo pasas, la card enlaza a ese destino
])

@php
  // Si no pasas href, por defecto abre la edición del producto (si existe la ruta)
  $to = $href ?: (function() use($product) {
    try { return route('admin.products.edit', $product); } catch (\Throwable $e) { return '#'; }
  })();

  // Placeholder SVG inline (no dependemos de archivos en /public/images)
  $svgNoImg = 'data:image/svg+xml;charset=UTF-8,' . rawurlencode('
    <svg xmlns="http://www.w3.org/2000/svg" width="400" height="400">
      <rect width="100%" height="100%" fill="#111827"/>
      <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
            fill="#9CA3AF" font-size="20" font-family="sans-serif">Sin imagen</text>
    </svg>');
@endphp

<a href="{{ $to }}"
   class="group block overflow-hidden rounded-2xl border border-gray-200 bg-white shadow hover:shadow-md transition">
  {{-- Imagen fija 400x400 --}}
  <div class="relative w-full bg-black/5 flex items-center justify-center overflow-hidden">
    <img
      src="{{ $product->image_url }}"
      alt="Imagen de {{ $product->name }}"
      style="width: 400px; height: 400px; object-fit: contain;"
      class="transition duration-300 group-hover:scale-105"
      loading="lazy"
      onerror="this.onerror=null;this.src='{{ $svgNoImg }}';"
    >
    @if (isset($product->status) && (int) $product->status !== 1)
      <span class="absolute left-2 top-2 rounded-md bg-red-600 px-2 py-1 text-xs font-semibold text-white">
        Inactivo
      </span>
    @endif
  </div>

  {{-- Info --}}
  <div class="p-3">
    <p class="line-clamp-1 text-sm font-medium text-gray-900">{{ $product->name }}</p>

    <div class="mt-1 flex items-center justify-between">
      <span class="text-sm font-semibold text-gray-900">
        {{ $product->price_formatted ?? '$'.number_format((float)$product->price, 0, ',', '.') }}
      </span>

      @if (isset($product->stock))
        <span class="text-xs text-gray-500">Stock: {{ (int) $product->stock }}</span>
      @endif
    </div>
  </div>
</a>
