<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Dashboard - Comercio Plus</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Script para toggle sidebar en móviles
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }
    </script>
</head>

<body class="flex h-screen bg-gray-100 overflow-hidden">

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-white shadow-md flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-30">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="text-2xl font-bold text-gray-900">Comercio</span>
                <span class="bg-[#ff9800] text-white rounded px-2 font-bold text-xl">+</span>
            </div>
            <button class="md:hidden text-gray-700 focus:outline-none" onclick="toggleSidebar()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded hover:bg-[#ff9800] hover:text-white">Dashboard</a>
            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 rounded hover:bg-[#ff9800] hover:text-white">Perfil</a>
            <a href="{{ route('store.index') }}" class="block px-4 py-2 rounded hover:bg-[#ff9800] hover:text-white">Mi Tienda</a>
            <a href="{{ route('products.index') }}" class="block px-4 py-2 rounded hover:bg-[#ff9800] hover:text-white">Productos</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 rounded hover:bg-red-600 hover:text-white text-red-600">Cerrar sesión</button>
            </form>
        </nav>
    </aside>

    <!-- Main content wrapper -->
    <div class="flex flex-col flex-1 min-h-screen md:ml-64">
        <!-- Header -->
        <header class="flex items-center justify-between bg-white shadow p-4">
            <button class="md:hidden text-gray-700 focus:outline-none" onclick="toggleSidebar()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <h1 class="text-xl font-bold text-gray-900">Comercio Plus - Panel de Administración</h1>
            <div>
                @php
                    use App\Models\Setting;
                    $storeSetting = Setting::where('key', 'store_config')->first();
                    $store = $storeSetting ? json_decode($storeSetting->value) : null;
                @endphp
                @if($store && !empty($store->logo))
                    <img src="{{ asset('storage/' . $store->logo) }}" alt="Logo Tienda" class="h-10 w-auto rounded inline-block mr-4 align-middle">
                @endif
                <a href="{{ route('store.index') }}" class="inline-block bg-primary text-white px-3 py-1 rounded hover:bg-primary-light transition-colors duration-300 align-middle">Configurar Logo</a>
            </div>
        </header>

        <!-- Main content -->
        <main class="flex-1 p-6 overflow-auto bg-gray-50">
            @yield('content')
        </main>
    </div>

</body>

</html>
