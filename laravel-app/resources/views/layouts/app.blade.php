<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Comercio Plus')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
    <!-- Vue.js -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <!-- Vue Router -->
    <script src="https://unpkg.com/vue-router@4/dist/vue-router.global.js"></script>
    <!-- Axios para peticiones HTTP -->
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <div id="app">
        <!-- Header Vue Component -->
        <header-vue
            :user="{{ auth()->user() ? json_encode(auth()->user()) : 'null' }}"
            csrf-token="{{ csrf_token() }}"
        ></header-vue>

        <main class="flex-grow container mx-auto p-4">
            @yield('content')
        </main>

        <footer class="bg-white shadow p-4 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} Comercio Plus. Todos los derechos reservados.
        </footer>
    </div>

    <!-- Vue Components -->
    <script>
        // Header Component
        const HeaderComponent = {
            template: `
                <header class="sticky top-0 z-50 w-full border-b bg-background/80 backdrop-blur-sm">
                    <div class="container h-16 flex items-center justify-between">
                        <div class="flex items-center gap-6">
                            <router-link to="/" class="flex items-center gap-2">
                                <logo-component></logo-component>
                            </router-link>
                        </div>

                        <nav class="hidden md:flex items-center gap-4">
                            <router-link to="/dashboard" class="text-sm font-medium text-muted-foreground hover:text-primary transition-colors">Panel</router-link>
                            <router-link to="/#stores" class="text-sm font-medium text-muted-foreground hover:text-primary transition-colors">Tiendas</router-link>
                        </nav>

                        <div class="flex items-center">
                            <auth-widget-component
                                :user="user"
                                :csrf-token="csrfToken"
                            ></auth-widget-component>
                        </div>
                    </div>
                </header>
            `,
            props: ['user', 'csrfToken'],
            components: {
                'logo-component': LogoComponent,
                'auth-widget-component': AuthWidgetComponent
            }
        };

        // Logo Component
        const LogoComponent = {
            template: `
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-lg flex items-center justify-center bg-primary">
                        <svg class="h-5 w-5 text-background" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-foreground">
                        ComercioPlus
                    </span>
                </div>
            `
        };

        // Auth Widget Component
        const AuthWidgetComponent = {
            template: `
                <div v-if="loading" class="h-8 w-24 rounded-md animate-pulse bg-muted"></div>

                <div v-else-if="!user" class="flex items-center gap-2">
                    <router-link to="/login" class="px-4 py-2 text-sm font-medium text-muted-foreground hover:text-primary transition-colors">
                        Entrar
                    </router-link>
                    <router-link to="/register" class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors">
                        Crear cuenta
                    </router-link>
                </div>

                <div v-else class="flex items-center gap-2">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-muted-foreground">Hola, {{ user.name }}</span>
                        <form method="POST" action="/logout" class="inline">
                            <input type="hidden" name="_token" :value="csrfToken" />
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-red-600 hover:text-red-800 transition-colors">
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            `,
            props: ['user', 'csrfToken'],
            data() {
                return {
                    loading: false
                }
            }
        };

        // Vue Router setup
        const routes = [
            { path: '/', component: { template: '<div>Home</div>' } },
            { path: '/login', component: { template: '<div>Login</div>' } },
            { path: '/register', component: { template: '<div>Register</div>' } },
            { path: '/dashboard', component: { template: '<div>Dashboard</div>' } }
        ];

        const router = VueRouter.createRouter({
            history: VueRouter.createWebHashHistory(),
            routes
        });

        // Vue App
        const app = Vue.createApp({
            components: {
                'header-vue': HeaderComponent
            }
        });

        app.use(router);
        app.mount('#app');
    </script>

    @stack('scripts')
</body>
</html>
