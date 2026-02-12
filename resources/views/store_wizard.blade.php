{{-- resources/views/store_wizard.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear Perfil & Tienda — ComercioPlus</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-[#0b0b0c] to-[#111827] text-white">
  <div class="max-w-6xl mx-auto px-4 py-8">
    <header class="mb-6">
      <h1 class="text-2xl font-extrabold">ComercioPlus</h1>
      <p class="text-white/70">Crear Perfil & Tienda</p>
    </header>

    {{-- Aquí Vue montará el asistente (perfil+tienda) y el dashboard --}}
    <div id="store-wizard"></div>
  </div>
</body>
</html>
