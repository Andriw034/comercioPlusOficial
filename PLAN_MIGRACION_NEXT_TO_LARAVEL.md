#  Laravel 11 + Inertia + Vue 3


## 🎯 STACK FINAL
- **Backend**: Laravel 11 con Inertia.js, Eloquent, Laravel Sanctum (auth), migrations/seeders
- **Frontend**: Vue 3 (SFC con `<script setup lang="ts">`), Pinia (stores), Vue Router, Tailwind
- **UI**: radix-vue o shadcn-vue (equivalentes 1:1 donde existan)
- **Carrusel**: embla-carousel-vue (reemplaza Embla React)
- **Gráficas**: vue-echarts (preferido) o vue-chartjs
- **Formularios**: vee-validate (+ zod/yup si conviene)
- **Validación Backend**: Form Requests de Laravel
- **Almacenamiento**: Laravel Filesystem (S3/local) reemplaza Firebase Storage
- **Auth/DB**: Laravel Auth + Eloquent reemplaza Firebase

---

## 📁 ESTRUCTURA FINAL DEL PROYECTO

```
comercio-plus-main/
├── app/                          # Laravel backend
│   ├── Http/Controllers/         # Controladores (API + Web)
│   ├── Models/                   # Modelos Eloquent
│   └── Services/                 # Servicios de negocio
├── database/
│   ├── migrations/               # Esquemas de BD
│   ├── seeders/                  # Datos de prueba
│   └── factories/                # Factories para tests
├── resources/
│   ├── js/                       # Frontend Vue.js
│   │   ├── components/           # Componentes Vue
│   │   ├── pages/               # Páginas Inertia
│   │   ├── stores/              # Stores Pinia
│   │   ├── composables/         # Composables Vue
│   │   └── layouts/             # Layouts Vue
│   └── views/                    # Vistas Blade (layouts)
├── routes/
│   ├── web.php                  # Rutas Inertia
│   └── api.php                  # Rutas API
├── public/                      # Assets públicos
├── storage/                     # Archivos subidos
├── tests/                       # Tests Laravel
├── composer.json               # Dependencias PHP
├── package.json               # Dependencias Node (Vue)
├── vite.config.js             # Config Vite
└── tailwind.config.js         # Config Tailwind
```

---

## 🔄 MAPEO DE EQUIVALENCIAS

### Arquitectura
 | Laravel + Inertia + Vue |
|---------------|------------------------|
| `pages/` | `routes/web.php` + `resources/js/pages/` |
| `components/` | `resources/js/components/` |
| `lib/hooks/` | `resources/js/composables/` |
| `contexts/` | `resources/js/stores/` (Pinia) |
| `styles/` | `resources/css/` (Tailwind) |
| `public/` | `public/` + `storage/` |

### Librerías
| Vue 3 |
|-------|-------|
| Radix UI / shadcn/ui | radix-vue / shadcn-vue |
| Embla Carousel | embla-carousel-vue |
| Recharts | vue-echarts |
| react-hook-form | vee-validate |
| Zod | yup (opcional) |
| Firebase Auth | Laravel Sanctum |
| Firebase Firestore | Eloquent + PostgreSQL |
| Firebase Storage | Laravel Filesystem |

### Patrones
| Vue 3 Pattern |
|----------------|----------------|
| `useState` | `ref()` / `reactive()` |
| `useEffect` | `onMounted()` / `watch()` |
| `useContext` | Pinia stores |
| `useCallback` | `computed()` |
| `useMemo` | `computed()` |
| JSX | SFC con `<template>` |

---

## 📝 PLAN DE MIGRACIÓN PASO A PASO

### FASE 1: Configuración Base
1. **Actualizar Laravel a v11**
   ```bash
   composer require laravel/framework:^11.0
   composer update
   ```

2. **Instalar Inertia.js**
   ```bash
   composer require inertiajs/inertia-laravel
   npm install @inertiajs/vue3
   ```

