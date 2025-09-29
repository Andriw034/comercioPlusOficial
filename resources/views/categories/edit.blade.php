@extends('layouts.dashboard')

@section('title', 'Editar Categoría Admin — ComercioPlus')

@section('content')
<div class="p-6 space-y-6">
    <div class="bg-white/10 ring-1 ring-white/15 rounded-3xl p-6">
        <h2 class="text-2xl font-bold mb-6 text-white">Editar Categoría</h2>

        @if ($errors->any())
            <div class="bg-red-500/20 border border-red-500/30 text-red-200 px-4 py-3 rounded-xl mb-6">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('categories.update', $category) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block font-semibold mb-2 text-white/90">Nombre</label>
                <input type="text" name="name" id="name" placeholder="Nombre de la categoría" class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-orange-500 smooth" value="{{ old('name', $category->name) }}" required>
            </div>
            <button type="submit" class="px-6 py-2 text-black bg-orange-500 rounded-xl hover:bg-orange-600 font-semibold shadow smooth">Actualizar Categoría</button>
        </form>
    </div>
</div>
@endsection
