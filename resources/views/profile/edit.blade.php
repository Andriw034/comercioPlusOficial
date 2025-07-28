@extends('layouts.admin')

@section('content')
<div class="max-w-xl mx-auto mt-10 p-6 bg-white rounded-2xl shadow-xl border border-gray-200">
    <h2 class="text-3xl font-bold text-orange-600 mb-6 text-center">Editar Perfil</h2>

    {{-- Mensaje de éxito --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Errores de validación --}}
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulario --}}
    <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Nombre --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
            <input type="text" name="name" id="name" 
                   class="w-full border border-gray-300 rounded-md px-4 py-2 shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                   value="{{ old('name', $user->name) }}" required>
        </div>

        {{-- Correo electrónico --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
            <input type="email" name="email" id="email" 
                   class="w-full border border-gray-300 rounded-md px-4 py-2 shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                   value="{{ old('email', $user->email) }}" required>
        </div>

        {{-- Contraseña nueva --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña (opcional)</label>
            <input type="password" name="password" id="password"
                   class="w-full border border-gray-300 rounded-md px-4 py-2 shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
        </div>

        {{-- Confirmar contraseña --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" id="password_confirmation"
                   class="w-full border border-gray-300 rounded-md px-4 py-2 shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
        </div>

        {{-- Botón --}}
        <div class="text-right">
            <button type="submit"
                    class="bg-orange-500 text-white font-semibold px-6 py-2 rounded-lg shadow hover:bg-orange-600 transition">
                Actualizar Perfil
            </button>
        </div>
    </form>
</div>
@endsection
