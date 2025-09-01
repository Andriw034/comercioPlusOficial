@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container py-8">
  <div class="mb-6">
    <h1 class="text-2xl font-bold">Hola, {{ $store['name'] }}</h1>
    <p class="text-muted-foreground">Resumen de tu tienda</p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="rounded-lg border bg-card p-4">
      <div class="text-sm text-muted-foreground">Órdenes</div>
      <div class="text-2xl font-bold mt-1">{{ $metrics['orders'] }}</div>
    </div>
    <div class="rounded-lg border bg-card p-4">
      <div class="text-sm text-muted-foreground">Ingresos</div>
      <div class="text-2xl font-bold mt-1">${{ number_format($metrics['revenue'], 0, ',', '.') }}</div>
    </div>
    <div class="rounded-lg border bg-card p-4">
      <div class="text-sm text-muted-foreground">Productos</div>
      <div class="text-2xl font-bold mt-1">{{ $metrics['products'] }}</div>
    </div>
  </div>

  <div class="mt-8 rounded-lg border bg-card">
    <div class="p-4 border-b font-semibold">Productos recientes</div>
    <div class="p-4 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach($products as $p)
        <div class="rounded-lg border bg-card overflow-hidden">
          <img src="{{ $p['image'] }}" alt="{{ $p['name'] }}" class="w-full h-40 object-cover">
          <div class="p-3">
            <div class="text-sm text-muted-foreground">{{ $p['category'] }}</div>
            <div class="font-semibold">{{ $p['name'] }}</div>
            <div class="font-bold mt-1">${{ number_format($p['price'], 0, ',', '.') }}</div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
@endsection
