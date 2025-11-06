<nav class="w-full border-b border-gray-200 bg-white/90 backdrop-blur">
  <div class="mx-auto max-w-7xl px-4 h-14 flex items-center justify-between">
    <a href="{{ route('welcome') }}" class="flex items-center gap-2">
      <img src="{{ asset('assets/comercioplus-logo.png') }}" class="h-7 w-7 rounded" alt="">
      <span class="font-extrabold">Comercio<span style="color:var(--cp-primary)">Plus</span></span>
    </a>
    <div class="flex items-center gap-3 text-sm">
      @auth
        <a class="text-[var(--cp-text)] hover:text-[var(--cp-primary)]" href="{{ route('admin.dashboard') }}">Dashboard</a>
        <form method="POST" action="{{ route('logout') }}"> @csrf
          <button class="text-[var(--cp-text)] hover:text-[var(--cp-primary)]">Cerrar sesión</button>
        </form>
      @else
        <a class="text-[var(--cp-text)] hover:text-[var(--cp-primary)]" href="{{ route('catalogo') }}">Catálogo</a>
        <a class="text-[var(--cp-text)] hover:text-[var(--cp-primary)]" href="{{ route('login') }}">Iniciar sesión</a>
        <a class="btn btn-primary !py-1.5" href="{{ route('register') }}">Crear cuenta</a>
      @endauth
    </div>
  </div>
</nav>