3. **Instalar Vue 3 + Pinia + Vue Router**
   ```bash
   npm install vue@latest @vue/compiler-sfc
   npm install pinia vue-router
   npm install @vitejs/plugin-vue
   ```

4. **Instalar UI Libraries**
   ```bash
   npm install radix-vue shadcn-vue
   npm install embla-carousel-vue vue-echarts
   npm install vee-validate @vee-validate/rules
   ```

5. **Configurar Vite para Vue**
   ```javascript
   // vite.config.js
   import { defineConfig } from 'vite'
   import laravel from 'laravel-vite-plugin'
   import vue from '@vitejs/plugin-vue'

   export default defineConfig({
     plugins: [
       laravel(),
       vue()
     ]
   })
   ```

### FASE 2: Base de Datos y Modelos
1. **Crear migraciones para reemplazar Firebase**
   ```bash
   php artisan make:migration create_users_table
   php artisan make:migration create_stores_table
   php artisan make:migration create_products_table
   php artisan make:migration create_categories_table
   php artisan make:migration create_carts_table
   php artisan make:migration create_orders_table
   ```

2. **Crear modelos Eloquent**
   ```bash
   php artisan make:model User
   php artisan make:model Store
   php artisan make:model Product
   php artisan make:model Category
   php artisan make:model Cart
   php artisan make:model Order
   ```

3. **Configurar relaciones y fillables**
   - Users: hasMany stores, hasMany orders
   - Stores: belongsTo user, hasMany products
   - Products: belongsTo store, belongsTo category
   - Categories: hasMany products (jerarquía opcional)

### FASE 3: Autenticación
1. **Configurar Laravel Sanctum**
   ```bash
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   php artisan migrate
   ```

2. **Crear middleware de auth**
   ```php
   // app/Http/Kernel.php
   'auth:sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
   ```

3. **Crear Form Requests para validación**
   ```bash
   php artisan make:request LoginRequest
   php artisan make:request RegisterRequest
   ```

### FASE 4: Migración de Componentes
1. **Convertir componentes UI base**
   - Button, Input, Dialog, Sheet, etc.
   - Mantener misma API de props
   - Conservar estilos Tailwind

2. **Convertir providers a Pinia stores**
   - AuthProvider → AuthStore
   - ProductsProvider → ProductsStore
   - CartProvider → CartStore

3. **Convertir hooks a composables**
   - useIsMobile → useIsMobile composable
   - useToast → useToast composable
   - useAuth → useAuth composable

### FASE 5: Rutas y Páginas
1. **Convertir páginas Next a rutas Laravel**
   ```php
   // routes/web.php
   Route::get('/', [HomeController::class, 'index'])->name('home');
   Route::get('/products', [ProductController::class, 'index'])->name('products');
   Route::get('/stores/{store}', [StoreController::class, 'show'])->name('stores.show');
   ```

2. **Crear controladores Inertia**
   ```php
   // app/Http/Controllers/HomeController.php
   public function index()
   {
       return Inertia::render('Home/Index', [
           'products' => Product::latest()->take(10)->get(),
           'categories' => Category::all()
       ]);
   }
   ```

3. **Crear páginas Vue**
   ```
   resources/js/pages/
   ├── Home/
   │   └── Index.vue
   ├── Products/
   │   └── Index.vue
   └── Stores/
       └── Show.vue
   ```

### FASE 6: Formularios y Validación
1. **Reemplazar react-hook-form con vee-validate**
   ```vue
   <script setup lang="ts">
   import { useForm } from 'vee-validate'
   import { object, string } from 'yup'

   const schema = object({
     email: string().email().required(),
     password: string().min(8).required()
   })

   const { handleSubmit, errors } = useForm({
     validationSchema: schema
   })
   </script>
   ```

2. **Crear Form Requests en Laravel**
   ```php
   // app/Http/Requests/CreateProductRequest.php
   public function rules()
   {
       return [
           'name' => 'required|string|max:255',
           'price' => 'required|numeric|min:0',
           'category_id' => 'required|exists:categories,id'
       ];
   }
   ```

