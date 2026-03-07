<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CartProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CreditController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\BarcodeController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PurchaseRequestController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductAlertController;
use App\Http\Controllers\Api\PublicCategoryController;
use App\Http\Controllers\Api\PublicProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReportsAlertsController;
use App\Http\Controllers\Api\ReportsTrendsController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\StoreVerificationController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\TaxSettingController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WompiController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\Merchant\InventoryReceiveController;
use App\Http\Controllers\Api\Merchant\MerchantStoreController;
use App\Http\Controllers\Api\Merchant\OrderPickingController;
use App\Http\Controllers\Api\Merchant\AutoRestockController;
use App\Http\Controllers\Api\Merchant\ProductCodeLookupController;
use App\Http\Controllers\Api\SettingsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| API Routes - ComercioPlus (DIA 1)
|--------------------------------------------------------------------------
*/

// Health check for smoke tests.
Route::get('/health', fn () => response()->json(['status' => 'ok']));
Route::get('/health/integrations', function () {
    $dbOk = false;
    $dbMessage = null;

    try {
        DB::connection()->getPdo();
        $dbOk = true;
    } catch (\Throwable $e) {
        $dbMessage = $e->getMessage();
    }

    $cloudinaryConfigured = trim((string) config('services.cloudinary.cloud_name', '')) !== ''
        && trim((string) config('services.cloudinary.api_key', '')) !== ''
        && trim((string) config('services.cloudinary.api_secret', '')) !== '';

    return response()->json([
        'status' => $dbOk ? 'ok' : 'degraded',
        'app_env' => app()->environment(),
        'database' => [
            'ok' => $dbOk,
            'connection' => config('database.default'),
            'database' => config('database.connections.mysql.database'),
            'host' => config('database.connections.mysql.host'),
            'error' => $dbOk ? null : $dbMessage,
        ],
        'cloudinary' => [
            'configured' => $cloudinaryConfigured,
            'cloud_name_present' => trim((string) config('services.cloudinary.cloud_name', '')) !== '',
            'api_key_present' => trim((string) config('services.cloudinary.api_key', '')) !== '',
            'api_secret_present' => trim((string) config('services.cloudinary.api_secret', '')) !== '',
        ],
    ], $dbOk ? 200 : 503);
});

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', fn () => response()->json([
    'message' => 'Metodo no permitido. Usa POST /api/login para autenticar.',
], 405));
Route::get('/register', fn () => response()->json([
    'message' => 'Metodo no permitido. Usa POST /api/register para crear cuenta.',
], 405));

// Public catalog endpoints.
Route::get('/hero-images', [StoreController::class, 'heroImages'])->middleware('throttle:60,1');
Route::get('/public/products', [PublicProductController::class, 'index']);
Route::get('/public/categories', [PublicCategoryController::class, 'index']);
Route::get('/public/stores', [StoreController::class, 'publicStores']);
Route::get('/public/stores/{store}', [StoreController::class, 'show']);
Route::get('/public/barcode/search', [BarcodeController::class, 'publicSearch'])->middleware('throttle:60,1');

// Public catalog used by frontend.
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/products/{product}/alerts/mine', [ProductAlertController::class, 'mine']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// Checkout order creation route used by Wompi flow.
Route::post('/orders/create', [WompiController::class, 'createOrder']);

// Wompi payment routes.
Route::prefix('payments/wompi')->group(function () {
    Route::post('/create', [WompiController::class, 'createPayment']);
    Route::post('/webhook', [WompiController::class, 'webhook']);
    Route::get('/status/{transactionId}', [WompiController::class, 'getTransactionStatus']);
    Route::get('/pse-banks', [WompiController::class, 'getPseBanks']);
});

