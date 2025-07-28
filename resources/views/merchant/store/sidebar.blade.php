
<aside class="w-64 h-screen bg-white shadow-md border-r fixed top-0 left-0 z-50">
    <div class="p-6 border-b">
        <h1 class="text-xl font-bold text-orange-600">Mi Tienda</h1>
        <p class="text-sm text-gray-500">Panel de comerciante</p>
    </div>

    <nav class="mt-4">
        <ul class="space-y-2 px-4">
            <li>
                <a href="{{ route('merchant.dashboard') }}" class="flex items-center p-2 rounded hover:bg-orange-100 transition {{ request()->routeIs('merchant.dashboard') ? 'bg-orange-50 text-orange-700 font-semibold' : 'text-gray-700' }}">
                    <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6M3 12v8a2 2 0 002 2h14a2 2 0 002-2v-8" />
                    </svg>
                    Inicio
                </a>
            </li>

            <li>
                <a href="{{ route('merchant.store.edit', $store->id) }}" class="flex items-center p-2 rounded hover:bg-orange-100 transition {{ request()->routeIs('merchant.store.edit') ? 'bg-orange-50 text-orange-700 font-semibold' : 'text-gray-700' }}">
                    <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M20 21V9a2 2 0 00-2-2H6a2 2 0 00-2 2v12m16 0H4" />
                    </svg>
                    Editar Tienda
                </a>
            </li>

            <li>
                <a href="{{ route('merchant.products.index') }}" class="flex items-center p-2 rounded hover:bg-orange-100 transition {{ request()->routeIs('merchant.products.*') ? 'bg-orange-50 text-orange-700 font-semibold' : 'text-gray-700' }}">
                    <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0H4" />
                    </svg>
                    Productos
                </a>
            </li>

            <li>
                <a href="{{ route('merchant.orders.index') }}" class="flex items-center p-2 rounded hover:bg-orange-100 transition {{ request()->routeIs('merchant.orders.*') ? 'bg-orange-50 text-orange-700 font-semibold' : 'text-gray-700' }}">
                    <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 17v-6h6v6m-3 4h.01M4 6h16" />
                    </svg>
                    Pedidos
                </a>
            </li>

            <li>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="flex items-center p-2 rounded hover:bg-red-100 transition text-red-600">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1" />
                    </svg>
                    Cerrar sesión
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            </li>
        </ul>
    </nav>
</aside>