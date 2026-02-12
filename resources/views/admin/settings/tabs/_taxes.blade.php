<form action="{{ route('admin.settings.update.taxes') }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="tax_percent" class="block text-sm font-medium text-gray-200 mb-2">Porcentaje de impuesto (%)</label>
            <input type="number" id="tax_percent" name="tax_percent" step="0.01" min="0" max="100" value="{{ old('tax_percent', $store->tax_percent) }}"
                   class="w-full px-4 py-3 bg-white text-gray-900 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 @error('tax_percent') ring-red-400 border-red-400 @enderror">
            @error('tax_percent')
                <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-400 mt-1">Impuesto aplicado a cada producto.</p>
        </div>

        <div class="flex items-center pt-8">
            <input type="checkbox" id="price_includes_tax" name="price_includes_tax" value="1" {{ old('price_includes_tax', $store->price_includes_tax) ? 'checked' : '' }}
                   class="h-4 w-4 text-orange-500 focus:ring-orange-400 border-gray-300 rounded">
            <label for="price_includes_tax" class="ml-2 block text-sm text-gray-200">
                Los precios incluyen impuesto
            </label>
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg transition">
            Guardar cambios
        </button>
    </div>
</form>
