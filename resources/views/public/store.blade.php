@extends('layouts.app')
@section('title', $store->name ?? 'Tienda')

@section('hero')
<header class="relative">
  @if($store && $store->cover_url)
    <div class="h-40 md:h-56 w-full bg-center bg-cover" style="background-image:url('{{ $store->cover_url }}')"></div>
  @else
    <div class="h-40 md:h-56 w-full" style="background:linear-gradient(90deg,var(--cp-primary),var(--cp-primary-2))"></div>
  @endif

  <div class="absolute inset-0 flex items-end p-4">
    <div class="flex items-center gap-3 bg-white/85 backdrop-blur px-3 py-2 rounded-xl shadow">
      @if($store && $store->logo_url)
        <img src="{{ $store->logo_url }}" class="h-10 w-10 rounded-lg object-contain" alt="Logo">
      @endif
      <div class="font-bold text-[var(--cp-ink)]">{{ $store->name }}</div>
    </div>
  </div>
</header>
@endsection

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="flex flex-col md:flex-row md:items-center gap-3 mb-6">
    <input class="input md:max-w-sm" placeholder="Buscar producto...">
    <select class="input md:w-48"><option>Categoria</option></select>
    <select class="input md:w-48"><option>Ordenar</option></select>
  </div>

  <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
    @forelse($products as $p)
      <div class="bg-white rounded-xl border border-[var(--cp-border)] overflow-hidden">
        <div class="h-36 w-full bg-gray-100"></div>
        <div class="p-3">
          <div class="font-semibold">{{ $p->name }}</div>
          <div class="text-sm text-[var(--cp-ink-2)]">${{ number_format($p->price,0) }}</div>
          <div class="mt-2 flex gap-2">
            <a href="#" class="btn btn-secondary">Ver</a>
            <a href="#" class="btn btn-primary">Agregar</a>
          </div>
        </div>
      </div>
    @empty
      <div class="col-span-full text-center text-[var(--cp-ink-2)]">No hay productos aún.</div>
    @endforelse
  </div>
</div>
@endsection
