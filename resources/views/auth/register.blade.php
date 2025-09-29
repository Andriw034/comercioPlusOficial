@extends('layouts.marketing')

@section('title', 'Crear cuenta — ComercioPlus')

@section('content')
<div class="min-h-screen bg-gray-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full space-y-8">
    <div>
      <h2 class="mt-6 text-center text-3xl font-bold text-orange-500">Crear cuenta ComercioPlus</h2>
      <p class="mt-2 text-center text-sm text-gray-300">Regístrate para comenzar</p>
    </div>
    <form class="mt-8 space-y-6 bg-gray-800 rounded-lg p-6" method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
      @csrf

      <!-- Name -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-300">Nombre completo</label>
        <input id="name" name="name" type="text" value="{{ old('name') }}" required
               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-600 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 bg-white sm:text-sm">
        @error('name')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

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
        <input id="password" name="password" type="password" required minlength="8"
               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-600 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 bg-white sm:text-sm">
        @error('password')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <!-- Password Confirmation -->
      <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-300">Confirmar contraseña</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required
               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-600 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 bg-white sm:text-sm">
      </div>

      <!-- Role -->
      <div>
        <label for="role" class="block text-sm font-medium text-gray-300">Tipo de cuenta</label>
        <select id="role" name="role" required
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-600 rounded-md shadow-sm placeholder-gray-500 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
          <option value="cliente" {{ old('role') == 'cliente' ? 'selected' : '' }}>Cliente comprador</option>
          <option value="comerciante" {{ old('role') == 'comerciante' ? 'selected' : '' }}>Comerciante</option>
        </select>
        @error('role')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <!-- Profile Photo -->
      <div>
        <label for="profile_photo" class="block text-sm font-medium text-gray-300">Foto de perfil (opcional)</label>
        <input id="profile_photo" name="profile_photo" type="file" accept="image/*"
               class="mt-1 block w-full text-sm text-gray-900 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
        @error('profile_photo')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-orange-500 hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
          Crear cuenta
        </button>
      </div>

      <div class="text-center">
        <p class="text-sm text-gray-300">
          ¿Ya tienes cuenta?
          <a href="{{ route('login') }}" class="font-medium text-orange-500 hover:text-orange-400 ml-1">Inicia sesión</a>
        </p>
      </div>
    </form>
  </div>
</div>
@endsection
