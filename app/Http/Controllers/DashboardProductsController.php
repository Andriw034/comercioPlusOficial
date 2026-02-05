@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-8 bg-gray-900 min-h-[70vh]">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-100 mb-8 drop-shadow-md">Agregar Nuevo Producto</h1>

        <form action="{{ route('dashboard.products.store') }}" method="POST" enctype="multipart/form-data"
              class="bg-gray-800/70 border border-white/20 p-8 rounded-xl shadow-xl">
            @csrf

            <div class="mb-6">
                <label for="name" class="block text-sm font-semibold text-gray-200 mb-3">Nombre del Producto</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Ingrese el nombre del producto"
                       class="w-full px-4 py-3 bg-white text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 placeholder-gray-500 @error('name') ring-red-400 border-red-400 @enderror transition-all"
                       required>
                @error('name')
                    <p class="text-red-400 text-sm mt-2 font-medium">{{ $message }}</p>
                @enderror
            </div>Ã±

            <div class="mb-6">
                <label for="description" class="block text-sm font-semibold text-gray-200 mb-3">DescripciÃ³n</label>
                <textarea name="description" id="description" rows="5" placeholder="Ingrese la descripciÃ³n detallada del producto"
                          class="w-full px-4 py-3 bg-white text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 placeholder-gray-500 @error('description') ring-red-400 border-red-400 @enderror transition-all resize-vertical"
                          required>{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-400 text-sm mt-2 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="price" class="block text-sm font-semibold text-gray-200 mb-3">Precio (S/.)</label>
                    <input type="number" name="price" id="price" step="0.01" min="0" value="{{ old('price') }}" placeholder="0.00"
                           class="w-full px-4 py-3 bg-white text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 placeholder-gray-500 @error('price') ring-red-400 border-red-400 @enderror transition-all"
                           required>
                    @error('price')
                        <p class="text-red-400 text-sm mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="stock" class="block text-sm font-semibold text-gray-200 mb-3">Stock</label>
                    <input type="number" name="stock" id="stock" min="0" value="{{ old('stock') }}" placeholder="0"
                           class="w-full px-4 py-3 bg-white text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 placeholder-gray-500 @error('stock') ring-red-400 border-red-400 @enderror transition-all"
                           required>
                    @error('stock')
                        <p class="text-red-400 text-sm mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- BLOQUE: CategorÃ­as populares --}}
            <div class="mb-4">
                <p class="text-sm text-gray-300 mb-2">CategorÃ­as mÃ¡s populares</p>

                @if(isset($popularCategories) && $popularCategories->count() > 0)
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach($popularCategories as $pc)
                            <button type="button"
                                    data-id="{{ $pc->id }}"
                                    class="btn-popular inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/90 text-gray-900 border border-gray-200 hover:bg-white transition text-sm shadow-sm">
                                {{ $pc->name }}
                                <span class="text-xs text-gray-500 ml-1">({{ $pc->products_count ?? 0 }})</span>
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="text-sm text-gray-500 mb-3">No hay categorÃ­as populares para mostrar.</div>
                @endif
            </div>

            {{-- SELECT de categorÃ­as con optgroups: "MÃ¡s populares" + "Todas" --}}
            <div class="mb-6">
                <label for="category_id" class="block text-sm font-semibold text-gray-200 mb-3">CategorÃ­a</label>

                <select name="category_id" id="category_id"
                        class="w-full px-4 py-3 bg-white text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 @error('category_id') ring-red-400 border-red-400 @enderror transition-all"
                        required>
                    <option value="" class="text-gray-500">Seleccionar categorÃ­a</option>

                    {{-- Optgroup populares (si existen) --}}
                    @if(isset($popularCategories) && $popularCategories->count() > 0)
                        <optgroup label="MÃ¡s populares">
                            @foreach($popularCategories as $pc)
                                <option value="{{ $pc->id }}" {{ old('category_id') == $pc->id ? 'selected' : '' }}>
                                    {{ $pc->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif

                    {{-- Optgroup todas las categorÃ­as (sin repetir las populares) --}}
                    <optgroup label="Todas las categorÃ­as">
                        @php
                            $popularIds = isset($popularCategories) ? $popularCategories->pluck('id')->toArray() : [];
                        @endphp

                        @foreach($categories as $cat)
                            @continue(in_array($cat->id, $popularIds))
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>

                @error('category_id')
                    <p class="text-red-400 text-sm mt-2 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-8">
                <label for="image" class="block text-sm font-semibold text-gray-200 mb-3">Imagen del Producto</label>
                <div class="flex items-center gap-4">
                    <label class="inline-block">
                        <input type="file" name="image" id="image" accept="image/*"
                               class="hidden file-input">
                        <span class="inline-block px-4 py-2 rounded-md bg-white text-gray-900 border border-gray-300 cursor-pointer hover:bg-gray-100 text-sm font-medium">Elegir archivo</span>
                    </label>
                    <span id="file-name" class="text-gray-300 text-sm">No file chosen</span>
                </div>
                <p class="text-sm text-gray-400 mt-2">Formatos permitidos: JPG, PNG, GIF. TamaÃ±o mÃ¡ximo: 2MB. La imagen se mostrarÃ¡ en tu vitrina.</p>
                @error('image')
                    <p class="text-red-400 text-sm mt-2 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-4 pt-4 border-t border-white/10">
                <a href="{{ route('dashboard.products.index') }}"
                   class="bg-gray-700 hover:bg-gray-600 text-gray-100 px-6 py-3 rounded-lg transition-all duration-200 shadow-md">
                    Cancelar
                </a>
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg transition-all duration-200 shadow-md font-semibold">
                    Crear Producto
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // File input display
    const realInput = document.querySelector('input[type="file"][name="image"]');
    const display = document.getElementById('file-name');
    const trigger = document.querySelector('label > span.inline-block');

    if (realInput) {
        trigger && trigger.addEventListener('click', () => realInput.click());
        realInput.addEventListener('change', () => {
            const f = realInput.files[0];
            display.textContent = f ? f.name : 'No file chosen';
        });
    }

    // Quick-select de categorÃ­as (chips)
    const popularButtons = document.querySelectorAll('.btn-popular');
    const select = document.getElementById('category_id');

    popularButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            if (!select) return;
            select.value = id;
            // Opcional: destacar visualmente el chip activo
            popularButtons.forEach(b => b.classList.remove('ring-2', 'ring-orange-400'));
            btn.classList.add('ring-2', 'ring-orange-400');
        });
    });
});
</script>
@endpush

@endsection
