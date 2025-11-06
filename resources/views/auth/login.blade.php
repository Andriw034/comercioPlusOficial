@extends('layouts.auth')
@section('title','Iniciar sesión — ComercioPlus')
@section('subtitle','Bienvenido de nuevo')

@section('content')
<form method="POST" action="{{ route('login') }}" class="space-y-5 form-light">
  @csrf

  <div>
    <label class="block text-sm font-medium text-white mb-1" for="email">Correo electrónico</label>
    <input id="email" name="email" type="email" class="form-control w-full" value="{{ old('email') }}" required autofocus>
    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
  </div>

  <div>
    <div class="flex items-center justify-between">
      <label class="block text-sm font-medium text-white mb-1" for="password">Contraseña</label>
      <a href="{{ route('password.request') }}" class="text-sm" style="color:var(--cp-primary)">¿Olvidaste tu contraseña?</a>
    </div>
    <input id="password" name="password" type="password" class="form-control w-full" required>
    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
  </div>

  <label class="inline-flex items-center gap-2 text-sm text-white">
    <input type="checkbox" name="remember" class="form-checkbox"> Recuérdame
  </label>

  <button class="btn btn-primary w-full" type="submit">Iniciar sesión</button>
</form>
@endsection

@section('below')
¿No tienes cuenta? <a href="{{ route('register') }}" style="color:var(--cp-primary)">Regístrate</a>
@endsection