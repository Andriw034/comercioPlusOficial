@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-6 text-center">Editar Usuario</h1>

    <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Nombre -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Nombre</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>

        <!-- Email -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>

        <!-- Teléfono -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Teléfono</label>
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full border border-gray-300 rounded px-3 py-2">
        </div>

        <!-- Dirección -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Dirección</label>
            <input type="text" name="address" value="{{ old('address', $user->address) }}" class="w-full border border-gray-300 rounded px-3 py-2">
        </div>

        <!-- Rol -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Rol</label>
            <select name="role_id" class="w-full border border-gray-300 rounded px-3 py-2" required>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Estado -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Estado</label>
            <select name="status" class="w-full border border-gray-300 rounded px-3 py-2">
                <option value="1" {{ $user->status ? 'selected' : '' }}>Activo</option>
                <option value="0" {{ !$user->status ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>

        <!-- Avatar -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Avatar</label>
            @if ($user->avatar)
                <div class="mb-2">
                    <img src="{{ asset('storage/avatars/' . $user->avatar) }}" class="w-16 h-16 rounded-full shadow">
                </div>
            @endif
            <input type="file" name="avatar" class="w-full border border-gray-300 rounded px-3 py-2">
        </div>

        <!-- Contraseña -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Contraseña (opcional)</label>
            <input type="password" name="password" class="w-full border border-gray-300 rounded px-3 py-2">
        </div>

        <!-- Confirmación -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Confirmar Contraseña</label>
            <input type="password" name="password_confirmation" class="w-full border border-gray-300 rounded px-3 py-2">
        </div>

        <!-- Botón -->
        <div class="text-right">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                Actualizar Usuario
            </button>
        </div>
    </form>
</div>
@endsection
