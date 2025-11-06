<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','ComercioPlus')</title>
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  @vite(['resources/css/app.css','resources/js/app.js'])
  <style>
    :root{ --cp-primary:#FF6000; --cp-primary-2:#FF8A3D; --cp-text:#111827; --cp-muted:#6B7280; --cp-bg:#F9FAFB; }
    .cp-hero-grad{ background: radial-gradient(1200px 500px at 50% -100px, rgba(255,138,61,.12), transparent 60%); }
  </style>
</head>
<body class="bg-[var(--cp-bg)] text-[var(--cp-text)]">
  @include('partials.public-navbar')
  <main class="cp-hero-grad">
    @yield('content')
  </main>
  @include('partials.public-footer')
</body>
</html>
