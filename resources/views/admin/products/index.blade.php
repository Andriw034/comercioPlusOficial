@extends('layouts.dashboard')

@section('title', 'Productos Admin — ComercioPlus')

@section('content')
<div class="p-6">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-white">Productos</h1>
    <a href="{{ route('admin.products.create') }}" class="bg-[#FF6000] hover:bg-[#ff741f] text-black font-semibold rounded-full px-5 py-2 transition">
      + Agregar producto
    </a>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
    {{-- Card Productos en promoción --}}
    <div class="rounded-2xl bg-gradient-to-br from-gray-900 to-gray-800 ring-1 ring-white/10 p-6 text-white relative overflow-hidden smooth">
      <div class="absolute inset-y-0 left-0 w-1.5 bg-[#FF6000] rounded-l-xl"></div>
      <h2 class="text-xl font-semibold mb-2">Productos en promoción</h2>
      <p class="mb-4 text-sm text-white/80">Aumenta la visibilidad de ofertas.</p>
      <ul class="mb-4 space-y-1 text-sm">
        <li><span class="inline-block bg-[#FF6000] text-black rounded px-2 py-0.5 mr-2 text-xs font-semibold">-15%</span> Casco integral Pro</li>
        <li><span class="inline-block bg-[#FF6000] text-black rounded px-2 py-0.5 mr-2 text-xs font-semibold">-20%</span> Llantas deportivas</li>
        <li><span class="inline-block bg-[#FF6000] text-black rounded px-2 py-0.5 mr-2 text-xs font-semibold">-10%</span> Pastillas de freno</li>
      </ul>
      <a href="#" class="text-[#FF6000] font-semibold hover:underline smooth">Ver todas →</a>
    </div>

    {{-- Cards de productos --}}
    @foreach ($products as $product)
      <div class="rounded-2xl bg-white/[.06] ring-1 ring-white/10 hover:bg-white/[.08] transition-all duration-300 smooth p-4 flex flex-col">
        <div class="aspect-[16/10] rounded-xl overflow-hidden ring-1 ring-white/10 bg-white/5 mb-4">
          <img src="{{ $product->image_url ?? asset('images/placeholder-product.jpg') }}"
               alt="{{ $product->name }}"
               class="object-cover w-full h-full"
               onerror="this.onerror=null;this.src='{{ asset('images/placeholder-product.jpg') }}';" />
        </div>
        <h3 class="font-semibold text-white mb-1 truncate">{{ $product->name }}</h3>
        <p class="text-white/80 text-sm mb-2">{{ $product->category?->name ?? 'Sin categoría' }}</p>
        @if ($product->price)
          <p class="text-white/90 font-semibold mb-4">${{ number_format($product->price, 2) }}</p>
        @endif
        <div class="mt-auto flex gap-2">
          <button type="button"
                  class="bg-white/90 text-black rounded-full px-4 py-1 font-semibold hover:bg-white/100 transition smooth edit-product-btn"
                  data-product-id="{{ $product->id }}"
                  data-image-url="{{ $product->image_url ?? '' }}">
            Editar
          </button>
          <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('¿Eliminar este producto?');" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white rounded-full px-4 py-1 font-semibold transition smooth">
              Eliminar
            </button>
          </form>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Paginación --}}
  @if (method_exists($products, 'links'))
    <div class="mt-8 flex justify-center">
      {{ $products->links('vendor.pagination.tailwind') }}
    </div>
  @endif

  {{-- Modal para cambiar imagen --}}
  <div id="editImageModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center hidden z-50">
    <div class="bg-neutral-900 rounded-2xl p-6 w-full max-w-lg relative">
      <button onclick="closeEditModal()" class="absolute top-4 right-4 text-white text-2xl font-bold">&times;</button>
      <h2 class="text-white text-xl font-semibold mb-4">Actualizar imagen del producto</h2>

      <form id="editImageForm" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PATCH')
        <input type="hidden" name="product_id" id="modalProductId" value="">

        <div>
          <nav class="flex space-x-4 mb-4" aria-label="Tabs">
            <button type="button" id="tabUrlBtn" class="tab-btn bg-[#FF6000] text-black px-4 py-2 rounded-t-xl font-semibold">Desde URL</button>
            <button type="button" id="tabFileBtn" class="tab-btn text-white px-4 py-2 rounded-t-xl font-semibold">Desde tu PC</button>
          </nav>

          <div id="tabUrl" class="tab-content">
            <label for="image_url" class="block text-white mb-1">URL de la imagen</label>
            <input type="url" name="image_url" id="image_url" placeholder="https://..." class="w-full rounded-xl p-2 bg-neutral-800 text-white border border-white/20 focus:outline-none focus:ring-2 focus:ring-[#FF6000]">
          </div>

          <div id="tabFile" class="tab-content hidden">
            <label for="image_file" class="block text-white mb-1">Selecciona un archivo</label>
            <input type="file" name="image_file" id="image_file" accept="image/*" class="w-full rounded-xl p-2 bg-neutral-800 text-white border border-white/20 focus:outline-none focus:ring-2 focus:ring-[#FF6000]">
          </div>
        </div>

        <div class="mt-4">
          <p class="text-white mb-2">Vista previa:</p>
          <img id="imagePreview" src="" alt="Vista previa" class="w-full max-h-64 object-contain rounded-xl border border-white/20 bg-neutral-800">
        </div>

        <div class="mt-6 flex justify-end gap-4">
          <button type="button" onclick="closeEditModal()" class="px-6 py-2 rounded-full border border-white/20 text-white hover:bg-white/10 transition">Cancelar</button>
          <button type="submit" class="px-6 py-2 rounded-full bg-[#FF6000] text-black font-semibold hover:bg-[#ff741f] transition">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  let currentProductId = null;

  function openEditModal(productId, imageUrl) {
    currentProductId = productId;
    document.getElementById('modalProductId').value = productId;
    document.getElementById('editImageForm').action = '/admin/products/' + productId + '/update-image';
    document.getElementById('image_url').value = imageUrl || '';
    document.getElementById('imagePreview').src = imageUrl || '{{ asset("images/placeholder-product.jpg") }}';
    showTab('url');
    document.getElementById('editImageModal').classList.remove('hidden');
  }

  function closeEditModal() {
    document.getElementById('editImageModal').classList.add('hidden');
    document.getElementById('editImageForm').reset();
    document.getElementById('imagePreview').src = '';
  }

  function showTab(tab) {
    const tabUrl = document.getElementById('tabUrl');
    const tabFile = document.getElementById('tabFile');
    const tabUrlBtn = document.getElementById('tabUrlBtn');
    const tabFileBtn = document.getElementById('tabFileBtn');

    if (tab === 'url') {
      tabUrl.classList.remove('hidden');
      tabFile.classList.add('hidden');
      tabUrlBtn.classList.add('bg-[#FF6000]');
      tabUrlBtn.classList.add('text-black');
      tabUrlBtn.classList.remove('text-white');
      tabFileBtn.classList.remove('bg-[#FF6000]');
      tabFileBtn.classList.remove('text-black');
      tabFileBtn.classList.add('text-white');
    } else {
      tabUrl.classList.add('hidden');
      tabFile.classList.remove('hidden');
      tabUrlBtn.classList.remove('bg-[#FF6000]');
      tabUrlBtn.classList.remove('text-black');
      tabUrlBtn.classList.add('text-white');
      tabFileBtn.classList.add('bg-[#FF6000]');
      tabFileBtn.classList.add('text-black');
      tabFileBtn.classList.remove('text-white');
    }
  }

  document.getElementById('tabUrlBtn').addEventListener('click', () => showTab('url'));
  document.getElementById('tabFileBtn').addEventListener('click', () => showTab('file'));

  document.getElementById('image_url').addEventListener('input', function() {
    const url = this.value;
    if (/^https?:\/\/.+\.(png|jpg|jpeg|webp|gif)$/i.test(url)) {
      document.getElementById('imagePreview').src = url;
    }
  });

  document.getElementById('image_file').addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
      document.getElementById('imagePreview').src = URL.createObjectURL(file);
    }
  });

  // Handle edit button clicks
  document.querySelectorAll('.edit-product-btn').forEach(button => {
    button.addEventListener('click', function() {
      const productId = this.getAttribute('data-product-id');
      const imageUrl = this.getAttribute('data-image-url');
      openEditModal(productId, imageUrl);
    });
</script>
@endsection
