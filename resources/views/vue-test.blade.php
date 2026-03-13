@extends('layouts.marketing')

@section('title', 'Catálogo — Demo Vue')

@section('content')
  <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
    <header class="mb-6">
      <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
        Catálogo (demo Vue)
      </h1>
      <p class="text-white/80 mt-1">Ejemplo de un widget Vue montado dentro de Blade.</p>
    </header>

    <!-- Aquí se monta Vue -->
    <div id="vue-catalog"></div>
  </section>
@endsection
