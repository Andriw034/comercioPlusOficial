# INFORME COMPLETO Y DETALLADO DE LA APLICACIÓN COMERCIO PLUS

## 📋 RESUMEN EJECUTIVO

ComercioPlus es una plataforma completa de e-commerce desarrollada con Laravel 11 (backend) y Vue.js 3 (frontend), utilizando Inertia.js para una experiencia SPA fluida. La aplicación permite a los usuarios crear y gestionar tiendas en línea, con funcionalidades completas de catálogo, carrito de compras, procesamiento de pedidos y paneles de administración.

**Tecnologías Principales:**
- Backend: Laravel 11, PHP 8.2+
- Frontend: Vue.js 3, Inertia.js
- Base de Datos: MySQL/PostgreSQL
- Estilos: Tailwind CSS
- Build: Vite
- Autenticación: Laravel Sanctum
- Testing: Pest, Playwright

---

## 🏗️ ARQUITECTURA Y ESTRUCTURA

### Estructura de Directorios Completa

```
c:/xampp/htdocs/ComercioRealPlus-main/
├── 📁 app/                          # Código PHP de Laravel
│   ├── 📁 Console/                  # Comandos Artisan
│   ├── 📁 Exceptions/               # Manejo de excepciones
│   ├── 📁 Http/
│   │   ├── 📁 Controllers/          # Controladores
│   │   │   ├── 📁 Api/             # APIs REST
│   │   │   ├── 📁 Auth/            # Autenticación
│   │   │   └── 📁 Web/             # Controladores web
│   │   ├── 📁 Middleware/          # Middlewares
│   │   └── 📁 Requests/            # Validación de requests
│   ├── 📁 Models/                  # Modelos Eloquent
│   ├── 📁 Policies/                # Políticas de autorización
│   ├── 📁 Providers/               # Service Providers
│   └── 📁 Support/                 # Clases auxiliares
├── 📁 bootstrap/                   # Inicialización Laravel
├── 📁 config/                      # Configuraciones
├── 📁 database/
│   ├── 📁 factories/               # Factories para testing
│   ├── 📁 migrations/              # Migraciones BD
│   └── 📁 seeders/                 # Datos de prueba
├── 📁 public/                      # Assets públicos
├── 📁 resources/
│   ├── 📁 css/                     # Estilos CSS
│   ├── 📁 js/                      # JavaScript/Vue
│   └── 📁 views/                   # Plantillas Blade
├── 📁 routes/                      # Definición de rutas
├── 📁 storage/                     # Archivos temporales
├── 📁 tests/                       # Tests automatizados
├── 📁 vendor/                      # Dependencias Composer
├── 📁 node_modules/                # Dependencias NPM
├── 📁 comercio-plus-frontend/      # Frontend separado (Next.js)
├── 📁 informe/                     # Reportes de auditoría
├── 📁 inforem/                     # Información adicional
└── 📄 *.md                         # Documentación
```

---

## 🗄️ MODELOS Y BASE DE DATOS

### 1. Modelo User (app/Models/User.php)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relaciones
    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
```

### 2. Modelo Store (app/Models/Store.php)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'logo',
        'banner',
        'status',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
```

### 3. Modelo Product (app/Models/Product.php)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'image',
        'image_url',
        'stock',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // Relaciones
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class)->withPivot('quantity', 'price');
    }
}
```

### 4. Migraciones Principales

#### Users Table (database/migrations/xxxx_xx_xx_create_users_table.php)
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->string('phone')->nullable();
    $table->foreignId('role_id')->constrained();
    $table->rememberToken();
    $table->timestamps();
});
```

#### Stores Table
```php
Schema::create('stores', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->string('logo')->nullable();
    $table->string('banner')->nullable();
    $table->enum('status', ['active', 'inactive'])->default('active');
    $table->timestamps();
});
```

