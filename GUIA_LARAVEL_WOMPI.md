# 🛒 SISTEMA COMPLETO DE E-COMMERCE CON WOMPI (LARAVEL + MYSQL)

## 🎯 LO QUE TE ACABO DE DAR

### Sistema completo de carrito + checkout + pagos con:
- ✅ **PSE** (Pago Seguro en Línea)
- ✅ **Nequi**
- ✅ **Bancolombia** (botón de pago)
- ✅ **Tarjetas** (crédito/débito)

---

## 📦 ARCHIVOS ENTREGADOS (9)

### Frontend (3 archivos)
1. **CartContext.tsx** - Context API para manejar el carrito
2. **Cart.tsx** - Página del carrito de compras
3. **Checkout.tsx** - Página de checkout con métodos de pago

### Backend Laravel (6 archivos)
4. **2025_01_16_create_orders_table.php** - Migración MySQL
5. **Order.php** - Modelo Eloquent
6. **WompiController.php** - Controlador de pagos
7. **api-routes.php** - Rutas de API
8. **services-config.php** - Configuración de Wompi
9. **.env.example** - Variables de entorno

---

## 🚀 IMPLEMENTACIÓN PASO A PASO

### PASO 1: Configurar Wompi (10 min)

#### 1.1 Crear cuenta en Wompi
```
1. Ir a https://comercios.wompi.co/
2. Registrarte como comercio
3. Completar verificación
4. Obtener credenciales:
   - Public Key (pub_xxx)
   - Private Key (prv_xxx)
   - Events Secret (para webhooks)
```

#### 1.2 Configurar variables de entorno
```bash
# Agregar a tu .env de Laravel
WOMPI_PUBLIC_KEY=pub_test_xxxxxxxxxxxxxxxxxxxxxxxx
WOMPI_PRIVATE_KEY=prv_test_xxxxxxxxxxxxxxxxxxxxxxxx
WOMPI_EVENTS_SECRET=xxxxxxxxxxxxxxxxxxxxxxxx

# Usar sandbox para testing:
WOMPI_API_URL=https://sandbox.wompi.co/v1

# Producción:
# WOMPI_API_URL=https://production.wompi.co/v1
```

---

### PASO 2: Backend Laravel (20 min)

#### 2.1 Copiar migración
```bash
# Copiar archivo de migración a Laravel
cp 2025_01_16_create_orders_table.php database/migrations/

# Ejecutar migración
php artisan migrate
```

#### 2.2 Copiar modelo
```bash
# Copiar modelo Order
cp Order.php app/Models/
```

#### 2.3 Copiar controlador
```bash
# Crear directorio si no existe
mkdir -p app/Http/Controllers/Api

# Copiar controlador
cp WompiController.php app/Http/Controllers/Api/
```

#### 2.4 Configurar servicios
```bash
# Editar config/services.php y agregar:
'wompi' => [
    'public_key' => env('WOMPI_PUBLIC_KEY'),
    'private_key' => env('WOMPI_PRIVATE_KEY'),
    'events_secret' => env('WOMPI_EVENTS_SECRET'),
    'api_url' => env('WOMPI_API_URL', 'https://production.wompi.co/v1'),
],
```

#### 2.5 Agregar rutas
```bash
# Editar routes/api.php y agregar estas rutas:

use App\Http\Controllers\Api\WompiController;

// Rutas de órdenes
Route::prefix('orders')->group(function () {
    Route::post('/create', [WompiController::class, 'createOrder']);
});

// Rutas de pagos Wompi
Route::prefix('payments/wompi')->group(function () {
    Route::post('/create', [WompiController::class, 'createPayment']);
    Route::post('/webhook', [WompiController::class, 'webhook'])->withoutMiddleware(['auth:sanctum']);
    Route::get('/status/{transactionId}', [WompiController::class, 'getTransactionStatus']);
    Route::get('/pse-banks', [WompiController::class, 'getPseBanks']);
});
```

#### 2.6 Configurar CORS (importante)
```bash
# Editar config/cors.php
'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_origins' => ['http://localhost:5173'], // Tu URL de frontend
'allowed_headers' => ['*'],
```

---

### PASO 3: Frontend React (15 min)

#### 3.1 Instalar dependencias
```bash
npm install framer-motion
```

#### 3.2 Configurar CartContext
```tsx
// En tu App.tsx o main.tsx
import { CartProvider } from '@/context/CartContext'

function App() {
  return (
    <CartProvider>
      <Router>
        {/* tus rutas */}
      </Router>
    </CartProvider>
  )
}
```

