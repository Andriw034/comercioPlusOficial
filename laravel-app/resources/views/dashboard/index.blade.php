@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Panel de Control</h1>

    @if (session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-semibold mb-2">Inicio</h2>
            <p class="text-gray-600">Bienvenido a tu panel de control.</p>
        </div>

        @if (auth()->user()->isMerchant())
            <div class="bg-white p-6 rounded shadow">
                <h2 class="text-xl font-semibold mb-2">Productos</h2>
                <p class="text-gray-600">Gestiona tus productos.</p>
                <a href="#" class="text-blue-600 hover:underline">Ver productos</a>
            </div>
        @endif

        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-semibold mb-2">Perfil</h2>
            <p class="text-gray-600">Actualiza tu información personal.</p>
            <a href="#" class="text-blue-600 hover:underline">Editar perfil</a>
        </div>
    </div>

    <div class="mt-8 bg-white p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-4">Información del Usuario</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <strong>Nombre:</strong> {{ auth()->user()->name }}
            </div>
            <div>
                <strong>Email:</strong> {{ auth()->user()->email }}
            </div>
            <div>
                <strong>Rol:</strong> {{ auth()->user()->role }}
            </div>
            <div>
                <strong>Estado:</strong> {{ auth()->user()->status ? 'Activo' : 'Inactivo' }}
            </div>
        </div>
    </div>
</div>
@endsection
