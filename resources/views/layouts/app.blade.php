<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>@yield('title', 'Commerce Plus')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  @vite('resources/css/app.css')

  <style>
    :root {
      --orange: #FF5722;
      --orange-light: #FF784E;
      --bg-sidebar: #1F2937;
    }

    .btn-primary {
      background-color: var(--orange);
      color: white;
    }

    .btn-primary:hover {
      background-color: var(--orange-light);
    }

    a.active {
      border-bottom-color: var(--orange);
    }

    a:hover {
      color: var(--orange);
    }
  </style>
</head>

<body class="bg-gray-100 min-h-screen">
  @vite('resources/js/app.js')

  {{-- Navbar --}}
  @include('includes.navbar')

  {{-- Sidebar --}}
  @include('includes.sidebar')

  {{-- Main content --}}
  <main class="min-h-screen flex items-center justify-center px-0 relative overflow-hidden pt-16 pb-16">
    {{-- Fondo animado SVG --}}
    <div class="absolute inset-0 z-0">
      <svg class="w-full h-full" preserveAspectRatio="xMidYMid slice" viewBox="0 0 1440 800" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#9CA3AF" />
            <stop offset="100%" stop-color="#FF884D" />
          </linearGradient>
        </defs>
        <path fill="url(#grad)">
          <animate attributeName="d" dur="15s" repeatCount="indefinite"
            values="
              M0,0 C360,200 1080,200 1440,0 L1440,800 L0,800 Z;
              M0,0 C360,400 1080,0 1440,300 L1440,800 L0,800 Z;
              M0,0 C360,200 1080,400 1440,0 L1440,800 L0,800 Z;
              M0,0 C360,200 1080,200 1440,0 L1440,800 L0,800 Z" />
        </path>
      </svg>
    </div>

    <div class="relative z-10 bg-black/10 shadow-xl rounded-none p-8 w-full h-full backdrop-blur-md text-white max-w-7xl mx-auto">
      @yield('content')

      <button id="btnScrollTop"
        class="fixed bottom-20 right-8 bg-blue-600 text-white px-4 py-2 rounded shadow-lg hover:bg-blue-700 transition-opacity opacity-0 pointer-events-none"
        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })" title="Ir al inicio">↑</button>
    </div>
  </main>

  {{-- Footer --}}
  @include('includes.footer')

  <script>
    const btnScrollTop = document.getElementById("btnScrollTop");

    window.addEventListener("scroll", () => {
      if (window.scrollY > 300) {
        btnScrollTop.classList.remove("opacity-0", "pointer-events-none");
        btnScrollTop.classList.add("opacity-100");
      } else {
        btnScrollTop.classList.add("opacity-0", "pointer-events-none");
        btnScrollTop.classList.remove("opacity-100");
      }
    });
  </script>
</body>

</html>
