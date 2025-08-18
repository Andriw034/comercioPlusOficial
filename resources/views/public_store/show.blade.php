<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ $store->name }} - Tienda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary: {{ $store->primary_color ?? '#FF6000' }};
            --bg: {{ $store->background_color ?? '#f9f9f9' }};
            --text: {{ $store->text_color ?? '#333333' }};
        }
        body {
            background-color: var(--bg);
            color: var(--text);
        }
        .btn-primary {
            background-color: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="min-h-screen">

    <!-- Banner de portada -->
    <div class="relative h-64 bg-gray-200">
        @if($store->cover_image)
            <img src="{{ asset('storage/' . $store->cover_image) }}" alt="Portada" class="w-full h-full object-cover">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
        
        <!-- Logo encima de la portada -->
        @if($store->logo)
            <div class="absolute -bottom-16 left-6 w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-lg">
                <img src="{{ asset('storage/' . $store->logo) }}" alt="Logo" class="w-full h-full object-cover">
            </div>
        @endif
    </div>

    <!-- Contenido -->
    <div class="pt-20 px-6 pb-12 max-w-6xl mx-auto">
        
        <!-- Nombre de la tienda -->
        <h1 class="text-4xl font-black mt-4" style="color: var(--primary)">
            {{ $store->name }}
        </h1>

        <!-- Descripción -->
        @if($store->description)
            <p class="text-lg mt-2 text-gray-700">
                {{ $store->description }}
            </p>
        @endif

        <!-- Productos -->
        <h2 class="text-2xl font-semibold mt-12 mb-6" style="color: var(--primary)">
            🛍️ Productos Disponibles
        </h2>

        @if($store->user->products->isEmpty())
            <p class="text-center text-gray-500 py-8">Esta tienda aún no tiene productos.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($store->user->products as $product)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden transform hover:scale-105 transition">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-48 object-cover">
                        @endif
                        <div class="p-5">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $product->name }}</h3>
                            <p class="text-gray-600 dark:text-gray-300 mt-1">{{ $product->description }}</p>
                            <p class="text-2xl font-bold mt-3" style="color: var(--primary)">
                                ${{ number_format($product->price, 0, ',', '.') }}
                            </p>

                            <!-- Botón de WhatsApp -->
                            <a 
                                href="https://wa.me/{{ $store->user->phone ?? '573000000000' }}?text=Hola,%20quiero%20comprar%20{{ urlencode($product->name) }}"
                                target="_blank"
                                class="btn-primary block text-center mt-4"
                            >
                                📱 Contactar por WhatsApp
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</body>
</html>