<aside class="w-64 bg-gray-900 text-white min-h-screen p-6 flex flex-col justify-between">
  <div>
    <h2 class="text-2xl font-bold mb-8">Mi Tienda</h2>
    <ul class="space-y-4">
      {{-- Eliminamos Dashboard --}}
      <li><a href="{{ route('admin.profile') }}"
             class="block py-1 text-lg hover:text-orange-400 transition @if(request()->is('admin/profile')) border-b-2 border-orange-400 @endif">
        Mi Perfil
      </a></li>
      <li><a href="{{ route('admin.store') }}"
             class="block py-1 text-lg hover:text-orange-400 transition @if(request()->is('admin/store')) border-b-2 border-orange-400 @endif">
        Personalizar Tienda
      </a></li>
      <li><a href="{{ route('admin.products.index') }}"
             class="block py-1 text-lg hover:text-orange-400 transition @if(request()->is('admin/products*')) border-b-2 border-orange-400 @endif">
        Mis Productos
      </a></li>
      <li><a href="{{ route('admin.inventory') }}"
             class="block py-1 text-lg hover:text-orange-400 transition @if(request()->is('admin/inventory')) border-b-2 border-orange-400 @endif">
        Inventario
      </a></li>
      <li><a href="{{ route('admin.sales') }}"
             class="block py-1 text-lg hover:text-orange-400 transition @if(request()->is('admin/sales*')) border-b-2 border-orange-400 @endif">
        Ventas
      </a></li>
      <li><a href="{{ route('admin.settings') }}"
             class="block py-1 text-lg hover:text-orange-400 transition @if(request()->is('admin/settings*')) border-b-2 border-orange-400 @endif">
        Configuración
      </a></li>
    </ul>
  </div>
  <div class="text-sm text-gray-500">
    © 2025 Comercio Plus
  </div>
</aside>
