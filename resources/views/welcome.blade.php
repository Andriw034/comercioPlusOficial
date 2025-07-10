{{-- resources/views/welcome.blade.php --}}
@extends('layouts.marketing')

@section('title', 'ComercioPlus — Catálogo de repuestos y accesorios de moto')

@section('content')
  <!-- HERO -->
  <section class="relative min-h-[86vh] flex items-center">
    <!-- Fondo: gradiente + imagen externa -->
    <div class="absolute inset-0 -z-10"
         style="
           background-image:
             linear-gradient(to right, rgba(14,15,18,.92), rgba(14,15,18,.65)),
             url('https://images.unsplash.com/photo-1517940310602-75f39d4ac6fb?q=80&w=1880&auto=format&fit=crop');
           background-size: cover;
           background-position: center;
           background-repeat: no-repeat;">
    </div>
    <!-- Tinte naranja suave -->
    <div class="absolute inset-0 -z-10 pointer-events-none"
         style="background: radial-gradient(700px 360px at 15% 25%, rgba(255,96,0,.25), transparent 60%);"></div>

    <div class="relative mx-auto max-w-7xl w-full px-4 sm:px-6 lg:px-8 grid gap-12 lg:grid-cols-2 items-center">
      <!-- Texto + CTAs -->
      <div class="max-w-xl">
        <h1 class="text-4xl/tight sm:text-5xl/tight font-extrabold tracking-tight text-white drop-shadow-[0_2px_18px_rgba(0,0,0,.65)]">
          Tu vitrina de <span class="text-orange-400">repuestos y accesorios</span> en minutos
        </h1>
        <p class="mt-5 text-lg text-white/90 drop-shadow-[0_1px_10px_rgba(0,0,0,.7)]">
          Crea tu <strong>tienda</strong>, carga productos, organiza por categorías y comparte tu catálogo profesional.
        </p>
        <div class="mt-8 flex flex-wrap items-center gap-3">
          <a href="{{ route('register') }}"
             class="inline-flex h-12 items-center rounded-full px-7 font-semibold bg-orange-500 hover:bg-orange-600 shadow-lg shadow-orange-500/25 transition">
            Empezar gratis
          </a>
          <a href="{{ route('login') }}"
             class="inline-flex h-12 items-center rounded-full px-7 font-semibold border border-white/25 text-white/90 hover:border-white/40 hover:bg-white/5 transition">
            Iniciar sesión
          </a>
        </div>

        <ul class="mt-8 grid gap-2 text-white/90 drop-shadow-[0_1px_8px_rgba(0,0,0,.6)] text-sm">
          <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span> Sube tu logo y portada</li>
          <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span> Catálogo por categorías (Frenos, Iluminación, Transmisión…)</li>
          <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span> Panel para crear/editar productos al instante</li>
        </ul>
      </div>

      <!-- Vista previa simple (tarjetas) -->
      <div class="lg:pl-6">
        <div class="rounded-3xl bg-white/10 backdrop-blur-md ring-1 ring-white/15 shadow-2xl p-6">
          <div class="flex items-center justify-between">
            <div class="font-semibold text-white/90">Vista previa de tu catálogo</div>
            <span class="text-xs text-white/60">demo</span>
          </div>

          @php
            $cards = [
              ['alt' => 'Cascos y protección',   'src' => 'https://images.unsplash.com/photo-1542362567-b07e54358753?q=80&w=900&auto=format&fit=crop'],
              ['alt' => 'Llantas y rines',       'src' => 'https://images.unsplash.com/photo-1517940310602-75f39d4ac6fb?q=80&w=900&auto=format&fit=crop'],
              ['alt' => 'Frenos y discos',       'src' => 'https://images.unsplash.com/photo-1526045478516-99145907023c?q=80&w=900&auto=format&fit=crop'],
              ['alt' => 'Transmisión y cadenas', 'src' => 'https://images.unsplash.com/photo-1602320734573-1b57f5813996?q=80&w=900&auto=format&fit=crop'],
              ['alt' => 'Aceites y lubricantes', 'src' => 'https://images.unsplash.com/photo-1589578527966-1e9b2ae05a0e?q=80&w=900&auto=format&fit=crop'],
              ['alt' => 'Iluminación/eléctricos','src' => 'https://images.unsplash.com/photo-1516738901171-8eb4fc13bd20?q=80&w=900&auto=format&fit=crop'],
            ];
          @endphp

          <div class="mt-4 grid grid-cols-3 gap-3">
            @foreach ($cards as $c)
              <figure class="group relative aspect-[4/3] rounded-xl overflow-hidden ring-1 ring-white/10 bg-white/5">
                <img class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                     src="{{ $c['src'] }}" alt="{{ $c['alt'] }}">
                <figcaption class="absolute inset-x-0 bottom-0 p-2 text-[11px] font-medium text-white/95 bg-gradient-to-t from-black/60 to-transparent">
                  {{ $c['alt'] }}
                </figcaption>
              </figure>
            @endforeach
          </div>

          <div class="mt-5 flex justify-end">
            <a href="{{ route('register') }}"
               class="inline-flex h-10 items-center rounded-full px-5 text-sm font-semibold bg-orange-500 hover:bg-orange-600 shadow hover:shadow-orange-500/25 transition">
              Crear mi tienda
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CATEGORÍAS -->
  <section id="categorias" class="relative bg-[#0e0f12]">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-20 pt-10">
      <h2 class="text-xl font-semibold text-white/95">Categorías populares</h2>
      <p class="text-sm text-white/70">Organiza tu catálogo rápidamente.</p>

      <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach (['Aceites & Lubricantes','Llantas & Rines','Frenos','Transmisión & Cadenas','Accesorios & Estética','Iluminación & Eléctricos'] as $c)
          <a href="#"
             class="group rounded-2xl border border-white/10 bg-white/[.06] px-5 py-4 hover:bg-white/[.1] transition">
            <div class="flex items-center justify-between">
              <div class="text-white font-medium">{{ $c }}</div>
              <span class="text-xs text-white/70 group-hover:text-white/90">Ver productos →</span>
            </div>
          </a>
        @endforeach
      </div>
    </div>
  </section>
@endsection
