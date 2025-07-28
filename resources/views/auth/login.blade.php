<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Iniciar Sesión - ComercioPlus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="/images/portada/comercio_plus_logo.png">
    <style>
        body {
            background: linear-gradient(135deg, #fff7ed, #ffe0b2);
            position: relative;
            overflow: hidden;
        }

        .wave {
            position: absolute;
            width: 100%;
            height: 200px;
            background: linear-gradient(to right, #ffcc80, #ffb74d);
            border-radius: 100% 50%;
            transform: rotate(180deg);
            bottom: 0;
            z-index: 0;
        }

        .wave2 {
            position: absolute;
            width: 100%;
            height: 250px;
            background: linear-gradient(to right, #ffe0b2, #ffcc80);
            border-radius: 100% 50%;
            bottom: -80px;
            z-index: 0;
            opacity: 0.6;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen px-4">

    <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-5 space-y-4 z-10 relative">
        <!-- Logo -->
        <div class="flex justify-center mb-2">
            <img src="/images/portada/comercio_plus_logo.png" alt="ComercioPlus" class="h-12">
        </div>

        <!-- Título -->
        <h2 class="text-lg font-bold text-center text-gray-800">Iniciar Sesión</h2>

        <!-- Éxito o error -->
        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-2 rounded text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-2 rounded text-sm">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulario -->
        <form method="POST" action="{{ url('/login') }}" class="space-y-3">
            @csrf

            <input type="email" name="email" placeholder="Correo electrónico"
                class="w-full p-2 border rounded-md text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-400"
                required>

            <input type="password" name="password" placeholder="Contraseña"
                class="w-full p-2 border rounded-md text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-400"
                required>

            <button type="submit"
                class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 rounded-md text-sm transition">
                Iniciar sesión
            </button>

            <div class="text-center">
                <a href="{{ route('password.request') }}" class="text-sm text-orange-500 hover:underline">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>
        </form>

        <p class="text-xs text-center text-gray-600">
            ¿No tienes cuenta?
            <a href="{{ route('register') }}" class="text-orange-500 font-semibold hover:underline">Registrarse</a>
        </p>
    </div>

    <!-- Olas -->
    <div class="wave2"></div>
    <div class="wave"></div>

</body>
</html>
