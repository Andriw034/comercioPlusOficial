@extends('layouts.admin')

@section('title', 'Dashboard — Comerciante')
@section('header', 'Dashboard')

@section('content')
  <div class="grid gap-6 lg:grid-cols-3">
    <div class="col-span-2 rounded-3xl bg-white/10 ring-1 ring-white/15 p-6">
      <h1 class="text-2xl font-extrabold">¡Bienvenido a tu panel!</h1>
      <p class="mt-1 text-white/80">Gestiona tu catálogo, personaliza tu tienda y empieza a vender.</p>

      <div class="mt-6 grid gap-3 sm:grid-cols-3">
        <a href="{{ route('admin.products.create') }}" class="rounded-2xl bg-white/10 ring-1 ring-white/15 p-4 hover:bg-white/15 transition">
          <div class="text-sm text-white/70">Catálogo</div>
          <div class="font-semibold">Agregar producto</div>
        </a>
        <a href="{{ route('store.create') }}" class="rounded-2xl bg-white/10 ring-1 ring-white/15 p-4 hover:bg-white/15 transition">
          <div class="text-sm text-white/70">Tienda</div>
          <div class="font-semibold">Logo y portada</div>
        </a>
        <a href="{{ route('admin.categories.index') }}" class="rounded-2xl bg-white/10 ring-1 ring-white/15 p-4 hover:bg-white/15 transition">
          <div class="text-sm text-white/70">Catálogo</div>
          <div class="font-semibold">Categorías</div>
        </a>
      </div>
    </div>

    <div class="rounded-3xl bg-white/10 ring-1 ring-white/15 p-6">
      <div class="text-sm text-white/70">Estado</div>
      <div class="mt-1 text-2xl font-bold">Tienda sin publicar</div>
      <p class="mt-1 text-sm text-white/80">Configura logo, portada y datos básicos para publicar.</p>
      <a href="{{ route('store.create') }}" class="mt-4 inline-flex h-10 items-center rounded-full px-5 text-sm font-semibold bg-orange-500 text-black hover:bg-orange-600 shadow">
        Completar configuración
      </a>
    </div>
  </div>
@endsection
