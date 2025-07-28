@extends('layouts.app')

@section('title', 'Lista de Usuarios')

@section('content')
<div class="max-w-6xl mx-auto p-6 bg-white shadow rounded">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Usuarios</h1>
        <a href="{{ route('users.create') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded">
            + Nuevo Usuario
        </a>
    </div>

    <table class="w-full table-auto text-left border">
        <thead>
            <tr class="bg-gray-100 text-gray-700">
                <th class="p-3">#</th>
                <th class="p-3">Nombre</th>
                <th class="p-3">Email</th>
                <th class="p-3">Rol</th>
                <th class="p-3">Estado</th>
                <th class="p-3">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
            <tr class="border-t hover:bg-gray-50">
                <td class="p-3">{{ $user->id }}</td>
                <td class="p-3 flex items-center gap-2">
                    @if ($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" class="w-8 h-8 rounded-full object-cover">
                    @endif
                    {{ $user->name }}
                </td>
                <td class="p-3">{{ $user->email }}</td>
                <td class="p-3">{{ $user->role->name ?? 'Sin rol' }}</td>
                <td class="p-3">
                    <span class="px-2 py-1 rounded text-sm
                        {{ $user->status ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $user->status ? 'Activo' : 'Inactivo' }}
                    </span>
                </td>
                <td class="p-3 flex gap-2">
                    <a href="{{ route('users.edit', $user) }}" class="text-blue-500 hover:underline">Editar</a>
                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('¿Eliminar este usuario?');">
                        @csrf @method('DELETE')
                        <button class="text-red-500 hover:underline">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