/*
|--------------------------------------------------------------------------
| Protected routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Stores.
    Route::get('/my/store', [StoreController::class, 'myStore']);
    Route::post('/stores', [StoreController::class, 'store']);
    Route::put('/stores/{store}', [StoreController::class, 'update']);
    Route::delete('/stores/{store}', [StoreController::class, 'destroy']);
    Route::prefix('stores/{store}')->group(function () {
        Route::get('tax-settings', [TaxSettingController::class, 'show']);
        Route::put('tax-settings', [TaxSettingController::class, 'update']);

        Route::get('reports', [ReportController::class, 'index']);
        Route::get('reports/latest', [ReportController::class, 'latest']);
        Route::post('reports/generate', [ReportController::class, 'generate']);

        Route::get('inventory/low-stock', [InventoryController::class, 'lowStock']);
        Route::get('inventory/movements', [InventoryController::class, 'movements']);
        Route::post('inventory/adjust', [InventoryController::class, 'adjust']);

        Route::get('reorder/suggestions', [PurchaseRequestController::class, 'suggestions']);
        Route::get('reorder/requests', [PurchaseRequestController::class, 'index']);
        Route::post('reorder/requests', [PurchaseRequestController::class, 'store']);
        Route::get('reorder/requests/{purchaseRequest}', [PurchaseRequestController::class, 'show']);
        Route::put('reorder/requests/{purchaseRequest}', [PurchaseRequestController::class, 'update']);
    });

    // Customer-store interactions.
    Route::post('/stores/register-customer', [CustomerController::class, 'registerCustomer']);
    Route::post('/stores/{store}/visit', [CustomerController::class, 'registerVisit']);
    Route::post('/stores/{store}/follow', fn () => response()->json(['status' => 'ok']));
    Route::delete('/stores/{store}/follow', fn () => response()->json(['status' => 'ok']));

    // Merchant dashboard.
    Route::get('/merchant/orders', [OrderController::class, 'merchantIndex']);
    Route::put('/merchant/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::get('/merchant/orders/{order}/picking', [OrderPickingController::class, 'show']);
    Route::post('/merchant/orders/{order}/picking/scan', [OrderPickingController::class, 'scan']);
    Route::post('/merchant/orders/{order}/picking/manual', [OrderPickingController::class, 'manual']);
    Route::post('/merchant/orders/{order}/picking/fallback', [OrderPickingController::class, 'fallback']);
    Route::post('/merchant/orders/{order}/picking/complete', [OrderPickingController::class, 'complete']);
    Route::post('/merchant/orders/{order}/picking/reset', [OrderPickingController::class, 'reset']);
    Route::get('/merchant/picking/events', [OrderPickingController::class, 'events']);
    Route::get('/merchant/customers', [CustomerController::class, 'myCustomers']);
    Route::get('/merchant/credit', [CreditController::class, 'index']);
    Route::post('/merchant/credit', [CreditController::class, 'store']);
    Route::get('/merchant/credit/{creditAccount}', [CreditController::class, 'show']);
    Route::post('/merchant/credit/{creditAccount}/charge', [CreditController::class, 'charge']);
    Route::post('/merchant/credit/{creditAccount}/payment', [CreditController::class, 'payment']);
    // Auto-restock module.
    Route::get('/merchant/restock', [AutoRestockController::class, 'index']);
    Route::get('/merchant/restock/{product}', [AutoRestockController::class, 'settings']);
    Route::put('/merchant/restock/{product}', [AutoRestockController::class, 'saveSettings']);
    Route::post('/merchant/restock/{product}/request', [AutoRestockController::class, 'request']);
    Route::post('/merchant/restock/{product}/dismiss', [AutoRestockController::class, 'dismiss']);

    Route::get('/merchant/store', [MerchantStoreController::class, 'show']);
    Route::put('/merchant/store', [MerchantStoreController::class, 'update']);
    Route::get('/merchant/store/verification', [StoreVerificationController::class, 'show']);
    Route::post('/merchant/store/verification', [StoreVerificationController::class, 'submit']);
    Route::get('/merchant/dashboard', [StatsController::class, 'summary']);
    Route::get('/merchant/stats', [StatsController::class, 'summary']);
    Route::get('/reports/summary', [ReportController::class, 'summary']);
    Route::get('/reports/sales', [ReportController::class, 'sales']);
    Route::get('/reports/tax', [ReportController::class, 'tax']);
    Route::get('/reports/top-products', [ReportController::class, 'topProducts']);
    Route::get('/reports/inventory', [ReportController::class, 'inventory']);
    Route::get('/reports/export/sales.csv', [ReportController::class, 'exportSalesCsv']);
    Route::get('/reports/export/tax.csv', [ReportController::class, 'exportTaxCsv']);
    Route::get('/reports/alerts', [ReportsAlertsController::class, 'alerts']);
    Route::get('/reports/trends', [ReportsTrendsController::class, 'trends']);
    Route::get('/inventory/summary', [InventoryController::class, 'summary']);
    Route::get('/inventory/stats', [InventoryController::class, 'stats']);
    Route::get('/inventory/movements', [InventoryController::class, 'merchantMovements']);
    Route::get('/inventory/template', [InventoryController::class, 'template'])->middleware('role.key:merchant');
    Route::post('/inventory/adjust', [InventoryController::class, 'merchantAdjust']);
    Route::post('/inventory/bulk-delete', [InventoryController::class, 'bulkDelete'])->middleware('role.key:merchant');
    Route::post('/inventory/preview', [InventoryController::class, 'preview'])->middleware('role.key:merchant');
    Route::post('/inventory/import', [InventoryController::class, 'import'])->middleware('role.key:merchant');
    Route::get('/inventory/invoices', [InventoryController::class, 'invoices']);
    Route::post('/merchant/inventory/scan-in', [InventoryReceiveController::class, 'scanIn']);
    Route::post('/merchant/inventory/create-from-scan', [InventoryReceiveController::class, 'createFromScan']);
    Route::get('/merchant/inventory/movements', [InventoryReceiveController::class, 'movements']);
    Route::post('/merchant/products/lookup-code', [ProductCodeLookupController::class, 'lookup']);
    Route::get('/products/{product}/barcode', [BarcodeController::class, 'show']);
    Route::get('/barcode/search', [BarcodeController::class, 'search']);
    Route::post('/barcode/generate-batch', [BarcodeController::class, 'generateBatch']);

    // Product/category management.
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    // Media uploads for dashboard forms.
    Route::post('/uploads/products', [UploadController::class, 'storeProductImage']);
    Route::post('/uploads/stores/logo', [UploadController::class, 'storeStoreLogo']);
    Route::post('/uploads/stores/cover', [UploadController::class, 'storeStoreCover']);
    Route::post('/uploads/profiles/photo', [UploadController::class, 'storeProfilePhoto']);

    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    Route::post('/products/{product}/alerts', [ProductAlertController::class, 'store']);
    Route::delete('/products/{product}/alerts', [ProductAlertController::class, 'destroy']);

    // API resources used by current tests.
    Route::apiResource('users', UserController::class);
    Route::apiResource('cart', CartController::class);
    Route::delete('/cart', [CartController::class, 'clear']);
    Route::get('/cart/count', [CartController::class, 'count']);
    Route::post('/cart/clear', [CartController::class, 'clear']);
    Route::apiResource('cart-products', CartProductController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('subscriptions', SubscriptionController::class);

    // Merchant settings (dashboard settings page).
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::put('/settings', [SettingsController::class, 'update']);
});




Route::get('/_debug/env', function () {
    return response()->json([
        'app_env' => app()->environment(),
        'config_cached' => app()->configurationIsCached(),

        // ¿Railway está inyectando estas variables?
        'has_DB_HOST' => !empty(env('DB_HOST')),
        'DB_HOST' => env('DB_HOST') ? 'SET' : 'EMPTY',

        'has_DB_DATABASE' => !empty(env('DB_DATABASE')),
        'DB_DATABASE' => env('DB_DATABASE') ? 'SET' : 'EMPTY',

        'has_CLOUDINARY_CLOUD_NAME' => !empty(env('CLOUDINARY_CLOUD_NAME')),
        'CLOUDINARY_CLOUD_NAME' => env('CLOUDINARY_CLOUD_NAME') ? 'SET' : 'EMPTY',
    ]);
});
