<footer class="mt-16 text-white" style="background-color: var(--cp-deep);">
  <div class="mx-auto max-w-7xl px-6 py-14 grid gap-10 md:grid-cols-3">
    <!-- Columna 1 -->
    <div>
      <h4 class="text-xl font-semibold">ComercioPlus</h4>
      <div class="mt-2 h-1 w-14 rounded-full" style="background: var(--cp-primary);"></div>
      <div class="mt-6 space-y-2 text-sm text-white/80">
        <p class="font-medium">Líneas de Atención</p>
        <p>WhatsApp: <a href="https://wa.me/573332508927" class="underline">+57 3332508927</a></p>
        <p>WhatsApp 2: <a href="https://wa.me/573332500168" class="underline">+57 3332500168</a></p>
      </div>
    </div>

    <!-- Columna 2 -->
    <div>
      <h4 class="text-xl font-semibold">Políticas</h4>
      <div class="mt-2 h-1 w-14 rounded-full" style="background: var(--cp-primary);"></div>
      <ul class="mt-6 space-y-3 text-sm text-white/80">
        <li class="flex items-center gap-2">✅ <a href="#" class="hover:underline">Términos y Condiciones</a></li>
        <li class="flex items-center gap-2">✅ <a href="#" class="hover:underline">Política de Tratamiento de Datos</a></li>
        <li class="flex items-center gap-2">✅ <a href="{{ route('contact') ?? '#' }}" class="hover:underline">Contáctanos</a></li>
      </ul>
    </div>

    <!-- Columna 3 -->
    <div>
      <h4 class="text-xl font-semibold">Buscar</h4>
      <div class="mt-2 h-1 w-14 rounded-full" style="background: var(--cp-primary);"></div>

      <form action="{{ route('search') ?? '#' }}" method="GET" class="mt-6 flex overflow-hidden rounded-xl">
        <input type="text" name="q" placeholder="Buscar …" class="w-full px-4 py-3 text-slate-900">
        <button class="px-5 text-white font-medium"
                style="background: linear-gradient(90deg, var(--cp-primary), var(--cp-primary-2));">
          Buscar
        </button>
      </form>
    </div>
  </div>

  <div class="border-t border-white/10">
    <div class="mx-auto max-w-7xl px-6 py-6 flex flex-col md:flex-row items-center justify-between gap-4">
      <p class="text-sm text-white/70">Copyright © {{ date('Y') }} ComercioPlus</p>

      <!-- Sociales simples -->
      <div class="flex items-center gap-3">
        <a href="#" aria-label="Compartir" class="h-9 w-9 grid place-content-center rounded-full bg-white/10 hover:bg-white/20">🔗</a>
        <a href="#" aria-label="Facebook" class="h-9 w-9 grid place-content-center rounded-full bg-white/10 hover:bg-white/20">f</a>
        <a href="#" aria-label="Instagram" class="h-9 w-9 grid place-content-center rounded-full bg-white/10 hover:bg-white/20">⌁</a>
      </div>
    </div>
  </div>
</footer>