### FASE 7: Almacenamiento de Archivos
1. **Configurar Laravel Filesystem**
   ```php
   // config/filesystems.php
   'disks' => [
       'local' => [...],
       'public' => [...],
       's3' => [...]
   ]
   ```

2. **Crear servicio de archivos**
   ```php
   // app/Services/FileUploadService.php
   public function uploadImage(UploadedFile $file, string $path = 'images'): string
   {
       return $file->store($path, 'public');
   }
   ```

### FASE 8: Testing
1. **Configurar testing con Vue**
   ```bash
   npm install --save-dev @vue/test-utils vitest jsdom
   ```

2. **Crear tests para componentes**
   ```javascript
   // tests/components/Button.test.js
   import { mount } from '@vue/test-utils'
   import Button from '@/components/ui/Button.vue'

   test('renders correctly', () => {
     const wrapper = mount(Button, {
       props: { variant: 'primary' }
     })
     expect(wrapper.text()).toContain('Button')
   })
   ```

---

## 🔧 EJEMPLOS DE CONVERSIÓN

### 1. Componente Button
**React/TSX:**
```tsx
interface ButtonProps {
  variant?: 'primary' | 'secondary'
  size?: 'sm' | 'md' | 'lg'
  children: React.ReactNode
  onClick?: () => void
}

export const Button = ({ variant = 'primary', size = 'md', children, onClick }: ButtonProps) => {
  return (
    <button
      className={cn(buttonVariants({ variant, size }))}
      onClick={onClick}
    >
      {children}
    </button>
  )
}
```

**Vue 3 SFC:**
```vue
<script setup lang="ts">
interface Props {
  variant?: 'primary' | 'secondary'
  size?: 'sm' | 'md' | 'lg'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'primary',
  size: 'md'
})

const emit = defineEmits<{
  click: []
}>()

const handleClick = () => {
  emit('click')
}

const buttonClasses = computed(() => {
  return cn(buttonVariants({
    variant: props.variant,
    size: props.size
  }))
})
</script>

<template>
  <button
    :class="buttonClasses"
    @click="handleClick"
  >
    <slot />
  </button>
</template>
```

### 2. Provider → Pinia Store
**React Context:**
```tsx
interface AuthContextType {
  user: User | null
  login: (email: string, password: string) => Promise<void>
  logout: () => void
}

const AuthContext = createContext<AuthContextType | undefined>(undefined)

export const AuthProvider = ({ children }: { children: React.ReactNode }) => {
  const [user, setUser] = useState<User | null>(null)

  const login = async (email: string, password: string) => {
    const response = await fetch('/api/login', {
      method: 'POST',
      body: JSON.stringify({ email, password })
    })
    const data = await response.json()
    setUser(data.user)
  }

  return (
    <AuthContext.Provider value={{ user, login, logout }}>
      {children}
    </AuthContext.Provider>
  )
}
```

**Pinia Store:**
```typescript
// resources/js/stores/auth.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)

  const isAuthenticated = computed(() => !!user.value)

  const login = async (email: string, password: string) => {
    const response = await fetch('/api/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    })
    const data = await response.json()
    user.value = data.user
  }

  const logout = () => {
    user.value = null
  }

  return {
    user,
    isAuthenticated,
    login,
    logout
  }
})
```

### 3. Página Next → Laravel + Inertia
**Next.js Page:**
```tsx
// pages/products/index.tsx
import { GetServerSideProps } from 'next'
import { useProducts } from '@/hooks/useProducts'

interface Props {
  products: Product[]
  categories: Category[]
}

export default function ProductsPage({ products, categories }: Props) {
  const { filteredProducts, setCategory } = useProducts(products)

  return (
    <div>
      <h1>Productos</h1>
      <CategoryFilter categories={categories} onSelect={setCategory} />
      <ProductGrid products={filteredProducts} />
    </div>
  )
}

export const getServerSideProps: GetServerSideProps = async () => {
  const products = await fetchProducts()
  const categories = await fetchCategories()

  return {
    props: { products, categories }
  }
}
```

