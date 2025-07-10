<head>

    @vite('resources/css/app.css')

  <!-- ... -->
  <style>
    :root {
      /* Antes: --orange: #FFA14F; */
      --orange: #FF5722;        /* Naranja más intenso */
      --orange-light: #FF784E;  /* Variante clara */
      --bg-sidebar: #1F2937;
    }

    /* Asegúrate de que tus componentes usen estas variables */
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
<body>
    @vite('resources/js/app.js')
</body>
</html>

