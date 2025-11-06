@extends('layouts.public')

@section('title', 'ComercioPlus — Tu vitrina de repuestos y accesorios')

@section('content')
<section
  class="relative min-h-[90vh] flex items-center justify-center text-white overflow-hidden isolate">
  {{-- Fondo animado --}}
  <div class="absolute inset-0 -z-10 animate-cpGradient bg-[radial-gradient(1200px_600px_at_10%_-20%,rgba(255,107,0,.18),transparent_60%),radial-gradient(1000px_500px_at_90%_10%,rgba(255,138,61,.14),transparent_55%),linear-gradient(120deg,#0d0d0d_0%,#1a1a1a_50%,#262626_100%)]"></div>

  <div class="relative z-10 w-full max-w-7xl px-6 py-16 grid grid-cols-1 lg:grid-cols-2 gap-14 items-center">
    {{-- Columna izquierda --}}
    <div class="order-2 lg:order-1">
      <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight">
        Tu vitrina de <span class="text-orange-500">repuestos y accesorios</span><br class="hidden sm:block"> en minutos
      </h1>

      <p class="mt-4 text-base sm:text-lg text-gray-300 max-w-xl">
        Crea tu <span class="text-white font-semibold">tienda</span>, carga productos, organiza por categorías y comparte tu catálogo profesional.
      </p>

      <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('register') }}"
           class="inline-flex items-center justify-center px-6 py-3 rounded-full bg-orange-500 text-white font-semibold shadow-md hover:bg-orange-600 active:bg-orange-700 transition">
          Empezar gratis
        </a>
        <a href="{{ route('login') }}"
           class="inline-flex items-center justify-center px-6 py-3 rounded-full border border-white/25 text-gray-100 hover:bg-white/5 transition">
          Iniciar sesión
        </a>
      </div>

      <ul class="mt-6 space-y-1 text-sm text-gray-400">
        <li>• Sube tu logo y portada</li>
        <li>• Catálogo por categorías (Frenos, Iluminación, Transmisión...)</li>
        <li>• Panel para crear y editar productos al instante</li>
      </ul>
    </div>

    {{-- Columna derecha (preview) --}}
    <div class="order-1 lg:order-2">
      <div class="relative rounded-3xl bg-white/5 backdrop-blur border border-white/10 p-6 sm:p-8 shadow-[0_20px_70px_-30px_rgba(0,0,0,0.7)]">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
          <h3 class="text-gray-100 font-semibold">Vista previa de tu catálogo</h3>
          <span class="text-xs text-gray-400">demo</span>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:gap-4">
          @php
            $cards = [
              ['img'=>'https://images.unsplash.com/photo-1503736334956-4c8f8e92946d?w=640','t'=>'Cascos y protección'],
              ['img'=>'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=640','t'=>'Llantas y rines'],
              ['img'=>'https://images.unsplash.com/photo-1558981403-c5f9891fa1a6?w=640','t'=>'Transmisión y cadenas'],
              ['img'=>'https://images.unsplash.com/photo-1610915526186-6aa5cb18228e?w=640','t'=>'Aceites y lubricantes'],
            ];
          @endphp
          @foreach($cards as $c)
          <div class="relative rounded-xl overflow-hidden bg-white/5 border border-white/10 shadow-inner">
            <img src="{{ $c['img'] }}"
                 alt="{{ $c['t'] }}"
                 loading="lazy"
                 class="object-cover w-full h-28 sm:h-32 opacity-95">
            <p class="absolute inset-x-0 bottom-0 text-[13px] sm:text-sm font-semibold bg-black/55 text-white py-1.5 px-2">
              {{ $c['t'] }}
            </p>
          </div>
          @endforeach
        </div>

        <div class="mt-5 sm:mt-6 text-center">
          <a href="{{ route('store.create') }}"
             class="inline-flex items-center justify-center px-5 py-2.5 rounded-full bg-orange-500 text-white font-semibold hover:bg-orange-600 transition">
            Crear mi tienda
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Features --}}
<section class="bg-[#0f0f0f] text-gray-200 py-16 sm:py-20">
  <div class="mx-auto max-w-6xl px-6 text-center mb-10">
    <h2 class="text-2xl sm:text-3xl font-bold">¿Por qué usar ComercioPlus?</h2>
    <p class="text-gray-400 mt-2">Herramientas integradas y diseño profesional para tu negocio.</p>
  </div>

  <div class="mx-auto max-w-6xl px-6 grid grid-cols-1 sm:grid-cols-3 gap-5 sm:gap-6">
    <article class="rounded-2xl bg-[#161616] border border-white/10 p-6 sm:p-8 shadow-lg hover:shadow-orange-500/10 transition">
      <img src="{{ asset('images/icons/cart.svg') }}" alt="" class="w-9 h-9 mb-4">
      <h3 class="text-lg sm:text-xl font-semibold mb-2">Gestión de Productos</h3>
      <p class="text-gray-400 text-sm">Administra catálogo con fotos, stock, precios y categorías.</p>
    </article>
    <article class="rounded-2xl bg-[#161616] border border-white/10 p-6 sm:p-8 shadow-lg hover:shadow-orange-500/10 transition">
      <img src="{{ asset('images/icons/chart.svg') }}" alt="" class="w-9 h-9 mb-4">
      <h3 class="text-lg sm:text-xl font-semibold mb-2">Estadísticas</h3>
      <p class="text-gray-400 text-sm">Monitorea ventas, ingresos y productos más vendidos.</p>
    </article>
    <article class="rounded-2xl bg-[#161616] border border-white/10 p-6 sm:p-8 shadow-lg hover:shadow-orange-500/10 transition">
      <img src="{{ asset('images/icons/paint.svg') }}" alt="" class="w-9 h-9 mb-4">
      <h3 class="text-lg sm:text-xl font-semibold mb-2">Personalización</h3>
      <p class="text-gray-400 text-sm">Configura marca, colores, portada y dominio de tu tienda.</p>
    </article>
  </div>
</section>
@endsection
