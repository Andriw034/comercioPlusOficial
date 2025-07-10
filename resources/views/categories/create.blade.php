@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto bg-white p-6 rounded shadow-md">
    <h2 class="text-3xl font-bold mb-6 text-gray-900">Agregar Categoría</h2>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-4 rounded mb-6 shadow">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('categories.store') }}" method="POST" class="space-y-6">
        @csrf
        <div>
            <label for="name" class="block font-semibold mb-2 text-gray-700">Nombre</label>
            <input type="text" name="name" id="name" placeholder="Nombre de la categoría" class="w-full border border-gray-300 rounded px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary" value="{{ old('name') }}" required>
        </div>
        <button type="submit" class="bg-primary text-white px-6 py-3 rounded font-semibold hover:bg-primary-light transition-colors duration-300">Guardar Categoría</button>
    </form>
</div>
@endsection
