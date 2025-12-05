<form action="{{ route('admin.settings.update.notifications') }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')

    <div>
        <label for="notify_email" class="block text-sm font-medium text-gray-200 mb-2">Email para notificaciones</label>
        <input type="email" id="notify_email" name="notify_email" value="{{ old('notify_email', $store->notify_email) }}"
               class="w-full px-4 py-3 bg-white text-gray-900 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 @error('notify_email') ring-red-400 border-red-400 @enderror"
               placeholder="ejemplo@tienda.com">
        @error('notify_email')
            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
        @enderror
        <p class="text-xs text-gray-400 mt-1">Recibirás notificaciones de pedidos en este email.</p>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg transition">
            Guardar cambios
        </button>
    </div>
</form>