#### 3.3 Copiar componentes
```bash
cd src

# Context
mkdir -p context
cp CartContext.tsx context/

# Pages
mkdir -p pages
cp Cart.tsx pages/
cp Checkout.tsx pages/
```

#### 3.4 Actualizar rutas
```tsx
import Cart from '@/pages/Cart'
import Checkout from '@/pages/Checkout'

<Route path="/cart" element={<Cart />} />
<Route path="/checkout" element={<Checkout />} />
```

#### 3.5 Configurar URL del backend
```typescript
// Crear archivo src/config/api.ts
export const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

// En tu .env de React
VITE_API_URL=http://localhost:8000
```

#### 3.6 Agregar botón "Agregar al carrito"
```tsx
import { useCart } from '@/context/CartContext'

function ProductCard({ product }) {
  const { addToCart } = useCart()
  
  return (
    <button onClick={() => addToCart(product)}>
      Agregar al carrito
    </button>
  )
}
```

---

### PASO 4: Webhooks (15 min)

#### 4.1 Configurar webhook en Wompi
```
1. Ir a tu dashboard de Wompi
2. Configuración > Webhooks
3. Agregar URL: https://tu-dominio.com/api/payments/wompi/webhook
4. Eventos: "transaction.updated"
5. Guardar
```

#### 4.2 Exponer webhook (desarrollo local)
```bash
# Opción 1: Usar ngrok
npm install -g ngrok
ngrok http 8000

# Usar la URL de ngrok en configuración de Wompi:
# https://xxxx-xxx-xxx-xxx-xxx.ngrok-free.app/api/payments/wompi/webhook

# Opción 2: Usar Laravel Valet (si estás en Mac)
valet share

# Opción 3: Subir a Railway/Heroku directamente
```

#### 4.3 Probar webhook localmente
```bash
# Puedes probar el webhook con curl:
curl -X POST http://localhost:8000/api/payments/wompi/webhook \
  -H "Content-Type: application/json" \
  -H "X-Event-Signature: test" \
  -d '{
    "event": "transaction.updated",
    "data": {
      "transaction": {
        "id": "test",
        "reference": "ORDER-1-123456",
        "status": "APPROVED"
      }
    },
    "timestamp": 1234567890
  }'
```

---

## 🎯 FLUJO COMPLETO

### 1. Usuario agrega productos al carrito
```
Usuario en /products
  → Click "Agregar al carrito"
  → useCart().addToCart(product)
  → Se guarda en localStorage
  → Contador en navbar aumenta
```

### 2. Usuario va al carrito
```
/cart
  → Ve lista de productos
  → Puede cambiar cantidades (+/-)
  → Ve total
  → Click "Proceder al pago"
  → Redirige a /checkout
```

### 3. Usuario completa checkout
```
/checkout
  → Completa formulario (email, nombre, teléfono)
  → Selecciona método de pago (PSE, Nequi, etc)
  → Click "Pagar ahora"
  
  → Frontend:
    1. POST /api/orders/create (Laravel)
    2. POST /api/payments/wompi/create (Laravel)
    3. Recibe checkoutUrl de Wompi
    4. window.location.href = checkoutUrl
```

### 4. Usuario paga en Wompi
```
Wompi checkout page
  → Ingresa datos de pago
  → Confirma
  → Wompi procesa
  → Redirige a tu redirectUrl
```

### 5. Webhook confirma pago
```
Wompi → POST /api/payments/wompi/webhook (Laravel)
  → Backend valida signature
  → Busca Order por reference en MySQL
  → Actualiza status a "paid"
  → Envía email de confirmación
  → Notifica al vendedor
```

---

## 💳 MÉTODOS DE PAGO CONFIGURADOS

### PSE (Pago Seguro en Línea)
```php
[
  'type' => 'PSE',
  'user_type' => '0', // 0 = Persona, 1 = Empresa
  'user_legal_id_type' => 'CC', // CC, CE, NIT
  'user_legal_id' => '1234567890',
  'financial_institution_code' => '', // Banco se selecciona en Wompi
]
```

### Nequi
```php
[
  'type' => 'NEQUI',
  'phone_number' => '3001234567',
]
```

### Bancolombia
```php
[
  'type' => 'BANCOLOMBIA_TRANSFER',
]
```

