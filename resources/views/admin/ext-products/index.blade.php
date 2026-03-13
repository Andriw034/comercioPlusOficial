@extends('layouts.dashboard')

@section('title', 'Productos Externos — ComercioPlus')

@section('content')
<div class="w-full">
  <div class="mx-auto w-full max-w-7xl p-4 sm:p-6 lg:p-8">

    <header class="mb-6 sm:mb-8">
      <h1 class="text-2xl sm:text-3xl font-bold text-gray-100 mb-2">Productos Externos</h1>
      <p class="text-gray-300">Listado consultado desde la API externa.</p>
    </header>

    <form method="GET" action="{{ route('admin.ext-products.index') }}" class="mb-6 grid grid-cols-1 sm:grid-cols-12 gap-3">
      <input
        type="text"
        name="search"
        value="{{ request('search') }}"
        placeholder="Buscar por nombre…"
        class="sm:col-span-6 px-4 py-2 rounded-lg bg-white text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400"
      />

      <select
        name="sort"
        class="sm:col-span-3 px-4 py-2 rounded-lg bg-white text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400"
      >
        @php $sort = request('sort'); @endphp
        <option value="" {{ $sort ? '' : 'selected' }}>Ordenar…</option>
        <option value="price:asc"  {{ $sort==='price:asc'  ? 'selected':'' }}>Precio: menor a mayor</option>
        <option value="price:desc" {{ $sort==='price:desc' ? 'selected':'' }}>Precio: mayor a menor</option>
        <option value="stock:desc" {{ $sort==='stock:desc' ? 'selected':'' }}>Stock: mayor a menor</option>
      </select>

      <select
        name="per_page"
        class="sm:col-span-2 px-4 py-2 rounded-lg bg-white text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400"
      >
        @php $pp = (int) request('per_page', $limit ?? 12); @endphp
        @foreach([6,12,24,48] as $n)
          <option value="{{ $n }}" {{ $pp===$n ? 'selected':'' }}>{{ $n }} por página</option>
        @endforeach
      </select>

      <button
        class="sm:col-span-1 inline-flex items-center justify-center px-4 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-semibold transition"
      >
        Aplicar
      </button>
    </form>

    @isset($error)
      <div class="mb-6 p-4 rounded-lg bg-red-600/20 border border-red-500 text-red-200">
        <strong>Error al consultar la API externa:</strong>
        <span class="block mt-1 text-red-100">{{ $error }}</span>
      </div>
    @endisset

    @php $items = $products ?? []; @endphp

    @if(empty($items))
      <div class="p-8 text-center text-gray-300 bg-gray-800/60 rounded-xl border border-gray-700">
        No hay productos para mostrar.
      </div>
    @else
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($items as $p)
          @php
            $id    = $p['id'] ?? $p['external_id'] ?? null;
            $title = $p['title'] ?? $p['name'] ?? 'Producto';
            $brand = $p['brand'] ?? null;
            $price = isset($p['price']) ? (float) $p['price'] : null;
            $stock = $p['stock'] ?? null;
            $cat   = $p['category'] ?? $p['category_id'] ?? null;
            $thumb = $p['thumbnail'] ?? (is_array($p['images'] ?? null) ? ($p['images'][0] ?? null) : null);
          @endphp

          <article class="bg-gray-800 border border-gray-700 rounded-2xl overflow-hidden shadow-lg flex flex-col">
            <div class="aspect-[4/3] bg-gray-700 flex items-center justify-center overflow-hidden">
              @if($thumb)
                <img src="{{ $thumb }}" alt="{{ $title }}" class="w-full h-full object-cover">
              @else
                <div class="text-gray-300 text-sm">Sin imagen</div>
              @endif
            </div>

            <div class="p-4 flex-1 flex flex-col">
              <h3 class="text-lg font-semibold text-white leading-snug line-clamp-2">{{ $title }}</h3>

              <div class="mt-2 text-sm text-gray-300 space-y-1">
                @if($brand)<div><span class="text-gray-400">Marca:</span> {{ $brand }}</div>@endif
                @if($cat)<div><span class="text-gray-400">Categoría:</span> {{ $cat }}</div>@endif
                @if(!is_null($stock))<div><span class="text-gray-400">Stock:</span> {{ $stock }}</div>@endif
              </div>

              <div class="mt-3 text-xl font-bold text-orange-400">
                @if(!is_null($price)) $ {{ number_format($price, 2, ',', '.') }} @else <span class="text-gray-400 text-base">Sin precio</span> @endif
              </div>

              <div class="mt-4 grid grid-cols-2 gap-2">
                <a href="{{ $id ? url('/api/ext/products/'.$id) : '#' }}"
                   target="_blank"
                   class="px-3 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-100 text-sm text-center transition {{ $id ? '' : 'pointer-events-none opacity-50' }}">
                  Ver JSON
                </a>

                @if($id)
                  <form method="POST" action="{{ route('admin.ext-products.import', $id) }}" class="inline">
                    @csrf
                    <button type="submit"
                      class="w-full px-3 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm transition font-medium"
                      title="Importar este producto a tu tienda">
                      Importar
                    </button>
                  </form>
                @else
                  <button type="button"
                    class="px-3 py-2 rounded-lg bg-gray-500 text-gray-300 text-sm cursor-not-allowed"
                    disabled
                    title="ID no disponible">
                    Importar
                  </button>
                @endif
              </div>
            </div>
          </article>
        @endforeach
      </section>

      @php
        $page = (int) ($page ?? request('page', 1));
        $limit = (int) ($limit ?? request('per_page', 12));
        $total = (int) ($total ?? 0);
        $hasPrev = $page > 1;
        $hasNext = $total > 0 ? ($page * $limit) < $total : (count($items) === $limit);
        $qs = request()->except('page');
      @endphp

      <div class="mt-8 flex items-center justify-between">
        <div class="text-gray-400 text-sm">
          Página {{ $page }} @if($total) de {{ (int) ceil($total / max(1,$limit)) }}@endif
        </div>

        <div class="flex gap-2">
          @if($hasPrev)
            <a href="{{ route('admin.ext-products.index', array_merge($qs, ['page' => $page - 1])) }}"
               class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-100 text-sm transition">
              Anterior
            </a>
          @else
            <span class="px-4 py-2 rounded-lg bg-gray-700/50 text-gray-500 text-sm">Anterior</span>
          @endif

          @if($hasNext)
            <a href="{{ route('admin.ext-products.index', array_merge($qs, ['page' => $page + 1])) }}"
               class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-100 text-sm transition">
              Siguiente
            </a>
          @else
            <span class="px-4 py-2 rounded-lg bg-gray-700/50 text-gray-500 text-sm">Siguiente</span>
          @endif
        </div>
      </div>
    @endif
  </div>
</div>
@endsection
