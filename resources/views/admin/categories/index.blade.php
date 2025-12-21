@extends('layouts.dashboard')
@section('title', 'Categorías')

@section('content')
<div class="mx-auto max-w-6xl p-4">
  <div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-semibold">Categorías</h1>

    <a href="{{ route('admin.categories.create') }}"
       class="rounded-xl bg-orange-600 px-4 py-2 text-white hover:bg-orange-700">
      + Nueva categoría
    </a>
  </div>

  <form method="GET" class="mb-4">
    <input name="q" value="{{ $q }}"
           placeholder="Buscar categoría..."
           class="w-full rounded-xl border border-gray-600 bg-gray-800 px-3 py-2 text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-600" />
  </form>

  @if(session('status'))
    <div class="mb-4 rounded-lg bg-green-100 p-3 text-green-800">
      {{ session('status') }}
    </div>
  @endif
  @if(session('error'))
    <div class="mb-4 rounded-lg bg-red-100 p-3 text-red-800">
      {{ session('error') }}
    </div>
  @endif

  @if($categories->count() === 0)
    <div class="rounded-2xl border border-dashed border-gray-600 p-10 text-center text-gray-400">
      No hay categorías aún. Crea la primera para organizar tu catálogo.
    </div>
  @else
  <div class="overflow-hidden rounded-2xl border border-gray-700">
    <table class="w-full text-left text-sm">
      <thead class="bg-gray-800 text-gray-300">
        <tr>
          <th class="px-4 py-3">Nombre</th>
          <th class="px-4 py-3">Slug</th>
          <th class="px-4 py-3">Productos</th>
          <th class="px-4 py-3 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-700">
        @foreach($categories as $cat)
        <tr class="hover:bg-gray-800/60">
          <td class="px-4 py-3 font-medium text-gray-100">{{ $cat->name }}</td>
          <td class="px-4 py-3 text-gray-400">{{ $cat->slug }}</td>
          <td class="px-4 py-3 text-gray-300">{{ $cat->products()->count() }}</td>
          <td class="px-4 py-3">
            <div class="flex justify-end gap-2">
              <a href="{{ route('admin.categories.edit', $cat) }}"
                 class="rounded-lg bg-gray-700 px-3 py-1 text-gray-100 hover:bg-gray-600">Editar</a>

              <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST"
                    onsubmit="return confirm('¿Eliminar esta categoría?');">
                @csrf @method('DELETE')
                <button class="rounded-lg bg-red-600 px-3 py-1 text-white hover:bg-red-700">
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

  <div class="mt-4">
    {{ $categories->links() }}
  </div>
  @endif
</div>
@endsection
