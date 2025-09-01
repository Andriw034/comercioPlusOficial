<template>
  <footer class="mt-10 border-t border-[var(--cp-border)] bg-white/70">
    <div class="max-w-6xl mx-auto px-4 py-8 text-sm text-[var(--cp-ink-2)] flex items-center justify-between">
      <p>© {{ new Date().getFullYear() }} ComercioPlus</p>
      <div class="flex gap-4">
        <a href="#" class="hover:text-[var(--cp-primary)]">Términos</a>
        <a href="#" class="hover:text-[var(--cp-primary)]">Privacidad</a>
        <a href="#" class="hover:text-[var(--cp-primary)]">Contacto</a>
      </div>
    </div>
  </footer>
</template>
