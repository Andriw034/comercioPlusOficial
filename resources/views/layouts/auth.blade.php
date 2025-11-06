<!doctype html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','ComercioPlus — Acceso')</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-900 text-white">
  <div class="w-full max-w-md px-6 py-8">
    <div class="mb-6 flex flex-col items-center">
      <img src="{{ asset('favicon.ico') }}" alt="ComercioPlus" class="h-12 w-12 rounded">
      <h1 class="mt-3 text-2xl font-bold">ComercioPlus</h1>
      <p class="text-sm text-gray-400">@yield('subtitle', 'Accede a tu cuenta')</p>
    </div>
    <div class="card cp-card-hover border border-gray-700 rounded-2xl bg-gray-800">
      <div class="card-body p-6">@yield('content')</div>
    </div>
    @hasSection('below')
      <div class="mt-4 text-center text-sm text-gray-400">@yield('below')</div>
    @endif
  </div>
</body>
</html>
