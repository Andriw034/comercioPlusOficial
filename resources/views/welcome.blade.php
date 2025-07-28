<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a ComercioPlus</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-orange-100 to-orange-300 flex items-center justify-center relative">

    <!-- Fondo decorativo opcional -->
    <div class="absolute inset-0 bg-[url('/images/portada/fondo_inicio.jpg')] bg-cover bg-center opacity-20"></div>

    <!-- Contenedor principal -->
    <div class="relative z-10 bg-white bg-opacity-90 backdrop-blur-md rounded-xl p-8 shadow-lg max-w-xl w-[90%] text-center space-y-6">
        <img src="/images/portada/comercio_plus_logo.png" alt="ComercioPlus Logo" class="mx-auto h-20">
        <h1 class="text-3xl font-bold text-orange-600">¡Bienvenido a ComercioPlus!</h1>
        <p class="text-gray-700 text-lg">Elige tu rol para continuar</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Comerciante -->
            <a href="{{ route('register') }}" class="bg-orange-500 text-white py-3 px-4 rounded-lg shadow hover:bg-orange-600 transition">
                Soy Comerciante
            </a>

            <!-- Cliente -->
            <a href="{{ route('register') }}" class="bg-orange-300 text-white py-3 px-4 rounded-lg shadow hover:bg-orange-400 transition">
                Soy Comprador
            </a>
        </div>

        <p class="text-sm text-gray-600 mt-4">
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" class="text-orange-600 hover:underline font-semibold">Iniciar sesión</a>
        </p>
    </div>

</body>
</html>
