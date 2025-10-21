@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-8 bg-gray-900 min-h-[70vh]">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-100 mb-8 drop-shadow-md">Agregar Nuevo Producto</h1>

        {{-- Errores de validación --}}
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-400/30 bg-red-50/10 p-4 text-red-200">
                <div class="font-semibold mb-2">Corrige los siguientes campos:</div>
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data"
              class="bg-gray-800/70 border border-white/20 p-8 rounded-xl shadow-xl" novalidate autocomplete="off">
            @csrf

            {{-- Nombre --}}
            <div class="mb-6">
                <label for="name" class="block text-sm font-semibold text-gray-200 mb-3">Nombre del Producto</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Ingrese el nombre del producto"
                       class="w-full px-4 py-3 bg-white text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 placeholder-gray-500 @error('name') ring-red-400 border-red-400 @enderror transition-all"
                       required aria-required="true" aria-describedby="nameHelp">
                <p id="nameHelp" class="sr-only">Nombre del producto. Requerido.</p>
                @error('name')
                    <p class="text-red-400 text-sm mt-2 font-medium" role="alert">{{ $message }}</p>
                @enderror
            </div>

            {{-- Descripción (opcional) --}}
            <div class="mb-6">
                <label for="description" class="block text-sm font-semibold text-gray-200 mb-3">Descripción</label>
                <textarea name="description" id="description" rows="5" placeholder="Ingrese la descripción detallada del producto"
                          class="w-full px-4 py-3 bg-white text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 placeholder-gray-500 @error('description') ring-red-400 border-red-400 @enderror transition-all resize-vertical">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-400 text-sm mt-2 font-medium" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- Precio --}}
                <div>
                    <label for="price" class="block text-sm font-semibold text-gray-200 mb-3">Precio (COP)</label>
                    <input type="text" name="price" id="price" inputmode="decimal" placeholder="300000"
                           value="{{ old('price') }}"
                           class="w-full px-4 py-3 bg-white text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 placeholder-gray-500 @error('price') ring-red-400 border-red-400 @enderror transition-all"
                           required>
                    @error('price')
                        <p class="text-red-400 text-sm mt-2 font-medium" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Stock --}}
                <div>
                    <label for="stock" class="block text-sm font-semibold text-gray-200 mb-3">Stock</label>
                    <input type="number" name="stock" id="stock" min="0" value="{{ old('stock') }}" placeholder="0"
                           class="w-full px-4 py-3 bg-white text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 placeholder-gray-500 @error('stock') ring-red-400 border-red-400 @enderror transition-all"
                           required>
                    @error('stock')
                        <p class="text-red-400 text-sm mt-2 font-medium" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Categoría --}}
            <div class="mb-6">
                <label for="category_id" class="block text-sm font-semibold text-gray-200 mb-3">Categoría</label>

                @if(isset($categories) && $categories->count())
                    <select name="category_id" id="category_id"
                            class="w-full px-4 py-3 bg-white text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 @error('category_id') ring-red-400 border-red-400 @enderror transition-all"
                            required aria-required="true" aria-describedby="catHelp">
                        <option value="" class="text-gray-500">Seleccionar categoría</option>

                        @php
                            $popular = $categories->where('is_popular', true);
                            $others  = $categories->where('is_popular', false);
                        @endphp

                        @if($popular->count())
                            <optgroup label="Populares">
                                @foreach($popular as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif

                        @if($others->count())
                            <optgroup label="Otras categorías">
                                @foreach($others as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                    <p id="catHelp" class="text-xs text-gray-400 mt-2">Si no ves categorías, crea primero la categoría desde tu panel de tienda.</p>
                @else
                    <div class="bg-gray-800 border border-gray-700 rounded p-4">
                        <p class="text-gray-300 mb-2">No tienes categorías aún.</p>
                        <a href="{{ route('admin.categories.create') }}" class="inline-block bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-md shadow-sm">
                            Crear categoría
                        </a>
                        <p class="text-xs text-gray-400 mt-2">Las categorías son por tienda — si aún no creaste la tienda, primero créala.</p>
                    </div>
                @endif

                @error('category_id')
                    <p class="text-red-400 text-sm mt-2 font-medium" role="alert">{{ $message }}</p>
                @enderror
            </div>

            {{-- Imagen --}}
            <div class="mb-8">
                <label for="image" class="block text-sm font-semibold text-gray-200 mb-3">Imagen del Producto</label>

                <div class="flex items-start gap-4">
                    <div class="w-32 h-32 bg-gray-700 rounded overflow-hidden flex items-center justify-center">
                        <img id="preview-image" src="{{ asset('images/no-image.png') }}" alt="Imagen previa del producto" class="object-cover w-full h-full">
                    </div>

                    <div class="flex-1">
                        <label for="image" class="inline-flex items-center gap-3 cursor-pointer">
                            <input type="file" name="image" id="image"
                                   accept="image/jpg,image/jpeg,image/png,image/webp"
                                   class="hidden" aria-describedby="imgHelp">
                            <span class="inline-block px-4 py-2 rounded-md bg-white text-gray-900 border border-gray-300 hover:bg-gray-100 text-sm font-medium">Elegir archivo</span>
                            <span id="file-name" class="text-gray-300 text-sm">No file chosen</span>
                        </label>

                        <div class="mt-2">
                            <p id="imgHelp" class="text-sm text-gray-400">
                                Formatos permitidos: JPG, JPEG, PNG, WEBP. Tamaño máximo: 4 MB.
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Se recomienda 800x800px para una vista óptima en la vitrina.</p>
                        </div>

                        @error('image')
                            <p class="text-red-400 text-sm mt-2 font-medium" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Estado --}}
            <div class="mb-8">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="status" value="1" {{ old('status', 1) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                    <span class="text-sm text-gray-200">Producto activo</span>
                </label>
                @error('status') <p class="text-red-400 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
            </div>

            {{-- Acciones --}}
            <div class="flex justify-end space-x-4 pt-4 border-t border-white/10">
                <a href="{{ route('admin.products.index') }}"
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
    const realInput = document.getElementById('image');
    const display = document.getElementById('file-name');
    const preview = document.getElementById('preview-image');

    if (!realInput) return;

    realInput.addEventListener('change', () => {
        const f = realInput.files?.[0];
        display.textContent = f ? f.name : 'No file chosen';

        if (!f) {
            preview.src = "{{ asset('images/no-image.png') }}";
            return;
        }

        // Límite coherente con el Request (4 MB)
        if (f.size > 4 * 1024 * 1024) {
            alert('El archivo supera el límite de 4 MB.');
            realInput.value = '';
            display.textContent = 'No file chosen';
            preview.src = "{{ asset('images/no-image.png') }}";
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => { preview.src = e.target.result; };
        reader.readAsDataURL(f);
    });

    const trigger = document.querySelector('label[for="image"]');
    if (trigger) {
        trigger.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                realInput.click();
            }
        });
    }
});
</script>
@endpush
@endsection
