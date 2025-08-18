@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl px-6 py-10">
  <div class="rounded-2xl border bg-white shadow overflow-hidden">

    <!-- Header -->
    <div class="px-6 py-5 text-white"
         style="background:linear-gradient(90deg,#FF6000,#FF8A3D);">
      <h2 class="text-xl font-bold">✏️ Editar producto</h2>
      <p class="text-white/90 text-sm">Actualiza la imagen, el precio y la descripción</p>
    </div>

    <div class="p-6">
      {{-- Errores --}}
      @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
          <ul class="list-disc pl-5 space-y-1 text-sm">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('products.update', ['product' => $product->id]) }}"
            method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Nombre (puedes ocultarlo si no lo deseas cambiar) --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Nombre</label>
          <input type="text" name="name" required
                 value="{{ old('name', $product->name) }}"
                 class="mt-1 w-full rounded-xl border-slate-300 focus:ring-2 focus:ring-orange-500">
        </div>

        {{-- Precio --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Precio ($)</label>
          <input type="number" step="0.01" name="price" required
                 value="{{ old('price', $product->price) }}"
                 class="mt-1 w-full rounded-xl border-slate-300 focus:ring-2 focus:ring-orange-500">
        </div>

        {{-- Descripción --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Descripción</label>
          <textarea name="description" rows="4"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:ring-2 focus:ring-orange-500"
                    placeholder="Describe el producto">{{ old('description', $product->description) }}</textarea>
        </div>

        {{-- Categoría (opcional pero recomendado) --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Categoría</label>
          <select name="category_id" required
                  class="mt-1 w-full rounded-xl border-slate-300 focus:ring-2 focus:ring-orange-500">
            <option value="">Selecciona una categoría</option>
            @foreach($categories as $category)
              <option value="{{ $category->id }}"
                @selected(old('category_id', $product->category_id) == $category->id)>
                {{ $category->name }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Imagen actual + nueva con previsualización --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Imagen</label>

          <div class="mt-2 grid gap-4 sm:grid-cols-[160px_1fr]">
            {{-- Imagen actual --}}
            <div class="space-y-2">
              <div class="aspect-square w-full max-w-[160px] rounded-xl bg-slate-100 overflow-hidden border">
                @if($product->image)
                  <img src="{{ asset('storage/'.$product->image) }}" alt="Actual"
                       class="h-full w-full object-cover">
                @else
                  <img src="{{ asset('images/logo_comercio_plus.png') }}" alt="Actual"
                       class="h-full w-full object-cover">
                @endif
              </div>
              <p class="text-xs text-slate-500 text-center">Imagen actual</p>
            </div>

            {{-- Nueva imagen + preview --}}
            <div class="rounded-2xl border border-dashed border-slate-300 p-4">
              <div class="grid gap-4 sm:grid-cols-[160px_1fr]">
                <div class="aspect-square w-full max-w-[160px] rounded-xl bg-slate-100 overflow-hidden">
                  <img id="preview" src="{{ asset('images/logo_comercio_plus.png') }}"
                       class="h-full w-full object-cover" alt="Preview">
                </div>
                <div class="flex flex-col justify-center gap-3">
                  <input id="image" type="file" name="image" accept="image/*"
                         class="block w-full text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-slate-200 file:px-4 file:py-2 hover:file:bg-slate-300">
                  <div class="text-xs text-slate-500">JPG/PNG, máx 2 MB. Si no seleccionas nada, se mantiene la imagen actual.</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Botones --}}
        <div class="pt-2 flex items-center gap-3">
          <a href="{{ route('products.index') }}" class="rounded-xl border px-4 py-2">Cancelar</a>
          <button type="submit" class="rounded-xl px-5 py-2 text-white"
                  style="background:linear-gradient(90deg,#FF6000,#FF8A3D);">
            Guardar cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- JS Preview de nueva imagen --}}
<script>
  const input = document.getElementById('image');
  const preview = document.getElementById('preview');
  input?.addEventListener('change', (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => { preview.src = ev.target.result; };
    reader.readAsDataURL(file);
  });
</script>
@endsection
