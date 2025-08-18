<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Crear Tienda - Comercio Plus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { @apply bg-gradient-to-br from-light to-orange-50 min-h-screen; }
        .btn-primary { @apply bg-gradient-to-r from-[#FF6000] to-[#FF8A3D] hover:from-[#CC4C00] hover:to-[#FF6000] text-white font-bold py-3 rounded-xl shadow transition; }
    </style>
</head>
<body class="flex items-center justify-center p-6">
    <div class="bg-white rounded-3xl shadow-2xl border border-orange-100 p-8 max-w-md w-full">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-gradient-to-br from-primary to-orange-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h6m-6 4h6m-6 4h6" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Crea tu Tienda</h1>
            <p class="text-gray-600">Da el primer paso para vender en línea</p>
        </div>

        {{-- CORREGIDO: la ruta ahora es store.store --}}
        <form action="{{ route('store.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="block text-gray-700 font-medium mb-1">Nombre</label>
                <input type="text" name="name" required placeholder="Mi Negocio Online"
                       class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Descripción</label>
                <textarea name="description" rows="3" placeholder="Describe tu negocio"
                          class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none"></textarea>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Logo (opcional)</label>
                <input type="file" name="logo" accept="image/*"
                       class="w-full text-sm file:py-2 file:px-4 file:rounded-md file:bg-orange-100 file:text-orange-700 hover:file:bg-orange-200">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Portada (opcional)</label>
                <input type="file" name="cover" accept="image/*"
                       class="w-full text-sm file:py-2 file:px-4 file:rounded-md file:bg-orange-100 file:text-orange-700 hover:file:bg-orange-200">
            </div>

            <button type="submit" class="btn-primary w-full">
                Crear Tienda y Empezar a Vender
            </button>
        </form>
    </div>
</body>
</html>
