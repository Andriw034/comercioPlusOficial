@props([
    'product',
    'href' => null, // si lo pasas, la card enlaza
])

@php
$to = $href ?: (route_exists('admin.products.edit') ? route('admin.products.edit', $product) : '#');
@endphp

<a href="{{ $to }}"
   class="group block overflow-hidden rounded-2xl border border-gray-700 bg-gray-900/40 hover:border-orange-500/70 transition">
    <div class="relative aspect-square w-full overflow-hidden bg-gray-800">
        <img
            src="{{ $product->image_url }}"
            alt="{{ $product->name }}"
            class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
            loading="lazy"
            onerror="this.onerror=null;this.src='{{ asset('images/no-image.png') }}';"
        >
        @if (isset($product->status) && (int) $product->status !== 1)
            <span class="absolute left-2 top-2 rounded-md bg-red-700/90 px-2 py-1 text-xs font-semibold text-red-50">
                Inactivo
            </span>
        @endif
    </div>
    <div class="p-3">
        <p class="line-clamp-1 text-sm font-medium text-gray-100">{{ $product->name }}</p>
        <div class="mt-1 flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-100">
                {{ $product->price_formatted ?? '$'.number_format((float)$product->price, 0, ',', '.') }}
            </span>
            @if (isset($product->stock))
                <span class="text-xs text-gray-300">Stock: {{ (int) $product->stock }}</span>
            @endif
        </div>
    </div>
</a>
