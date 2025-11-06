@extends('layouts.dashboard')

@section('content')
<div class="min-h-[calc(100vh-120px)] w-full">
  @if(session('status'))
    <div class="mx-6 my-4 rounded-md border border-emerald-600/30 bg-emerald-500/20 px-4 py-3 text-emerald-100 lg:mx-8">
      {{ session('status') }}
    </div>
  @endif

  <div class="mx-6 lg:mx-8">
    <div class="mb-4 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-white">Productos</h1>
        <p class="text-sm text-slate-300">Gestiona tu catalogo de ComercioPlus</p>
      </div>
      <a href="{{ route('admin.products.create') }}"
         class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold text-white"
         style="background:#FF6000">
        Nuevo producto
      </a>
    </div>

    @if($products->count())
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($products as $product)
          <article class="overflow-hidden rounded-xl border border-white/10 bg-[#111827] shadow-sm transition hover:shadow-md">
            <div class="p-3">
              <div class="mb-3">
                <img
                  src="{{ $product->image_url ?? asset('images/no-image.png') }}"
                  alt="Imagen de {{ $product->name }}"
                  class="aspect-[4/3] w-full rounded-lg object-cover"
                  onerror="this.onerror=null;this.src='{{ asset('images/no-image.png') }}';"
                >
              </div>

              <div class="space-y-1">
                <h2 class="font-semibold leading-tight text-white">{{ $product->name }}</h2>
                <span class="text-xs uppercase text-slate-400">
                  {{ optional($product->category)->name ?? 'Sin categoria' }}
                </span>
                <p class="text-sm font-semibold text-white/90">
                  {{ $product->price_formatted ?? '$'.number_format((float) $product->price, 0, ',', '.') }}
                </p>
              </div>

              <div class="mt-3 flex items-center justify-between">
                <span class="text-xs text-slate-400">Stock: {{ $product->stock ?? 0 }}</span>
                <div class="flex items-center gap-2">
                  <a href="{{ route('admin.products.edit', $product) }}"
                     class="rounded-md px-3 py-1 text-[13px] font-semibold text-white"
                     style="background:#FF6000">
                    Editar
                  </a>
                  <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                        onsubmit="return confirm('Seguro que deseas eliminar este producto?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="rounded-md px-3 py-1 text-[13px] font-semibold text-white"
                            style="background:#FF6000">
                      Eliminar
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </article>
        @endforeach
      </div>

      <div class="mt-8 text-white">
        {{ $products->links() }}
      </div>
    @else
      <div class="rounded-xl border border-white/10 bg-[#111827] p-8 text-center text-slate-200">
        No hay productos todavia.
      </div>
    @endif
  </div>
</div>
@endsection