### Tarjetas (Visa, Mastercard, Amex)
```php
[
  'type' => 'CARD',
  // Wompi maneja el widget de tarjetas
]
```

---

## 🔒 SEGURIDAD

### 1. Validación de Webhooks
```php
// El controlador ya valida la signature del webhook
private function validateSignature($event, $signature)
{
    $payload = $event['data']['transaction']['id'] .
               $event['data']['transaction']['status'] .
               $event['timestamp'];
    
    $expectedSignature = hash_hmac('sha256', $payload, $this->eventsSecret);
    
    return hash_equals($expectedSignature, $signature);
}
```

### 2. Variables de entorno
```bash
# NUNCA hagas commit de las llaves
# Asegúrate que .env esté en .gitignore
```

### 3. HTTPS en producción
```bash
# Wompi REQUIERE HTTPS para webhooks en producción
# Railway ya provee HTTPS automáticamente
# Para dominios custom, configura SSL
```

### 4. Autenticación opcional
```php
// Si quieres proteger las rutas con autenticación:
Route::middleware('auth:sanctum')->prefix('orders')->group(function () {
    Route::post('/create', [WompiController::class, 'createOrder']);
});
```

---

## 💰 COMISIONES DE WOMPI

### Tarifas (aproximadas):
```
PSE:         2.9% + IVA
Nequi:       2.9% + IVA
Bancolombia: 2.9% + IVA
Tarjetas:    2.9% + IVA + $900 COP

No hay costos de instalación ni mensualidades
```

---

## 🧪 TESTING

### Modo Sandbox (desarrollo):
```bash
# .env
WOMPI_API_URL=https://sandbox.wompi.co/v1
WOMPI_PUBLIC_KEY=pub_test_xxxx
WOMPI_PRIVATE_KEY=prv_test_xxxx
```

### Tarjetas de prueba:
```
Visa:       4242 4242 4242 4242
Mastercard: 5555 5555 5555 4444
CVV:        123
Fecha:      Cualquier fecha futura
```

### PSE de prueba:
```
Banco: Banco de Pruebas
Usuario: cualquiera
Contraseña: cualquiera
```

### Probar flujo completo:
```bash
# 1. Iniciar Laravel
cd backend
php artisan serve

# 2. Iniciar React
cd frontend
npm run dev

# 3. Ir a http://localhost:5173
# 4. Agregar productos al carrito
# 5. Ir a checkout
# 6. Completar formulario
# 7. Seleccionar método de pago
# 8. Pagar con tarjeta de prueba
```

---

## 📧 EMAILS AUTOMÁTICOS

### Configurar email en Laravel:
```bash
# .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io  # Para testing
MAIL_PORT=2525
MAIL_USERNAME=tu_username
MAIL_PASSWORD=tu_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@comercioplus.com
MAIL_FROM_NAME="ComercioPlus"
```

### Crear Mailable:
```bash
php artisan make:mail OrderConfirmed
```

```php
// app/Mail/OrderConfirmed.php
namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;

class OrderConfirmed extends Mailable
{
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        return $this->subject('¡Orden confirmada! #' . $this->order->id)
                    ->view('emails.order-confirmed');
    }
}
```

### Enviar email en el webhook:
```php
// En WompiController.php
use App\Mail\OrderConfirmed;
use Illuminate\Support\Facades\Mail;

private function sendOrderConfirmationEmail($order)
{
    Mail::to($order->customer_email)->send(new OrderConfirmed($order));
}
```

---

## 📊 CONSULTAR ÓRDENES

### API Endpoints adicionales:
```php
// Agregar a routes/api.php
Route::middleware('auth:sanctum')->prefix('orders')->group(function () {
    // Listar órdenes del usuario
    Route::get('/', [OrderController::class, 'index']);
    
    // Ver detalle de orden
    Route::get('/{id}', [OrderController::class, 'show']);
});
```

### Crear OrderController:
```bash
php artisan make:controller Api/OrderController
```

```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($orders);
    }

    public function show($id)
    {
        $order = Order::findOrFail($id);
        
        // Verificar que el usuario sea dueño de la orden
        if ($order->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        return response()->json($order);
    }
}
```

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

### Backend Laravel
- [ ] Copiar migración a database/migrations/
- [ ] Ejecutar `php artisan migrate`
- [ ] Copiar modelo Order a app/Models/
- [ ] Copiar WompiController a app/Http/Controllers/Api/
- [ ] Agregar rutas en routes/api.php
- [ ] Configurar servicios en config/services.php
- [ ] Agregar variables de entorno (.env)
- [ ] Configurar CORS en config/cors.php

