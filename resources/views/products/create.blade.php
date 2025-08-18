@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl px-6 py-10">
  <div class="rounded-2xl border bg-white shadow overflow-hidden">
    <!-- Header con degradado naranja ComercioPlus -->
    <div class="px-6 py-5 text-white"
         style="background:linear-gradient(90deg,#FF6000,#FF8A3D);">
      <h2 class="text-xl font-bold">➕ Agregar Producto</h2>
      <p class="text-white/90 text-sm">Sube una imagen y completa los datos</p>
    </div>

    <div class="p-6">
      <!-- Errores -->
      @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
          <ul class="list-disc pl-5 space-y-1 text-sm">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <!-- Formulario -->
      <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Nombre -->
        <div>
          <label class="block text-sm font-medium text-slate-700">Nombre del producto</label>
          <input type="text" name="name" required
                 value="{{ old('name') }}"
                 placeholder="Casco deportivo"
                 class="mt-1 w-full rounded-xl border-slate-300 focus:ring-2 focus:ring-orange-500">
        </div>

        <!-- Descripción -->
        <div>
          <label class="block text-sm font-medium text-slate-700">Descripción</label>
          <textarea name="description" rows="4"
                    placeholder="Describe el producto"
                    class="mt-1 w-full rounded-xl border-slate-300 focus:ring-2 focus:ring-orange-500">{{ old('description') }}</textarea>
        </div>

        <!-- Precio y Stock -->
        <div class="grid gap-6 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-slate-700">Precio ($)</label>
            <input type="number" step="0.01" name="price" required
                   value="{{ old('price') }}"
                   class="mt-1 w-full rounded-xl border-slate-300 focus:ring-2 focus:ring-orange-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Stock</label>
            <input type="number" name="stock" required
                   value="{{ old('stock') }}"
                   class="mt-1 w-full rounded-xl border-slate-300 focus:ring-2 focus:ring-orange-500">
          </div>
        </div>

        <!-- Categoría -->
        <div>
          <label class="block text-sm font-medium text-slate-700">Categoría</label>
          <select name="category_id" required
                  class="mt-1 w-full rounded-xl border-slate-300 focus:ring-2 focus:ring-orange-500">
            <option value="">Selecciona una categoría</option>
            @foreach($categories as $category)
              <option value="{{ $category->id }}" @selected(old('category_id')==$category->id)>{{ $category->name }}</option>
            @endforeach
          </select>
        </div>

        <!-- Imagen con previsualización -->
        <div>
          <label class="block text-sm font-medium text-slate-700">Imagen del producto</label>

          <!-- Área de preview -->
          <div class="mt-2 rounded-2xl border border-dashed border-slate-300 p-4">
            <div class="grid gap-4 sm:grid-cols-[160px_1fr]">
              <div class="aspect-square w-full max-w-[160px] rounded-xl bg-slate-100 overflow-hidden">
                <img id="preview" src="{{ asset('images/placeholder-product.png') }}"
                     alt="Preview" class="h-full w-full object-cover">
              </div>

              <div class="flex flex-col justify-center gap-3">
                <input id="image" type="file" name="image" accept="image/*"
                       class="block w-full text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-slate-200 file:px-4 file:py-2 hover:file:bg-slate-300">
                <div class="flex items-center gap-3">
                  <button type="button" id="btnClear"
                          class="rounded-xl border px-3 py-2 text-sm">
                    Quitar imagen
                  </button>
                  <span class="text-xs text-slate-500">Formatos: JPG/PNG. Máx 2MB.</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Botones -->
        <div class="pt-2 flex items-center gap-3">
          <a href="{{ route('products.index') }}" class="rounded-xl border px-4 py-2">Cancelar</a>
          <button type="submit" class="rounded-xl px-5 py-2 text-white"
                  style="background:linear-gradient(90deg,#FF6000,#FF8A3D);">
            Guardar producto
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- JS: previsualización y limpiar -->
<script>
  const input = document.getElementById('image');
  const preview = document.getElementById('preview');
  const btnClear = document.getElementById('btnClear');

  input?.addEventListener('change', (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => { preview.src = ev.target.result; };
    reader.readAsDataURL(file);
  });

  btnClear?.addEventListener('click', () => {
    input.value = '';
    preview.src = "{{ asset('images/placeholder-product.png') }}";
  });
</script>
@endsection
