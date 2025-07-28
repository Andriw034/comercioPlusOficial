@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">

    {{-- Título y botón crear --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-orange-500">Tiendas Públicas</h1>
        <a href="{{ route('public_stores.create') }}"
           class="bg-orange-500 text-white px-4 py-2 rounded-md shadow hover:bg-orange-600 transition duration-200">
            + Nueva Tienda
        </a>
    </div>

    {{-- Grid de tiendas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($publicStores as $store)
            <div class="border rounded-xl p-4 shadow-sm hover:shadow-md transition duration-200 bg-white">
                <div class="flex items-center justify-center mb-3">
                    <img src="{{ $store->store_image ? asset('storage/' . $store->store_image) : asset('images/default-store.png') }}"
                         alt="Logo tienda"
                         class="w-20 h-20 object-contain rounded-full border shadow-sm">
                </div>
                <h2 class="text-lg font-semibold text-gray-800 text-center">{{ $store->store_name }}</h2>
                <p class="text-sm text-gray-600 mt-1 text-center">{{ Str::limit($store->store_description, 80) }}</p>
                <div class="text-center mt-4">
                    <a href="{{ route('public_stores.show', $store->id) }}"
                       class="text-orange-500 hover:underline font-medium">
                        Ver más
                    </a>
                </div>
            </div>
        @empty
            <p class="col-span-full text-center text-gray-500">No hay tiendas registradas aún.</p>
        @endforelse
    </div>
</div>
@endsection
