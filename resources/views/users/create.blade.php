@extends('layouts.app')

@section('title', 'Crear Usuario')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-6 text-center">Crear Nuevo Usuario</h1>

    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Nombre -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Nombre</label>
            <input type="text" name="name" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>

        <!-- Email -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Email</label>
            <input type="email" name="email" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>

        <!-- Teléfono -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Teléfono</label>
            <input type="text" name="phone" class="w-full border border-gray-300 rounded px-3 py-2">
        </div>

        <!-- Dirección -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Dirección</label>
            <input type="text" name="address" class="w-full border border-gray-300 rounded px-3 py-2">
        </div>

        <!-- Rol -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Rol</label>
            <select name="role_id" class="w-full border border-gray-300 rounded px-3 py-2" required>
                <option value="">Selecciona un rol</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Estado -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Estado</label>
            <select name="status" class="w-full border border-gray-300 rounded px-3 py-2">
                <option value="1" selected>Activo</option>
                <option value="0">Inactivo</option>
            </select>
        </div>

        <!-- Avatar -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Avatar</label>
            <input type="file" name="avatar" class="w-full border border-gray-300 rounded px-3 py-2">
        </div>

        <!-- Contraseña -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Contraseña</label>
            <input type="password" name="password" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>

        <!-- Confirmación -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Confirmar Contraseña</label>
            <input type="password" name="password_confirmation" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>

        <!-- Botón -->
        <div class="text-right">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">
                Guardar Usuario
            </button>
        </div>
    </form>
</div>
@endsection
