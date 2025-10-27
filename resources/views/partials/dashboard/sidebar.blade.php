<aside class="hidden md:block border-r bg-card h-full">
  <div class="flex h-full max-h-screen flex-col gap-2">
    <div class="flex h-16 items-center border-b px-6">
      <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-semibold">
        <div class="h-8 w-8 rounded-lg flex items-center justify-center bg-primary">
          <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>
        </div>
        <span class="text-lg font-bold">ComercioPlus</span>
      </a>
    </div>
    <div class="flex-1">
      <nav class="grid items-start px-4 text-sm font-medium">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-muted-foreground transition-all hover:text-primary hover:bg-secondary">
          <span>🏠</span>
          Inicio
        </a>
        <span class="flex items-center gap-3 rounded-lg px-3 py-2 text-muted-foreground/50 transition-all cursor-not-allowed">
          <span>🛒</span>
          Órdenes
        </span>
        <a href="{{ route('products.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-muted-foreground transition-all hover:text-primary hover:bg-secondary">
          <span>📦</span>
          Productos
        </a>
        <a href="#" class="flex items-center gap-3 rounded-lg px-3 py-2 text-muted-foreground transition-all hover:text-primary hover:bg-secondary">
          <span>🎨</span>
          Branding IA
        </a>
        <a href="#" class="flex items-center gap-3 rounded-lg px-3 py-2 text-muted-foreground transition-all hover:text-primary hover:bg-secondary">
          <span>⚙️</span>
          Ajustes
        </a>
      </nav>
    </div>
  </div>
</aside>
