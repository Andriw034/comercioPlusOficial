@extends('layouts.app')

@section('content')
    <h2 class="text-2xl font-bold mb-4">Bienvenido, {{ Auth::user()->name }}</h2>
    <p class="mb-6 text-gray-600">Este es tu panel de control.</p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded shadow text-center">
            <h3 class="text-xl font-semibold">Usuarios Activos</h3>
            <p class="text-3xl mt-2">154</p>
        </div>
        <div class="bg-white p-6 rounded shadow text-center">
            <h3 class="text-xl font-semibold">Productos en Stock</h3>
            <p class="text-3xl mt-2">320</p>
        </div>
        <div class="bg-white p-6 rounded shadow text-center">
            <h3 class="text-xl font-semibold">Ventas del Día</h3>
            <p class="text-3xl mt-2">25</p>
        </div>
    </div>
@endsection





