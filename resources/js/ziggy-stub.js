// resources/js/ziggy-stub.js
export const Ziggy = window.Ziggy || { namedRoutes: {}, baseUrl: '' }

// ZiggyVue stub: instala un helper $route que evita errores si se llama
export const ZiggyVue = {
  install(app) {
    app.config.globalProperties.$route = function (name, params = {}, absolute = true) {
      // comportamiento mínimo: devuelve el nombre + params para diagnóstico
      // Puedes mejorar esta función para generar URLs simples si lo necesitas.
      if (!name) return ''
      let paramsStr = Object.keys(params).length ? `?${new URLSearchParams(params).toString()}` : ''
      console.warn(`[ziggy-stub] Se usó route("${name}") — revisa cuando Ziggy esté presente.`)
      return `${Ziggy.baseUrl || ''}/${name}${paramsStr}`
    }
  },
}
