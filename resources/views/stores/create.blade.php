<x-guest-layout>
  {{-- Fondo negro sólido como en la imagen --}}
  <div class="min-h-[85vh] flex items-center justify-center bg-black">

    <div class="w-full max-w-xl rounded-3xl bg-black text-white
                shadow-2xl ring-1 ring-white/10 p-6 sm:p-8">

      {{-- Encabezado --}}
      <header class="text-center mb-6">
        <h1 class="text-3xl font-bold tracking-tight">Crea tu Tienda</h1>
        <p class="mt-2 text-white/70 text-sm">
          Configura el nombre, logo y portada.<br>
          Podrás editarlos más adelante
        </p>
      </header>

      {{-- Errores de validación --}}
      @if ($errors->any())
        <div class="mb-4 rounded-xl bg-red-500/15 text-red-300 ring-1 ring-red-500/30 px-3 py-2 text-sm">
          <ul class="list-disc pl-4 space-y-1">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- Formulario --}}
      <form method="POST" action="{{ route('store.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        {{-- Nombre --}}
        <div>
          <label class="block text-sm font-medium mb-2">Nombre de la tienda</label>
          <input name="name" required value="{{ old('name') }}"
                 class="w-full rounded-xl bg-white text-gray-900 placeholder-gray-500
                        border border-white/10 focus:border-[#FF6000] focus:ring-[#FF6000] px-3 py-2 text-sm" />
        </div>

        {{-- Descripción --}}
        <div>
          <label class="block text-sm font-medium mb-2">Descripción</label>
          <textarea name="description" rows="3"
                    class="w-full rounded-xl bg-white text-gray-900 placeholder-gray-500
                           border border-white/10 focus:border-[#FF6000] focus:ring-[#FF6000] px-3 py-2 text-sm">{{ old('description') }}</textarea>
        </div>

        {{-- Logo y Portada --}}
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-2">Logo (archivo)</label>
            <input type="file" name="logo" accept="image/*"
                   class="w-full text-white file:mr-2 file:rounded-lg file:border-0
                          file:bg-[#FF6000] file:text-white file:px-3 file:py-1 file:text-xs file:hover:opacity-90" />
            <label class="block mt-2 text-sm font-medium">o URL del logo</label>
            <input type="url" name="logo_url" value="{{ old('logo_url') }}" placeholder="https://..."
                   class="w-full rounded-xl bg-white text-gray-900 placeholder-gray-500
                          border border-white/10 focus:border-[#FF6000] focus:ring-[#FF6000] px-3 py-2 text-sm" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-2">Portada/Fondo</label>
            <input type="file" name="cover" accept="image/*"
                   class="w-full text-white file:mr-2 file:rounded-lg file:border-0
                          file:bg-[#FF6000] file:text-white file:px-3 file:py-1 file:text-xs file:hover:opacity-90" />
            <label class="block mt-2 text-sm font-medium">o URL de portada</label>
            <input type="url" name="cover_url" value="{{ old('cover_url') }}" placeholder="https://..."
                   class="w-full rounded-xl bg-white text-gray-900 placeholder-gray-500
                          border border-white/10 focus:border-[#FF6000] focus:ring-[#FF6000] px-3 py-2 text-sm" />
          </div>
        </div>

        {{-- Acciones --}}
        <div class="flex justify-end gap-2 pt-2">
          <a href="{{ url('/') }}"
             class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-white
                    text-[#FF6000] font-medium text-sm hover:bg-white/90">
            Cancelar
          </a>

          <button type="submit"
                  class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-[#FF6000]
                         text-white font-medium text-sm shadow hover:opacity-95">
            Guardar y continuar
          </button>
        </div>
      </form>
    </div>
  </div>
</x-guest-layout>
