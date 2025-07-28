<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registro - ComercioPlus</title>
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

    <!-- Formulario -->
    <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-5 space-y-4 z-10 relative">
        <!-- Logo -->
        <div class="flex justify-center mb-1">
            <img src="/images/portada/comercio_plus_logo.png" alt="ComercioPlus" class="h-12">
        </div>

        <!-- Título -->
        <h2 class="text-lg font-bold text-center text-gray-800">Crear cuenta</h2>

        <!-- Formulario -->
        <form method="POST" action="{{ route('register.submit') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf

            <input type="text" name="name" placeholder="Nombre completo"
                class="w-full p-2 border rounded-md text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-400" required>

            <input type="email" name="email" placeholder="Correo electrónico"
                class="w-full p-2 border rounded-md text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-400" required>

            <select name="role_id" required
                class="w-full p-2 border rounded-md text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-orange-400">
                <option value="" disabled selected>Seleccione un rol</option>
                <option value="1">Administrador comerciante</option>
                <option value="2">Cliente comprador</option>
            </select>

            <input type="password" name="password" placeholder="Contraseña"
                class="w-full p-2 border rounded-md text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-400" required>

            <input type="password" name="password_confirmation" placeholder="Confirmar contraseña"
                class="w-full p-2 border rounded-md text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-400" required>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Foto de perfil (opcional)</label>
                <input type="file" name="avatar"
                    class="block w-full text-sm text-gray-600 file:mr-3 file:py-1 file:px-3
                    file:rounded-md file:border-0 file:text-sm file:font-medium
                    file:bg-orange-500 file:text-white hover:file:bg-orange-600" />
            </div>

            <button type="submit"
                class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 rounded-md text-sm transition">
                Registrarse
            </button>
        </form>

        <p class="text-xs text-center text-gray-600">
            ¿Ya tienes una cuenta?
            <a href="{{ route('login') }}" class="text-orange-500 font-semibold hover:underline">Iniciar sesión</a>
        </p>
    </div>

    <!-- Olas de fondo -->
    <div class="wave2"></div>
    <div class="wave"></div>

</body>
</html>
