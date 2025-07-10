<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registro</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex items-center justify-center relative" style="background-image: url('/images/portada/imag1.moto.register_p.m..png'); background-size: cover; background-position: center; background-repeat: no-repeat;">

    <!-- Filtro oscuro para mejorar el contraste -->
    <div class="absolute inset-0 bg-black opacity-40"></div>

    <!-- Contenedor del formulario -->
    <div class="bg-white rounded-2xl shadow-lg p-8 w-80 flex flex-col space-y-6 relative z-10">

        <!-- Logo editable -->
        <div class="flex justify-center">
            <img src="/images/portada/comercio_plus_logo.png" alt="Comercio Plus Logo" class="h-16 w-auto" />
        </div>

        <!-- Título -->
        <h2 class="text-2xl font-bold text-gray-900 text-center">Registro</h2>

        <!-- Formulario -->
        <form method="POST" action="{{ url('/register') }}" class="flex flex-col space-y-4">
            @csrf

            <input type="text" name="name" placeholder="Nombre"
                class="p-3 rounded-lg bg-white border border-gray-300 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#ff9800]"
                required>

            <input type="email" name="email" placeholder="Correo electrónico"
                class="p-3 rounded-lg bg-white border border-gray-300 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#ff9800]"
                required>

            <input type="password" name="password" placeholder="Contraseña"
                class="p-3 rounded-lg bg-white border border-gray-300 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#ff9800]"
                required>

            <input type="password" name="password_confirmation" placeholder="Confirmar contraseña"
                class="p-3 rounded-lg bg-white border border-gray-300 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#ff9800]"
                required>

            <button type="submit"
                class="bg-[#ff9800] text-white font-bold p-3 rounded-lg hover:bg-[#e68a00] transition">
                Registrarse
            </button>
        </form>

        <!-- Enlace para iniciar sesión -->
        <p class="text-sm text-gray-700 text-center">
            ¿Ya tienes una cuenta?:
            <a href="{{ route('login') }}" class="text-[#ff9800] hover:underline">Iniciar sesión</a>
        </p>
    </div>

</body>

</html>
