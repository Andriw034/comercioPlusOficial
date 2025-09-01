@extends('layouts.app')
@section('title','404')

@section('content')
<div class="max-w-md mx-auto text-center px-4 py-20">
  <div class="text-6xl font-extrabold mb-4" style="color:var(--cp-primary)">404</div>
  <p class="text-[var(--cp-ink-2)] mb-6">Página no encontrada</p>
  <a href="{{ \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/') }}" class="btn btn-primary">Volver al inicio</a>
</div>
@endsection
