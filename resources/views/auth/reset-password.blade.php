<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nueva contraseña - ComercioPlus</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center">
  <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow">
    <h1 class="text-2xl font-bold mb-2">Restablecer contraseña</h1>
    <p class="text-sm text-gray-600 mb-6">Escribe tu nueva contraseña.</p>

    @if ($errors->any())
      <div class="mb-4">
        <ul class="list-disc list-inside text-sm text-red-600">
          @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <input type="hidden" name="email" value="{{ $email ?? old('email') }}">

      <div>
        <label class="block text-sm font-medium mb-1">Correo</label>
        <input type="email" value="{{ $email ?? old('email') }}" class="w-full p-3 rounded-lg bg-gray-100" disabled />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Nueva contraseña</label>
        <input type="password" name="password"
               class="w-full p-3 rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#ff9800]" required />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Confirmar contraseña</label>
        <input type="password" name="password_confirmation"
               class="w-full p-3 rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#ff9800]" required />
      </div>

      <button type="submit" class="w-full bg-[#ff9800] text-white font-bold p-3 rounded-lg hover:bg-[#e68a00] transition">
        Guardar nueva contraseña
      </button>
    </form>

    <div class="mt-6 text-center">
      <a href="{{ url('/login') }}" class="text-sm text-[#ff9800] hover:underline">Volver al inicio de sesión</a>
    </div>
  </div>
</body>
</html>
