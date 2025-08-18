@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4">Roles del sistema</h1>

    <a href="{{ route('admin.roles.create') }}" class="bg-orange-500 text-white px-4 py-2 rounded mb-4 inline-block">+ Crear Rol</a>

    @if (session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <table class="w-full table-auto bg-white shadow rounded">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-3 text-left">Nombre</th>
                <th class="p-3 text-left">Permisos</th>
                <th class="p-3 text-left">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $role)
                <tr class="border-b">
                    <td class="p-3">{{ $role->name }}</td>
                    <td class="p-3">
                        @foreach($role->permissions as $permiso)
                            <span class="text-sm bg-gray-200 px-2 py-1 rounded mr-1">{{ $permiso->name }}</span>
                        @endforeach
                    </td>
                    <td class="p-3 space-x-2">
                        <a href="{{ route('admin.roles.edit', $role) }}" class="text-blue-600">Editar</a>
                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline-block">
                            @csrf @method('DELETE')
                            <button class="text-red-600" onclick="return confirm('¿Eliminar este rol?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
