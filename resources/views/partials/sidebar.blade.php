<aside class="hidden md:block border-r bg-card h-full">
  @php
    // Detecta si la ruta actual coincide con un patrón (por nombre de ruta)
    $isActive = fn(string $pattern) => request()->routeIs($pattern);

    // Clases base / estados
    $baseLink = 'flex items-center gap-3 rounded-lg px-3 py-2 transition-all';
    $hover    = 'hover:text-[#FF6000] hover:bg-[#FF6000]/10';
    $idle     = 'text-white/70';
    $active   = 'text-[#FF6000] bg-[#FF6000]/10 border-l-2 border-[#FF6000]';
  @endphp

  <div class="flex h-full max-h-screen flex-col gap-2">
    {{-- Encabezado / Marca --}}
    <div class="flex h-16 items-center border-b px-6">
      <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 font-semibold">
        <div class="h-8 w-8 rounded-lg flex items-center justify-center bg-[#FF6000]">
          <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/>
          </svg>
        </div>
        <span class="text-lg font-bold text-white">ComercioPlus</span>
      </a>
    </div>

    {{-- Navegación lateral --}}
    <div class="flex-1">
      <nav class="grid items-start px-4 text-sm font-medium">

        {{-- Dashboard --}}
        @php $a = $isActive('admin.dashboard'); @endphp
        <a href="{{ route('admin.dashboard') }}"
           class="{{ $baseLink }} {{ $a ? $active : $idle }} {{ $hover }}"
           @if($a) aria-current="page" @endif>
          <span class="h-4 w-4 {{ $a ? 'text-[#FF6000]' : 'text-white/70' }}">🏠</span>
          <span>Dashboard</span>
        </a>

        {{-- Productos --}}
        @php $a = $isActive('admin.products.*'); @endphp
        <a href="{{ route('admin.products.index') }}"
           class="{{ $baseLink }} {{ $a ? $active : $idle }} {{ $hover }}"
           @if($a) aria-current="page" @endif>
          <span class="h-4 w-4 {{ $a ? 'text-[#FF6000]' : 'text-white/70' }}">📦</span>
          <span>Productos</span>
        </a>

        {{-- Categorías --}}
        @php $a = $isActive('admin.categories.*'); @endphp
        <a href="{{ route('admin.categories.index') }}"
           class="{{ $baseLink }} {{ $a ? $active : $idle }} {{ $hover }}"
           @if($a) aria-current="page" @endif>
          <span class="h-4 w-4 {{ $a ? 'text-[#FF6000]' : 'text-white/70' }}">🏷️</span>
          <span>Categorías</span>
        </a>

        {{-- Configuración / Tienda (branding, pagos, envíos, dominio, etc.) --}}
        @php $a = $isActive('admin.store.*') || $isActive('admin.profile.*'); @endphp
        <a href="{{ route('admin.store.index') }}"
           class="{{ $baseLink }} {{ $a ? $active : $idle }} {{ $hover }}"
           @if($a) aria-current="page" @endif>
          <span class="h-4 w-4 {{ $a ? 'text-[#FF6000]' : 'text-white/70' }}">⚙️</span>
          <span>Configuración</span>
        </a>

      </nav>
    </div>
  </div>
</aside>
