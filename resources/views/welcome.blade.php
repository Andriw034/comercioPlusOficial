<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Comercio Plus - Tienda de Motos y Repuestos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        zapote: '#ff6f00',
                    },
                },
            },
        };
    </script>
</head>

<body class="bg-white text-gray-800 font-sans leading-relaxed">

    <!-- PORTADA -->
    <section id="hero"
        class="relative min-h-screen text-white flex flex-col justify-center items-center text-center px-6 overflow-hidden">
        <!-- Fondo cambiante -->
        <div id="background-slider" class="absolute inset-0 bg-cover bg-center transition-all duration-1000 z-0 rounded-lg"></div>

        <!-- Capa oscura para mejor contraste -->
        <div class="absolute inset-0 bg-black bg-opacity-50 z-10 rounded-lg"></div>

        <!-- Contenido -->
        <div class="z-20 max-w-4xl px-4">
            <h1 class="text-6xl font-extrabold drop-shadow-lg animate-fade-in mb-4">Comercio Plus</h1>
            <p class="text-2xl mt-4 animate-fade-in-delay max-w-xl mx-auto">Tu tienda confiable de motos y repuestos</p>
            <a href="{{ route('register') }}" target="_blank" class="mt-10 animate-bounce inline-block">
                <button
                    class="bg-white text-zapote font-bold py-4 px-8 rounded-full shadow-lg hover:bg-orange-100 transition-all duration-300">
                    Ir a la tienda
                </button>
            </a>
        </div>
    </section>

    <!-- BENEFICIOS -->
    <section class="py-24 bg-gray-50 text-center px-6">
        <h2 class="text-4xl font-bold mb-12">¿Por qué Comercio Plus?</h2>
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-12">
            <div class="bg-white shadow-xl p-8 rounded-lg hover:scale-105 transition-transform duration-300">
                <img src="https://cdn-icons-png.flaticon.com/512/2972/2972185.png" class="h-24 mx-auto mb-6" />
                <h3 class="text-2xl font-bold mb-3">Repuestos de Alta Calidad</h3>
                <p class="text-gray-700 text-lg">Trabajamos con las marcas más confiables del mercado.</p>
            </div>
            <div class="bg-white shadow-xl p-8 rounded-lg hover:scale-105 transition-transform duration-300">
                <img src="https://cdn-icons-png.flaticon.com/512/1048/1048310.png" class="h-24 mx-auto mb-6" />
                <h3 class="text-2xl font-bold mb-3">Motos Modernas</h3>
                <p class="text-gray-700 text-lg">Modelos actuales a precios competitivos.</p>
            </div>
            <div class="bg-white shadow-xl p-8 rounded-lg hover:scale-105 transition-transform duration-300">
                <img src="https://cdn-icons-png.flaticon.com/512/747/747376.png" class="h-24 mx-auto mb-6" />
                <h3 class="text-2xl font-bold mb-3">Atención Personalizada</h3>
                <p class="text-gray-700 text-lg">Estamos para ayudarte en todo momento.</p>
            </div>
        </div>
    </section>

    <!-- PRODUCTOS -->
    <section class="py-24 px-6 bg-white">
        <h2 class="text-4xl font-bold text-center mb-12">Productos Destacados</h2>
        <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-12">
            <div class="bg-gray-100 p-6 rounded-lg shadow hover:shadow-xl transition duration-300">
                <img src="/images/ProductosDestacados/img1.jpg" class="mb-6 rounded-lg" />
                <h3 class="font-bold text-xl mb-2">Moto 250cc</h3>
                <p class="text-zapote font-bold text-lg">$5.200.000</p>
            </div>
            <div class="bg-gray-100 p-6 rounded-lg shadow hover:shadow-xl transition duration-300">
                <img src="/images/ProductosDestacados/img2.jpg" class="mb-6 rounded-lg" />
                <h3 class="font-bold text-xl mb-2">Repuesto Premium</h3>
                <p class="text-zapote font-bold text-lg">$220.000</p>
            </div>
            <div class="bg-gray-100 p-6 rounded-lg shadow hover:shadow-xl transition duration-300">
                <img src="/images/ProductosDestacados/img3.jpg" class="mb-6 rounded-lg" />
                <h3 class="font-bold text-xl mb-2">Casco Pro</h3>
                <p class="text-zapote font-bold text-lg">$300.000</p>
            </div>
        </div>
    </section>

    <!-- TESTIMONIOS -->
    <section class="bg-orange-50 py-24 text-center px-6">
        <h2 class="text-4xl font-bold mb-12">Lo que dicen nuestros clientes</h2>
        <div class="max-w-4xl mx-auto space-y-12 text-lg text-gray-700 italic">
            <p>“Entrega rápida y repuestos de calidad. ¡Volveré a comprar sin duda!”<br><span
                    class="not-italic font-semibold">– Andrés M.</span></p>
            <p>“ la mejor Atención, y un servicio excelente.”<br><span class="not-italic font-semibold">–
                    Daniela G.</span></p>
        </div>
    </section>

    <!-- LLAMADO A LA ACCIÓN FINAL -->
    <section class="bg-zapote text-white text-center py-24 px-6">
        <h2 class="text-4xl font-bold mb-8">¡Consigue tus repuestos de forma ágil y segura!</h2>
        <p class="text-xl mb-8">Haz clic para explorar todo nuestro catálogo</p>
        <a href="https://tutienda.com" target="_blank">
            <button
                class="bg-white text-zapote font-bold py-4 px-8 rounded-full shadow hover:bg-orange-100 transition-all duration-300">
                Ir a la tienda
            </button>
        </a>
    </section>

    <!-- PIE DE PÁGINA -->
    <footer class="bg-gray-900 text-white py-8 text-center">
        <p>© 2025 Comercio Plus. Todos los derechos reservados.</p>
        <div class="mt-4 flex justify-center space-x-8 text-zapote">
        <a href="#" class="hover:underline">Facebook</a>
        <a href="#" class="hover:underline">Instagram</a>
        <a href="#" class="hover:underline">WhatsApp</a>
        </div>
    </footer>

    <!-- Animaciones -->
    <style>
        .animate-fade-in {
            animation: fadeIn 1s ease-in-out forwards;
            opacity: 0;
        }

        .animate-fade-in-delay {
            animation: fadeIn 1.5s ease-in-out forwards;
            opacity: 0;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }

            from {
                opacity: 0;
                transform: translateY(20px);
            }
        }
    </style>

    <script>
        const images = [
            "/images/portada/imag3.webp",
            "/images/portada/imag3.webp",
            "/images/portada/imag3.webp"
        ];

        let index = 0;
        const slider = document.getElementById("background-slider");

        function changeBackground() {
            slider.style.backgroundImage = `url('${images[index]}')`;
            index = (index + 1) % images.length;
        }

        // Cambia imagen cada 5 segundos
        changeBackground(); // primera
        setInterval(changeBackground, 5000);
    </script>
</body>

</html>
