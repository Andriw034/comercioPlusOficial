@if(auth()->check())
<nav class="bg-white dark:bg-gray-900 shadow-sm fixed top-0 left-0 right-0 z-50 border-b border-gray-200 dark:border-gray-700 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">

            <!-- Logo -->
            <div class="flex items-center gap-2">
                <svg class="w-8 h-8 text-[#FF6000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white">Commerce Plus</h1>
            </div>

            <!-- Usuario y botones -->
            <div class="flex items-center gap-4">

                <!-- Botón de Modo Oscuro -->
                <button id="darkModeToggle" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200">
                    <!-- Ícono de Sol (visible en modo oscuro) -->
                    <svg id="sunIcon" class="w-5 h-5 text-yellow-500 hidden" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                    </svg>
                    <!-- Ícono de Luna (visible en modo claro) -->
                    <svg id="moonIcon" class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                    </svg>
                </button>

                <!-- Saludo -->
                <span class="text-gray-900 dark:text-white font-medium hidden sm:inline">
                    👋 Hola, <strong>{{ Auth::user()->name }}</strong>
                </span>

                <!-- Botón de Cerrar Sesión -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="bg-[#FF6000] hover:bg-orange-600 text-white px-4 py-2 rounded-md text-sm font-semibold transition">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Script de Modo Oscuro -->
    <script>
        const html = document.documentElement;
        const darkModeToggle = document.getElementById('darkModeToggle');
        const sunIcon = document.getElementById('sunIcon');
        const moonIcon = document.getElementById('moonIcon');

        // Verificar preferencia almacenada
        if (localStorage.getItem('darkMode') === 'enabled') {
            html.classList.add('dark');
            sunIcon.classList.remove('hidden');
            moonIcon.classList.add('hidden');
        }

        // Alternar modo al hacer clic
        darkModeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');

            if (html.classList.contains('dark')) {
                localStorage.setItem('darkMode', 'enabled');
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
            } else {
                localStorage.setItem('darkMode', 'disabled');
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
            }
        });
    </script>
</nav>
@endif