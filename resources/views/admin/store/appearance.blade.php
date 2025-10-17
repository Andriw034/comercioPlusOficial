@extends('layouts.dashboard')

@section('title', 'Logo y portada — Configuración de tienda')

@section('content')
<div class="p-6">
  <div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-white">Logo y portada</h1>
    <a href="{{ route('admin.dashboard') }}" class="text-sm px-3 py-2 rounded-md bg-white/10 hover:bg-white/15 text-white">
      ← Volver al panel
    </a>
  </div>

  {{-- Alerts --}}
  @foreach (['success' => 'green', 'error' => 'red', 'info' => 'blue'] as $k => $color)
    @if(session($k))
      <div class="mb-4 rounded-lg bg-{{ $color }}-500/10 border border-{{ $color }}-400/40 p-3 text-{{ $color }}-200 text-sm">
        {{ session($k) }}
      </div>
    @endif
  @endforeach

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Logo --}}
    <div class="rounded-2xl bg-white/[.06] ring-1 ring-white/10 p-6">
      <h2 class="text-lg font-semibold text-white mb-4">Logo</h2>

      <div class="mb-4">
        <div class="aspect-[1/1] w-40 rounded-xl overflow-hidden ring-1 ring-white/10 bg-white/5 flex items-center justify-center">
          @php
            $logo = $store->logo_path ?? null;
          @endphp
          @if($logo)
            <img src="{{ asset('storage/' . $logo) }}" alt="Logo" class="object-contain w-full h-full">
          @else
            <span class="text-white/60 text-sm">Sin logo</span>
          @endif
        </div>
      </div>

      <form action="{{ route('admin.store.appearance.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
          <label class="block text-white mb-1" for="logo">Subir nuevo logo</label>
          <input id="logo" name="logo" type="file" accept="image/*"
                 class="w-full rounded-xl p-2 bg-neutral-800 text-white border border-white/20 focus:outline-none focus:ring-2 focus:ring-[#FF6000]">
          <p class="text-xs text-white/50 mt-1">PNG / JPG / WEBP. Recomendado: fondo transparente.</p>
          @error('logo') <p class="text-red-300 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="px-4 py-2 rounded-full bg-[#FF6000] text-black font-semibold hover:bg-[#ff741f] transition">
          Guardar logo
        </button>
      </form>
    </div>

    {{-- Portada / Cover --}}
    <div class="rounded-2xl bg-white/[.06] ring-1 ring-white/10 p-6">
      <h2 class="text-lg font-semibold text-white mb-4">Portada</h2>

      <div class="mb-4">
        <div class="aspect-[16/9] max-w-xl rounded-xl overflow-hidden ring-1 ring-white/10 bg-white/5">
          @php
            $cover = $store->cover_path ?? null;
          @endphp
          @if($cover)
            <img src="{{ asset('storage/' . $cover) }}" alt="Portada" class="object-cover w-full h-full">
          @else
            <div class="w-full h-full flex items-center justify-center text-white/60 text-sm">Sin portada</div>
          @endif
        </div>
      </div>

      <form action="{{ route('admin.store.appearance.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
          <label class="block text-white mb-1" for="cover">Subir nueva portada</label>
          <input id="cover" name="cover" type="file" accept="image/*"
                 class="w-full rounded-xl p-2 bg-neutral-800 text-white border border-white/20 focus:outline-none focus:ring-2 focus:ring-[#FF6000]">
          <p class="text-xs text-white/50 mt-1">PNG / JPG / WEBP. Recomendado: 1600×600 o similar.</p>
          @error('cover') <p class="text-red-300 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="px-4 py-2 rounded-full bg-[#FF6000] text-black font-semibold hover:bg-[#ff741f] transition">
          Guardar portada
        </button>
      </form>
    </div>
  </div>
</div>
@endsection
