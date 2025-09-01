@extends('layouts.app')

@section('title', 'Productos')

@section('content')
<div class="container py-8">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold">Productos</h1>
      <p class="text-muted-foreground">Administra los productos de tu tienda.</p>
    </div>
    <a href="{{ route('dashboard.products.create') }}" class="h-10 px-4 rounded-md bg-primary text-primary-foreground font-semibold inline-flex items-center">Nuevo producto</a>
  </div>

  <div class="rounded-lg border overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-muted">
        <tr>
          <th class="text-left p-3">Producto</th>
          <th class="text-left p-3">Categoría</th>
          <th class="text-left p-3">Precio</th>
          <th class="text-left p-3">Stock</th>
          <th class="text-left p-3">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @foreach($products as $p)
        <tr class="border-t">
          <td class="p-3">
            <div class="flex items-center gap-3">
              <img src="{{ $p['image'] }}" class="w-12 h-12 rounded object-cover" alt="{{ $p['name'] }}">
              <div class="font-medium">{{ $p['name'] }}</div>
            </div>
          </td>
          <td class="p-3 text-muted-foreground">{{ $p['category'] }}</td>
          <td class="p-3 font-semibold">${{ number_format($p['price'], 0, ',', '.') }}</td>
          <td class="p-3">{{ $p['stock'] }}</td>
          <td class="p-3">
            @if(isset($p['created_at']))
              <div class="flex gap-2">
                <a href="{{ route('dashboard.products.edit', $p['id']) }}" class="text-blue-600 hover:text-blue-800">Editar</a>
                <form action="{{ route('dashboard.products.destroy', $p['id']) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este producto?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="text-red-600 hover:text-red-800">Eliminar</button>
                </form>
              </div>
            @else
              <span class="text-muted-foreground">Producto base</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
