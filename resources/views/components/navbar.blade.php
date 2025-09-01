<header class="sticky top-0 z-40 backdrop-blur bg-white/90 border-b border-[var(--cp-border)]">
  <nav class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="h-16 flex items-center justify-between">
      <a href="{{ \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/') }}" class="flex items-center gap-3">
        <div class="h-9 w-9 rounded-2xl flex items-center justify-center"
             style="background:linear-gradient(135deg,var(--cp-primary),var(--cp-primary-2));"></div>
        <span class="text-lg font-extrabold">Comercio<span style="color:var(--cp-primary)">Plus</span></span>
      </a>

      <div class="hidden md:flex items-center gap-6 text-sm">
        <a href="{{ \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/') }}" class="hover:text-[var(--cp-primary)]">Inicio</a>
        @if(Route::has('public.store.show'))
          <a href="{{ route('public.store.show',['slug'=>'demo']) }}" class="hover:text-[var(--cp-primary)]">Catálogo</a>
        @endif
        @auth
          <a href="{{ route('products.index') }}" class="hover:text-[var(--cp-primary)]">Productos</a>
        @endauth
      </div>

      <div class="hidden md:flex items-center gap-3">
        @guest
          <a href="{{ route('login') }}" class="btn btn-secondary">Entrar</a>
          <a href="{{ route('register') }}" class="btn btn-primary">Crear cuenta</a>
        @else
          <a href="{{ route('logout') }}"
             onclick="event.preventDefault();document.getElementById('logout-form').submit();"
             class="btn btn-secondary">Salir</a>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        @endguest
      </div>
    </div>
  </nav>
</header>
