# Page snapshot

```yaml
- generic [ref=e3]:
  - generic [ref=e4]:
    - generic [ref=e5]: "[plugin:vite:import-analysis]"
    - generic [ref=e6]: Failed to resolve import "/logo.svg" from "resources/js/components/Navbar.vue". Does the file exist?
  - generic [ref=e7]:
    - text: "C:"
    - generic [ref=e8] [cursor=pointer]: /xampp/htdocs/ComercioRealPlus-main/resources/js/components/Navbar.vue:35:6
  - generic [ref=e9]: "33 | 34 | 35 | const _hoisted_1 = { class: \"fixed top-0 left-0 right-0 bg-black text-white flex items-center justify-between px-6 py-3 z-50 shadow-md\" } | ^ 36 | const _hoisted_2 = { class: \"flex items-center gap-4\" } 37 | const _hoisted_3 = { key: 0 }"
  - generic [ref=e10]:
    - text: "at TransformPluginContext._formatLog (file:"
    - generic [ref=e11] [cursor=pointer]: ///C:/xampp/htdocs/ComercioRealPlus-main/node_modules/vite/dist/node/chunks/dep-DBxKXgDP.js:42499:41
    - text: ") at TransformPluginContext.error (file:"
    - generic [ref=e12] [cursor=pointer]: ///C:/xampp/htdocs/ComercioRealPlus-main/node_modules/vite/dist/node/chunks/dep-DBxKXgDP.js:42496:16
    - text: ") at normalizeUrl (file:"
    - generic [ref=e13] [cursor=pointer]: ///C:/xampp/htdocs/ComercioRealPlus-main/node_modules/vite/dist/node/chunks/dep-DBxKXgDP.js:40475:23
    - text: ") at async file:"
    - generic [ref=e14] [cursor=pointer]: ///C:/xampp/htdocs/ComercioRealPlus-main/node_modules/vite/dist/node/chunks/dep-DBxKXgDP.js:40594:37
    - text: "at async Promise.all (index 2) at async TransformPluginContext.transform (file:"
    - generic [ref=e15] [cursor=pointer]: ///C:/xampp/htdocs/ComercioRealPlus-main/node_modules/vite/dist/node/chunks/dep-DBxKXgDP.js:40521:7
    - text: ") at async EnvironmentPluginContainer.transform (file:"
    - generic [ref=e16] [cursor=pointer]: ///C:/xampp/htdocs/ComercioRealPlus-main/node_modules/vite/dist/node/chunks/dep-DBxKXgDP.js:42294:18
    - text: ") at async loadAndTransform (file:"
    - generic [ref=e17] [cursor=pointer]: ///C:/xampp/htdocs/ComercioRealPlus-main/node_modules/vite/dist/node/chunks/dep-DBxKXgDP.js:35735:27
    - text: ") at async viteTransformMiddleware (file:"
    - generic [ref=e18] [cursor=pointer]: ///C:/xampp/htdocs/ComercioRealPlus-main/node_modules/vite/dist/node/chunks/dep-DBxKXgDP.js:37250:24
  - generic [ref=e19]:
    - text: Click outside, press
    - generic [ref=e20]: Esc
    - text: key, or fix the code to dismiss.
    - text: You can also disable this overlay by setting
    - code [ref=e21]: server.hmr.overlay
    - text: to
    - code [ref=e22]: "false"
    - text: in
    - code [ref=e23]: vite.config.js
    - text: .
```