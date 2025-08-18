<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Comercio Plus — Panel</title>

  <!-- Tailwind por CDN (suficiente para el diseño) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              50:'#fff7ed',100:'#ffedd5',200:'#fed7aa',300:'#fdba74',400:'#fb923c',
              500:'#f97316',600:'#ea580c',700:'#c2410c',800:'#9a3412',900:'#7c2d12'
            }
          },
          boxShadow: { soft: '0 10px 30px rgba(15, 23, 42, 0.08)' }
        }
      }
    }
  </script>

  <style>
    /* Fondo con degradado suave + blobs */
    body::before {
      content: "";
      position: fixed; inset: 0;
      background:
        radial-gradient(900px 500px at -10% -10%, rgba(249,117,22,.12), transparent 60%),
        radial-gradient(800px 450px at 110% 5%, rgba(59,130,246,.10), transparent 60%),
        linear-gradient(180deg, #f8fafc 0%, #ffffff 60%, #f8fafc 100%);
      z-index: -2;
    }
    body::after {
      content: "";
      position: fixed; inset: 0;
      backdrop-filter: blur(.5px);
      z-index: -1;
    }

    /* Utilidades glass */
    .glass { background: rgba(255,255,255,.6); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,.5) }

    /* Sidebar */
    .nav-link { display:flex; align-items:center; gap:.75rem; padding:.5rem .75rem; border-radius:.75rem; color:#475569; transition:.2s }
    .nav-link:hover { color:#0f172a; background:rgba(255,255,255,.7) }
    .nav-active { position:relative; background:#fff; color:#0f172a; box-shadow:0 8px 20px rgba(15,23,42,.06) }
    .nav-active::before{
      content:""; position:absolute; left:-12px; top:8px; bottom:8px; width:4px;
      border-radius:999px; background:linear-gradient(180deg, #fb923c, #60a5fa);
    }
    .icon-wrap{
      width: 36px; height: 36px;
      display:flex; align-items:center; justify-content:center;
      border-radius: 12px;
      background: linear-gradient(135deg, rgba(255,255,255,.85), rgba(255,255,255,.55));
      box-shadow: inset 0 6px 14px rgba(15,23,42,.06);
    }

    /* Botones */
    .btn { display:inline-flex; align-items:center; gap:.5rem; border-radius:.75rem; padding:.5rem 1rem; font-weight:500; box-shadow:0 10px 30px rgba(15,23,42,.08) }
    .btn-primary { background:#f97316; color:#fff }
    .btn-primary:hover{ background:#ea580c }
    .btn-ghost { background:rgba(255,255,255,.6); color:#334155 }
    .btn-ghost:hover{ background:#fff }

    /* Tarjetas glass flotantes para productos */
    .card-float {
      position: relative;
      border-radius: 1rem;
      background: rgba(255,255,255,0.20);
      border: 1px solid rgba(255,255,255,0.35);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      transition: transform .35s ease, box-shadow .35s ease, background .35s ease;
      overflow: hidden;
    }
    .card-float::after {
      content:"";
      position:absolute; inset:-10px;
      border-radius: 1.25rem;
      background: radial-gradient(60% 60% at 20% 0%, rgba(249,115,22,.20), transparent 60%),
                  radial-gradient(60% 60% at 100% 0%, rgba(59,130,246,.18), transparent 60%);
      filter: blur(18px);
      opacity: .0;
      transition: opacity .35s ease, transform .35s ease;
      z-index:-1;
    }
    .card-float:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 40px rgba(15,23,42,.14);
      background: rgba(255,255,255,0.25);
    }
    .card-float:hover::after { opacity: .9; }

    .card-thumb {
      position: relative;
      border-top-left-radius: 1rem;
      border-top-right-radius: 1rem;
      min-height: 180px;
      background: linear-gradient(135deg, rgba(255,255,255,.16), rgba(255,255,255,.06));
      overflow: hidden;
    }
    .thumb-empty{
      display:flex; align-items:center; justify-content:center; height:100%;
      color:#64748b; font-size:.9rem; border:2px dashed rgba(148,163,184,.35);
      background: transparent;
    }
    .card-thumb img { width:100%; height:100%; object-fit:cover; transform:scale(1.00); transition:transform .5s ease }
    .card-float:hover .card-thumb img { transform:scale(1.03) }
  </style>
</head>
<body class="min-h-screen text-slate-800">

  <!-- SIDEBAR -->
  <aside class="fixed inset-y-0 left-0 w-72 hidden md:flex flex-col p-5 gap-4 glass">
    <div class="flex items-center gap-3">
      <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-white via-white/70 to-white/40 flex items-center justify-center shadow-soft">
        <span class="text-brand-600 font-extrabold text-xl">+</span>
      </div>
      <div>
        <p class="text-lg font-bold leading-none">Trade</p>
        <p class="text-xs text-slate-500 -mt-0.5">administration</p>
      </div>
    </div>

    <nav class="mt-4 flex flex-col gap-2">
      <a href="{{ route('admin.dashboard') }}"
         class="nav-link {{ request()->routeIs('admin.dashboard') ? 'nav-active' : '' }}">
        <span class="icon-wrap">
          <svg class="w-5 h-5 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h7V3H3v9zm0 9h7v-7H3v7zm11 0h7V12h-7v9zm0-18v7h7V3h-7z"/></svg>
        </span>
        <span class="text-[15px]">Dashboard</span>
      </a>

      <a href="{{ route('profile.edit') }}"
         class="nav-link {{ request()->routeIs('profile.*') ? 'nav-active' : '' }}">
        <span class="icon-wrap">
          <svg class="w-5 h-5 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A7 7 0 0112 15a7 7 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </span>
        <span class="text-[15px]">Profile</span>
      </a>

      <a href="{{ route('store.index', [], false) ?: (route('stores.index', [], false) ?? '#') }}"
         class="nav-link {{ request()->routeIs('store.*') || request()->routeIs('stores.*') ? 'nav-active' : '' }}">
        <span class="icon-wrap">
          <svg class="w-5 h-5 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l1-5h16l1 5M5 9v10a2 2 0 002 2h10a2 2 0 002-2V9M9 13h6"/></svg>
        </span>
        <span class="text-[15px]">My Store</span>
      </a>

      <a href="{{ route('products.index') }}"
         class="nav-link {{ request()->routeIs('products.*') ? 'nav-active' : '' }}">
        <span class="icon-wrap">
          <svg class="w-5 h-5 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0v6a2 2 0 01-1 1.732l-7 4.041a2 2 0 01-2 0l-7-4.041A2 2 0 014 13V7m16 0L12 11m0 0L4 7"/></svg>
        </span>
        <span class="text-[15px]">Products</span>
      </a>

      <form method="POST" action="{{ route('logout') }}" class="mt-2">
        @csrf
        <button type="submit" class="nav-link text-rose-600 hover:text-white hover:bg-rose-600/90">
          <span class="icon-wrap">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v1"/></svg>
          </span>
          <span class="text-[15px]">Log out</span>
        </button>
      </form>
    </nav>

    <div class="mt-auto text-xs text-slate-500">© {{ date('Y') }} Comercio Plus</div>
  </aside>

  <!-- HEADER (único) -->
  <div class="md:ml-72">
    <header class="sticky top-0 z-20 glass px-4 md:px-8 py-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
            @yield('page_title', 'Panel')
          </h1>
          <span class="hidden md:block h-6 w-px bg-gradient-to-b from-slate-300 to-transparent rounded"></span>
          <p class="hidden md:block text-slate-500">
            @yield('page_subtitle', 'Manage your store and products')
          </p>
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('products.create') }}" class="btn btn-ghost">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New product
          </a>
          <a href="{{ (Route::has('store.index') ? route('store.index') : (Route::has('stores.index') ? route('stores.index') : '#')) }}"
             class="btn btn-primary">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.983 12a3 3 0 100-6 3 3 0 000 6zm7.79 1.61a1 1 0 00.2 1.1l.02.02a2 2 0 010 2.82l-.02.02a1 1 0 00-1.1.2l-.02.02a2 2 0 01-2.82 0l-.02-.02a1 1 0 00-1.1-.2l-.02.02a2 2 0 01-2.22 0l-.02-.02a1 1 0 00-1.1.2l-.02.02a2 2 0 01-2.82 0l-.02-.02a1 1 0 00-1.1-.2l-.02.02a2 2 0 010-2.82l.02-.02a1 1 0 00.2-1.1l-.02-.02a2 2 0 010-2.22l.02-.02"/></svg>
            Set up store
          </a>
        </div>
      </div>
    </header>

    <!-- CONTENIDO -->
    <main class="p-4 md:p-8">
      @if(session('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50/60 px-4 py-3 text-sm text-green-700">
          {{ session('success') }}
        </div>
      @endif

      @yield('content')
    </main>
  </div>
</body>
</html>
