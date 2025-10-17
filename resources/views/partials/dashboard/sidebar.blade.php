<aside class="hidden md:block border-r bg-card h-full">
  @php
    // Helpers
    $isActive = fn(string $pattern) => request()->routeIs($pattern);

    $baseLink = 'flex items-center gap-3 rounded-lg px-3 py-2 transition-all';
    $hover    = 'hover:text-[#FF6000] hover:bg-[#FF6000]/10';
    $idle     = 'text-white/70';
    $active   = 'text-[#FF6000] bg-[#FF6000]/10 border-l-2 border-[#FF6000]';
  @endphp

  <div class="flex h-full max-h-screen flex-col gap-2">
    <div class="flex h-16 items-center border-b px-6">
      <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 font-semibold">
        <div class="h-8 w-8 rounded-lg flex items-center justify-center bg-[#FF6000]">
          @isset($useLucide)
            <x-lucide-bike class="h-5 w-5 text-background" />
          @else
            <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>
          @endisset
        </div>
        <span class="text-lg font-bold text-white">ComercioPlus</span>
      </a>
    </div>

    <div class="flex-1">
      <nav class="grid items-start px-4 text-sm font-medium">

        {{-- Inicio --}}
        @php $activeHome = $isActive('admin.dashboard'); @endphp
        <a href="{{ route('admin.dashboard') }}"
           class="{{ $baseLink }} {{ $activeHome ? $active : $idle }} {{ $hover }}"
           @if($activeHome) aria-current="page" @endif>
          @isset($useLucide)
            <x-lucide-home class="h-4 w-4 {{ $activeHome ? 'text-[#FF6000]' : 'text-white/70' }}" />
          @else
            <span class="{{ $activeHome ? 'text-[#FF6000]' : 'text-white/70' }}">🏠</span>
          @endisset
          <span>Inicio</span>
        </a>

        {{-- Órdenes (placeholder) --}}
        <span class="flex items-center gap-3 rounded-lg px-3 py-2 text-white/40 cursor-not-allowed">
          @isset($useLucide)<x-lucide-shopping-cart class="h-4 w-4" />@else <span>🛒</span> @endisset
          Órdenes
        </span>

        {{-- Productos --}}
        @php $activeProducts = $isActive('admin.products.*'); @endphp
        <a href="{{ route('admin.products.index') }}"
           class="{{ $baseLink }} {{ $activeProducts ? $active : $idle }} {{ $hover }}"
           @if($activeProducts) aria-current="page" @endif>
          @isset($useLucide)
            <x-lucide-package class="h-4 w-4 {{ $activeProducts ? 'text-[#FF6000]' : 'text-white/70' }}" />
          @else
            <span class="{{ $activeProducts ? 'text-[#FF6000]' : 'text-white/70' }}">📦</span>
          @endisset
          <span>Productos</span>
        </a>

        {{-- Categorías (NUEVO) --}}
        @php $activeCategories = $isActive('admin.categories.*'); @endphp
        <a href="{{ route('admin.categories.index') }}"
           class="{{ $baseLink }} {{ $activeCategories ? $active : $idle }} {{ $hover }}"
           @if($activeCategories) aria-current="page" @endif>
          @isset($useLucide)
            <x-lucide-tags class="h-4 w-4 {{ $activeCategories ? 'text-[#FF6000]' : 'text-white/70' }}" />
          @else
            <span class="{{ $activeCategories ? 'text-[#FF6000]' : 'text-white/70' }}">🏷️</span>
          @endisset
          <span>Categorías</span>
        </a>

        {{-- Branding IA --}}
        @php $activeBrand = $isActive('admin.store.appearance'); @endphp
        <a href="{{ route('admin.store.appearance') }}"
           class="{{ $baseLink }} {{ $activeBrand ? $active : $idle }} {{ $hover }}"
           @if($activeBrand) aria-current="page" @endif>
          @isset($useLucide)
            <x-lucide-palette class="h-4 w-4 {{ $activeBrand ? 'text-[#FF6000]' : 'text-white/70' }}" />
          @else
            <span class="{{ $activeBrand ? 'text-[#FF6000]' : 'text-white/70' }}">🎨</span>
          @endisset
          <span>Branding IA</span>
        </a>

        {{-- Ajustes --}}
        @php $activeSettings = $isActive('admin.profile.*') || $isActive('admin.store.*'); @endphp
        <a href="{{ route('admin.store.index') }}"
           class="{{ $baseLink }} {{ $activeSettings ? $active : $idle }} {{ $hover }}"
           @if($activeSettings) aria-current="page" @endif>
          @isset($useLucide)
            <x-lucide-settings class="h-4 w-4 {{ $activeSettings ? 'text-[#FF6000]' : 'text-white/70' }}" />
          @else
            <span class="{{ $activeSettings ? 'text-[#FF6000]' : 'text-white/70' }}">⚙️</span>
          @endisset
          <span>Ajustes</span>
        </a>

      </nav>
    </div>
  </div>
</aside>

