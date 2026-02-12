@props(['name','logo','rating','category'])
@php
  $src = null;
  if ($logo) {
    $clean = ltrim($logo, '/');
    $src = str_starts_with($logo, 'http') ? $logo : asset($clean);
  }
@endphp
<article class="bg-white rounded-xl shadow-soft hover:shadow-soft-hover transition overflow-hidden border border-border">
  <div class="aspect-[4/3] bg-background grid place-items-center">
    @if($src)
      <img src="{{ $src }}" alt="{{ $name }}" class="w-16 h-16 rounded-full object-cover" loading="lazy">
    @else
      <div class="w-16 h-16 rounded-full bg-primary/10 grid place-items-center text-primary font-bold">{{ substr($name, 0, 1) }}</div>
    @endif
  </div>
  <div class="p-3">
    <h3 class="font-medium text-text line-clamp-2">{{ $name }}</h3>
    <div class="mt-1 flex items-center justify-between">
      <span class="text-sm text-text-light">{{ $category }}</span>
      @if(!is_null($rating))
        <span class="text-sm text-text-light">★ {{ number_format($rating,1) }}</span>
      @endif
    </div>
  </div>
</article>
