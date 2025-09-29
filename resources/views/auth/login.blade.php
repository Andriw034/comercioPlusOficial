@extends('layouts.marketing')

@section('title', 'Iniciar sesión — ComercioPlus')

@section('content')
<div class="min-h-screen bg-gray-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full space-y-8">
    <div>
      <h2 class="mt-6 text-center text-3xl font-bold text-orange-500">Bienvenido ComercioPlus</h2>
      <p class="mt-2 text-center text-sm text-gray-300">Ingresa a tu cuenta</p>
    </div>
    <form class="mt-8 space-y-6 bg-gray-800 rounded-lg p-6" method="POST" action="{{ route('login') }}">
      @csrf

      <!-- Email -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-300">Correo electrónico</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required
               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-600 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 bg-white sm:text-sm">
        @error('email')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <!-- Password -->
      <div>
        <label for="password" class="block text-sm font-medium text-gray-300">Contraseña</label>
        <input id="password" name="password" type="password" required
               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-600 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 bg-white sm:text-sm">
        @error('password')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <!-- Remember me -->
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
          <label for="remember" class="ml-2 block text-sm text-gray-300">Recuérdame</label>
        </div>

        @if (Route::has('password.request'))
          <div class="text-sm">
            <a href="{{ route('password.request') }}" class="font-medium text-orange-500 hover:text-orange-400">¿Olvidaste tu contraseña?</a>
          </div>
        @endif
      </div>

      <div>
        <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-orange-500 hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
          Iniciar sesión
        </button>
      </div>

      <div class="text-center">
        <p class="text-sm text-gray-300">
          ¿No tienes cuenta?
          <a href="{{ route('register') }}" class="font-medium text-orange-500 hover:text-orange-400 ml-1">Crea tu cuenta</a>
        </p>
      </div>
    </form>
  </div>
</div>
@endsection