### Frontend React
- [ ] Instalar framer-motion
- [ ] Copiar CartContext.tsx
- [ ] Copiar Cart.tsx
- [ ] Copiar Checkout.tsx
- [ ] Configurar CartProvider en App
- [ ] Actualizar rutas
- [ ] Configurar API_URL
- [ ] Agregar botón "Agregar al carrito"

### Wompi
- [ ] Crear cuenta en Wompi
- [ ] Obtener credenciales (public, private, events secret)
- [ ] Configurar webhook en dashboard
- [ ] Testing en sandbox

### Producción
- [ ] Cambiar a llaves de producción
- [ ] Subir backend a Railway
- [ ] Subir frontend a Vercel/Netlify
- [ ] Configurar webhook con dominio real
- [ ] Testing con tarjeta real
- [ ] Emails configurados

---

## 🛠 TROUBLESHOOTING

### Error: "Invalid signature"
**Causa:** WOMPI_EVENTS_SECRET incorrecto
**Solución:** Verificar que el secret en .env coincide con Wompi

### Error: "Order not found"
**Causa:** Referencia no coincide en MySQL
**Solución:** Verificar que payment_reference se guarda correctamente

### Webhook no llega
**Causa:** URL no expuesta o bloqueada por firewall
**Solución:** 
- Usar ngrok en desarrollo
- Verificar que Railway tenga la ruta accesible
- Revisar logs de Laravel: `tail -f storage/logs/laravel.log`

### Pago aprobado pero orden no se actualiza
**Causa:** Error en webhook handler
**Solución:** 
```bash
# Ver logs
php artisan log:clear
tail -f storage/logs/laravel.log

# Verificar que la orden existe
php artisan tinker
> Order::where('payment_reference', 'ORDER-1-123456')->first()
```

### CORS error
**Causa:** Frontend no puede acceder al backend
**Solución:**
```bash
# Instalar laravel-cors si no está
composer require fruitcake/laravel-cors

# Verificar config/cors.php
'allowed_origins' => ['http://localhost:5173']
```

### Database connection error
**Causa:** Railway MySQL no conecta
**Solución:**
```bash
# Verificar .env
DB_CONNECTION=mysql
DB_HOST=containers-us-west-xxx.railway.app
DB_PORT=6xxx
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=xxxxx

# Probar conexión
php artisan db:show
```

---

## 📝 COMANDOS ÚTILES

```bash
# Ver logs de Laravel
tail -f storage/logs/laravel.log

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Ver rutas
php artisan route:list

# Tinker (consola de Laravel)
php artisan tinker
> Order::all()
> Order::where('status', 'paid')->count()

# Crear nueva migración
php artisan make:migration add_column_to_orders_table

# Rollback migración
php artisan migrate:rollback

# Crear seeder (datos de prueba)
php artisan make:seeder OrderSeeder
```

---

## 🎉 RESULTADO FINAL

Tu tienda tendrá:
- ✅ Carrito funcional con localStorage
- ✅ Checkout profesional
- ✅ 4 métodos de pago (PSE, Nequi, Bancolombia, Tarjetas)
- ✅ Confirmación automática por webhook
- ✅ Base de datos MySQL en Railway
- ✅ Backend Laravel robusto
- ✅ Sistema seguro y confiable

**Tiempo estimado: ~50 minutos** ⚡

---

## 🔧 PRÓXIMOS PASOS OPCIONALES

### 1. Agregar sistema de envíos
```php
// Migración
Schema::table('orders', function (Blueprint $table) {
    $table->string('shipping_status')->default('pending');
    $table->string('tracking_number')->nullable();
    $table->timestamp('shipped_at')->nullable();
});
```

### 2. Dashboard de vendedor
- Ver órdenes recibidas
- Marcar como enviado
- Generar etiquetas de envío

### 3. Sistema de notificaciones
- Email al vendedor cuando hay nueva orden
- Email al comprador cuando se envía
- SMS con estado de envío

### 4. Integración con inventario
- Descontar stock al confirmar pago
- Alertas de bajo inventario
- Gestión de productos

---

## 📞 SOPORTE

¿Dudas sobre la implementación? 🚀

### Recursos útiles:
- Documentación Wompi: https://docs.wompi.co/
- Laravel Docs: https://laravel.com/docs
- Railway Docs: https://docs.railway.app/

---

¡Listo para implementar! 💪
