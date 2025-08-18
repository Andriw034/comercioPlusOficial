<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ComercioPlus — Crea tu tienda online en minutos | Vende sin complicaciones</title>
  <meta name="description" content="Crea tu tienda online en minutos con ComercioPlus. Gestiona productos, comparte catálogos públicos y empieza a vender sin complicaciones. ¡Registro gratuito!">
  <meta name="keywords" content="tienda online, ecommerce, catálogo digital, vender online, comercio electrónico">
  <meta property="og:title" content="ComercioPlus — Crea tu tienda online en minutos">
  <meta property="og:description" content="Gestiona productos y comparte catálogos públicos elegantes. Empieza a vender hoy mismo.">
  <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">
  <meta property="og:type" content="website">
  <meta name="twitter:card" content="summary_large_image">
  @vite('resources/css/app.css')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    .animate-float {
      animation: float 6s ease-in-out infinite;
    }
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-20px); }
    }
    .animate-pulse-slow {
      animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    .gradient-text {
      background: linear-gradient(90deg, #FF6000, #FF7E47);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
  </style>
</head>
<body class="font-inter text-gray-800 antialiased">

  <!-- Navigation -->
  <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100">
    <nav class="max-w-7xl mx-auto px-4 md:px-6 py-4">
      <div class="flex items-center justify-between">
        <a href="{{ route('welcome') }}" class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-2xl animate-pulse-slow" style="background:linear-gradient(145deg,#FF6000,#FF7E47);"></div>
          <span class="font-bold text-xl tracking-tight">ComercioPlus</span>
        </a>

        <div class="hidden lg:flex items-center gap-8">
          <a href="#features" class="text-sm font-medium hover:text-orange-600 transition">Funciones</a>
          <a href="#como-funciona" class="text-sm font-medium hover:text-orange-600 transition">Cómo funciona</a>
          <a href="#precios" class="text-sm font-medium hover:text-orange-600 transition">Precios</a>
          <a href="{{ route('public.store.show',['slug'=>'demo']) }}" class="text-sm font-medium hover:text-orange-600 transition">Demo</a>
        </div>

        <div class="flex items-center gap-3">
          <a href="{{ route('login') }}" class="text-sm px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 transition">
            Iniciar sesión
          </a>
          <a href="{{ route('register') }}" class="text-sm px-5 py-2.5 rounded-xl text-white font-medium shadow-lg transition hover:shadow-xl" style="background:linear-gradient(90deg,#FF6000,#FF7E47);">
            Crear cuenta
          </a>
        </div>
      </div>
    </nav>
  </header>

  <!-- Hero Section -->
  <section class="relative overflow-hidden bg-gradient-to-br from-gray-900 via-purple-900 to-orange-600">
    <div class="absolute inset-0">
      <div class="absolute inset-0 bg-black/20"></div>
      <div class="absolute top-0 left-0 w-96 h-96 bg-orange-500/20 rounded-full blur-3xl animate-float"></div>
      <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl animate-float" style="animation-delay: 2s;"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 md:px-6 py-20 md:py-32">
      <div class="grid lg:grid-cols-2 gap-12 items-center">
        <div class="text-white space-y-8">
          <div class="space-y-6">
            <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-white/10 backdrop-blur border border-white/20">
              <span class="w-2 h-2 rounded-full mr-2 animate-pulse" style="background:#FF7E47;"></span>
              🚀 Nuevo en ComercioPlus
            </div>
            
            <h1 class="text-4xl md:text-6xl font-extrabold leading-tight">
              <span class="block">Crea tu tienda</span>
              <span class="block gradient-text">online en minutos</span>
            </h1>
            
            <p class="text-xl md:text-2xl text-white/90 leading-relaxed">
              Sube tus productos, comparte tu catálogo público y empieza a vender sin complicaciones. 
              Todo en un solo lugar, sin necesidad de conocimientos técnicos.
            </p>
          </div>

          <div class="space-y-6">
            <div class="flex flex-wrap gap-4">
              <a href="{{ route('register') }}" class="group px-8 py-4 rounded-2xl font-bold text-lg shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 transition-all duration-300 bg-white text-gray-900">
                <span class="flex items-center gap-2">
                  Comenzar gratis
                  <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                  </svg>
                </span>
              </a>
              <a href="{{ route('public.store.show',['slug'=>'demo']) }}" class="group px-8 py-4 rounded-2xl border-2 border-white/30 font-bold text-lg hover:bg-white/10 transition-all">
                <span class="flex items-center gap-2">
                  Ver demo
                  <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                </span>
              </a>
            </div>

            <div class="flex flex-wrap gap-6 text-sm">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>
                <span>Instalación en 2 minutos</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></div>
                <span>Soporte 24/7</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-purple-400 animate-pulse"></div>
                <span>Sin tarjeta de crédito</span>
              </div>
            </div>
          </div>
        </div>

        <div class="relative">
          <div class="absolute -inset-4 bg-gradient-to-r from-orange-400 to-purple-600 rounded-3xl blur-2xl opacity-30"></div>
          <div class="relative bg-white/10 backdrop-blur-xl rounded-3xl p-4 border border-white/20">
            <div class="bg-white rounded-2xl shadow-2xl p-6 space-y-4">
              <div class="flex items-center justify-between">
                <div class="h-8 w-8 rounded-full" style="background:linear-gradient(145deg,#FF6000,#FF7E47);"></div>
                <div class="flex gap-2">
                  <div class="w-3 h-3 rounded-full bg-gray-300"></div>
                  <div class="w-3 h-3 rounded-full bg-gray-300"></div>
                  <div class="w-3 h-3 rounded-full bg-gray-300"></div>
                </div>
              </div>
              
              <div class="space-y-3">
                <div class="h-4 bg-gray-200 rounded-lg w-3/4"></div>
                <div class="grid grid-cols-2 gap-3">
                  <div class="space-y-2">
                    <div class="h-24 rounded-xl" style="background:linear-gradient(135deg,#FFE4D6,#FFD0B8);"></div>
                    <div class="h-3 bg-gray-200 rounded"></div>
                    <div class="h-2 bg-gray-200 rounded w-2/3"></div>
                  </div>
                  <div class="space-y-2">
                    <div class="h-24 rounded-xl" style="background:linear-gradient(135deg,#E9E5FF,#D7D0FF);"></div>
                    <div class="h-3 bg-gray-200 rounded"></div>
                    <div class="h-2 bg-gray-200 rounded w-2/3"></div>
                  </div>
                </div>
              </div>
              
              <div class="flex gap-2">
                <div class="flex-1 h-10 rounded-lg" style="background:linear-gradient(90deg,#FF6000,#FF7E47);"></div>
                <div class="w-10 h-10 bg-gray-200 rounded-lg"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section id="features" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 md:px-6">
      <div class="text-center max-w-3xl mx-auto">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900">Todo lo que necesitas para vender online</h2>
        <p class="mt-4 text-lg text-gray-600">Herramientas poderosas, interfaz simple. Sin complicaciones técnicas.</p>
      </div>

      <div class="mt-16 grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition">
          <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl mb-6" style="background:linear-gradient(135deg,#FFE4D6,#FFD0B8);">🛍️</div>
          <h3 class="text-xl font-bold mb-3">Gestión de Productos</h3>
          <p class="text-gray-600">Crea, edita y organiza tu inventario con imágenes, precios, stock y categorías.</p>
        </div>

        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition">
          <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl mb-6" style="background:linear-gradient(135deg,#E9E5FF,#D7D0FF);">🎨</div>
          <h3 class="text-xl font-bold mb-3">Personalización Total</h3>
          <p class="text-gray-600">Logo, portada, colores y estilos personalizados para tu marca.</p>
        </div>

        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition">
          <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl mb-6" style="background:linear-gradient(135deg,#D1FAE5,#A7F3D0);">📱</div>
          <h3 class="text-xl font-bold mb-3">Catálogo Público</h3>
          <p class="text-gray-600">Comparte tu tienda en una URL elegante, sin necesidad de login.</p>
        </div>

        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition">
          <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl mb-6" style="background:linear-gradient(135deg,#FED7D7,#FEB2B2);">📊</div>
          <h3 class="text-xl font-bold mb-3">Estadísticas</h3>
          <p class="text-gray-600">Conoce cuántas personas visitan tu tienda y qué productos ven más.</p>
        </div>

        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition">
          <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl mb-6" style="background:linear-gradient(135deg,#E0E7FF,#C7D2FE);">⚡</div>
          <h3 class="text-xl font-bold mb-3">Rápido y Seguro</h3>
          <p class="text-gray-600">Servidores rápidos y seguridad de nivel empresarial para tu negocio.</p>
        </div>

        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition">
          <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl mb-6" style="background:linear-gradient(135deg,#FEF3C7,#FDE68A);">🎯</h3>
          <h3 class="text-xl font-bold mb-3">Soporte 24/7</h3>
          <p class="text-gray-600">Estamos aquí para ayudarte cuando lo necesites. Chat y email incluidos.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- How It Works -->
  <section id="como-funciona" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 md:px-6">
      <div class="text-center max-w-3xl mx-auto">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900">Así de fácil es empezar</h2>
        <p class="mt-4 text-lg text-gray-600">En 4 simples pasos tendrás tu tienda online funcionando.</p>
      </div>

      <div class="mt-16">
        <div class="grid lg:grid-cols-4 gap-8">
          <div class="text-center">
            <div class="w-20 h-20 rounded-full flex items-center justify-center text-3xl font-bold text-white mx-auto mb-4" style="background:linear-gradient(135deg,#FF6000,#FF7E47);">1</div>
            <h3 class="text-xl font-bold mb-2">Regístrate</h3>
            <p class="text-gray-600">Crea tu cuenta en 30 segundos</p>
          </div>

          <div class="text-center">
            <div class="w-20 h-20 rounded-full flex items-center justify-center text-3xl font-bold text-white mx-auto mb-4" style="background:linear-gradient(135deg,#FF7E47,#FF6000);">2</div>
            <h3 class="text-xl font-bold mb-2">Crea tu tienda</h3>
            <p class="text-gray-600">Personaliza con tu marca</p>
          </div>

          <div class="text-center">
            <div class="w-20 h-20 rounded-full flex items-center justify-center text-3xl font-bold text-white mx-auto mb-4" style="background:linear-gradient(135deg,#FF6000,#FF7E47);">3</div>
            <h3 class="text-xl font-bold mb-2">Agrega productos</h3>
            <p class="text-gray-600">Sube imágenes y precios</p>
          </div>

          <div class="text-center">
            <div class="w-20 h-20 rounded-full flex items-center justify-center text-3xl font-bold text-white mx-auto mb-4" style="background:linear-gradient(135deg,#FF7E47,#FF6000);">4</div>
            <h3 class="text-xl font-bold mb-2">¡Empieza a vender!</h3>
            <p class="text-gray-600">Comparte tu catálogo</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="py-20 bg-gradient-to-r from-orange-500 to-red-500">
    <div class="max-w-4xl mx-auto px-4 md:px-6 text-center">
      <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
        ¿Listo para empezar a vender online?
      </h2>
      <p class="text-xl text-white/90 mb-8">
        Únete a miles de comerciantes que ya usan ComercioPlus para crecer su negocio.
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="{{ route('register') }}" class="px-8 py-4 bg-white text-orange-600 font-bold rounded-2xl shadow-lg hover:shadow-xl transition">
          Crear cuenta gratis
        </a>
        <a href="{{ route('public.store.show',['slug'=>'demo']) }}" class="px-8 py-4 border-2 border-white text-white font-bold rounded-2xl hover:bg-white/10 transition">
          Ver demo en vivo
        </a>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 md:px-6 py-12">
      <div class="grid md:grid-cols-4 gap-8">
        <div>
          <div class="flex items-center gap-3 mb-4">
            <div class="h-8 w-8 rounded-2xl" style="background:linear-gradient(145deg,#FF6000,#FF7E47);"></div>
            <span class="font-bold text-lg">ComercioPlus</span>
          </div>
          <p class="text-gray-400 text-sm">
            La forma más simple de crear tu tienda online y empezar a vender.
          </p>
        </div>
        
        <div>
          <h4 class="font-semibold mb-4">Producto</h4>
          <ul class="space-y-2 text-sm text-gray-400">
            <li><a href="#features" class="hover:text-white transition">Funciones</a></li>
            <li><a href="#precios" class="hover:text-white transition">Precios</a></li>
            <li><a href="{{ route('public.store.show',['slug'=>'demo']) }}" class="hover:text-white transition">Demo</a></li>
          </ul>
        </div>
        
        <div>
          <h4 class="font-semibold mb-4">Soporte</h4>
          <ul class="space-y-2 text-sm text-gray-400">
            <li><a href="#" class="hover:text-white transition">Ayuda</a></li>
            <li><a href="#" class="hover:text-white transition">Contacto</a></li>
            <li><a href="#" class="hover:text-white transition">Tutoriales</a></li>
          </ul>
        </div>
        
        <div>
          <h4 class="font-semibold mb-4">Legal</h4>
          <ul class="space-y-2 text-sm text-gray-400">
            <li><a href="#" class="hover:text-white transition">Privacidad</a></li>
            <li><a href="#" class="hover:text-white transition">Términos</a></li>
            <li><a href="#" class="hover:text-white transition">Cookies</a></li>
          </ul>
        </div>
      </div>
      
      <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm text-gray-400">
        <p>&copy; 2024 ComercioPlus. Todos los derechos reservados.</p>
      </div>
    </div>
  </footer>

  <script>
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({ behavior: 'smooth' });
        }
      });
    });

    // Add loading animation
    window.addEventListener('load', function() {
      document.body.classList.add('loaded');
    });
  </script>
</body>
</html>
