<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Comercio Plus')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="bg-white shadow p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="{{ url('/') }}" class="text-xl font-bold">Comercio Plus</a>
            <nav>
                @auth
                    <span class="mr-4">Hola, {{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800">Cerrar sesión</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="mr-4 hover:underline">Entrar</a>
                    <a href="{{ route('register') }}" class="hover:underline">Crear cuenta</a>
                @endauth
            </nav>
        </div>
    </header>
    <main class="flex-grow container mx-auto p-4">
        @yield('content')
    </main>
    <footer class="bg-white shadow p-4 text-center text-sm text-gray-500">
        &copy; {{ date('Y') }} Comercio Plus. Todos los derechos reservados.
    </footer>
</body>
</html>
