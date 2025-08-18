<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ComercioPlus — Vende en minutos</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-bg-50 text-textc-800 antialiased">

  <!-- NAV -->
  <header class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-gray-100">
    <nav class="max-w-7xl mx-auto px-4 md:px-6 py-3 flex items-center justify-between">
      <a href="{{ route('welcome') }}" class="flex items-center gap-2">
        <!-- LOGO ComercioPlus -->
        <div class="h-9 w-9 rounded-2xl" style="background:linear-gradient(145deg,#FF6000,#FF7E47);"></div>
        <span class="font-semibold text-lg tracking-tight">ComercioPlus</span>
      </a>

      <div class="hidden md:flex items-center gap-6">
        <a href="#features" class="text-sm hover:opacity-80 transition">Funciones</a>
        <a href="#como-funciona" class="text-sm hover:opacity-80 transition">Cómo funciona</a>
        <a href="#precios" class="text-sm hover:opacity-80 transition">Precios</a>
        <a href="{{ route('public.store.show', ['slug' => 'demo']) }}" class="text-sm hover:opacity-80 transition">Demo</a>
      </div>

      <div class="flex items-center gap-3">
        <a href="{{ route('login') }}" class="text-sm px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 transition">
          Iniciar sesión
        </a>
        <a href="{{ route('register') }}"
           class="text-sm px-4 py-2 rounded-xl text-white shadow-md transition"
           style="background:linear-gradient(90deg,#FF6000,#FF7E47);">
          Crear cuenta
        </a>
      </div>
    </nav>
  </header>

  <!-- HERO (naranja + morado) -->
  <section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10">
      <!-- Degradado principal -->
      <div class="absolute inset-0 bg-gradient-to-br from-[#FF6000] via-[#FF7E47] to-[#7E3AF2]"></div>
      <!-- Sombra tenue -->
      <div class="absolute inset-0 bg-black/20"></div>
      <!-- Blobs suaves -->
      <div class="absolute inset-0">
        <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute top-1/2 right-1/4 w-96 h-96 bg-orange-400/30 rounded-full blur-3xl animate-pulse"></div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 md:px-6 py-20 md:py-32 text-white">
      <div class="grid md:grid-cols-2 gap-12 items-center">
        <div class="space-y-8">
          <div class="space-y-4">
            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 backdrop-blur">
              <span class="w-2 h-2 bg-white rounded-full mr-2 animate-pulse"></span>
              Nuevo en ComercioPlus
            </div>
            <h1 class="text-4xl md:text-6xl font-extrabold leading-tight tracking-tight">
              <span class="block">Crea tu tienda</span>
              <span class="block bg-gradient-to-r from-white to-orange-200 bg-clip-text text-transparent">
                online en minutos
              </span>
            </h1>
            <p class="text-xl md:text-2xl text-white/90 leading-relaxed">
              Sube tus productos, comparte tu catálogo público y empieza a vender sin complicaciones.
            </p>
          </div>

          <div class="space-y-6">
            <div class="flex flex-wrap gap-4">
              <a href="{{ route('register') }}"
                 class="group px-8 py-4 rounded-2xl bg-white text-[#FF6000] font-bold text-lg shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 transition-all duration-300">
                <span class="flex items-center gap-2">
                  Comenzar gratis
                  <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                  </svg>
                </span>
              </a>
              <a href="{{ route('public.store.show', ['slug'=>'demo']) }}"
                 class="group px-8 py-4 rounded-2xl border-2 border-white/80 text-white font-bold text-lg hover:bg-white/20 backdrop-blur transform hover:-translate-y-1 transition-all duration-300">
                <span class="flex items-center gap-2">
                  Ver demo
                  <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                </span>
              </a>
            </div>

            <div class="flex items-center gap-6 text-white/90">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                <span class="text-sm font-medium">Sin instalaciones</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-[#7E3AF2] rounded-full animate-pulse"></div>
                <span class="text-sm font-medium">100% seguro</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                <span class="text-sm font-medium">Soporte 24/7</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Mockup con colores de marca -->
        <div class="relative">
          <div class="absolute -inset-4 bg-gradient-to-r from-[#FF6000] to-[#7E3AF2] rounded-3xl blur-2xl opacity-20"></div>
          <div class="relative bg-white/10 backdrop-blur-xl rounded-3xl p-3 border border-white/20">
            <div class="bg-white rounded-2xl shadow-2xl p-6 space-y-4">
              <div class="flex items-center justify-between">
                <div class="h-8 w-8 rounded-full bg-gradient-to-r from-[#FF6000] to-[#7E3AF2]"></div>
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
                    <div class="h-24 bg-gradient-to-br from-orange-100 to-pink-100 rounded-xl"></div>
                    <div class="h-3 bg-gray-200 rounded"></div>
                    <div class="h-2 bg-gray-200 rounded w-2/3"></div>
                  </div>
                  <div class="space-y-2">
                    <div class="h-24 bg-gradient-to-br from-purple-100 to-orange-100 rounded-xl"></div>
                    <div class="h-3 bg-gray-200 rounded"></div>
                    <div class="h-2 bg-gray-200 rounded w-2/3"></div>
                  </div>
                </div>
              </div>

              <div class="flex gap-2">
                <div class="flex-1 h-10 bg-gradient-to-r from-[#FF6000] to-[#7E3AF2] rounded-lg"></div>
                <div class="w-10 h-10 bg-gray-200 rounded-lg"></div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- FEATURES -->
  <section id="features" class="max-w-7xl mx-auto px-4 md:px-6 py-16">
    <div class="text-center max-w-2xl mx-auto">
      <h2 class="text-3xl md:text-4xl font-bold text-textc-900">Todo lo que necesitas</h2>
      <p class="mt-2 text-textc-700">Gestiona productos y comparte un catálogo público elegante.</p>
    </div>

    <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div class="bg-white rounded-2xl shadow p-6 hover:shadow-xl transition border border-transparent hover:border-[#FF6000]/20">
        <div class="text-3xl mb-3">🛒</div>
        <h3 class="font-semibold text-lg text-textc-900">Gestión de productos</h3>
        <p class="mt-1 text-textc-700 text-sm">Crea, edita y organiza tu inventario con imágenes, precios y stock.</p>
      </div>

      <div class="bg-white rounded-2xl shadow p-6 hover:shadow-xl transition border border-transparent hover:border-[#FF6000]/20">
        <div class="text-3xl mb-3">🎨</div>
        <h3 class="font-semibold text-lg text-textc-900">Personalización visual</h3>
        <p class="mt-1 text-textc-700 text-sm">Sube logo y portada, y elige tu color primario (naranja por defecto).</p>
      </div>

      <div class="bg-white rounded-2xl shadow p-6 hover:shadow-xl transition border border-transparent hover:border-[#FF6000]/20">
        <div class="text-3xl mb-3">🌍</div>
        <h3 class="font-semibold text-lg text-textc-900">Catálogo público</h3>
        <p class="mt-1 text-textc-700 text-sm">Comparte tu tienda en <code class="bg-bg-100 px-1 rounded">/tienda/{slug}</code> sin login.</p>
      </div>

      <div class="bg-white rounded-2xl shadow p-6 hover:shadow-xl transition border border-transparent hover:border-[#FF6000]/20">
        <div class="text-3xl mb-3">🔔</div>
        <h3 class="font-semibold text-lg text-textc-900">Feedback claro</h3>
        <p class="mt-1 text-textc-700 text-sm">Banners de éxito/error suaves y accesibles.</p>
      </div>

      <div class="bg-white rounded-2xl shadow p-6 hover:shadow-xl transition border border-transparent hover:border-[#FF6000]/20">
        <div class="text-3xl mb-3">⚙️</div>
        <h3 class="font-semibold text-lg text-textc-900">Panel de configuración</h3>
        <p class="mt-1 text-textc-700 text-sm">Cambia logo, portada, colores y modo claro/oscuro.</p>
      </div>

      <div class="bg-white rounded-2xl shadow p-6 hover:shadow-xl transition border border-transparent hover:border-[#FF6000]/20">
        <div class="text-3xl mb-3">📱</div>
        <h3 class="font-semibold text-lg text-textc-900">Responsive</h3>
        <p class="mt-1 text-textc-700 text-sm">Diseño fluido en móviles, tablets y escritorio.</p>
      </div>
    </div>
  </section>

  <!-- CÓMO FUNCIONA -->
  <section id="como-funciona" class="max-w-7xl mx-auto px-4 md:px-6 py-16">
    <div class="grid lg:grid-cols-2 gap-10 items-center">
      <div class="bg-white rounded-3xl shadow-xl p-6">
        <ol class="space-y-4">
          <li class="flex gap-4">
            <div class="h-8 w-8 rounded-full flex items-center justify-center text-white bg-[#FF6000]">1</div>
            <div>
              <h4 class="font-semibold text-textc-900">Regístrate y elige tu rol</h4>
              <p class="text-sm text-textc-700">Comerciante o cliente. El comerciante será guiado a crear su tienda.</p>
            </div>
          </li>
          <li class="flex gap-4">
            <div class="h-8 w-8 rounded-full flex items-center justify-center text-white bg-[#FF6000]">2</div>
            <div>
              <h4 class="font-semibold text-textc-900">Crea tu tienda</h4>
              <p class="text-sm text-textc-700">Nombre, logo, portada, color primario (naranja por defecto) y ¡listo!</p>
            </div>
          </li>
          <li class="flex gap-4">
            <div class="h-8 w-8 rounded-full flex items-center justify-center text-white bg-[#FF6000]">3</div>
            <div>
              <h4 class="font-semibold text-textc-900">Agrega productos</h4>
              <p class="text-sm text-textc-700">Categoría → nombre, precio, descripción, imagen, stock.</p>
            </div>
          </li>
          <li class="flex gap-4">
            <div class="h-8 w-8 rounded-full flex items-center justify-center text-white bg-[#FF6000]">4</div>
            <div>
              <h4 class="font-semibold text-textc-900">Comparte tu catálogo</h4>
              <p class="text-sm text-textc-700">Tu tienda pública queda disponible en <code class="bg-bg-100 px-1 rounded">/tienda/{slug}</code>.</p>
            </div>
          </li>
        </ol>
        <div class="mt-6">
          <a href="{{ route('register') }}"
             class="inline-flex items-center gap-2 rounded-xl px-5 py-3 text-white"
             style="background:linear-gradient(90deg,#FF6000,#7E3AF2);">
            Empezar ahora
            <span>→</span>
          </a>
        </div>
      </div>

      <div class="grid sm:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow p-6">
          <div class="text-3xl font-extrabold text-textc-900">+99%</div>
          <p class="text-sm text-textc-700">de usuarios integran su tienda en el primer día</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-6">
          <div class="text-3xl font-extrabold text-textc-900">Minutos</div>
          <p class="text-sm text-textc-700">para publicar tus primeros productos</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-6">
          <div class="text-3xl font-extrabold text-textc-900">0</div>
          <p class="text-sm text-textc-700">instalaciones complejas • todo es web</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-6">
          <div class="text-3xl font-extrabold text-textc-900">∞</div>
          <p class="text-sm text-textc-700">posibilidades para tu marca</p>
        </div>
      </div>
    </div>
  </section>

  <!-- PRECIOS -->
  <section id="precios" class="max-w-7xl mx-auto px-4 md:px-6 py-16">
    <div class="text-center max-w-2xl mx-auto">
      <h2 class="text-3xl md:text-4xl font-bold text-textc-900">Precios simples</h2>
      <p class="mt-2 text-textc-700">Empieza gratis. Escala cuando lo necesites.</p>
    </div>

    <div class="mt-10 grid md:grid-cols-3 gap-6">
      <!-- Free -->
      <div class="bg-white rounded-2xl shadow p-6 flex flex-col border border-transparent hover:border-[#FF6000]/20 transition">
        <h3 class="font-semibold text-lg">Gratis</h3>
        <div class="mt-2 text-3xl font-extrabold">0€</div>
        <ul class="mt-4 text-sm text-textc-700 space-y-2">
          <li>• Catálogo público</li>
          <li>• Hasta 20 productos</li>
          <li>• Personalización básica</li>
        </ul>
        <a href="{{ route('register') }}" class="mt-6 rounded-xl px-4 py-2 text-white text-center"
           style="background:#FF6000;">Crear cuenta</a>
      </div>

      <!-- Pro -->
      <div class="bg-white rounded-2xl shadow-xl p-6 ring-2 ring-[rgba(255,96,0,0.15)] scale-[1.02] border border-transparent hover:border-[#7E3AF2]/20 transition">
        <h3 class="font-semibold text-lg">Pro</h3>
        <div class="mt-2 text-3xl font-extrabold">19€/mes</div>
        <ul class="mt-4 text-sm text-textc-700 space-y-2">
          <li>• Productos ilimitados</li>
          <li>• Dominio personalizado</li>
          <li>• Prioridad en soporte</li>
        </ul>
        <a href="{{ route('register') }}" class="mt-6 rounded-xl px-4 py-2 text-white inline-block"
           style="background:linear-gradient(90deg,#FF6000,#7E3AF2);">Probar Pro</a>
      </div>

      <!-- Business -->
      <div class="bg-white rounded-2xl shadow p-6 flex flex-col border border-transparent hover:border-[#FF6000]/20 transition">
        <h3 class="font-semibold text-lg">Business</h3>
        <div class="mt-2 text-3xl font-extrabold">49€/mes</div>
        <ul class="mt-4 text-sm text-textc-700 space-y-2">
          <li>• Equipo y roles</li>
          <li>• Integraciones avanzadas</li>
          <li>• SLA y soporte dedicado</li>
        </ul>
        <a href="{{ route('register') }}" class="mt-6 rounded-xl px-4 py-2 text-white text-center"
           style="background:#FF6000;">Contactar ventas</a>
      </div>
    </div>
  </section>

  <!-- CTA FINAL -->
  <section class="max-w-7xl mx-auto px-4 md:px-6 py-16">
    <div class="rounded-3xl px-6 py-10 md:px-10 md:py-14 text-white text-center shadow-xl"
         style="background:linear-gradient(90deg,#FF6000,#7E3AF2);">
      <h3 class="text-2xl md:text-3xl font-extrabold">¿Listo para empezar?</h3>
      <p class="mt-2 text-white/90">Crea tu cuenta y lanza tu tienda en minutos.</p>
      <div class="mt-6 flex justify-center gap-3">
        <a href="{{ route('register') }}" class="px-6 py-3 rounded-xl bg-white text-textc-900 font-medium shadow hover:opacity-90 transition">
          Crear cuenta
        </a>
        <a href="{{ route('login') }}" class="px-6 py-3 rounded-xl border border-white/80 text-white hover:bg-white/10 transition">
          Ya tengo cuenta
        </a>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="border-t border-gray-100">
    <div class="max-w-7xl mx-auto px-4 md:px-6 py-8 text-sm text-textc-700 flex flex-col md:flex-row items-center justify-between gap-3">
      <p>© {{ date('Y') }} ComercioPlus. Todos los derechos reservados.</p>
      <div class="flex items-center gap-4">
        <a href="#" class="hover:opacity-80">Términos</a>
        <a href="#" class="hover:opacity-80">Privacidad</a>
        <a href="mailto:soporte@comercio.plus" class="hover:opacity-80">Soporte</a>
      </div>
    </div>
  </footer>

</body>
</html>
