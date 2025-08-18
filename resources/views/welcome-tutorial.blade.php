@extends('layouts.app')

@section('content')
  <!-- HERO con degradado naranja -->
  <section class="relative">
    <div class="absolute inset-0 -z-10"
         style="background: linear-gradient(135deg, var(--cp-primary), var(--cp-primary-2));"></div>

    <!-- imagen de fondo configurable por comerciante -->
    @if(!empty($heroImage))
      <div class="absolute inset-0 -z-10 opacity-20 bg-cover bg-center"
           style="background-image:url('{{ $heroImage }}')"></div>
    @endif

    <div class="mx-auto max-w-6xl px-6 py-24 text-center text-white">
      <h1 class="text-4xl sm:text-6xl font-extrabold drop-shadow">
        Crea tu tienda online con <span class="underline decoration-white/40">ComercioPlus</span>
      </h1>
      <p class="mt-4 text-lg sm:text-xl text-white/90">
        La plataforma para montar tu tienda en minutos. Sin complicaciones, sin código.
      </p>

      <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
        <!-- CTAs por rol -->
        <button id="openRegister"
          class="px-6 py-3 rounded-2xl bg-white text-slate-900 shadow hover:shadow-md">
          Soy Comerciante (crear tienda)
        </button>
        <a href="{{ route('store.public', ['slug' => 'demo']) }}"
           class="px-6 py-3 rounded-2xl border border-white/70 text-white hover:bg-white/10">
          Soy Cliente (ver demo)
        </a>
      </div>
    </div>
  </section>

  <!-- SECCIÓN features corta -->
  <section class="mx-auto max-w-6xl px-6 py-16">
    <h2 class="text-3xl font-bold text-center">Todo lo que necesitas para vender online</h2>
    <div class="mt-10 grid gap-6 sm:grid-cols-3">
      <div class="rounded-2xl p-6 shadow hover:shadow-md border border-slate-100"
           style="background: linear-gradient(180deg, #fff, #fff7f2);">
        <div class="text-3xl">💬</div>
        <h3 class="mt-3 font-semibold">Gestión simple</h3>
        <p class="text-sm text-slate-600">Carga productos, categorías y precios en minutos.</p>
      </div>
      <div class="rounded-2xl p-6 shadow hover:shadow-md border border-slate-100"
           style="background: linear-gradient(180deg, #fff, #fff7f2);">
        <div class="text-3xl">💳</div>
        <h3 class="mt-3 font-semibold">Pagos y pedidos</h3>
        <p class="text-sm text-slate-600">Flujo de compra claro y notificaciones.</p>
      </div>
      <div class="rounded-2xl p-6 shadow hover:shadow-md border border-slate-100"
           style="background: linear-gradient(180deg, #fff, #fff7f2);">
        <div class="text-3xl">📈</div>
        <h3 class="mt-3 font-semibold">Crece con datos</h3>
        <p class="text-sm text-slate-600">Métricas de visitas, clics y ventas.</p>
      </div>
    </div>
  </section>

  <!-- MODAL: LOGIN -->
  <div id="modalLogin" class="cp-modal fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" data-close-modal></div>
    <div class="relative mx-auto mt-20 w-[92%] max-w-xl rounded-2xl bg-white p-6 shadow-xl">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold">Iniciar Sesión</h3>
        <button class="text-slate-500" data-close-modal>&times;</button>
      </div>

      <form method="POST" action="{{ route('login') }}" class="mt-4 space-y-4">
        @csrf
        <div>
          <label class="text-sm">Email</label>
          <input type="email" name="email" class="mt-1 w-full rounded-xl border-slate-300" required>
        </div>
        <div>
          <label class="text-sm">Contraseña</label>
          <input type="password" name="password" class="mt-1 w-full rounded-xl border-slate-300" required>
        </div>

        <!-- Opcional: selector de rol al iniciar (si tu flujo lo requiere) -->
        <div>
          <label class="text-sm">Rol</label>
          <select name="role" class="mt-1 w-full rounded-xl border-slate-300">
            <option value="" selected>Detectar automáticamente</option>
            <option value="comerciante">Comerciante</option>
            <option value="cliente">Cliente</option>
          </select>
        </div>

        <button class="w-full rounded-xl py-3 text-white"
                style="background:linear-gradient(90deg, var(--cp-primary), var(--cp-primary-2));">
          Iniciar Sesión
        </button>
      </form>
    </div>
  </div>

  <!-- MODAL: REGISTRO (con rol obligatorio) -->
  <div id="modalRegister" class="cp-modal fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" data-close-modal></div>
    <div class="relative mx-auto mt-16 w-[92%] max-w-2xl rounded-2xl bg-white p-6 shadow-xl">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold">Crear cuenta</h3>
        <button class="text-slate-500" data-close-modal>&times;</button>
      </div>

      <form method="POST" action="{{ route('register') }}" class="mt-4 grid gap-4">
        @csrf
        <div>
          <label class="text-sm">Nombre</label>
          <input type="text" name="name" class="mt-1 w-full rounded-xl border-slate-300" required>
        </div>
        <div>
          <label class="text-sm">Email</label>
          <input type="email" name="email" class="mt-1 w-full rounded-xl border-slate-300" required>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="text-sm">Contraseña</label>
            <input type="password" name="password" class="mt-1 w-full rounded-xl border-slate-300" required>
          </div>
          <div>
            <label class="text-sm">Confirmar Contraseña</label>
            <input type="password" name="password_confirmation" class="mt-1 w-full rounded-xl border-slate-300" required>
          </div>
        </div>

        <!-- Selector de rol ESENCIAL -->
        <div>
          <label class="text-sm">Rol</label>
          <!-- Si usas Spatie: envía "comerciante" / "cliente". 
               Si usas role_id, cambia name="role_id" y los value (1/2) -->
          <select name="role" class="mt-1 w-full rounded-xl border-slate-300" required>
            <option value="" disabled selected>Selecciona tu rol</option>
            <option value="comerciante">Comerciante (crear mi tienda)</option>
            <option value="cliente">Cliente (comprar / ver tiendas)</option>
          </select>
        </div>

        <button class="mt-2 w-full rounded-xl py-3 text-white"
                style="background:linear-gradient(90deg, var(--cp-primary), var(--cp-primary-2));">
          Registrarse
        </button>
      </form>
    </div>
  </div>
@endsection
