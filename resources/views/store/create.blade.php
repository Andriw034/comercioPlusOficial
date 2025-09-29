powershell -NoProfile -Command @"
Set-Content -Path 'resources/views/stores/create.blade.php' -Encoding UTF8 -Value @'
@extends('dashboard')

@section('title', 'Crear tienda — ComercioPlus')
@section('header', 'Crear tienda')

@section('content')
  {{-- Intro breve --}}
  <div class="rounded-3xl bg-white/5 ring-1 ring-white/10 p-6 mb-6">
    <h2 class="text-xl font-extrabold">Asistente de creación de tienda</h2>
    <p class="text-white/70 mt-1">
      Completa los datos de tu perfil y tienda. Al finalizar verás el logo y la portada
      aplicados en el dashboard de productos.
    </p>
  </div>

  {{-- Ancla donde se montará el wizard con Vue --}}
  <div id="store-wizard"></div>
@endsection
'@
"@
