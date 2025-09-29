@extends('layouts.dashboard')

@section('title', 'Crear categoría')

@section('content')
<div class="container mx-auto px-4 py-8 bg-gray-900 min-h-[70vh]">
    <div class="max-w-2xl mx-auto">
        <header class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-100">Crear categoría</h1>
            <a href="{{ route('admin.categories.index') }}" class="text-sm px-3 py-2 bg-gray-800 border border-gray-700 text-gray-200 rounded-md hover:bg-gray-700">
                Volver al listado
            </a>
        </header>

        @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-900 text-emerald-100 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 bg-rose-900 text-rose-100 rounded">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.categories.store') }}" method="POST" class="bg-gray-800/70 border border-white/10 p-6 rounded-xl shadow" novalidate>
            @csrf

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-200 mb-2">Nombre de la categoría</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}"
                       class="w-full px-4 py-3 bg-white text-gray-900 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 @error('name') ring-rose-400 border-rose-400 @enderror"
                       placeholder="Ej: Neumáticos" required aria-required="true">
                @error('name')
                    <p class="text-rose-400 text-sm mt-2" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="short_description" class="block text-sm font-medium text-gray-200 mb-2">Descripción corta (opcional)</label>
                <input id="short_description" name="short_description" type="text" value="{{ old('short_description') }}"
                       class="w-full px-4 py-3 bg-white text-gray-900 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 @error('short_description') ring-rose-400 border-rose-400 @enderror"
                       placeholder="Breve descripción para mostrar en la tienda">
                @error('short_description')
                    <p class="text-rose-400 text-sm mt-2" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 items-end">
                <div>
                    <label for="popularity" class="block text-sm font-medium text-gray-200 mb-2">Popularidad (score)</label>
                    <input id="popularity" name="popularity" type="number" min="0" value="{{ old('popularity', 0) }}"
                           class="w-full px-4 py-3 bg-white text-gray-900 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 @error('popularity') ring-rose-400 border-rose-400 @enderror">
                    @error('popularity')
                        <p class="text-rose-400 text-sm mt-2" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2 flex items-center gap-4">
                    <div class="flex items-center">
                        <input id="is_popular" name="is_popular" type="checkbox" value="1" {{ old('is_popular') ? 'checked' : '' }}
                               class="h-5 w-5 rounded text-orange-500 focus:ring-orange-400 border-gray-300">
                        <label for="is_popular" class="ml-2 text-sm text-gray-200">Marcar como categoría popular</label>
                    </div>

                    <p class="text-xs text-gray-400">Marcar como popular prioriza la categoría en listados y selects.</p>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-white/10">
                <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 rounded bg-gray-700 hover:bg-gray-600 text-gray-100">Cancelar</a>
                <button type="submit" class="px-4 py-2 rounded bg-orange-500 hover:bg-orange-600 text-white font-semibold">Crear categoría</button>
            </div>
        </form>
    </div>
</div>
@endsection