#### Products Table
```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('store_id')->constrained()->onDelete('cascade');
    $table->foreignId('category_id')->constrained();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description');
    $table->decimal('price', 10, 2);
    $table->string('image')->nullable();
    $table->string('image_url')->nullable();
    $table->integer('stock')->default(0);
    $table->enum('status', ['active', 'inactive', 'draft'])->default('active');
    $table->timestamps();
});
```

---

## 🚀 CONTROLADORES Y APIs

### 1. API Controllers

#### ProductController (app/Http/Controllers/Api/ProductController.php)

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['store', 'category']);

        // Filtros
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(12);

        return response()->json($products);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json($product->load(['store', 'category']));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product = Product::create($validated);

        return response()->json($product, 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'status' => 'sometimes|in:active,inactive,draft',
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }
}
```

#### CartController (app/Http/Controllers/Api/CartController.php)

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function index(): JsonResponse
    {
        $cart = auth()->user()->cart()->with('products')->first();

        if (!$cart) {
            $cart = Cart::create(['user_id' => auth()->id()]);
        }

        return response()->json($cart);
    }

    public function addProduct(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = auth()->user()->cart()->firstOrCreate();

        $cart->products()->syncWithoutDetaching([
            $validated['product_id'] => [
                'quantity' => $validated['quantity'],
                'price' => Product::find($validated['product_id'])->price,
            ]
        ]);

        return response()->json($cart->load('products'));
    }

    public function updateProduct(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $cart = auth()->user()->cart()->first();

        if ($validated['quantity'] > 0) {
            $cart->products()->updateExistingPivot($product->id, [
                'quantity' => $validated['quantity'],
            ]);
        } else {
            $cart->products()->detach($product->id);
        }

        return response()->json($cart->load('products'));
    }

    public function removeProduct(Product $product): JsonResponse
    {
        $cart = auth()->user()->cart()->first();

        $cart->products()->detach($product->id);

        return response()->json($cart->load('products'));
    }
}
```

### 2. Web Controllers

#### WebController (app/Http/Controllers/WebController.php)

```php
<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WebController extends Controller
{
    public function welcome()
    {
        return Inertia::render('Welcome');
    }

    public function products(Request $request)
    {
        $query = Product::with(['store', 'category'])
            ->where('status', 'active');

        // Filtros
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('store')) {
            $query->where('store_id', $request->store);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(12);
        $categories = Category::all();
        $stores = Store::where('status', 'active')->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'stores' => $stores,
            'filters' => $request->only(['category', 'store', 'search']),
        ]);
    }

    public function stores()
    {
        $stores = Store::where('status', 'active')
            ->with('user')
            ->paginate(12);

        return Inertia::render('Stores/Index', [
            'stores' => $stores,
        ]);
    }
}
```

---

## 🛣️ RUTAS Y ENDPOINTS

### API Routes (routes/api.php)

```php
<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rutas públicas
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('products', ProductController::class)->only(['index', 'show']);
Route::apiResource('stores', StoreController::class)->only(['index', 'show']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    Route::apiResource('stores', StoreController::class)->except(['index', 'show']);
    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);

    // Carrito
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'addProduct']);
    Route::patch('/cart/product/{product}', [CartController::class, 'updateProduct']);
    Route::delete('/cart/product/{product}', [CartController::class, 'removeProduct']);

    // Pedidos
    Route::apiResource('orders', OrderController::class);

    // Suscripciones
    Route::apiResource('subscriptions', SubscriptionController::class);

    // Usuario
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::patch('/user/profile', [UserController::class, 'updateProfile']);
});
```

### Web Routes (routes/web.php)

