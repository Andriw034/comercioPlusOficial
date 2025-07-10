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
         background-position: center;
         background-repeat: no-repeat;">
  </div>
  <div class="fixed inset-0 -z-40 pointer-events-none"
       style="background: radial-gradient(700px 360px at 15% 25%, rgba(255,96,0,.25), transparent 60%);">
  </div>

  @php
    use Illuminate\Support\Facades\Auth;
    $user = Auth::user();
    $initials = method_exists($user, 'getInitialsAttribute') ? ($user->initials ?? 'U') : (strtoupper(substr($user->name ?? 'U', 0, 1)));
  @endphp

  <div class="min-h-screen grid grid-cols-1 lg:grid-cols-[300px_1fr]">
    {{-- SIDEBAR --}}
    <aside class="lg:sticky lg:top-0 lg:h-screen bg-white/10 backdrop-blur-md ring-1 ring-white/15">
      {{-- Brand / Toggle --}}
      <div class="p-4 border-b border-white/10 flex items-center justify-between">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
          <span class="text-2xl font-extrabold">Comercio</span>
          <span class="rounded px-2 font-extrabold text-xl bg-orange-500 text-black">+</span>
        </a>
        <button class="lg:hidden text-white/90 focus:outline-none" onclick="toggleSidebar()">
          <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      {{-- NAV --}}
      <nav id="sidebar" class="p-4 space-y-6 overflow-y-auto transform lg:translate-x-0 -translate-x-full transition-transform duration-300 ease-in-out">

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

        {{-- Configuración / Perfil --}}
        <div>
          <button type="button" class="w-full px-3 py-2 rounded-lg hover:bg-white/10 flex items-center justify-between"
                  onclick="toggleSection('conf')">
            <span class="text-sm font-medium">Configuración</span>
            <span id="chev-conf" class="text-xs">▾</span>
          </button>
          <ul id="sec-conf" class="mt-2 space-y-1 pl-2">
            <li><a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-lg hover:bg-white/10">Mi perfil</a></li>
            <li><a href="#" class="block px-3 py-2 rounded-lg hover:bg-white/10">Seguridad</a></li>
            <li><a href="#" class="block px-3 py-2 rounded-lg hover:bg-white/10">Notificaciones</a></li>
          </ul>
        </div>

        {{-- Catálogo --}}
        <div>
          <button type="button" class="w-full px-3 py-2 rounded-lg hover:bg-white/10 flex items-center justify-between"
                  onclick="toggleSection('catalogo')">
            <span class="text-sm font-medium">Catálogo</span>
            <span id="chev-catalogo" class="text-xs">▾</span>
          </button>
          <ul id="sec-catalogo" class="mt-2 space-y-1 pl-2">
            <li>
              <a href="{{ route('admin.products.index') }}"
                 class="block px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.products.*') ? 'bg-white/10' : '' }}">
                Productos
              </a>
            </li>
            <li>
              <a href="{{ route('admin.categories.index') }}"
                 class="block px-3 py-2 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.categories.*') ? 'bg-white/10' : '' }}">
                Categorías
              </a>
            </li>
            <li><a href="#" class="block px-3 py-2 rounded-lg hover:bg-white/10">Inventario</a></li>
          </ul>
        </div>

        {{-- Tienda --}}
        <div>
          <button type="button" class="w-full px-3 py-2 rounded-lg hover:bg-white/10 flex items-center justify-between"
                  onclick="toggleSection('tienda')">
            <span class="text-sm font-medium">Tienda</span>
            <span id="chev-tienda" class="text-xs">▾</span>
          </button>
          <ul id="sec-tienda" class="mt-2 space-y-1 pl-2">
            <li><a href="{{ route('store.create') }}" class="block px-3 py-2 rounded-lg hover:bg-white/10">Datos de la tienda</a></li>
            <li><a href="{{ route('store.create') }}" class="block px-3 py-2 rounded-lg hover:bg-white/10">Logo y portada</a></li>
            <li><a href="#" class="block px-3 py-2 rounded-lg hover:bg-white/10">Apariencia</a></li>
            <li><a href="#" class="block px-3 py-2 rounded-lg hover:bg-white/10">Métodos de pago</a></li>
            <li><a href="#" class="block px-3 py-2 rounded-lg hover:bg-white/10">Envíos</a></li>
            <li><a href="#" class="block px-3 py-2 rounded-lg hover:bg-white/10">Dominio/URL pública</a></li>
          </ul>
        </div>

        {{-- Gestión --}}
        <div>
          <button type="button" class="w-full px-3 py-2 rounded-lg hover:bg-white/10 flex items-center justify-between"
                  onclick="toggleSection('gestion')">
            <span class="text-sm font-medium">Gestión</span>
            <span id="chev-gestion" class="text-xs">▾</span>
          </button>
          <ul id="sec-gestion" class="mt-2 space-y-1 pl-2">
            <li><a href="#" class="block px-3 py-2 rounded-lg hover:bg-white/10">Órdenes</a></li>
            <li><a href="#" class="block px-3 py-2 rounded-lg hover:bg-white/10">Clientes</a></li>
            <li><a href="#" class="block px-3 py-2 rounded-lg hover:bg-white/10">Calificaciones</a></li>
            <li><a href="#" class="block px-3 py-2 rounded-lg hover:bg-white/10">Analítica</a></li>
            <li><a href="#" class="block px-3 py-2 rounded-lg hover:bg-white/10">Soporte</a></li>
          </ul>
        </div>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}" class="pt-4">
          @csrf
          <button type="submit"
                  class="w-full text-left px-4 py-2 rounded-xl text-red-200 hover:bg-red-600/20 hover:text-white transition">
            Cerrar sesión
          </button>
        </form>
      </nav>
    </aside>

    {{-- MAIN --}}
    <div class="min-h-screen">
      {{-- TOPBAR --}}
      <header class="sticky top-0 z-10 bg-white/10 backdrop-blur-md ring-1 ring-white/15">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-16 flex items-center gap-4">
          {{-- Breadcrumb / Título --}}
          <div class="hidden sm:block font-semibold">@yield('header', 'Panel del Comerciante')</div>

          {{-- Search --}}
          <div class="flex-1">
            <form action="#" class="max-w-xl">
              <label class="relative block">
                <input type="search" placeholder="Buscar productos, categorías…"
                       class="w-full rounded-full bg-white/10 ring-1 ring-white/15 px-4 h-10 placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-orange-500">
              </label>
            </form>
          </div>

          {{-- Quick actions --}}
          <div class="flex items-center gap-2">
            <a href="{{ route('admin.products.create') }}"
               class="hidden sm:inline-flex items-center h-9 rounded-full px-4 text-sm font-semibold bg-white/10 hover:bg-white/15">
              + Producto
            </a>
            <a href="{{ route('store.create') }}"
               class="inline-flex items-center h-9 rounded-full px-4 text-sm font-semibold bg-orange-500 text-black hover:bg-orange-600 shadow">
              Crear tienda
            </a>

            {{-- User menu (simple) --}}
            <div class="ml-1 h-9 w-9 rounded-full bg-white/15 grid place-items-center font-bold text-sm">
              {{ $initials }}
            </div>
          </div>
        </div>
      </header>

      {{-- CONTENT --}}
      <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
      </main>
    </div>
  </div>

  <script>
    function toggleSidebar() {
      const sb = document.getElementById('sidebar');
      sb.classList.toggle('-translate-x-full');
    }
    function toggleSection(id) {
      const list = document.getElementById('sec-' + id);
      const chev = document.getElementById('chev-' + id);
      if (!list || !chev) return;
      const hidden = list.classList.toggle('hidden');
      chev.textContent = hidden ? '▸' : '▾';
    }
    // Por defecto, secciones abiertas
    ['conf','catalogo','tienda','gestion'].forEach(id => {
      const list = document.getElementById('sec-' + id);
      const chev = document.getElementById('chev-' + id);
      if (list && chev) { list.classList.remove('hidden'); chev.textContent = '▾'; }
    });
  </script>
</body>
</html>
