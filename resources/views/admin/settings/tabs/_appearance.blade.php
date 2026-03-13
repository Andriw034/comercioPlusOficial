<form action="{{ route('admin.settings.update.appearance') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-200 mb-3">Logo actual</label>
            <div class="aspect-square w-32 overflow-hidden rounded-lg border border-gray-600 bg-gray-700">
                <img src="{{ $store->logo_url ?? 'https://placehold.co/200x200?text=Logo' }}" alt="Logo" class="h-full w-full object-contain">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-200 mb-3">Portada actual</label>
            <div class="aspect-video w-48 overflow-hidden rounded-lg border border-gray-600 bg-gray-700">
                <img src="{{ $store->cover_url ?? 'https://placehold.co/400x200?text=Portada' }}" alt="Portada" class="h-full w-full object-cover">
            </div>
        </div>
    </div>

    <div>
        <label for="logo" class="block text-sm font-medium text-gray-200 mb-2">Nuevo logo (PNG/JPG/SVG, máx 2MB)</label>
        <input type="file" id="logo" name="logo" accept="image/*"
               class="w-full px-4 py-3 bg-white text-gray-900 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 @error('logo') ring-red-400 border-red-400 @enderror">
        @error('logo')
            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="cover" class="block text-sm font-medium text-gray-200 mb-2">Nueva portada (PNG/JPG, máx 4MB)</label>
        <input type="file" id="cover" name="cover" accept="image/*"
               class="w-full px-4 py-3 bg-white text-gray-900 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 @error('cover') ring-red-400 border-red-400 @enderror">
        @error('cover')
            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex justify-end">
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg transition">
            Guardar cambios
        </button>
    </div>
</form>
