@extends('layouts.dashboard')

@section('title', 'Ver Usuario Admin — ComercioPlus')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-white">Detalles del Usuario</h1>
        <div class="space-x-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 text-white bg-white/10 rounded-xl hover:bg-white/15 smooth">Editar</a>
            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este usuario?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 text-white bg-red-500/20 rounded-xl hover:bg-red-500/30 border border-red-500/30 smooth">Eliminar</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 bg-white/10 ring-1 ring-white/15 rounded-3xl p-6">
        <div>
            <h2 class="text-xl font-semibold text-white mb-4">Información Personal</h2>
            <div class="space-y-3 text-white/90">
                <p><strong>ID:</strong> {{ $user->id }}</p>
                <p><strong>Nombre:</strong> {{ $user->name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Teléfono:</strong> {{ $user->phone ?? 'No proporcionado' }}</p>
                <p><strong>Fecha de Registro:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Último Acceso:</strong> {{ $user->last_login_at ?? 'Nunca' }}</p>
            </div>
        </div>

        <div class="text-center">
            <h2 class="text-xl font-semibold text-white mb-4">Avatar</h2>
            @if($user->avatar)
                <img src="{{ Storage::url($user->avatar) }}" alt="Avatar de {{ $user->name }}" class="w-32 h-32 rounded-full object-cover mx-auto ring-2 ring-white/20">
            @else
                <div class="w-32 h-32 rounded-full bg-white/10 flex items-center justify-center mx-auto ring-1 ring-white/20 text-white/70 font-semibold">Sin avatar</div>
            @endif
        </div>
    </div>
</div>
@endsection
