{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.marketing')

@section('title', 'Crear cuenta — ComercioPlus')

@section('content')
<section class="relative min-h-[92vh] flex items-center">
  {{-- Fondo: igual estilo que welcome --}}
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
    {{-- Lado izquierdo: Título y valor --}}
    <div class="max-w-xl">
      <h1 class="text-4xl/tight sm:text-5xl/tight font-extrabold tracking-tight text-white drop-shadow-[0_2px_18px_rgba(0,0,0,.65)]">
        Crea tu cuenta en <span class="text-orange-400">ComercioPlus</span>
      </h1>
      <p class="mt-4 text-white/90">
        En minutos tendrás tu <strong>tienda</strong>, podrás cargar productos, organizarlos por categorías y compartir tu catálogo profesional.
      </p>

      <ul class="mt-6 space-y-2 text-white/90">
        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span> Personaliza logo, portada y colores</li>
        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span> Catálogo por categorías</li>
        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span> Panel para crear/editar productos</li>
      </ul>

      <p class="mt-6 text-sm text-white/80">
        ¿Ya tienes cuenta?
        <a href="{{ route('login') }}" class="text-orange-400 hover:text-orange-300 underline underline-offset-2">Inicia sesión</a>
      </p>
    </div>

    {{-- Lado derecho: Formulario --}}
    <div class="lg:pl-6">
      <div class="rounded-3xl bg-white/10 backdrop-blur-md ring-1 ring-white/15 shadow-2xl p-6 sm:p-8">
        {{-- Mensajes de error --}}
        @if ($errors->any())
          <div class="mb-4 rounded-xl bg-red-500/15 text-red-300 ring-1 ring-red-500/30 px-4 py-3 text-sm">
            <div class="font-semibold">Revisa los campos:</div>
            <ul class="mt-1 list-disc list-inside">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        {{-- Éxito (si usas flashes) --}}
        @if (session('success'))
          <div class="mb-4 rounded-xl bg-green-500/15 text-green-300 ring-1 ring-green-500/30 px-4 py-3 text-sm">
            {{ session('success') }}
          </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
          @csrf

          {{-- Nombre --}}
          <div>
            <label for="name" class="block text-sm font-medium text-white/90">Nombre completo</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required
                   class="mt-2 w-full rounded-xl border border-white/20 bg-black text-black placeholder-black/50 focus:outline-none focus:ring-2 focus:ring-orange-500 h-11 px-4">
          </div>

          {{-- Email --}}
          <div>
            <label for="email" class="block text-sm font-medium text-white/90">Correo electrónico</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                   class="mt-2 w-full rounded-xl border border-white/20 bg-black text-black placeholder-black/50 focus:outline-none focus:ring-2 focus:ring-orange-500 h-11 px-4">
          </div>

          {{-- Rol (desplegable comerciante/cliente) --}}
          <div>
            <label for="role" class="block text-sm font-medium text-white/90">Tipo de cuenta</label>
            <div class="mt-2 relative">
              <select id="role" name="role" required
                      class="w-full appearance-none rounded-xl border border-white/20 bg-black text-white focus:outline-none focus:ring-2 focus:ring-orange-500 h-11 px-4 pr-10">
                <option value="" disabled {{ old('role') ? '' : 'selected' }}>Selecciona una opción</option>
                <option value="comerciante" {{ old('role') === 'comerciante' ? 'selected' : '' }}>Comerciante</option>
                <option value="cliente" {{ old('role') === 'cliente' ? 'selected' : '' }}>Cliente</option>
              </select>
              {{-- Icono flecha --}}
              <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-black/70" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            <p class="mt-2 text-xs text-white/70">Si eliges <span class="text-orange-300 font-semibold">Comerciante</span>, al terminar el registro te llevamos a crear tu tienda.</p>
          </div>

          {{-- Contraseña --}}
          <div>
            <label for="password" class="block text-sm font-medium text-white/90">Contraseña</label>
            <div class="mt-2 relative">
              <input id="password" name="password" type="password" required autocomplete="new-password"
                     class="w-full rounded-xl border border-white/20 bg-black text-black placeholder-black/50 focus:outline-none focus:ring-2 focus:ring-orange-500 h-11 px-4 pr-12">
              <button type="button" data-toggle="password" data-target="#password"
                      class="absolute inset-y-0 right-2 my-auto h-8 w-8 rounded-lg bg-black/5 hover:bg-black/10 flex items-center justify-center"
                      title="Mostrar/ocultar">
                <span class="sr-only">Mostrar/ocultar contraseña</span>
                {{-- icono ojo --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-black/70" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 5c-7.633 0-11 7-11 7s3.367 7 11 7 11-7 11-7-3.367-7-11-7zm0 12a5 5 0 110-10 5 5 0 010 10z"/>
                </svg>
              </button>
            </div>
          </div>

          {{-- Confirmación --}}
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-white/90">Confirmar contraseña</label>
            <div class="mt-2 relative">
              <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                     class="w-full rounded-xl border border-white/20 bg-black text-black placeholder-black/50 focus:outline-none focus:ring-2 focus:ring-orange-500 h-11 px-4 pr-12">
              <button type="button" data-toggle="password" data-target="#password_confirmation"
                      class="absolute inset-y-0 right-2 my-auto h-8 w-8 rounded-lg bg-black/5 hover:bg-black/10 flex items-center justify-center"
                      title="Mostrar/ocultar">
                <span class="sr-only">Mostrar/ocultar confirmación</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-black/70" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 5c-7.633 0-11 7-11 7s3.367 7 11 7 11-7 11-7-3.367-7-11-7zm0 12a5 5 0 110-10 5 5 0 010 10z"/>
                </svg>
              </button>
            </div>
          </div>

          {{-- Términos (opcional) --}}
          <div class="flex items-start gap-3">
            <input id="terms" name="terms" type="checkbox" class="mt-1 h-4 w-4 rounded border-white/20 bg-black text-black focus:ring-orange-500">
            <label for="terms" class="text-sm text-white/90">Acepto los términos y condiciones</label>
          </div>

          {{-- Botón principal: naranja con texto negro (alta visibilidad) --}}
          <div class="pt-2">
            <button type="submit"
                    class="inline-flex h-12 items-center rounded-full px-7 font-semibold bg-orange-500 hover:bg-orange-600 shadow-lg shadow-orange-500/25 transition text-black">
              Registrarme
            </button>
          </div>
        </form>
      </div>

      <p class="mt-4 text-[13px] text-white/70">
        Al registrarte, aceptas nuestras políticas. Usa un correo válido para activar funciones de tu tienda.
      </p>
    </div>
  </div>
</section>

{{-- Script mínimo para mostrar/ocultar contraseña, sin depender de framework --}}
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
