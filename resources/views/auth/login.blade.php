<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>{{ __('messages.login') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-orange-100 to-orange-200 p-4">

    <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-sm flex flex-col space-y-6">
        <h1 class="text-3xl font-extrabold text-gray-900 flex flex-col items-center space-y-2">
            <span>Comercio</span>
            <span class="flex items-center space-x-2">
                <span>Plus</span>
                <span class="bg-[#ff9800] text-white rounded-md px-2 font-bold text-xl">+</span>
            </span>
        </h1>

        @if (session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ url('/login') }}" class="flex flex-col space-y-4">
            @csrf

            <input type="email" name="email" placeholder="Correo electrónico"
                class="p-3 rounded-lg bg-[#f9f1e7] placeholder-gray-700 focus:outline-none focus:ring-2 focus:ring-[#ff9800]"
                required>

            <input type="password" name="password" placeholder="Contraseña"
                class="p-3 rounded-lg bg-[#f9f1e7] placeholder-gray-700 focus:outline-none focus:ring-2 focus:ring-[#ff9800]"
                required>

            <button type="submit"
                class="bg-[#ff9800] text-white font-bold p-3 rounded-lg hover:bg-[#e68a00] transition">
                Iniciar sesión
            </button>
            <div class="mt-4 text-center">
                <a href="{{ route('password.request') }}" class="text-sm text-[#ff9800] hover:underline">¿Olvidaste tu contraseña?</a>
            </div>
        </form>
    </div>

</body>

</html>
