@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6">
    <h2 class="text-2xl font-semibold mb-6">Configuración de la tienda</h2>

    <form action="{{ route('settings.update') }}" method="POST" class="space-y-4">
        @csrf
        @method('POST')

        <div>
            <label class="block text-sm font-medium">Nombre de la tienda</label>
            <input type="text" name="store_name" value="{{ get_setting('store_name') }}" class="w-full border rounded p-2" />
        </div>

        <div>
            <label class="block text-sm font-medium">Color primario</label>
            <input type="color" name="primary_color" value="{{ get_setting('primary_color', '#F97316') }}" class="w-16 h-10 p-1 border rounded" />
        </div>

        <div>
            <label class="block text-sm font-medium">Estilo visual</label>
            <select name="theme_style" class="w-full border rounded p-2">
                <option value="moderno" {{ get_setting('theme_style') == 'moderno' ? 'selected' : '' }}>Moderno</option>
                <option value="minimalista" {{ get_setting('theme_style') == 'minimalista' ? 'selected' : '' }}>Minimalista</option>
                <option value="oscuro" {{ get_setting('theme_style') == 'oscuro' ? 'selected' : '' }}>Oscuro</option>
            </select>
        </div>

        <div class="pt-4">
            <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                Guardar configuración
            </button>
        </div>
    </form>
</div>
@endsection
