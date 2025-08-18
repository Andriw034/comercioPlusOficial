<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registro - ComercioPlus</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-bg-50 text-textc-800">
<div class="flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl p-8 space-y-6">
        <div class="flex items-center gap-2 mb-4">
            <div class="h-10 w-10 rounded-xl" style="background:linear-gradient(135deg,#FFC1A3,#FF9F75);"></div>
            <span class="font-semibold text-lg text-textc-900">ComercioPlus</span>
        </div>
        <h1 class="text-2xl font-bold text-textc-900">Crear cuenta</h1>

        @if($errors->any())
            <div class="bg-state-danger/20 text-state-danger px-4 py-2 rounded-xl text-sm">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm text-textc-700" for="name">Nombre</label>
                <input id="name" name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-xl border border-gray-200 bg-bg-100 px-4 py-2 focus:border-brand-300 focus:ring-brand-300" />
            </div>
            <div>
                <label class="text-sm text-textc-700" for="email">Correo electrónico</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-xl border border-gray-200 bg-bg-100 px-4 py-2 focus:border-brand-300 focus:ring-brand-300" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-textc-700" for="password">Contraseña</label>
                    <input id="password" name="password" type="password" required class="mt-1 w-full rounded-xl border border-gray-200 bg-bg-100 px-4 py-2 focus:border-brand-300 focus:ring-brand-300" />
                </div>
                <div>
                    <label class="text-sm text-textc-700" for="password_confirmation">Confirmar</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-1 w-full rounded-xl border border-gray-200 bg-bg-100 px-4 py-2 focus:border-brand-300 focus:ring-brand-300" />
                </div>
            </div>
            <div>
                <span class="text-sm text-textc-700">Tipo de cuenta</span>
                <div class="mt-2 space-y-3">
                    <label class="flex items-start gap-3 p-4 border border-gray-200 rounded-xl cursor-pointer hover:bg-bg-100">
                        <input type="radio" name="role" value="comerciante" class="mt-1 h-4 w-4 text-brand-300 border-gray-300 focus:ring-brand-300" {{ old('role')=='comerciante' ? 'checked' : '' }}>
                        <span>
                            <span class="block text-sm font-medium text-textc-900">Comerciante</span>
                            <span class="block text-sm text-textc-700">Vende y gestiona tu propia tienda</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-4 border border-gray-200 rounded-xl cursor-pointer hover:bg-bg-100">
                        <input type="radio" name="role" value="cliente" class="mt-1 h-4 w-4 text-brand-300 border-gray-300 focus:ring-brand-300" {{ old('role')=='cliente' ? 'checked' : '' }}>
                        <span>
                            <span class="block text-sm font-medium text-textc-900">Cliente</span>
                            <span class="block text-sm text-textc-700">Compra productos de tus tiendas favoritas</span>
                        </span>
                    </label>
                </div>
            </div>
            <button type="submit" class="w-full bg-brand-300 text-white rounded-xl py-3 font-medium hover:opacity-90 transition">Crear cuenta</button>
        </form>
        <p class="text-center text-sm text-textc-700">¿Ya tienes cuenta? <a href="{{ route('login') }}" class="text-brand-300 hover:underline">Inicia sesión</a></p>
    </div>
</div>
</body>
</html>
