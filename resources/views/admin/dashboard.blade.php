@extends('layouts.admin')

@section('page_title', 'Administration Panel')
@section('page_subtitle', 'Manage your store and products')

@section('content')
  {{-- Sección "Featured products" con línea degradada --}}
  <div class="mt-2">
    <h2 class="text-lg md:text-xl font-semibold">Featured Products</h2>
    <div class="mt-2 h-[3px] w-full rounded bg-gradient-to-r from-brand-400 via-slate-200 to-transparent"></div>
  </div>

  <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    @forelse($products as $product)
      <article class="card-float">
        {{-- Imagen representativa o placeholder --}}
        <div class="card-thumb">
          @if(!empty($product->image))
            <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
          @else
            <div class="thumb-empty">Aquí va una imagen</div>
          @endif
        </div>

        {{-- Cuerpo minimal --}}
        <div class="p-5">
          <h3 class="font-semibold text-slate-900">{{ $product->name }}</h3>
          <div class="mt-4 flex items-center justify-between">
            <span class="text-brand-600 font-semibold">
              ${{ number_format($product->price ?? 0, 2) }}
            </span>
            <div class="flex gap-2">
              <a href="{{ route('products.edit', $product) }}" class="btn btn-ghost text-sm">Editar</a>
              <form action="{{ route('products.destroy', $product) }}" method="POST"
                    onsubmit="return confirm('¿Eliminar este producto?')">
                @csrf @method('DELETE')
                <button class="btn bg-rose-500 hover:bg-rose-600 text-white text-sm">
                  Eliminar
                </button>
              </form>
            </div>
          </div>
        </div>
      </article>
    @empty
      <div class="col-span-full card-float p-10 text-center">
        <p class="text-slate-600">Aún no tienes productos.</p>
        <a href="{{ route('products.create') }}" class="btn btn-primary mt-3">Crear el primero</a>
      </div>
    @endforelse
  </div>

  <div class="mt-6">
    {{ $products->links() }}
  </div>
@endsection