**Laravel Controller + Vue Page:**
```php
// app/Http/Controllers/ProductController.php
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        return Inertia::render('Products/Index', [
            'products' => Product::with('category')->paginate(20),
            'categories' => Category::all()
        ]);
    }
}
```

```vue
<!-- resources/js/pages/Products/Index.vue -->
<script setup lang="ts">
import { ref, computed } from 'vue'
import { useProductsStore } from '@/stores/products'

const props = defineProps<{
  products: Product[]
  categories: Category[]
}>()

const productsStore = useProductsStore()
const selectedCategory = ref<string>('')

const filteredProducts = computed(() => {
  if (!selectedCategory.value) return props.products
  return props.products.filter(p => p.category_id === selectedCategory.value)
})
</script>

<template>
  <div>
    <h1>Productos</h1>
    <CategoryFilter
      :categories="categories"
      @select="selectedCategory = $event"
    />
    <ProductGrid :products="filteredProducts" />
  </div>
</template>
```

---

## ✅ CHECKLIST DE ACEPTACIÓN

### Verificaciones de Eliminación
- [ ] `grep -R "from 'react'" -n` devuelve 0 resultados
- [ ] `grep -R "next/" -n` devuelve 0 resultados
- [ ] `grep -R "\.tsx" -n` devuelve 0 resultados
- [ ] `grep -R "react-hook-form" -n` devuelve 0 resultados
- [ ] `grep -R "firebase" -n` devuelve 0 resultados (excepto configuración)

### Verificaciones de Implementación
- [ ] Laravel 11 instalado y funcionando
- [ ] Inertia.js configurado correctamente
- [ ] Vue 3 + Pinia + Vue Router instalados
- [ ] Vite compila sin errores
- [ ] Todas las rutas web.php creadas
- [ ] Todos los modelos Eloquent creados
- [ ] Todas las migraciones ejecutadas
- [ ] Sanctum configurado para autenticación
- [ ] Form Requests creados para validación
- [ ] Stores Pinia reemplazan React contexts
- [ ] Composables reemplazan React hooks
- [ ] Componentes UI convertidos a Vue
- [ ] Páginas Next convertidas a Vue + Inertia

### Verificaciones de Funcionalidad
- [ ] Autenticación funciona con Sanctum
- [ ] Navegación entre páginas funciona
- [ ] Formularios validan correctamente
- [ ] API endpoints responden correctamente
- [ ] Base de datos poblada con seeders
- [ ] Archivos se suben correctamente
- [ ] UI se ve igual que la versión React

---

## 🚀 COMANDOS DE VERIFICACIÓN

```bash
# Verificar eliminación de React/Next
grep -R "from 'react'" --include="*.js" --include="*.ts" --include="*.vue"
grep -R "next/" --include="*.js" --include="*.ts" --include="*.vue"
grep -R "\.tsx" --include="*.js" --include="*.ts" --include="*.vue"

# Verificar instalación de dependencias
composer show | grep laravel
npm list vue pinia vue-router

# Verificar configuración
php artisan route:list
php artisan migrate:status

# Verificar compilación
npm run build
npm run dev

# Verificar tests
php artisan test
npm run test
```

---

## 📋 SIGUIENTES PASOS

1. **Ejecutar Fase 1**: Configuración base de Laravel 11 + Inertia + Vue 3
2. **Crear estructura de directorios**: `resources/js/components/`, `resources/js/stores/`, etc.
3. **Migrar modelos y migraciones**: Convertir esquemas Firebase a Eloquent
4. **Convertir componentes UI**: Button, Input, Dialog, etc.
5. **Crear stores Pinia**: Auth, Products, Cart
6. **Convertir páginas**: Home, Products, Stores
7. **Implementar formularios**: Login, Register, Product creation
8. **Configurar archivos**: File upload service
9. **Testing**: Component tests y feature tests
10. **Verificación final**: Ejecutar checklist de aceptación

¿Te gustaría que comience con la Fase 1 o tienes alguna pregunta específica sobre el plan?
