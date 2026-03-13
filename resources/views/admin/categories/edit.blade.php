@extends('layouts.dashboard')
@section('title', 'Editar categoría')

@section('content')
<div class="mx-auto max-w-xl p-4">
  <h1 class="mb-6 text-2xl font-semibold">Editar categoría</h1>

  <form method="POST" action="{{ route('admin.categories.update', $category) }}"
        class="rounded-2xl border border-gray-700 p-6">
    @csrf @method('PUT')

    <div class="mb-4">
      <label class="mb-2 block text-sm text-gray-300">Nombre</label>
      <input name="name" value="{{ old('name', $category->name) }}"
             class="w-full rounded-xl border border-gray-600 bg-gray-800 px-3 py-2 text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-600" />
      @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center gap-3">
      <button class="rounded-xl bg-orange-600 px-5 py-2.5 text-white hover:bg-orange-700">
        Actualizar
      </button>
      <a href="{{ route('admin.categories.index') }}" class="text-gray-400 hover:underline">Cancelar</a>
    </div>
  </form>
</div>
@endsection
