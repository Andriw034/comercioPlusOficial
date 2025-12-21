<form action="{{ route('admin.settings.update.general') }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-200 mb-2">Teléfono</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $store->phone) }}"
                   class="w-full px-4 py-3 bg-white text-gray-900 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 @error('phone') ring-red-400 border-red-400 @enderror">
            @error('phone')
                <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="whatsapp" class="block text-sm font-medium text-gray-200 mb-2">WhatsApp</label>
            <input type="text" id="whatsapp" name="whatsapp" value="{{ old('whatsapp', $store->whatsapp) }}"
                   class="w-full px-4 py-3 bg-white text-gray-900 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 @error('whatsapp') ring-red-400 border-red-400 @enderror">
            @error('whatsapp')
                <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="support_email" class="block text-sm font-medium text-gray-200 mb-2">Email de soporte</label>
        <input type="email" id="support_email" name="support_email" value="{{ old('support_email', $store->support_email) }}"
               class="w-full px-4 py-3 bg-white text-gray-900 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 @error('support_email') ring-red-400 border-red-400 @enderror">
        @error('support_email')
            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="address" class="block text-sm font-medium text-gray-200 mb-2">Dirección</label>
            <input type="text" id="address" name="address" value="{{ old('address', $store->address) }}"
                   class="w-full px-4 py-3 bg-white text-gray-900 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 @error('address') ring-red-400 border-red-400 @enderror">
            @error('address')
                <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="city" class="block text-sm font-medium text-gray-200 mb-2">Ciudad</label>
            <input type="text" id="city" name="city" value="{{ old('city', $store->city) }}"
                   class="w-full px-4 py-3 bg-white text-gray-900 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 @error('city') ring-red-400 border-red-400 @enderror">
            @error('city')
                <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="flex items-center">
        <input type="checkbox" id="is_visible" name="is_visible" value="1" {{ old('is_visible', $store->is_visible) ? 'checked' : '' }}
               class="h-4 w-4 text-orange-500 focus:ring-orange-400 border-gray-300 rounded">
        <label for="is_visible" class="ml-2 block text-sm text-gray-200">
            Tienda visible al público
        </label>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg transition">
            Guardar cambios
        </button>
    </div>
</form>
