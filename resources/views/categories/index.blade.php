@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-4 md:mb-0">Categorías</h1>
        <a href="{{ route('categories.create') }}" class="bg-primary text-white px-5 py-3 rounded font-semibold hover:bg-primary-light transition-colors duration-300">Agregar Categoría</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded mb-6 shadow">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded shadow p-4">
        <table class="w-full table-auto border-collapse border border-gray-200">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 px-4 py-2 text-left">Nombre</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                <tr>
                    <td class="border border-gray-300 px-4 py-2">{{ $category->name }}</td>
                    <td class="border border-gray-300 px-4 py-2 flex space-x-2">
                        <a href="{{ route('categories.edit', $category) }}" class="bg-primary text-white px-3 py-1 rounded hover:bg-primary-light transition-colors duration-300">Editar</a>
                        <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta categoría?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition-colors duration-300">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $categories->links() }}
        </div>
    </div>
</div>
@endsection
