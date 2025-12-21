<form action="{{ route('admin.settings.update.shipping') }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="shipping_radius_km" class="block text-sm font-medium text-gray-200 mb-2">Radio de envío (km)</label>
            <input type="number" id="shipping_radius_km" name="shipping_radius_km" step="0.1" min="0" value="{{ old('shipping_radius_km', $store->shipping_radius_km) }}"
                   class="w-full px-4 py-3 bg-white text-gray-900 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 @error('shipping_radius_km') ring-red-400 border-red-400 @enderror">
            @error('shipping_radius_km')
                <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-400 mt-1">Distancia máxima para entregas. 0 = sin límite.</p>
        </div>

        <div>
            <label for="shipping_base_cost" class="block text-sm font-medium text-gray-200 mb-2">Costo base de envío (S/.)</label>
            <input type="number" id="shipping_base_cost" name="shipping_base_cost" step="0.01" min="0" value="{{ old('shipping_base_cost', $store->shipping_base_cost) }}"
                   class="w-full px-4 py-3 bg-white text-gray-900 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 @error('shipping_base_cost') ring-red-400 border-red-400 @enderror">
            @error('shipping_base_cost')
                <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-400 mt-1">Costo mínimo por envío.</p>
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg transition">
            Guardar cambios
        </button>
    </div>
</form>
