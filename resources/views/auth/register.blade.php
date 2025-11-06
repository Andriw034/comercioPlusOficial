@extends('layouts.auth')
@section('title','Crear cuenta — ComercioPlus')
@section('subtitle','Crea tu cuenta')
@section('content')
<form method="POST" action="{{ route('register.post') }}" class="space-y-4 form-light">@csrf
  <div><label class="block text-sm font-medium text-[#111827] mb-1" for="name">Nombre</label>
    <input id="name" name="name" class="form-control w-full" required value="{{ old('name') }}">
    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
  </div>
  <div><label class="block text-sm font-medium text-[#111827] mb-1" for="email">Correo electrónico</label>
    <input id="email" name="email" type="email" class="form-control w-full" required value="{{ old('email') }}">
    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
  </div>
  <div><label class="block text-sm font-medium text-[#111827] mb-1" for="password">Contraseña</label>
    <input id="password" name="password" type="password" class="form-control w-full" required>
    @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
  </div>
  <div><label class="block text-sm font-medium text-[#111827] mb-1" for="password_confirmation">Confirmar contraseña</label>
    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control w-full" required>
  </div>
  <label class="inline-flex items-center gap-2 text-sm text-[#111827]"><input type="checkbox" name="terms" required> Acepto los términos y condiciones.</label>
  <button class="btn btn-primary w-full" type="submit">Crear cuenta</button>
</form>
@endsection
@section('below') ¿Ya tienes cuenta? <a href="{{ route('login') }}" style="color:var(--cp-primary)">Inicia sesión</a> @endsection
