<!-- resources/views/store/settings.blade.php -->

<x-app-layout>
    <div class="max-w-4xl mx-auto py-8 px-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Configuración de la Tienda</h2>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('store.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre de la tienda</label>
                <input type="text" name="name" id="name" value="{{ old('name', $store->name ?? '') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea name="description" id="description" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500">{{ old('description', $store->description ?? '') }}</textarea>
            </div>

            <div>
                <label for="color" class="block text-sm font-medium text-gray-700">Color principal</label>
                <input type="color" name="color" id="color" value="{{ old('color', get_setting('store_color', '#f97316')) }}"
                       class="mt-1 h-10 w-24 border border-gray-300 rounded">
            </div>

            <div>
                <label for="logo" class="block text-sm font-medium text-gray-700">Logo</label>
                <input type="file" name="logo" id="logo"
                       class="mt-1 block w-full text-sm text-gray-500">
                @if($store->logo)
                    <img src="{{ asset('storage/' . $store->logo) }}" alt="Logo actual" class="mt-2 h-16">
                @endif
            </div>

            <div>
                <label for="cover" class="block text-sm font-medium text-gray-700">Portada</label>
                <input type="file" name="cover" id="cover"
                       class="mt-1 block w-full text-sm text-gray-500">
                @if($store->cover)
                    <img src="{{ asset('storage/' . $store->cover) }}" alt="Portada actual" class="mt-2 h-32">
                @endif
            </div>

            <div>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-orange-500 border border-transparent rounded-md font-semibold text-white hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
