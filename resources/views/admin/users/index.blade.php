@extends('layouts.dashboard')

@section('title', 'Usuarios Admin — ComercioPlus')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-white">Usuarios</h1>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-white smooth">
            + Crear Usuario
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-500/20 border border-green-500/30 text-green-200 px-4 py-3 rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-3xl bg-white/10 ring-1 ring-white/15 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/10">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/70 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/70 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/70 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/70 uppercase tracking-wider">Teléfono</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/70 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($users as $user)
                    <tr class="hover:bg-white/5 smooth">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $user->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-white font-medium">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-white/90">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-white/90">{{ $user->phone ?? 'No proporcionado' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="{{ route('admin.users.show', $user) }}" class="text-blue-400 hover:text-blue-300 smooth">Ver</a>
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-orange-400 hover:text-orange-300 smooth">Editar</a>
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este usuario?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300 smooth">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-white/70">No hay usuarios registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-center">
        {{ $users->links('vendor.pagination.tailwind') }}
    </div>
</div>
@endsection
