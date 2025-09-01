@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
<div class="max-w-md mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Iniciar sesión</h1>

    @if ($errors->any())
        <div class="mb-4 text-red-600">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>- {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <div class="mb-4">
            <label for="email" class="block font-semibold mb-1">Correo electrónico</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus class="w-full border border-gray-300 rounded px-3 py-2" placeholder="m@ejemplo.com" />
        </div>
        <div class="mb-4">
            <label for="password" class="block font-semibold mb-1">Contraseña</label>
            <input type="password" name="password" id="password" required class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Iniciar sesión</button>
    </form>

    <p class="mt-4 text-center text-sm text-gray-600">
        ¿Aún no tienes cuenta? <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Crear cuenta</a>
    </p>
</div>
@endsection
