{{-- resources/views/products/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Productos')

@section('content')
<!--
  Vista de Productos - Implementación profesional
  - Blade + Tailwind CSS + Alpine.js (no dependencias extra)
  - Accessible, responsive y modular dentro de un solo archivo para copiar/pegar
  - Uso de componentes y convenciones modernas (BEM-like classes para claridad)
-->

<div class="min-h-screen bg-gray-900 text-gray-100 p-6 md:p-10">
  <div class="max-w-7xl mx-auto">
    <header class="flex items-start justify-between gap-4 mb-6">
      <div>
        <h1 class="text-3xl font-extrabold tracking-tight">Productos</h1>
        <p class="mt-1 text-sm text-gray-400">Gestiona tus productos, stock y precios desde aquí.</p>
      </div>

      <div class="flex items-center gap-3">
        <!-- Nuevo Producto (abre sidebar) -->
        <button @click="openCreate = true" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 focus-visible:ring-2 focus-visible:ring-orange-300 text-white px-4 py-2 rounded-lg shadow-md">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/></svg>
          Nuevo producto
        </button>

        <!-- Buscador y filtro simple -->
        <div class="relative">
          <label for="search" class="sr-only">Buscar productos</label>
          <input id="search" name="search" type="search" placeholder="Buscar..." class="w-64 bg-gray-800 text-gray-100 placeholder-gray-500 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400" />
        </div>

        <div>
          <select aria-label="Filtro de estado" class="bg-gray-800 text-gray-100 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400">
            <option value="all">Todos</option>
            <option value="active">Solo activos</option>
            <option value="inactive">Inactivos</option>
            <option value="low_stock">Stock bajo</option>
          </select>
        </div>
      </div>
    </header>

    <!-- Métricas -->
    <section class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-8">
      <div class="bg-gray-800 rounded-2xl p-4 shadow-sm">
        <p class="text-sm text-gray-400">Total de productos</p>
        <p class="text-2xl font-bold">54</p>
      </div>
      <div class="bg-gray-800 rounded-2xl p-4 shadow-sm">
        <p class="text-sm text-gray-400">Activos</p>
        <p class="text-2xl font-bold text-emerald-300">Indreos</p>
      </div>
      <div class="bg-gray-800 rounded-2xl p-4 shadow-sm">
        <p class="text-sm text-gray-400">Categoría destacada</p>
        <p class="text-2xl font-bold">Iluminación</p>
      </div>
      <div class="bg-gray-800 rounded-2xl p-4 shadow-sm">
        <p class="text-sm text-gray-400">Stock bajo</p>
        <p class="text-2xl font-bold text-amber-400">Inactivo</p>
      </div>
    </section>

    <!-- Grid de tarjetas de producto -->
    <section>
      <ul class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" role="list">
        <!-- Iterar productos desde el backend -->
        @foreach($products as $product)
        <li class="bg-gray-800 rounded-2xl overflow-hidden shadow-md group" aria-labelledby="product-{{ $product->id }}-name">
          <div class="relative h-48 md:h-40 lg:h-44 bg-gray-900/40">
            <img src="{{ $product->image_url ?? asset('images/placeholder.png') }}" alt="{{ $product->name }}" class="w-full h-full object-cover brightness-[0.55]" />
            <div class="absolute inset-0 p-3 flex items-start justify-end">
              <!-- Menu de acciones (oculto por defecto) -->
              <div class="relative" x-data="{}">
                <button aria-haspopup="true" aria-expanded="false" class="bg-gray-700/60 text-gray-200 p-2 rounded-md hover:bg-gray-700 focus-visible:ring-2 focus-visible:ring-orange-400">
                  <span class="sr-only">Acciones</span>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </button>
                <div class="absolute right-0 mt-2 w-40 bg-gray-800 rounded-lg shadow-lg py-1 hidden group-hover:block" role="menu" aria-label="Opciones de producto">
                  <a href="{{ route('products.edit', $product) }}" class="block px-3 py-2 text-sm hover:bg-gray-700">Editar</a>
                  <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar este producto?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full text-left block px-3 py-2 text-sm text-rose-400 hover:bg-gray-700">Eliminar</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <div class="p-4">
            <h3 id="product-{{ $product->id }}-name" class="text-lg font-semibold">{{ 
              Str::limit($product->name, 40) }}</h3>
            <p class="text-sm text-gray-400 mt-1">{{ $product->category->name ?? 'Sin categoría' }}</p>

            <div class="mt-3 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-900/30 text-emerald-300">{{ $product->status_text }}</span>
                <span class="text-sm font-bold">${{ number_format($product->price, 2) }}</span>
              </div>

              <div class="text-sm text-gray-400">Stock: <strong class="text-gray-100">{{ $product->stock }}</strong></div>
            </div>

            <div class="mt-3 flex items-center justify-between">
              <a href="{{ route('products.show', $product) }}" class="text-sm text-orange-400 hover:underline">Ver</a>
              <a href="#" class="text-sm text-gray-400">Compartir</a>
            </div>
          </div>
        </li>
        @endforeach
      </ul>

      <!-- Paginación accesible -->
      <div class="mt-8">
        {{ $products->links() }}
      </div>
    </section>
  </div>
</div>

<!-- Sidebar crear/editar producto (Alpine.js) -->
<div x-data="productSidebar()" x-show="openCreate" x-cloak @keydown.window.escape="openCreate = false" class="fixed inset-0 z-50 flex">
  <!-- Fondo semitransparente -->
  <div x-show="openCreate" x-transition.opacity class="fixed inset-0 bg-black/60" aria-hidden="true"></div>

  <!-- Panel lateral -->
  <aside x-show="openCreate" x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="ml-auto w-full max-w-md bg-gray-900 shadow-2xl rounded-l-2xl p-6 overflow-auto">
    <div class="flex items-start justify-between">
      <h2 class="text-2xl font-bold">Crear producto</h2>
      <button @click="openCreate = false" class="text-gray-300 hover:text-gray-100 focus-visible:ring-2 focus-visible:ring-orange-400 rounded-md p-2">
        <span class="sr-only">Cerrar panel</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
      </button>
    </div>

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4">
      @csrf

      <div>
        <label for="name" class="block text-sm font-medium text-gray-300">Nombre</label>
        <input id="name" name="name" type="text" required placeholder="Nombre del producto" class="mt-1 block w-full bg-gray-800 text-gray-100 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400" />
      </div>

      <div>
        <label for="category_id" class="block text-sm font-medium text-gray-300">Categoría</label>
        <select id="category_id" name="category_id" class="mt-1 block w-full bg-gray-800 text-gray-100 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400">
          <option value="">-- Seleccionar categoría --</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label for="price" class="block text-sm font-medium text-gray-300">Precio</label>
          <input id="price" name="price" type="number" step="0.01" min="0" placeholder="0.00" class="mt-1 block w-full bg-gray-800 text-gray-100 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400" />
        </div>
        <div>
          <label for="stock" class="block text-sm font-medium text-gray-300">Existencias</label>
          <input id="stock" name="stock" type="number" min="0" placeholder="0" class="mt-1 block w-full bg-gray-800 text-gray-100 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400" />
        </div>
      </div>

      <!-- Subida de imagen con vista previa -->
      <div>
        <label class="block text-sm font-medium text-gray-300">Imagen</label>
        <div class="mt-2 flex items-center gap-3">
          <div class="w-28 h-20 bg-gray-800 rounded-md overflow-hidden flex items-center justify-center">
            <img :src="previewUrl || '{{ asset('images/placeholder.png') }}'" alt="Vista previa" class="object-cover w-full h-full" />
          </div>
          <div class="flex-1">
            <input id="image" name="image" type="file" accept="image/*" @change="handleFileChange" class="block w-full text-sm text-gray-400 file:bg-gray-700 file:text-gray-100 file:rounded-md file:px-3 file:py-2 file:border-0" />
            <p class="mt-1 text-xs text-gray-400">Formatos: JPG, PNG. Máx: 2MB.</p>
          </div>
        </div>
      </div>

      <div>
        <label for="description" class="block text-sm font-medium text-gray-300">Descripción</label>
        <textarea id="description" name="description" rows="4" class="mt-1 block w-full bg-gray-800 text-gray-100 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="Descripción corta del producto"></textarea>
      </div>

      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <label for="status" class="text-sm text-gray-300">Activo</label>
          <input id="status" name="status" type="checkbox" value="1" class="h-5 w-9 rounded-full bg-gray-700 appearance-none checked:bg-orange-500 relative after:content-[''] after:absolute after:left-0.5 after:top-0.5 after:bg-white after:w-4 after:h-4 after:rounded-full after:transition-all checked:after:translate-x-4" />
        </div>

        <div class="flex gap-3">
          <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg focus-visible:ring-2 focus-visible:ring-orange-300">Guardar</button>
          <button type="button" @click="openCreate = false" class="px-4 py-2 rounded-lg border border-gray-700 text-gray-300 hover:border-gray-600">Cancelar</button>
        </div>
      </div>
    </form>
  </aside>
</div>


<!-- Scripts Alpine -->
@push('scripts')
<script>
  function productSidebar(){
    return {
      openCreate: false,
      previewUrl: null,
      handleFileChange(e){
        const file = e.target.files[0];
        if(!file) return this.previewUrl = null;
        if(file.size > 2 * 1024 * 1024){
          alert('El archivo supera el límite de 2MB.');
          e.target.value = null;
          return this.previewUrl = null;
        }
        const reader = new FileReader();
        reader.onload = (ev) => { this.previewUrl = ev.target.result; };
        reader.readAsDataURL(file);
      }
    }
  }
</script>
@endpush

@endsection
