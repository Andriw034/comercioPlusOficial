@extends('layouts.dashboard')

@section('title', 'Tienda')

@section('content')
<div class="p-6 space-y-6">
    @if($store)
        {{-- KPIs --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white/10 ring-1 ring-white/15 rounded-3xl p-6">
                <h3 class="text-white text-lg font-semibold">Productos</h3>
                <p class="text-white/70 text-2xl font-bold">{{ $store->products->count() }}</p>
            </div>
            <div class="bg-white/10 ring-1 ring-white/15 rounded-3xl p-6">
                <h3 class="text-white text-lg font-semibold">Ventas</h3>
                <p class="text-white/70 text-2xl font-bold">{{ $store->orders->count() }}</p>
            </div>
            <div class="bg-white/10 ring-1 ring-white/15 rounded-3xl p-6">
                <h3 class="text-white text-lg font-semibold">Ingresos</h3>
                <p class="text-white/70 text-2xl font-bold">${{ number_format($store->orders->sum('total'), 2) }}</p>
            </div>
        </div>

        {{-- Formulario de actualización --}}
        <div class="bg-white/10 ring-1 ring-white/15 rounded-3xl p-6">
            <h2 class="text-2xl font-bold mb-6 text-white">Configurar Tienda</h2>

            @if(session('success'))
                <div class="bg-green-500/20 border border-green-500/30 text-green-200 px-4 py-3 rounded-xl mb-6">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('store.update', $store) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                <div>
                    <label for="name" class="block font-semibold mb-2 text-white/90">Nombre de la Tienda</label>
                    <input type="text" name="name" id="name" placeholder="Ejemplo: Tienda Plus" class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-orange-500 smooth" required value="{{ old('name', $store->name) }}">
                </div>
                <div>
                    <label for="description" class="block font-semibold mb-2 text-white/90">Descripción</label>
                    <textarea name="description" id="description" rows="5" placeholder="Describe tu tienda" class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-orange-500 smooth" required>{{ old('description', $store->description) }}</textarea>
                </div>
                <div>
                    <label for="logo" class="block font-semibold mb-2 text-white/90">Logo de la Tienda</label>
                    <input type="file" name="logo" id="logo" accept="image/*" class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-orange-500 smooth">
                    @if(!empty($store->logo))
                        <img src="{{ asset('storage/' . $store->logo) }}" alt="Logo actual" class="mt-4 h-20 w-20 object-cover rounded-xl ring-1 ring-white/20">
                    @endif
                </div>
                <button type="submit" class="px-6 py-2 text-black bg-orange-500 rounded-xl hover:bg-orange-600 font-semibold shadow smooth">Actualizar Tienda</button>
            </form>
        </div>
    @else
        <div class="bg-white/10 ring-1 ring-white/15 rounded-3xl p-6 text-center">
            <h2 class="text-2xl font-bold mb-4 text-white">No tienes una tienda</h2>
            <p class="text-white/70 mb-6">Crea tu tienda para comenzar a vender.</p>
            <a href="{{ route('store.create') }}" class="px-6 py-2 text-black bg-orange-500 rounded-xl hover:bg-orange-600 font-semibold shadow smooth">Crear Tienda</a>
        </div>
    @endif
</div>
@endsection
