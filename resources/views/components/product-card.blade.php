@props(['name','price','image'=>null,'rating'=>null])
@php
  $src = null;
  if ($image) {
    $clean = ltrim($image, '/');
    $src = str_starts_with($image, 'http') ? $image : asset($clean);
  }
@endphp
<article class="bg-white rounded-xl shadow-soft hover:shadow-soft-hover transition overflow-hidden border border-border">
  <div class="aspect-[4/3] bg-background">
    @if($src)
      <img src="{{ $src }}" alt="{{ $name }}" class="w-full h-full object-cover" loading="lazy">
    @else
      <div class="w-full h-full grid place-items-center text-text-light">Sin imagen</div>
    @endif
  </div>
  <div class="p-3">
    <h3 class="font-medium text-text line-clamp-2">{{ $name }}</h3>
    <div class="mt-1 flex items-center justify-between">
      <span class="font-bold">${{ number_format($price, 2) }}</span>
      @if(!is_null($rating))
        <span class="text-sm text-text-light">★ {{ number_format($rating,1) }}</span>
      @endif
    </div>
  </div>
</article>
