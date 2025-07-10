<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Recuperar contraseña</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-orange-100 to-orange-200 p-4">

    <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-md flex flex-col space-y-6">
        <h1 class="text-3xl font-extrabold text-gray-900 text-center mb-4">Recuperar contraseña</h1>

        @if (session('status'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col space-y-4">
            @csrf

            <input type="email" name="email" placeholder="Correo electrónico"
                class="p-3 rounded-lg bg-[#f9f1e7] placeholder-gray-700 focus:outline-none focus:ring-2 focus:ring-[#ff9800]"
                required autofocus>

            @error('email')
                <div class="text-red-600 text-sm">{{ $message }}</div>
            @enderror

            <button type="submit"
                class="bg-[#ff9800] text-white font-bold p-3 rounded-lg hover:bg-[#e68a00] transition">
                Enviar enlace de recuperación
            </button>
        </form>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-sm text-[#ff9800] hover:underline">Volver al inicio de sesión</a>
        </div>
    </div>

</body>

</html>
