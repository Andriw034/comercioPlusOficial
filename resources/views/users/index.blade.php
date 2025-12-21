@extends('layouts.app')

@section('title', 'Lista de Usuarios')

@section('content')



    <div class="flex justify-between items-center mb-6 px-12">
        <h1 class="text-3xl font-bold text-center">Usuarios</h1>

        <a href="{{ route('users.create') }}"
            class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-all duration-200">
            + Nuevo Usuario
        </a>
    </div>

    <div class="overflow-x-auto flex justify-center">
        <table class="min-w-[1200px] bg-white border border-gray-300 rounded-lg shadow-lg text-sm">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-4 py-2 border-b">ID</th>
                    <th class="px-4 py-2 border-b">Nombre</th>
                    <th class="px-4 py-2 border-b">Email</th>
                    <th class="px-4 py-2 border-b">Teléfono</th>
                    <th class="px-4 py-2 border-b">Dirección</th>
                    <th class="px-4 py-2 border-b">Rol</th>
                    <th class="px-4 py-2 border-b">Estado</th>
                    <th class="px-4 py-2 border-b">Avatar</th>
                    <th class="px-4 py-2 border-b">Creado</th>
                    <th class="px-4 py-2 border-b">Actualizado</th>
                    <th class="px-4 py-2 border-b">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="text-center hover:bg-gray-50">
                        <td class="px-4 py-2 border-b">{{ $user->id }}</td>
                        <td class="px-4 py-2 border-b">{{ $user->name }}</td>
                        <td class="px-4 py-2 border-b">{{ $user->email }}</td>
                        <td class="px-4 py-2 border-b">{{ $user->phone ?? '-' }}</td>
                        <td class="px-4 py-2 border-b">{{ $user->address ?? '-' }}</td>
                        <td class="px-4 py-2 border-b">{{ $user->role ? $user->role->name : 'Sin rol' }}</td>
                        <td class="px-4 py-2 border-b">
                            @if ($user->status)
                                <span class="text-green-600 font-semibold">Activo</span>
                            @else
                                <span class="text-red-600 font-semibold">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 border-b">
                            <img src="{{ $user->avatar ? asset('storage/avatars/' . $user->avatar) : 'https://i.pravatar.cc/100?u=' . $user->email }}"
                                alt="Avatar" class="w-10 h-10 rounded-full mx-auto shadow">
                        </td>
                        <td class="px-4 py-2 border-b">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 border-b">{{ $user->updated_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 border-b space-x-1">
                            <div class="flex space-x-2">
                                <a href="{{ route('users.edit', $user->id) }}"
                                    class="inline-block w-15 text-center bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-1 rounded transition-all duration-200">
                                    Editar
                                </a>
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                    onsubmit="return confirm('¿Estás seguro de eliminar este usuario?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="w-15 text-center bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded transition-all duration-200">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
