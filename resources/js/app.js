import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'ComercioPlus';

// Fábrica async para inicializar Ziggy si existe, o usar stub si no.
(async () => {
  let Ziggy = undefined;
  let ZiggyVue = undefined;

  try {
    // Intentamos importar Ziggy generado en vendor (si existe)
    // Usamos @vite-ignore para que el bundler no falle si no lo encuentra
    const ziggyPath = '../../vendor/tightenco/ziggy/dist/vue.m';
    const mod = await import(/* @vite-ignore */ ziggyPath);
    Ziggy = window.Ziggy || mod.Ziggy || {};
    ZiggyVue = mod.ZiggyVue;
    console.log('Ziggy cargado desde vendor/tightenco/ziggy');
  } catch (err) {
    // Fallback: usamos un stub ligero que permite compilar y evita errores en tiempo de ejecución
    console.warn('No se encontró Ziggy en vendor — usando stub. Cuando se arregle PHP, ejecutar php artisan ziggy:generate', err);
    const stub = await import('./ziggy-stub');
    Ziggy = stub.Ziggy;
    ZiggyVue = stub.ZiggyVue;
  }

  createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .mount(el);
    },
    progress: {
        color: '#FF6A00',
    },
  });
})();
