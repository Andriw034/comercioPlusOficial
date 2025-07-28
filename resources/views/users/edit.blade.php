@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-md">
    <h1 class="text-3xl font-bold mb-8 text-center text-orange-600">Editar Usuario</h1>

    <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nombre -->
            <div>
                <label class="block text-gray-700 font-semibold mb-1">Nombre</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                    class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400" required>
                @error('name')
                    <small class="text-red-600">{{ $message }}</small>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label class="block text-gray-700 font-semibold mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                    class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400" required>
                @error('email')
                    <small class="text-red-600">{{ $message }}</small>
                @enderror
            </div>

            <!-- Teléfono -->
            <div>
                <label class="block text-gray-700 font-semibold mb-1">Teléfono</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                    class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400">
            </div>

            <!-- Dirección -->
            <div>
                <label class="block text-gray-700 font-semibold mb-1">Dirección</label>
                <input type="text" name="address" value="{{ old('address', $user->address) }}"
                    class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400">
            </div>

            <!-- Rol -->
            <div>
                <label class="block text-gray-700 font-semibold mb-1">Rol</label>
                <select name="role_id"
                    class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Estado -->
            <div>
                <label class="block text-gray-700 font-semibold mb-1">Estado</label>
                <select name="status"
                    class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400">
                    <option value="1" {{ $user->status ? 'selected' : '' }}>Activo</option>
