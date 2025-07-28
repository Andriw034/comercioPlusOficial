@extends('layouts.app')

@section('title', 'Lista de Usuarios')

@section('content')
<div class="max-w-7xl mx-auto p-6 bg-white rounded shadow">

    <h1 class="text-2xl font-bold mb-6 text-center">Usuarios</h1>

    <!-- Formulario de búsqueda y filtros -->
    <form method="GET" action="{{ route('users.index') }}" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <input type="text" name="search" value="{{ request('search') }}"
               class="border-gray-300 rounded px-3 py-2 w-full"
               placeholder="Buscar por nombre o email">

        <select name="status" class="border-gray-300 rounded px-3 py-2 w-full">
            <option value="">Todos los estados</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Activo</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactivo</option>
        </select>

        <select name="role_id" class="border-gray-300 rounded px-3 py-2 w-full">
            <option value="">Todos los roles</option>
            @foreach(\App\Models\Role::all() as $role)
                <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Buscar
        </button>
    </form>

    <!-- Botón para crear -->
    <div class="mb-4 text-right">
        <a href="{{ route('users.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            Crear Usuario
        </a>
    </div>

    <!-- Tabla de usuarios -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 border">Avatar</th>
                    <th class="px-4 py-2 border">Nombre</th>
                    <th class="px-4 py-2 border">Email</th>
                    <th class="px-4 py-2 border">Rol</th>
                    <th class="px-4 py-2 border">Estado</th>
                    <th class="px-4 py-2 border">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr class="text-center">
                    <td class="px-4 py-2 border">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" class="w-10 h-10 rounded-full mx-auto">
                        @else
                            <span class="text-gray-400">Sin avatar</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 border">{{ $user->name }}</td>
                    <td class="px-4 py-2 border">{{ $user->email }}</td>
                    <td class="px-4 py-2 border">{{ $user->role->name ?? '-' }}</td>
                    <td class="px-4 py-2 border">
                        @if($user->status)
                            <span class="text-green-600 font-semibold">Activo</span>
                        @else
                            <span class="text-red-600 font-semibold">Inactivo</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 border space-x-2">
                        <a href="{{ route('users.edit', $user) }}" class="text-blue-600 hover:underline">Editar</a>
                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline"
                              onsubmit="return confirm('¿Estás seguro de eliminar este usuario?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-500">No se encontraron usuarios.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-6">
        {{ $users->appends(request()->query())->links() }}
    </div>
</div>
@endsection
