<form action="{{ route('admin.settings.update.payments') }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')

    <div>
        <label for="payment_instructions" class="block text-sm font-medium text-gray-200 mb-2">Instrucciones de pago</label>
        <textarea id="payment_instructions" name="payment_instructions" rows="6"
                  class="w-full px-4 py-3 bg-white text-gray-900 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 @error('payment_instructions') ring-red-400 border-red-400 @enderror"
                  placeholder="Ej: Transferencia bancaria a cuenta XXX, Yape a número XXX, etc.">{{ old('payment_instructions', $store->payment_instructions) }}</textarea>
        @error('payment_instructions')
            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
        @enderror
        <p class="text-xs text-gray-400 mt-1">Estas instrucciones se mostrarán al cliente en el checkout.</p>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg transition">
            Guardar cambios
        </button>
    </div>
</form>
