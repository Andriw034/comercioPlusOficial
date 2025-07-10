@extends('layouts.admin')

@section('content')
<div class="text-2xl font-bold mb-4">Editar Perfil</div>

@if(session('success'))
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

<form action="{{ route('profile.update') }}" method="POST" class="space-y-4 bg-white p-6 rounded shadow-md max-w-lg">
    @csrf
    <div>
        <label for="name" class="block font-semibold mb-1">Nombre</label>
        <input type="text" name="name" id="name" class="w-full border border-gray-300 rounded px-3 py-2" value="{{ old('name', $user->name) }}" required>
    </div>
    <div>
        <label for="email" class="block font-semibold mb-1">Correo Electrónico</label>
        <input type="email" name="email" id="email" class="w-full border border-gray-300 rounded px-3 py-2" value="{{ old('email', $user->email) }}" required>
    </div>
    <div>
        <label for="password" class="block font-semibold mb-1">Nueva Contraseña (opcional)</label>
        <input type="password" name="password" id="password" class="w-full border border-gray-300 rounded px-3 py-2">
    </div>
    <div>
        <label for="password_confirmation" class="block font-semibold mb-1">Confirmar Nueva Contraseña</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="w-full border border-gray-300 rounded px-3 py-2">
    </div>
    <button type="submit" class="bg-[#ff9800] text-white px-4 py-2 rounded hover:bg-[#e68a00]">Actualizar Perfil</button>
</form>
@endsection
