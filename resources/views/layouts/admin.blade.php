<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="utf-8" />
  <title>@yield('title', 'Panel — ComercioPlus')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen text-white">
  {{-- Fondo estilo WELCOME (gradiente + imagen + tinte naranja) --}}
  <div class="fixed inset-0 -z-50"
       style="
         background-image:
           linear-gradient(to right, rgba(14,15,18,.92), rgba(14,15,18,.65)),
           url('https://images.unsplash.com/photo-1517940310602-75f39d4ac6fb?q=80&w=1880&auto=format&fit=crop');
         background-size: cover;
         background-position: center;">
  </div>
  <div class="fixed inset-0 -z-40 pointer-events-none"
       style="background: radial-gradient(700px 360px at 15% 25%, rgba(255,96,0,.25), transparent 60%);">
  </div>

  @php
    use Illuminate\Support\Facades\Auth;
    $user = Auth::user();
    $initials = $user ? collect(explode(' ', trim($user->name)))->map(fn($p)=>mb_strtoupper(mb_substr($p,0,1)))->join('') : 'U';
  @endphp

  {{-- SIDEBAR fijo en desktop + drawer en móvil --}}
  <aside class="hidden lg:flex fixed left-0 top-0 h-screen w-72 flex-col bg-white/10 backdrop-blur-md ring-1 ring-white/15 z-40">
    <div class="p-4 border-b border-white/10 flex items-center">
      <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
        <span class="text-2xl font-extrabold">Comercio</span>
        <span class="rounded px-2 font-extrabold text-xl bg-orange-500 text-black">+</span>
      </a>
    </div>

    <nav class="p-4 space-y-6 overflow-y-auto">
      {{-- Inicio --}}
      <div>
        <div class="px-2 text-[11px] uppercase tracking-wide text-white/60">Inicio</div>
        <ul class="mt-2 space-y-1">
          <li>
            <a href="{{ route('admin.dashboard') }}"
               class="block px-4 py-2 rounded-xl hover:bg-white/10 {{ request()->routeIs('admin.dashboard') ? 'bg-white/10' : '' }}">
              Dashboard
            </a>
          </li>
        </ul>
      </div>

      {{-- Catálogo --}}
      <div>
        <div class="px-2 text-[11px] uppercase tracking-wide text-white/60">Catálogo</div>
        <ul class="mt-2 space-y-1">
          <li>
            <a href="{{ route('admin.products.index') }}"
               class="block px-4 py-2 rounded-xl hover:bg-white/10 {{ request()->routeIs('admin.products.*') ? 'bg-white/10' : '' }}">
              Productos
            </a>
          </li>
          <li>
            <a href="{{ route('admin.categories.index') }}"
               class="block px-4 py-2 rounded-xl hover:bg-white/10 {{ request()->routeIs('admin.categories.*') ? 'bg-white/10' : '' }}">
              Categorías
            </a>
          </li>
          <li><a href="#" class="block px-4 py-2 rounded-xl hover:bg-white/10">Inventario</a></li>
        </ul>
      </div>

      {{-- Tienda --}}
      <div>
        <div class="px-2 text-[11px] uppercase tracking-wide text-white/60">Tienda</div>
        <ul class="mt-2 space-y-1">
          <li><a href="{{ route('store.create') }}" class="block px-4 py-2 rounded-xl hover:bg-white/10">Datos de la tienda</a></li>
          <li><a href="{{ route('store.create') }}" class="block px-4 py-2 rounded-xl hover:bg-white/10">Logo y portada</a></li>
          <li><a href="#" class="block px-4 py-2 rounded-xl hover:bg-white/10">Apariencia</a></li>
          <li><a href="#" class="block px-4 py-2 rounded-xl hover:bg-white/10">Métodos de pago</a></li>
          <li><a href="#" class="block px-4 py-2 rounded-xl hover:bg-white/10">Envíos</a></li>
          <li><a href="#" class="block px-4 py-2 rounded-xl hover:bg-white/10">Dominio / URL pública</a></li>
        </ul>
      </div>

      {{-- Configuración --}}
      <div>
        <div class="px-2 text-[11px] uppercase tracking-wide text-white/60">Configuración</div>
        <ul class="mt-2 space-y-1">
          <li><a href="{{ route('profile.edit') }}" class="block px-4 py-2 rounded-xl hover:bg-white/10">Mi perfil</a></li>
          <li><a href="#" class="block px-4 py-2 rounded-xl hover:bg-white/10">Seguridad</a></li>
          <li><a href="#" class="block px-4 py-2 rounded-xl hover:bg-white/10">Notificaciones</a></li>
        </ul>
      </div>

      <form method="POST" action="{{ route('logout') }}" class="pt-2">
        @csrf
        <button type="submit"
                class="w-full text-left px-4 py-2 rounded-xl text-red-200 hover:bg-red-600/20 hover:text-white transition">
          Cerrar sesión
        </button>
      </form>
    </nav>
  </aside>

  {{-- MAIN con padding a la izquierda para el sidebar --}}
  <div class="min-h-screen lg:pl-72">
    {{-- Topbar --}}
    <header class="sticky top-0 z-30 bg-white/10 backdrop-blur-md ring-1 ring-white/15">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-16 flex items-center gap-4">
        {{-- Botón del drawer en móvil --}}
        <button class="lg:hidden -ml-1 inline-flex items-center justify-center h-9 w-9 rounded-lg bg-white/10"
                onclick="document.getElementById('drawer').classList.remove('hidden')">☰</button>

        <div class="font-semibold truncate">@yield('header', 'Panel del Comerciante')</div>

        <div class="flex-1">
          <form action="#" class="max-w-xl">
            <input type="search" placeholder="Busca productos, categorías…"
                   class="w-full rounded-full bg-white/10 ring-1 ring-white/15 px-4 h-10 placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-orange-500">
          </form>
        </div>

        <div class="flex items-center gap-2">
          <a href="{{ route('admin.products.create') }}"
             class="hidden sm:inline-flex items-center h-9 rounded-full px-4 text-sm font-semibold bg-white/10 hover:bg-white/15">
            + Producto
          </a>
          <a href="{{ route('store.create') }}"
             class="inline-flex items-center h-9 rounded-full px-4 text-sm font-semibold bg-orange-500 text-black hover:bg-orange-600 shadow">
            Crear tienda
          </a>
          <div class="ml-1 h-9 w-9 rounded-full bg-white/15 grid place-items-center font-bold text-sm">{{ $initials }}</div>
        </div>
      </div>
    </header>

    {{-- Contenido --}}
    <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
      @yield('content')
    </main>

    {{-- Footer compacto --}}
    <footer class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-8">
      <div class="text-xs text-white/70">© {{ now()->year }} ComercioPlus — Panel del Comerciante</div>
    </footer>
  </div>

  {{-- Drawer móvil del menú --}}
  <div id="drawer" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="this.parentElement.classList.add('hidden')"></div>
    <div class="absolute left-0 top-0 h-full w-72 bg-white/10 backdrop-blur-md ring-1 ring-white/15 p-4">
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
          <span class="text-xl font-extrabold">Comercio</span>
          <span class="rounded px-2 font-extrabold bg-orange-500 text-black">+</span>
        </div>
        <button class="text-white/90" onclick="document.getElementById('drawer').classList.add('hidden')">✕</button>
      </div>
      <nav class="space-y-2">
        <a class="block px-3 py-2 rounded-xl hover:bg-white/10" href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a class="block px-3 py-2 rounded-xl hover:bg-white/10" href="{{ route('admin.products.index') }}">Productos</a>
        <a class="block px-3 py-2 rounded-xl hover:bg-white/10" href="{{ route('admin.categories.index') }}">Categorías</a>
        <a class="block px-3 py-2 rounded-xl hover:bg-white/10" href="{{ route('store.create') }}">Mi Tienda</a>
        <a class="block px-3 py-2 rounded-xl hover:bg-white/10" href="{{ route('profile.edit') }}">Perfil</a>
      </nav>
    </div>
  </div>
</body>
</html>
