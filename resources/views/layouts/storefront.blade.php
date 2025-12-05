<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>@yield('title', 'ComercioPlus')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @vite(['resources/css/app.css','resources/js/app.js'])

  @hasSection('og_image')
    <meta property="og:image" content="@yield('og_image')" />
  @endif
</head>
<body class="min-h-screen bg-[#0f141b] text-gray-200">

  {{-- Header compacto con logo + nombre --}}
  <header class="border-b border-gray-800">
    <div class="mx-auto flex max-w-6xl items-center gap-3 p-4">
      @isset($store)
        @if($store->logo_url)
          <img src="{{ $store->logo_url }}" alt="Logo {{ $store->name }}" class="h-8 w-8 rounded-md object-cover">
        @endif
        <a href="{{ route('storefront.public.home', $store->slug) }}" class="text-xl font-semibold">
          <span class="text-gray-100">Comercio</span><span class="text-orange-500">Plus</span>
          <span class="ml-2 text-sm text-gray-400">/ {{ $store->name }}</span>
        </a>
      @else
        <a href="{{ url('/') }}" class="text-xl font-semibold">
          <span class="text-gray-100">Comercio</span><span class="text-orange-500">Plus</span>
        </a>
      @endisset
    </div>
  </header>

  {{-- Hero/Portada de tienda --}}
  @isset($store)
    <section class="border-b border-gray-800">
      <div class="mx-auto max-w-6xl">
        <div class="relative overflow-hidden rounded-2xl @if($store->cover_url) ring-1 ring-gray-800 @endif"
             style="@if($store->cover_url) background-image:url('{{ $store->cover_url }}'); background-size:cover; background-position:center; @endif">
          <div class="bg-gradient-to-b from-black/40 to-black/70">
            <div class="p-8 md:p-12">
              <h1 class="text-2xl md:text-3xl font-semibold text-white">{{ $store->name }}</h1>
              @if($store->description)
                <p class="mt-2 max-w-3xl text-gray-200">{{ $store->description }}</p>
              @endif
            </div>
          </div>
        </div>
      </div>
    </section>
  @endisset

  <main class="py-6">
    @yield('content')
  </main>

  <footer class="border-t border-gray-800">
    <div class="mx-auto max-w-6xl p-4 text-sm text-gray-400">
      © {{ date('Y') }} {{ $store->name ?? config('app.name','ComercioPlus') }}
    </div>
  </footer>
</body>
</html>
