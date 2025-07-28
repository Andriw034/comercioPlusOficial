<nav x-data="{ menuOpen: false }" class="bg-white shadow-sm fixed top-0 left-0 right-0 z-50 border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            
            <!-- Logo -->
            <div class="flex items-center gap-2">
                <x-application-logo class="w-8 h-8 text-[#ff9800]" />
                <h1 class="text-2xl font-black text-[#0f172a]">Commerce Plus</h1>
            </div>

            <!-- Buscador (desktop) -->
            <div class="hidden md:flex flex-1 mx-6">
                <input type="text" placeholder="Buscar productos o categorías..."
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#ff9800] text-sm text-gray-700 placeholder-gray-400">
            </div>

            <!-- Usuario y menú -->
            <div class="flex items-center gap-4">

                <x-nav-menu />

                <span class="text-gray-900 font-medium hidden sm:inline">
                    @if(Auth::check())
                        👋 Hola, <strong>{{ Auth::user()->name }}</strong>
                    @else
                        Invitado
                    @endif
                </span>

                @if(Auth::check())
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="bg-[#ff9800] hover:bg-orange-600 text-white px-4 py-2 rounded-md text-sm font-semibold transition">Cerrar sesión</button>
                </form>
                @endif

            </div>
        </div>
    </div>

    <!-- Buscador móvil -->
    <div class="block md:hidden px-4 pb-4 pt-2">
        <label for="mobile-search" class="sr-only">Buscar</label>
        <div class="relative">
            <input id="mobile-search" type="search" placeholder="Buscar productos, categorías..."
                class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-300 bg-gray-100 placeholder-gray-500 text-gray-700 shadow-inner focus:outline-none focus:ring-2 focus:ring-[#ff9800] transition"
                autocomplete="off" />
            <span class="absolute inset-y-0 left-4 flex items-center text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
            </span>
        </div>
    </div>

    <!-- Script -->
    <script>
        const searchInput = document.getElementById('mobile-search');
        searchInput?.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                const query = encodeURIComponent(this.value.trim());
                if (query) {
                    window.location.href = `/buscar?q=${query}`;
                }
            }
        });
    </script>
</nav>
