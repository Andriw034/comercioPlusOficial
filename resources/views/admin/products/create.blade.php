@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-8 bg-gray-900 min-h-[70vh]">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-100 mb-8 drop-shadow-md">Agregar Nuevo Producto</h1>

        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data"
              class="bg-gray-800/70 border border-white/20 p-8 rounded-xl shadow-xl" novalidate>
            @csrf

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

            <div class="mb-6">
                <label for="description" class="block text-sm font-semibold text-gray-200 mb-3">Descripción</label>
                <textarea name="description" id="description" rows="5" placeholder="Ingrese la descripción detallada del producto"
                          class="w-full px-4 py-3 bg-white text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 placeholder-gray-500 @error('description') ring-red-400 border-red-400 @enderror transition-all resize-vertical"
                          required>{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-400 text-sm mt-2 font-medium" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="price" class="block text-sm font-semibold text-gray-200 mb-3">Precio (S/.)</label>
                    <input type="number" name="price" id="price" step="0.01" min="0" value="{{ old('price') }}" placeholder="0.00"
                           class="w-full px-4 py-3 bg-white text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 placeholder-gray-500 @error('price') ring-red-400 border-red-400 @enderror transition-all"
                           required>
                    @error('price')
                        <p class="text-red-400 text-sm mt-2 font-medium" role="alert">{{ $message }}</p>
                    @enderror
                </div>

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

            <div class="mb-6">
                <label for="category_id" class="block text-sm font-semibold text-gray-200 mb-3">Categoría</label>

                @if(isset($categories) && $categories->count())
                    <select name="category_id" id="category_id"
                            class="w-full px-4 py-3 bg-white text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 @error('category_id') ring-red-400 border-red-400 @enderror transition-all"
                            required aria-required="true" aria-describedby="catHelp">
                        <option value="" class="text-gray-500">Seleccionar categoría</option>

                        {{-- Mostrar primero las populares si existen --}}
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
                    {{-- No hay categorías: sugerir crear una --}}
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

            <div class="mb-8">
                <label for="image" class="block text-sm font-semibold text-gray-200 mb-3">Imagen del Producto</label>

                <div class="flex items-start gap-4">
                    <div class="w-32 h-32 bg-gray-700 rounded overflow-hidden flex items-center justify-center">
                        <img id="preview-image" src="{{ old('image_preview') ? asset('storage/' . old('image_preview')) : asset('images/placeholder.png') }}" alt="Imagen previa del producto" class="object-cover w-full h-full">
                    </div>

                    <div class="flex-1">
                        <label for="image" class="inline-flex items-center gap-3 cursor-pointer">
                            <input type="file" name="image" id="image" accept="image/*"
                                   class="hidden" aria-describedby="imgHelp">
                            <span class="inline-block px-4 py-2 rounded-md bg-white text-gray-900 border border-gray-300 hover:bg-gray-100 text-sm font-medium">Elegir archivo</span>
                            <span id="file-name" class="text-gray-300 text-sm">No file chosen</span>
                        </label>

                        <div class="mt-2">
                            <p id="imgHelp" class="text-sm text-gray-400">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB.</p>
                            <p class="text-xs text-gray-500 mt-1">Se recomienda 800x800px para una vista óptima en la vitrina.</p>
                        </div>

                        @error('image')
                        <p class="text-red-400 text-sm mt-2 font-medium" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

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

    // Mostrar nombre del archivo y vista previa
    realInput.addEventListener('change', () => {
        const f = realInput.files[0];
        display.textContent = f ? f.name : 'No file chosen';

        if (!f) {
            preview.src = "{{ asset('images/placeholder.png') }}";
            return;
        }

        if (f.size > 2 * 1024 * 1024) { // 2MB
            alert('El archivo supera el límite de 2MB.');
            realInput.value = '';
            display.textContent = 'No file chosen';
            preview.src = "{{ asset('images/placeholder.png') }}";
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(f);
    });

    // Mejor accesibilidad: permitir focus en el span para activar input con teclado
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