```php
<?php

use App\Http\Controllers\DashboardProductsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\Web\ProductController as WebProductController;
use App\Http\Controllers\Web\StoreWebController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebController::class, 'welcome'])->name('welcome');

// Productos públicos
Route::get('/products', [WebController::class, 'products'])->name('products.index');
Route::get('/products/{product}', [WebProductController::class, 'show'])->name('products.show');

// Tiendas públicas
Route::get('/stores', [WebController::class, 'stores'])->name('stores.index');
Route::get('/stores/{store}', [StoreWebController::class, 'show'])->name('stores.show');

// Autenticación
require __DIR__.'/auth.php';

// Rutas protegidas
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Gestión de productos
    Route::resource('dashboard/products', DashboardProductsController::class)
        ->names('dashboard.products');

    // Gestión de tienda
    Route::get('/store/create', [StoreController::class, 'create'])->name('store.create');
    Route::post('/store', [StoreController::class, 'store'])->name('store.store');
    Route::get('/store/{store}/edit', [StoreController::class, 'edit'])->name('store.edit');
    Route::patch('/store/{store}', [StoreController::class, 'update'])->name('store.update');

    // Carrito y checkout
    Route::get('/cart', function () {
        return Inertia::render('Cart/Index');
    })->name('cart.index');

    Route::get('/checkout', function () {
        return Inertia::render('Checkout/Index');
    })->name('checkout.index');

    // Pedidos
    Route::get('/orders', function () {
        return Inertia::render('Orders/Index');
    })->name('orders.index');
});
```

---

## 🎨 FRONTEND VUE.JS

### 1. Configuración Principal (resources/js/app.js)

```javascript
// resources/js/app.js
import { createApp, defineComponent, computed, reactive } from 'vue'
import '../css/app.css'

/* ==========
   1) HERO mínimo (si existe #app, como en welcome.blade)
========== */
const heroRoot = document.getElementById('app')
if (heroRoot) {
  const AppRoot = {
    template: `
      <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-cp-primary to-cp-primary-2 text-white">
        <div class="p-8 rounded-3xl shadow-xl bg-white/10 backdrop-blur-md">
          <h1 class="text-3xl font-bold mb-2">ComercioPlus</h1>
          <p class="opacity-90">Laravel + Vite + Vue + Tailwind funcionando ✅</p>
        </div>
      </div>
    `
  }
  createApp(AppRoot).mount('#app')
}

/* ==========
   2) Catálogo (demo Vue en Blade) — se monta en #vue-catalog
========== */
const catalogRoot = document.getElementById('vue-catalog')
if (catalogRoot) {
  const CatalogDemo = defineComponent({
    name: 'CatalogDemo',
    setup() {
      const state = reactive({
        query: '',
        categoria: 'Todos',
        categorias: ['Todos','Frenos','Iluminación','Transmisión','Accesorios','Lubricantes','Llantas'],
        items: [
          { id: 1, nombre: 'Casco integral Pro', cat: 'Accesorios', precio: 280000, img: 'https://images.unsplash.com/photo-1542362567-b07e54358753?q=80&w=800&auto=format&fit=crop' },
          { id: 2, nombre: 'Pastillas de freno', cat: 'Frenos', precio: 45000, img: 'https://images.unsplash.com/photo-1526045478516-99145907023c?q=80&w=800&auto=format&fit=crop' },
          { id: 3, nombre: 'Cadena reforzada', cat: 'Transmisión', precio: 120000, img: 'https://images.unsplash.com/photo-1602320734573-1b57f5813996?q=80&w=900&auto=format&fit=crop' },
          { id: 4, nombre: 'Bombillo LED H4', cat: 'Iluminación', precio: 35000, img: 'https://images.unsplash.com/photo-1516738901171-8eb4fc13bd20?q=80&w=900&auto=format&fit=crop' },
          { id: 5, nombre: 'Aceite sintético 10W40', cat: 'Lubricantes', precio: 58000, img: 'https://images.unsplash.com/photo-1589578527966-1e9b2ae05a0e?q=80&w=900&auto=format&fit=crop' },
          { id: 6, nombre: 'Llanta 130/70 R17', cat: 'Llantas', precio: 310000, img: 'https://images.unsplash.com/photo-1517940310602-75f39d4ac6fb?q=80&w=900&auto=format&fit=crop' },
        ]
      })

      const filtrados = computed(() => {
        return state.items.filter(it => {
          const pasaCat = state.categoria === 'Todos' || it.cat === state.categoria
          const pasaTxt = state.query.trim().length === 0 ||
                          it.nombre.toLowerCase().includes(state.query.toLowerCase())
          return pasaCat && pasaTxt
        })
      })

      return { state, filtrados }
    },
    template: `
      <div class="rounded-3xl bg-white/10 backdrop-blur-md ring-1 ring-white/15 shadow-2xl p-6">
        <!-- filtros -->
        <div class="flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
          <div class="flex-1">
            <input v-model="state.query" type="search" placeholder="Buscar producto..."
              class="w-full rounded-xl bg-white/10 text-white placeholder-white/60 ring-1 ring-white/15 px-4 py-3 focus:outline-none focus:ring-white/30" />
          </div>
          <div class="flex flex-wrap gap-2">
            <select v-model="state.categoria"
              class="rounded-xl bg-white/10 text-white ring-1 ring-white/15 px-4 py-3 focus:outline-none focus:ring-white/30">
              <option v-for="c in state.categorias" :key="c" :value="c">{{ c }}</option>
            </select>
          </div>
        </div>

        <!-- grid -->
        <div class="mt-5 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <div v-for="it in filtrados" :key="it.id"
               class="group rounded-2xl overflow-hidden ring-1 ring-white/10 bg-white/5">
            <div class="aspect-[4/3] overflow-hidden">
              <img :src="it.img" :alt="it.nombre"
                   class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
            </div>
            <div class="p-3">
              <div class="text-white/95 font-medium truncate">{{ it.nombre }}</div>
              <div class="text-white/70 text-xs">{{ it.cat }}</div>
              <div class="mt-2 flex items-center justify-between">
                <span class="text-white font-semibold">$ {{ new Intl.NumberFormat('es-CO').format(it.precio) }}</span>
                <button class="text-xs rounded-full px-3 py-1 bg-orange-500 hover:bg-orange-600 text-white transition">
                  Agregar
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- vacíos -->
        <div v-if="filtrados.length === 0" class="text-center text-white/80 py-10">
          No hay resultados para tu búsqueda.
        </div>
      </div>
    `
  })

  createApp(CatalogDemo).mount('#vue-catalog')
}
```

