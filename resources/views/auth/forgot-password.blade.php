<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Olvidé mi contraseña - ComercioPlus</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center">
  <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow">
    <h1 class="text-2xl font-bold mb-2">¿Olvidaste tu contraseña?</h1>
    <p class="text-sm text-gray-600 mb-6">Ingresa tu correo y te enviaremos un enlace.</p>

    @if (session('status'))
      <div class="mb-4 text-sm text-green-600 font-medium">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
      <div class="mb-4">
        <ul class="list-disc list-inside text-sm text-red-600">
          @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
      @csrf
      <input type="email" name="email" value="{{ old('email') }}" placeholder="Correo electrónico"
             class="w-full p-3 rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#ff9800]" required autofocus />
      <button type="submit" class="w-full bg-[#ff9800] text-white font-bold p-3 rounded-lg hover:bg-[#e68a00] transition">
        Enviar enlace
      </button>
    </form>

    <div class="mt-6 text-center">
      <a href="{{ url('/login') }}" class="text-sm text-[#ff9800] hover:underline">Volver al inicio de sesión</a>
    </div>
  </div>
</body>
</html>
