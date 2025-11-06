@extends('layouts.dashboard')

@section('content')
<div class="relative min-h-[calc(100vh-120px)] w-full overflow-hidden" style="--cp-primary:#FF6000; --cp-primary-2:#FF8A3D;">
  <div class="absolute inset-0 bg-gradient-to-br from-black via-neutral-900 to-neutral-800"></div>
  <div class="absolute -top-40 -left-40 h-96 w-96 rounded-full opacity-20 blur-3xl" style="background: radial-gradient(closest-side, var(--cp-primary), transparent);"></div>
  <div class="absolute -bottom-40 -right-40 h-[28rem] w-[28rem] rounded-full opacity-20 blur-3xl" style="background: radial-gradient(closest-side, var(--cp-primary-2), transparent);"></div>

  <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-3xl font-extrabold text-white">Editar producto</h1>
        <p class="text-sm text-neutral-300">Actualiza la informacion de tu catalogo en ComercioPlus</p>
      </div>
      <a href="{{ route('admin.products.index') }}" class="btn-ghost border border-white/10 text-white/90">
        Volver
      </a>
    </div>

    <div class="rounded-2xl border border-white/10 bg-white/10 p-6 backdrop-blur-md">
      @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-400/30 bg-red-500/10 p-4 text-red-200">
          <ul class="space-y-1 pl-5 text-sm">
            @foreach ($errors->all() as $error)
              <li class="list-disc">{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        @csrf
        @method('PUT')

        <div class="space-y-5 lg:col-span-7">
          <div>
            <label class="mb-1 block text-sm text-neutral-300" for="name">Nombre</label>
            <input id="name" type="text" name="name" value="{{ old('name', $product->name) }}" required
                   class="w-full rounded-lg border border-white/20 bg-white/10 px-3 py-2 text-white placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-[--cp-primary]">
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
              <label class="mb-1 block text-sm text-neutral-300" for="price">Precio</label>
              <input id="price" type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" required
                     class="w-full rounded-lg border border-white/20 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-[--cp-primary]">
            </div>
            <div>
              <label class="mb-1 block text-sm text-neutral-300" for="stock">Stock</label>
              <input id="stock" type="number" step="1" min="0" name="stock" value="{{ old('stock', $product->stock) }}"
                     class="w-full rounded-lg border border-white/20 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-[--cp-primary]">
            </div>
            <div>
              <label class="mb-1 block text-sm text-neutral-300" for="category_id">Categoria</label>
              <select id="category_id" name="category_id"
                      class="w-full rounded-lg border border-white/20 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-[--cp-primary]">
                <option value="">Sin categoria</option>
                @foreach (($categories ?? []) as $cat)
                  <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                @endforeach
              </select>
              @if(empty($categories) || ($categories instanceof \Illuminate\Support\Collection && $categories->isEmpty()))
                <p class="mt-1 text-xs text-neutral-400">
                  No tienes categorias aun. Puedes crearlas en
                  <a href="{{ route('admin.categories.index') }}" class="text-[--cp-primary] underline">Categorias</a>.
                </p>
              @endif
            </div>
          </div>

          <div>
            <label class="mb-1 block text-sm text-neutral-300" for="description">Descripcion</label>
            <textarea id="description" name="description" rows="5"
                      class="w-full rounded-lg border border-white/20 bg-white/10 px-3 py-2 text-white placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-[--cp-primary]">{{ old('description', $product->description) }}</textarea>
          </div>

          <div class="flex items-center gap-3">
            <input id="status" type="checkbox" name="status" value="1" @checked(old('status', $product->status) == 1)
                   class="h-4 w-4 rounded border-white/30 bg-white/10 text-[--cp-primary] focus:ring-[--cp-primary]">
            <label for="status" class="text-neutral-300">Publicar (activo)</label>
          </div>
        </div>

        <div class="lg:col-span-5">
          <div class="space-y-4 rounded-2xl border border-white/10 bg-white/5 p-5">
            <div class="flex items-center justify-between">
              <h3 class="font-semibold text-white">Imagen del producto</h3>
              <span class="text-xs text-neutral-400">400x400</span>
            </div>

            <div class="relative mx-auto h-[400px] w-full max-w-[400px] overflow-hidden rounded-2xl bg-black/20">
              <img id="preview" class="h-full w-full object-cover" alt="Vista previa"
                   src="{{ $product->image_url ?? asset('images/no-image.png') }}">
            </div>

            <div class="flex items-center gap-3">
              <label for="image" class="btn-cp cursor-pointer">Subir imagen</label>
              <input id="image" type="file" name="image" accept="image/*" class="hidden">
              <button type="button" id="clearImage" class="btn-ghost border border-white/10 text-white/90">Quitar</button>
            </div>

            <p class="text-xs text-neutral-400">Recomendado: JPG/PNG/WebP, hasta 2MB.</p>
          </div>
        </div>

        <div class="lg:col-span-12 flex items-center justify-end gap-3 pt-2">
          <a href="{{ route('admin.products.index') }}" class="btn-ghost border border-white/10 text-white/90">Cancelar</a>
          <button type="submit" class="btn-cp">Actualizar producto</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  const input = document.getElementById('image');
  const preview = document.getElementById('preview');
  const clearBtn = document.getElementById('clearImage');
  const fallback = "{{ $product->image_url ?? asset('images/no-image.png') }}";

  input?.addEventListener('change', e => {
    const file = e.target.files?.[0];
    if (file) {
      preview.src = URL.createObjectURL(file);
    }
  });

  clearBtn?.addEventListener('click', () => {
    if (input) {
      input.value = '';
    }
    if (preview) {
      preview.src = fallback;
    }
  });
</script>
@endsection
