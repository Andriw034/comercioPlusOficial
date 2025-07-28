@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-12 px-6">
    <h2 class="text-3xl font-bold text-center text-blue-800 mb-10">Editar Perfil</h2>

    @if (session('success'))
        <div class="mb-6 bg-green-100 text-green-800 p-4 rounded-md font-medium border border-green-300 shadow">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('profile.update', $profile->id) }}" method="POST" enctype="multipart/form-data" class="bg-white p-8 rounded-2xl shadow-xl border border-gray-200 space-y-6">
        @csrf
        @method('PUT')

        {{-- Rol del usuario --}}
        <div class="text-sm text-right text-gray-500 italic">
            Rol actual: <span class="font-semibold text-blue-700 capitalize">{{ Auth::user()->role }}</span>
        </div>

        {{-- Nombre de usuario --}}
        <div>
            <label for="username" class="block text-sm font-semibold text-gray-700 mb-1">Nombre de usuario</label>
            <input type="text" id="username" name="username" value="{{ old('username', $profile->username) }}"
                   class="w-full px-4 py-2 border rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:border-blue-500 @error('username') border-red-500 @enderror">
            @error('username')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Fecha de nacimiento --}}
        <div>
            <label for="birthdate" class="block text-sm font-semibold text-gray-700 mb-1">Fecha de nacimiento</label>
            <input type="date" id="birthdate" name="birthdate" value="{{ old('birthdate', $profile->birthdate) }}"
                   class="w-full px-4 py-2 border rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:border-blue-500 @error('birthdate') border-red-500 @enderror">
            @error('birthdate')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Otra información --}}
        @if (in_array(Auth::user()->role, ['admin', 'comerciante']))
        <div>
            <label for="other_info" class="block text-sm font-semibold text-gray-700 mb-1">Otra información</label>
            <textarea id="other_info" name="other_info" rows="3"
                      class="w-full px-4 py-2 border rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:border-blue-500 @error('other_info') border-red-500 @enderror">{{ old('other_info', $profile->other_info) }}</textarea>
            @error('other_info')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        @endif

        {{-- Foto de perfil --}}
        <div>
            <label for="image" class="block text-sm font-semibold text-gray-700 mb-1">Foto de perfil</label>
            <input type="file" id="image" name="image"
                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                          file:rounded-md file:border-0 file:text-sm file:font-semibold
                          file:bg-orange-500 file:text-white hover:file:bg-orange-600
                          transition duration-200 cursor-pointer">
            @error('image')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Botón --}}
        <div class="text-right pt-4">
            <button type="submit"
                    class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-2 rounded-md shadow-md transition">
                Guardar cambios
            </button>
        </div>
    </form>
</div>
@endsection
