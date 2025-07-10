<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ComercioPlus</title>
    @vite('resources/js/app.js')
</head>
<body class="antialiased">
    <div id="app"></div>

    @if (session('store_branding'))
      <script>
        window.__STORE_BRANDING__ = @json(session('store_branding'));
      </script>
    @endif
</body>
</html>
