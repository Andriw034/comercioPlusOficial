@extends('layouts.app')

@section('title', $store['name'])

@section('content')
<div class="bg-background">
  <section class="border-b">
    <div class="relative h-48 md:h-64 w-full">
      @if(!empty($store['cover']))
        <img src="{{ $store['cover'] }}" alt="Portada de {{ $store['name'] }}" class="absolute inset-0 w-full h-full object-cover" data-ai-hint="motorcycle road">
      @else
        <div class="w-full h-full bg-muted"></div>
      @endif
      <div class="absolute inset-0 bg-gradient-to-t from-background via-background/80 to-black/20"></div>
    </div>

    <div class="container -mt-16 sm:-mt-20">
      <div class="flex flex-col sm:flex-row items-end gap-4 relative z-10">
        <div class="h-32 w-32 rounded-full bg-card p-1.5 flex-shrink-0 flex items-center justify-center border-4 border-background shadow-md">
          @if(!empty($store['logo']))
            <img src="{{ $store['logo'] }}" alt="Logo de {{ $store['name'] }}" class="rounded-full object-cover h-32 w-32">
          @else
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 5h4l2 5h5"/><path d="M15 16H9"/><circle cx="5" cy="18" r="3"/><circle cx="19" cy="18" r="3"/></svg>
          @endif
        </div>

        <div class="flex-grow py-4">
          <div class="flex flex-col sm:flex-row justify-between items-start gap-2">
            <div>
              <h1 class="text-3xl md:text-4xl font-bold">{{ $store['name'] }}</h1>
              <p class="text-muted-foreground flex items-center gap-2 mt-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                {{ $store['address'] ?? 'Dirección no disponible' }}
              </p>
            </div>
            <div class="flex-shrink-0 mt-2 sm:mt-0">
              <div class="flex items-center gap-2 text-sm bg-secondary backdrop-blur-sm px-3 py-1.5 rounded-full border">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current text-primary" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path d="M12 17.27 18.18 21l-1.64-7L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                <span class="font-semibold text-secondary-foreground">{{ number_format($store['averageRating'] ?? 0, 1) }}</span>
                <span class="text-muted-foreground">(15 reseñas)</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <main class="container pt-8 pb-16">
    <form method="GET" class="flex flex-col md:flex-row gap-4 mb-8">
      <div class="relative flex-grow">
        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar en la tienda..." class="pl-12 h-11 bg-card w-full rounded-md border border-input text-sm px-3 py-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" />
      </div>

      <select name="category" class="w-full md:w-[200px] h-11 bg-card rounded-md border border-input text-sm px-3 py-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
        <option value="" @selected(!$categorySel)>Categoría</option>
        <option value="all" @selected($categorySel==='all')>Todas las categorías</option>
        @foreach($categories as $cat)
          <option value="{{ $cat['id'] }}" @selected($categorySel===$cat['id'])>{{ $cat['name'] }}</option>
        @endforeach
      </select>

      <select name="sort" class="w-full md:w-[200px] h-11 bg-card rounded-md border border-input text-sm px-3 py-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
        <option value="" @selected(!$sortSel)>Ordenar por</option>
        <option value="popular" @selected($sortSel==='popular')>Más populares</option>
        <option value="price-asc" @selected($sortSel==='price-asc')>Precio: bajo a alto</option>
        <option value="price-desc" @selected($sortSel==='price-desc')>Precio: alto a bajo</option>
        <option value="newest" @selected($sortSel==='newest')>Más nuevos</option>
      </select>

      <button type="submit" class="h-11 px-5 rounded-md bg-primary text-primary-foreground font-semibold">Aplicar</button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      @if(count($products))
        @foreach($products as $product)
          <div class="rounded-lg border bg-card text-card-foreground shadow-sm overflow-hidden group transition-all duration-200 hover:shadow-md hover:-translate-y-1">
            <a href="{{ route('products.show', $product['id']) }}">
              <div class="aspect-square overflow-hidden bg-card">
                @if(!empty($product['image']))
                  <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" data-ai-hint="motorcycle part" class="object-cover w-full h-full transition-transform duration-200 group-hover:scale-105">
                @endif
              </div>
            </a>
            <div class="p-4">
              <p class="text-muted-foreground text-sm">{{ $product['category'] ?? 'Sin categoría' }}</p>
              <h3 class="font-semibold text-lg truncate mt-1">
                <a href="{{ route('products.show', $product['id']) }}" class="hover:text-primary transition-colors">
                  {{ $product['name'] }}
                </a>
              </h3>
              <div class="flex items-center justify-between mt-4">
                <p class="font-bold text-xl">${{ number_format($product['price'], 0, ',', '.') }}</p>
                <button class="h-9 px-3 rounded-md bg-primary text-primary-foreground text-sm font-semibold">Agregar</button>
              </div>
            </div>
          </div>
        @endforeach
      @else
        <div class="col-span-full text-center py-12 text-muted-foreground">
          <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 5h4l2 5h5"/><path d="M15 16H9"/><circle cx="5" cy="18" r="3"/><circle cx="19" cy="18" r="3"/></svg>
          <h3 class="mt-4 text-lg font-semibold">No hay productos todavía</h3>
          <p class="mt-1 text-sm">Este comerciante aún no ha añadido ningún producto a su tienda.</p>
        </div>
      @endif
    </div>
  </main>
</div>
@endsection
