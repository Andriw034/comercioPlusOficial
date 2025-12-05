<!DOCTYPE html>
<html lang="es" class="h-full bg-black">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Configuración · Mi perfil</title>
    {{-- Cargamos tus assets de Vite (Tailwind + JS) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full text-white">

    {{-- NAV / Encabezado mínimo (puedes quitarlo si ya tienes navbar global) --}}
    <header class="border-b border-white/10 bg-black/40 backdrop-blur-md">
        <div class="mx-auto max-w-7xl h-16 px-6 flex items-center justify-between">
            <a href="{{ route('home') }}" class="font-semibold tracking-wide">
                Comercio<span class="text-orange-500">Plus</span>
            </a>
            <nav class="hidden md:flex items-center gap-6 text-sm">
                <a href="{{ route('dashboard') }}" class="hover:text-orange-400">Dashboard</a>
                <a href="{{ route('products.index') }}" class="hover:text-orange-400">Productos</a>
                <a href="{{ route('stores.index') }}" class="hover:text-orange-400">Tiendas</a>
            </nav>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-8">
        {{-- Título --}}
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white">
                Configuración · <span class="text-orange-400">Mi perfil</span>
            </h1>
            <p class="text-white/60 text-sm mt-1">
                Actualiza tu información personal y tu foto de perfil.
            </p>
        </div>

        {{-- Alertas --}}
        @if (session('success'))
            <div class="mb-4 rounded-xl bg-green-500/15 text-green-300 ring-1 ring-green-500/30 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-xl bg-red-500/15 text-red-300 ring-1 ring-red-500/30 px-4 py-3 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Formulario --}}
        <form method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data"
              class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @csrf
            @method('PUT')

            {{-- Card Avatar --}}
            <div class="rounded-3xl bg-white/5 ring-1 ring-white/10 p-6">
                <div class="flex items-center gap-4">
                    <img
                        src="{{ ($user->avatar_path ?? null) ? asset('storage/'.$user->avatar_path) : 'https://ui-avatars.com/api/?name='.urlencode($user->name ?? 'U').'&background=FF6000&color=ffffff' }}"
                        class="h-20 w-20 rounded-2xl object-cover shadow"
                        alt="Avatar">
                    <div>
                        <p class="text-white font-semibold">Foto de perfil</p>
                        <p class="text-xs text-white/60">PNG/JPG/WEBP hasta 2MB</p>
                    </div>
                </div>

                <label class="mt-5 block">
                    <span class="text-white/80 text-sm">Subir nueva imagen</span>
                    <input type="file" name="avatar" accept="image/*"
                           class="mt-2 block w-full text-white/80 file:mr-4 file:rounded-xl file:border-0 file:bg-orange-500/90 file:px-4 file:py-2 file:text-white hover:file:bg-orange-500 cursor-pointer">
                </label>
            </div>

            {{-- Card Datos --}}
            <div class="lg:col-span-2 rounded-3xl bg-white/5 ring-1 ring-white/10 p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm text-white/80 mb-1">Nombre</label>
                        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}"
                               class="w-full rounded-2xl bg-black/40 text-white border border-white/10 focus:border-orange-500 focus:ring-orange-500 px-4 py-2.5"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm text-white/80 mb-1">Correo electrónico</label>
                        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}"
                               class="w-full rounded-2xl bg-black/40 text-white border border-white/10 focus:border-orange-500 focus:ring-orange-500 px-4 py-2.5"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm text-white/80 mb-1">Teléfono</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}"
                               class="w-full rounded-2xl bg-black/40 text-white border border-white/10 focus:border-orange-500 focus:ring-orange-500 px-4 py-2.5">
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <a href="{{ url()->previous() }}"
                       class="px-4 py-2 rounded-2xl border border-white/10 text-white/80 hover:bg-white/5">Cancelar</a>
                    <button type="submit"
                            class="px-5 py-2.5 rounded-2xl bg-orange-500 text-white font-semibold shadow hover:bg-orange-600">
                        Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </main>

    {{-- FOOTER mínimo --}}
    <footer class="mt-10 border-t border-white/10">
        <div class="max-w-7xl mx-auto px-6 py-8 text-sm text-white/70">
            © {{ date('Y') }} ComercioPlus
        </div>
    </footer>

</body>
</html>
