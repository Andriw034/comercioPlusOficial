@extends('layouts.app')

@section('content')
<div class="container text-center">

    {{-- Fondo/banner --}}
    @if($store->background)
        <div class="mb-4">
            <img src="{{ asset('storage/' . $store->background) }}" class="img-fluid w-100" alt="Fondo de la tienda">
        </div>
    @endif
<input type="text" name="primary_color" value="#FFA14F">
  <h1 @if($store->primary_color) style="color: {{ $store->primary_color }}" @endif>
    {{ $store->name }}
</h1>



    

    {{-- Logo --}}
    @if($store->logo)
        <img src="{{ asset('storage/' . $store->logo) }}" width="150" alt="Logo de la tienda" class="my-3">
    @endif

    {{-- Descripción --}}
    <p class="mt-2">{{ $store->description }}</p>

    {{-- Productos del comerciante --}}
    <h3 class="mt-5">Productos del comerciante:</h3>
    <div class="row mt-4">
        @foreach($store->user->products as $product)
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" class="card-img-top" alt="Imagen del producto">
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $product->name }}</h5>
                        <p class="card-text">{{ $product->description }}</p>
                        <p class="card-text fw-bold text-success">${{ number_format($product->price, 0, ',', '.') }}</p>

                        {{-- WhatsApp --}}
                        <a href="https://wa.me/{{ $store->user->phone }}" class="btn btn-success btn-sm d-block mb-2" target="_blank">
                            Contactar por WhatsApp
                        </a>

                        {{-- Agregar al carrito --}}
                        <form action="{{ route('cart.add', $product->id) }}" method="POST">
                            @csrf
                            <button class="btn btn-primary btn-sm w-100">Agregar al carrito</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection






