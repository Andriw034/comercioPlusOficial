// Menú móvil simple (si lo necesitas en alguna vista con id)
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('cp-mobile-open');
  const menu = document.getElementById('cp-mobile-menu');
  if (btn && menu) {
    btn.addEventListener('click', () => menu.classList.toggle('hidden'));
  }
});
