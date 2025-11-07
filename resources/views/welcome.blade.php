<!-- resources/views/welcome.blade.php -->
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ComercioPlus</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="welcome-page antialiased">
  <div class="welcome-shell">
    <header class="welcome-header">
      <img src="{{ asset('assets/comercioplus-logo.png') }}" alt="ComercioPlus" class="welcome-logo" loading="lazy">
      <nav class="welcome-nav">
        <a href="#demo-preview" class="welcome-nav__link">Catálogo</a>
        <a href="{{ route('login') }}" class="welcome-nav__link">Iniciar sesión</a>
        <a href="{{ route('register') }}" class="welcome-nav__link welcome-nav__link--outline">Crear cuenta</a>
      </nav>
    </header>

    <main class="welcome-main">
      <section class="welcome-hero">
        <div class="welcome-grid">
          <div class="welcome-copy">
            <p class="welcome-eyebrow">Catálogo inteligente para tiendas de repuestos</p>
            <h1 class="welcome-title">
              Tu vitrina de <span class="welcome-title__accent">repuestos y accesorios</span> en minutos
            </h1>
            <p class="welcome-lead">
              Crea tu tienda, carga productos, organízalos por categorías y comparte tu catálogo profesional con tus clientes.
            </p>
            <div class="welcome-cta">
              <a href="{{ route('register') }}" class="btn-pill btn-pill--primary">Empezar gratis</a>
              <a href="{{ route('login') }}" class="btn-pill btn-pill--dark" data-login-cta>Iniciar sesión</a>
            </div>
            <ul class="welcome-list">
              <li>Catálogo limpio y veloz</li>
              <li>Carga por categorías (llantas, frenos, cascos, etc.)</li>
              <li>Comparte tu tienda en redes o WhatsApp</li>
            </ul>
          </div>

          <div id="demo-preview" class="cp-preview-panel">
            <div class="cp-preview-head">
              <div>
                <p class="cp-preview-eyebrow">Vista previa de tu catálogo</p>
              </div>
              <span class="cp-preview-demo">demo</span>
            </div>

            <div class="cp-preview-search">
              <label for="busqueda" class="sr-only">Buscar productos</label>
              <input id="busqueda" type="search" placeholder="Buscar repuestos de moto"
                     class="cp-preview-input" autocomplete="off" />
              <button id="btnBuscar" type="button" class="btn-pill btn-pill--small btn-pill--search">Buscar</button>
            </div>

            <p id="error" class="cp-preview-error" role="status" aria-live="polite"></p>

            <div id="lista" data-empty="No encontramos productos para esta búsqueda."
                 class="cp-preview-grid"></div>

            <div class="cp-preview-subhead">
              <p>Mis motos favoritas</p>
              <button id="btnClearFav" type="button" class="cp-preview-chip">Limpiar</button>
            </div>

            <div id="favoritos" data-empty="Agrega productos para mostrarlos aquí."
                 class="cp-preview-grid cp-preview-grid--mini"></div>

            <a href="{{ route('register') }}" class="btn-pill btn-pill--primary cp-preview-cta">
              Crear mi tienda
            </a>
          </div>
        </div>
      </section>

      <section class="welcome-section">
        <div class="welcome-section__inner">
          <h2 class="welcome-section__title">Categorías populares</h2>
          <p class="welcome-section__copy">
            Llantas y rines · Frenos y discos · Transmisión y cadenas · Aceites y lubricantes · Iluminación / eléctricos
          </p>
        </div>
      </section>
    </main>

    <footer class="welcome-footer">
      &copy; {{ date('Y') }} ComercioPlus.
    </footer>
  </div>

  <script>
    document.documentElement.dataset.placeholder = @json(asset('assets/placeholders/product.png'));
  </script>
</body>
</html>
