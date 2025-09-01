@extends('layouts.app')

@section('title', 'Crear cuenta')

@section('content')
<div class="max-w-md mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Crea tu cuenta</h1>

    @if ($errors->any())
        <div class="mb-4 text-red-600">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>- {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register.post') }}">
        @csrf
        <div class="mb-4">
            <label for="name" class="block font-semibold mb-1">Nombre completo</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Juan Pérez" />
        </div>
        <div class="mb-4">
            <label for="email" class="block font-semibold mb-1">Correo electrónico</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required class="w-full border border-gray-300 rounded px-3 py-2" placeholder="m@ejemplo.com" />
        </div>
        <div class="mb-4">
            <label for="password" class="block font-semibold mb-1">Contraseña</label>
            <input type="password" name="password" id="password" required class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>
        <div class="mb-4">
            <label for="password_confirmation" class="block font-semibold mb-1">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>
        <div class="mb-4">
            <label for="role" class="block font-semibold mb-1">Quiero usar la plataforma como</label>
            <select name="role" id="role" required class="w-full border border-gray-300 rounded px-3 py-2">
                <option value="Comerciante" {{ old('role') === 'Comerciante' ? 'selected' : '' }}>Comerciante (Quiero vender)</option>
                <option value="Cliente" {{ old('role') === 'Cliente' ? 'selected' : '' }}>Cliente (Quiero comprar)</option>
            </select>
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Crear cuenta</button>
    </form>

    <p class="mt-4 text-center text-sm text-gray-600">
        ¿Ya tienes una cuenta? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Iniciar sesión</a>
    </p>
</div>
@endsection
