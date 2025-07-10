<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'ComercioPlus')</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-neutral-950 text-white antialiased flex flex-col">
  <!-- NAVBAR -->
  <header class="sticky top-0 z-30 bg-neutral-900/70 backdrop-blur border-b border-white/10">
    <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
      <a href="{{ url('/') }}" class="flex items-center gap-2 font-semibold">
        <span class="inline-block h-3.5 w-3.5 rounded-full bg-orange-500"></span>
        Comercio<span class="text-orange-500">Plus</span>
      </a>
      <div class="hidden md:flex items-center gap-6 text-sm text-white/80">
        <a href="#inicio" class="hover:text-white">Inicio</a>
        <a href="#categorias" class="hover:text-white">Categorías</a>
        <a href="#footer" class="hover:text-white">Contacto</a>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('login') }}" class="px-3 h-9 inline-flex items-center rounded-full border border-white/20 hover:bg-white/5">Iniciar sesión</a>
        <a href="{{ route('register') }}" class="px-3 h-9 inline-flex items-center rounded-full bg-orange-500 hover:bg-orange-600">Registrarme</a>
      </div>
    </nav>
  </header>

  <!-- CONTENIDO -->
  <main id="inicio" class="flex-1">
    @yield('content')
  </main>

  <!-- FOOTER -->
  <footer id="footer" class="border-t border-white/10 bg-neutral-900/70">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 grid gap-6 md:grid-cols-3 text-sm">
      <div>
        <div class="flex items-center gap-2 font-semibold">
          <span class="inline-block h-3.5 w-3.5 rounded-full bg-orange-500"></span>
          Comercio<span class="text-orange-500">Plus</span>
        </div>
        <p class="mt-3 text-white/70">Gestión moderna para comerciantes de repuestos y accesorios de moto.</p>
      </div>
      <div>
        <div class="font-medium text-white">Producto</div>
        <ul class="mt-2 space-y-1 text-white/70">
          <li><a href="#categorias" class="hover:text-white">Características</a></li>
          <li><a href="#" class="hover:text-white">Precios</a></li>
        </ul>
      </div>
      <div>
        <div class="font-medium text-white">Cuenta</div>
        <ul class="mt-2 space-y-1 text-white/70">
          <li><a href="{{ route('login') }}" class="hover:text-white">Iniciar sesión</a></li>
          <li><a href="{{ route('register') }}" class="hover:text-white">Registrarme</a></li>
        </ul>
      </div>
    </div>
    <div class="text-center text-[12px] text-white/50 py-4">© {{ date('Y') }} ComercioPlus</div>
  </footer>
</body>
</html>
