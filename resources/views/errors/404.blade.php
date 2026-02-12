@extends('layouts.guest')
@section('title','Página no encontrada | ComercioPlus')

@section('content')
<div class="mx-auto max-w-5xl px-6 py-24 text-center">
  <div class="mx-auto w-28 h-28 rounded-3xl grid place-content-center bg-white/5 border border-white/10">
    <div class="text-5xl font-bold text-[var(--cp-primary)]">404</div>
  </div>
  <h1 class="mt-8 text-3xl md:text-4xl font-semibold">Página no encontrada</h1>
  <p class="mt-3 opacity-80">La ruta que intentas abrir no existe o fue movida.</p>
  <div class="mt-8 flex items-center justify-center gap-3">
    <a href="{{ url('/') }}" class="px-5 py-3 rounded-xl bg-[var(--cp-primary)] hover:bg-[var(--cp-primary-2)] transition font-medium">Ir al inicio</a>
    <a href="{{ url()->previous() }}" class="px-5 py-3 rounded-xl border border-white/15 hover:bg-white/5 transition">Regresar</a>
  </div>
</div>
@endsection
