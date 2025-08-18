<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ComercioPlus — Bienvenido</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-bg-50 text-textc-800 antialiased">
  <!-- Contenedor principal a dos columnas -->
  <div class="md:grid md:grid-cols-2 min-h-screen">
    <!-- Columna izquierda: tarjeta de inicio de sesión -->
    <div class="flex items-center justify-center bg-white">
      <div class="w-full max-w-sm p-8 space-y-6">
        <!-- Logo y nombre -->
        <div class="flex items-center gap-2 mb-6">
          <div class="h-10 w-10 rounded-xl" style="background:linear-gradient(135deg,#FFC1A3,#FF9F75);"></div>
          <span class="font-semibold text-lg text-textc-900">ComercioPlus</span>
        </div>

        <!-- Formulario de login -->
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
          @csrf
          <div>
            <label class="text-sm text-textc-700">Usuario o correo</label>
            <input
              type="text"
              name="email"
              required
              class="mt-1 w-full rounded-xl border border-gray-200 bg-bg-100 px-4 py-2 focus:border-brand-300 focus:ring-brand-300"
            />
          </div>

          <div>
            <label class="text-sm text-textc-700">Contraseña</label>
            <input
              type="password"
              name="password"
              required
              class="mt-1 w-full rounded-xl border border-gray-200 bg-bg-100 px-4 py-2 focus:border-brand-300 focus:ring-brand-300"
            />
          </div>

          <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2">
              <input type="checkbox" name="remember" class="rounded border-gray-300 text-brand-300 focus:ring-brand-300" />
              Recuérdame
            </label>
            <a href="{{ route('password.request') }}" class="text-brand-300 hover:underline">¿Olvidaste tu contraseña?</a>
          </div>

          <button type="submit" class="w-full bg-brand-300 text-white rounded-xl py-3 font-medium hover:opacity-90 transition">
            Iniciar sesión
          </button>

          <p class="text-center text-sm text-textc-700">
            ¿Aún no tienes cuenta?
            <a href="{{ route('register') }}" class="text-brand-300 hover:underline">Crear cuenta</a>
          </p>
        </form>
      </div>
    </div>

    <!-- Columna derecha: mensaje de bienvenida -->
    <div class="relative flex items-center justify-center p-8 text-white bg-gradient-to-br from-brand-200 via-brand-300 to-brand-400">
      <div class="max-w-md text-center space-y-6">
        <h1 class="text-3xl md:text-4xl font-bold">
          Empieza gratis y comparte tu catálogo hoy.
        </h1>

        <div class="flex flex-col sm:flex-row sm:justify-center gap-4">
          <a href="{{ route('register') }}" class="px-6 py-3 bg-white text-brand-300 rounded-xl font-medium shadow hover:opacity-90 transition">
            Crear tienda
          </a>
          <a href="{{ route('public.store.show', ['slug' => 'demo']) }}" class="px-6 py-3 rounded-xl border border-white/80 hover:bg-white/10 transition">
            Ver demo
          </a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