### 2. Página Welcome (resources/js/Pages/Welcome.vue)

```vue
<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { Link } from '@inertiajs/vue3'

// Cambia esta imagen por la tuya (ideal 2400x1200 o más)
const HERO_URL = 'https://images.unsplash.com/photo-1483721310020-03333e577078?q=80&w=2400&auto=format&fit=crop'

const menu = ref(false)
const y = ref(0)
const onScroll = () => { y.value = window.scrollY || 0 }

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }))
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll))
</script>

<template>
  <div class="min-h-screen text-white bg-black">
    <!-- NAVBAR sticky -->
    <header class="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-black/40 backdrop-blur-md">
      <nav class="mx-auto max-w-7xl h-16 px-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <span class="h-8 w-8 rounded-full bg-orange-500 shadow-[0_0_32px_rgba(255,98,0,.4)]"></span>
          <span class="font-semibold tracking-wide">Comercio<span class="text-orange-500">Plus</span></span>
        </div>

        <div class="hidden md:flex items-center gap-8 text-sm">
          <a href="#inicio" class="hover:text-orange-400">Inicio</a>
          <a href="#funciones" class="hover:text-orange-400">Funciones</a>
          <a href="#tiendas" class="hover:text-orange-400">Tiendas</a>
          <a href="#contacto" class="hover:text-orange-400">Contacto</a>
        </div>

        <div class="hidden md:flex items-center gap-3">
          <Link href="/login" class="px-4 py-2 rounded-xl border border-white/15 hover:bg-white/5 text-sm">Entrar</Link>
          <Link href="/register" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-400 text-sm font-medium shadow-lg shadow-orange-500/20">Crear cuenta</Link>
        </div>

        <button class="md:hidden p-2" @click="menu=!menu" aria-label="Abrir menú">
          <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/>
          </svg>
        </button>
      </nav>

      <transition name="fade">
        <div v-if="menu" class="md:hidden px-6 py-4 bg-black/90 border-t border-white/10">
          <div class="flex flex-col gap-3">
            <a href="#inicio" @click="menu=false" class="py-1 hover:text-orange-400">Inicio</a>
            <a href="#funciones" @click="menu=false" class="py-1 hover:text-orange-400">Funciones</a>
            <a href="#tiendas" @click="menu=false" class="py-1 hover:text-orange-400">Tiendas</a>
            <a href="#contacto" @click="menu=false" class="py-1 hover:text-orange-400">Contacto</a>
            <div class="pt-2 flex gap-2">
              <Link href="/login" class="flex-1 px-4 py-2 rounded-lg border border-white/15 text-center hover:bg-white/5">Entrar</Link>
              <Link href="/register" class="flex-1 px-4 py-2 rounded-lg bg-orange-500 text-center hover:bg-orange-400">Crear cuenta</Link>
            </div>
          </div>
        </div>
      </transition>
    </header>

    <!-- HERO full-width con parallax -->
    <section id="inicio" class="relative h-[92vh] w-full overflow-hidden">
      <div
        class="absolute inset-0 will-change-transform"
        :style="{
          backgroundImage: `url(${HERO_URL})`,
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          transform: `translateY(${y * 0.18}px) scale(1.04)`
        }"
      />
      <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/10 to-black/80"></div>

      <div class="relative z-10 max-w-7xl mx-auto h-full px-6 flex flex-col justify-center">
        <h1 class="text-4xl md:text-6xl font-extrabold leading-[1.1] animate-rise">
          Vende más con <span class="text-orange-500">ComercioPlus</span>
        </h1>
        <p class="mt-4 max-w-xl text-base md:text-lg opacity-90">
          Crea tu catálogo, gestiona inventario y cobra de forma segura desde un panel simple.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
          <Link href="/register" class="px-6 py-3 rounded-xl bg-orange-500 hover:bg-orange-400 font-medium shadow-lg shadow-orange-500/25">Empezar gratis</Link>
          <a href="#funciones" class="px-6 py-3 rounded-xl border border-white/15 hover:bg-white/5">Ver funciones</a>
        </div>
      </div>

      <!-- halos decorativos -->
      <div class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full bg-orange-500/15 blur-3xl animate-float"></div>
      <div class="pointer-events-none absolute -bottom-24 -right-16 h-96 w-96 rounded-full bg-fuchsia-500/10 blur-3xl animate-float-delayed"></div>
    </section>

    <!-- FUNCIONES -->
    <section id="funciones" class="relative py-20 bg-gradient-to-b from-black to-[#0a0612]">
      <div class="max-w-7xl mx-auto px-6">
        <h2 class="text-3xl md:text-4xl font-bold">Todo lo que necesitas</h2>
        <p class="mt-2 opacity-80">Herramientas para vender sin complicaciones.</p>

        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="rounded-2xl border border-white/10 bg-white/5 p-6 hover:border-white/20 transition">
            <div class="text-sm opacity-80">Catálogo</div>
            <div class="mt-2 text-xl font-semibold">Productos y categorías</div>
            <p class="mt-2 text-sm opacity-80">Organiza repuestos por marcas y variantes.</p>
          </div>
          <div class="rounded-2xl border border-white/10 bg-white/5 p-6 hover:border-white/20 transition">
            <div class="text-sm opacity-80">Pagos</div>
            <div class="mt-2 text-xl font-semibold">Cobros seguros</div>
            <p class="mt-2 text-sm opacity-80">Conecta tu pasarela favorita y cobra fácil.</p>
          </div>
          <div class="rounded-2xl border border-white/10 bg-white/5 p-6 hover:border-white/20 transition">
            <div class="text-sm opacity-80">Logística</div>
            <div class="mt-2 text-xl font-semibold">Envíos claros</div>
            <p class="mt-2 text-sm opacity-80">Precios y tiempos de entrega sin sorpresas.</p>
          </div>
          <div class="rounded-2xl border border-white/10 bg-white/5 p-6 hover:border-white/20 transition">
            <div class="text-sm opacity-80">Analítica</div>
            <div class="mt-2 text-xl font-semibold">Métricas en vivo</div>
            <p class="mt-2 text-sm opacity-80">Ventas, vistas y conversión en un vistazo.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- SHOWCASE TIENDAS (scroll horizontal) -->
    <section id="tiendas" class="py-20 bg-[#0a0612]">
      <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between gap-4">
          <h2 class="text-3xl md:text-4xl font-bold">Tiendas destacadas</h2>
          <Link href="/stores" class="hidden sm:inline-flex px-5 py-2 rounded-xl border border-white/15 hover:bg-white/5">Ver todas</Link>
        </div>

        <div class="mt-8 overflow-x-auto no-scrollbar">
          <div class="flex gap-6 min-w-max">
            <div class="w-[320px] shrink-0 rounded-2xl overflow-hidden border border-white/10 bg-white/5">
              <div class="h-40 bg-[url('https://picsum.photos/seed/s1/800/400')] bg-cover bg-center"></div>
              <div class="p-5">
                <div class="text-lg font-semibold">Moto Center Pro</div>
                <p class="text-sm opacity-80 mt-1">Rendimiento y estética premium.</p>
              </div>
            </div>
            <div class="w-[320px] shrink-0 rounded-2xl overflow-hidden border border-white/10 bg-white/5">
              <div class="h-40 bg-[url('https://picsum.photos/seed/s2/800/400')] bg-cover bg-center"></div>
              <div class="p-5">
                <div class="text-lg font-semibold">Racing Parts</div>
                <p class="text-sm opacity-80 mt-1">Componentes de competición.</p>
              </div>
            </div>
            <div class="w-[320px] shrink-0 rounded-2xl overflow-hidden border border-white/10 bg-white/5">
              <div class="h-40 bg-[url('https://picsum.photos/seed/s3/800/400')] bg-cover bg-center"></div>
              <div class="p-5">
                <div class="text-lg font-semibold">Full Repuestos</div>
                <p class="text-sm opacity-80 mt-1">Todo en un solo lugar.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 sm:hidden">
          <Link href="/stores" class="inline-flex px-5 py-2 rounded-xl border border-white/15 hover:bg-white/5">Ver todas</Link>
        </div>
      </div>
    </section>

    <!-- CONTACTO / CTA -->
    <section id="contacto" class="py-20 bg-black">
      <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-[1.2fr_.8fr] gap-10 items-center">
        <div>
          <h3 class="text-2xl md:text-3xl font-bold">¿Listo para empezar?</h3>
          <p class="mt-2 opacity-80 max-w-xl">Crea tu cuenta, configura tu marca y comparte tu catálogo hoy mismo.</p>
          <div class="mt-6 flex flex-wrap gap-3">
            <Link href="/register" class="px-6 py-3 rounded-xl bg-orange-500 hover:bg-orange-400 font-medium shadow-lg shadow-orange-500/20">Crear tienda</Link>
            <a href="#inicio" class="px-6 py-3 rounded-xl border border-white/15 hover:bg-white/5">Volver arriba</a>
          </div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
          <div class="h-56 rounded-xl bg-[url('https://picsum.photos/seed/dashboard/1200/800')] bg-cover bg-center"></div>
          <div class="mt-4 text-sm opacity-80">Panel simple y poderoso para administrar todo.</div>
        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="border-t border-white/10 bg-black">
      <div class="max-w-7xl mx-auto px-6 py-10 text-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="opacity-80">© {{ new Date().getFullYear() }} ComercioPlus. Todos los derechos reservados.</div>
        <div class="flex items-center gap-6">
          <a href="#" class="hover:text-orange-400">Términos</a>
          <a href="#" class="hover:text-orange-400">Privacidad</a>
          <a href="#" class="hover:text-orange-400">Contacto</a>
