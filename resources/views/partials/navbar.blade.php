<nav class="bg-white bg-opacity-80 backdrop-blur-sm px-6 py-4 flex justify-between items-center shadow">
  <span class="text-lg font-semibold text-gray-800">👋 Hola, {{ Auth::user()->name }}</span>
  <form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit"
      class="text-orange-500 font-medium hover:text-orange-600 transition">
      Cerrar sesión
    </button>
  </form>
</nav>
