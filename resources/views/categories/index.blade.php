@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto py-12 px-6">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-4 md:mb-0">Categorías</h1>
        <a href="{{ route('categories.create') }}"
           class="bg-[#ff9800] hover:bg-orange-600 text-white px-5 py-3 rounded-lg font-semibold shadow transition duration-300">
            + Nueva Categoría
        </a>
    </div>

    {{-- Mensaje de éxito --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 shadow-md text-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tabla de categorías --}}
    <div class="bg-white rounded-2xl shadow-xl p-6 overflow-x-auto">
        <table class="w-full table-auto border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-700 text-left">
                    <th class="px-6 py-3 border-b border-gray-300">Nombre</th>
                    <th class="px-6 py-3 border-b border-gray-300">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 border-b border-gray-200 text-gray-800 text-lg">
                            {{ $category->name }}
                        </td>
                        <td class="px-6 py-4 border-b border-gray-200">
                            <div class="flex space-x-3">
                                <a href="{{ route('categories.edit', $category) }}"
                                   class="bg-[#ff9800] hover:bg-orange-600 text-white px-4 py-2 rounded-md shadow text-sm font-medium transition">
                                    ✏️ Editar
                                </a>
                                <form action="{{ route('categories.destroy', $category) }}" method="POST"
                                      onsubmit="return confirm('¿Está seguro de eliminar esta categoría?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md shadow text-sm font-medium transition">
                                        🗑 Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Paginación --}}
        <div class="mt-6">
            {{ $categories->links() }}
        </div>
    </div>
</div>
@endsection
