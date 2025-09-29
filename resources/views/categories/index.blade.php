@extends('layouts.dashboard')

@section('title', 'Categorías Admin — ComercioPlus')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-white">Categorías</h1>
        <a href="{{ route('categories.create') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-white smooth">
            + Agregar Categoría
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/70 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/70 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($categories as $category)
                    <tr class="hover:bg-white/5 smooth">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $category->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="{{ route('categories.edit', $category) }}" class="text-blue-400 hover:text-blue-300 smooth">Editar</a>
                            <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar esta categoría?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300 smooth">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-center text-white/70">No hay categorías registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-center">
        {{ $categories->links('vendor.pagination.tailwind') }}
    </div>
</div>
@endsection
