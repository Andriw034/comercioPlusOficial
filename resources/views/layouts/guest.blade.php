<!DOCTYPE html>
<html lang="es" class="h-full">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'ComercioPlus' }}</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

    <style>
      .cp-auth-bg {
        background-image:
          radial-gradient(40rem 30rem at 10% -10%, rgba(255,160,122,.25), transparent 60%),
          radial-gradient(35rem 25rem at 90% -10%, rgba(255,165,0,.18), transparent 60%),
          linear-gradient(180deg, #fff, #fff);
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
      }
      @media (max-width: 640px){
        .cp-auth-bg { background-position: center top; }
      }
    </style>
  </head>

  <body class="cp-auth-bg font-sans antialiased text-white">
    <div class="min-h-screen flex flex-col items-center pt-8 sm:pt-10">

      <!-- Encabezado limpio sin logo Laravel -->
      <h1 class="text-3xl font-extrabold text-orange-500 tracking-tight">
        Comercio<span class="text-gray-900">Plus</span>
      </h1>

      <!-- Card / contenedor del formulario -->
      <div class="w-full sm:max-w-sm mt-8 p-6 sm:p-7 rounded-2xl 
                  bg-white/10 backdrop-blur-md ring-1 ring-white/15 shadow-2xl">
        {{-- Soporta uso como componente (slot) y como layout extendido (yield) --}}
        @isset($slot)
          {{ $slot }}
        @else
          @yield('content')
        @endisset
      </div>

      <!-- Pie -->
      <div class="mt-6 text-xs text-gray-700">
        © {{ date('Y') }} ComercioPlus. Catálogos para repuestos y accesorios de moto.
      </div>
    </div>
  </body>
</html>
