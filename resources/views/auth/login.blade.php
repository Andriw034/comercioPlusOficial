{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.marketing')

@section('title', 'Iniciar sesión — ComercioPlus')

@section('content')
<section class="relative min-h-[92vh] flex items-center">
  {{-- Fondo coherente con welcome/register --}}
  <div class="absolute inset-0 -z-10"
       style="
         background-image:
           linear-gradient(to right, rgba(14,15,18,.92), rgba(14,15,18,.65)),
           url('https://images.unsplash.com/photo-1517940310602-75f39d4ac6fb?q=80&w=1880&auto=format&fit=crop');
         background-size: cover;
         background-position: center;
         background-repeat: no-repeat;">
  </div>
  <div class="absolute inset-0 -z-10 pointer-events-none"
       style="background: radial-gradient(700px 360px at 15% 25%, rgba(255,96,0,.25), transparent 60%);"></div>

  <div class="relative mx-auto max-w-7xl w-full px-4 sm:px-6 lg:px-8 py-10 grid gap-10 lg:grid-cols-2 items-center">
    {{-- Lado izquierdo: branding --}}
    <div class="max-w-xl">
      <h1 class="text-4xl/tight sm:text-5xl/tight font-extrabold tracking-tight text-white drop-shadow-[0_2px_18px_rgba(0,0,0,.65)]">
        Bienvenido a <span class="text-orange-400">ComercioPlus</span>
      </h1>
      <p class="mt-4 text-white/90">
        Accede a tu panel para gestionar <strong>productos</strong>, <strong>categorías</strong> y tu <strong>tienda pública</strong>.
      </p>

      <ul class="mt-6 space-y-2 text-white/90">
        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span> Catálogo elegante y rápido</li>
        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span> Estilo JBL con detalles en naranja</li>
        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span> Integración Blade + Vue</li>
      </ul>

      <p class="mt-6 text-sm text-white/80">
        ¿No tienes cuenta?
        <a href="{{ route('register') }}" class="text-orange-400 hover:text-orange-300 underline underline-offset-2">Crea tu cuenta</a>
      </p>
    </div>

    {{-- Lado derecho: formulario login --}}
    <div class="lg:pl-6">
      <div class="rounded-3xl bg-white/10 backdrop-blur-md ring-1 ring-white/15 shadow-2xl p-6 sm:p-8">
        {{-- Errores --}}
        @if ($errors->any())
          <div class="mb-4 rounded-xl bg-red-500/15 text-red-300 ring-1 ring-red-500/30 px-4 py-3 text-sm">
            <div class="font-semibold">No se pudo iniciar sesión:</div>
            <ul class="mt-1 list-disc list-inside">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        @if (session('status'))
          <div class="mb-4 rounded-xl bg-green-500/15 text-green-300 ring-1 ring-green-500/30 px-4 py-3 text-sm">
            {{ session('status') }}
          </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
          @csrf

          {{-- Email --}}
          <div>
            <label for="email" class="block text-sm font-medium text-white/90">Correo electrónico</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                   class="mt-2 w-full rounded-xl border border-white/20 bg-black text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-orange-500 h-11 px-4">
          </div>

          {{-- Password --}}
          <div>
            <label for="password" class="block text-sm font-medium text-white/90">Contraseña</label>
            <div class="mt-2 relative">
              <input id="password" name="password" type="password" required autocomplete="current-password"
                     class="w-full rounded-xl border border-white/20 bg-black text-w placeholder-black/50 focus:outline-none focus:ring-2 focus:ring-orange-500 h-11 px-4 pr-12">
              <button type="button" data-toggle="password" data-target="#password"
                      class="absolute inset-y-0 right-2 my-auto h-8 w-8 rounded-lg bg-black/5 hover:bg-black/10 flex items-center justify-center"
                      title="Mostrar/ocultar">
                <span class="sr-only">Mostrar/ocultar contraseña</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-black/70" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 5c-7.633 0-11 7-11 7s3.367 7 11 7 11-7 11-7-3.367-7-11-7zm0 12a5 5 0 110-10 5 5 0 010 10z"/>
                </svg>
              </button>
            </div>
          </div>

          {{-- Recordarme + Olvidé contraseña --}}
          <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-sm text-black/90">
              <input type="checkbox" name="remember" class="h-4 w-4 rounded border-white/20 bg-black text-white focus:ring-orange-500">
              Recuérdame
            </label>
            @if (Route::has('password.request'))
              <a href="{{ route('password.request') }}" class="text-sm/90 hover:text-white/100 underline underline-offset-2">
                ¿Olvidaste tu contraseña?
              </a>
            @endif
          </div>

          {{-- Botón principal --}}
          <div class="pt-2">
            <button type="submit"
                    class="inline-flex h-12 items-center rounded-full px-7 font-semibold bg-orange-500 hover:bg-orange-600 shadow-lg shadow-orange-500/25 transition text-black">
              Iniciar sesión
            </button>
          </div>
        </form>
      </div>

      <p class="mt-4 text-[13px] text-white/70">Accede para continuar con la configuración de tu tienda y catálogo.</p>
    </div>
  </div>
</section>

<script>
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-toggle="password"]');
    if (!btn) return;
    const input = document.querySelector(btn.getAttribute('data-target'));
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
  });
</script>
@endsection
