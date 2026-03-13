<?php

declare(strict_types=1);

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

/** @var HttpKernel $kernel */
$kernel = $app->make(HttpKernel::class);

final class QaAssertionException extends RuntimeException {}

final class QaRunner
{
    private array $results = [];
    private int $passed = 0;
    private int $failed = 0;

    public function __construct(private readonly HttpKernel $kernel) {}

    public function run(string $module, string $step, callable $callback): mixed
    {
        $startedAt = microtime(true);
        try {
            $value = $callback();
            $this->results[] = [
                'module' => $module,
                'step' => $step,
                'status' => 'PASS',
                'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
                'detail' => is_scalar($value) || $value === null ? $value : json_encode($value, JSON_UNESCAPED_UNICODE),
            ];
            $this->passed++;
            return $value;
        } catch (Throwable $e) {
            $this->results[] = [
                'module' => $module,
                'step' => $step,
                'status' => 'FAIL',
                'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
                'detail' => $e->getMessage(),
            ];
            $this->failed++;
            return null;
        }
    }

    public function request(
        string $method,
        string $uri,
        ?array $payload = null,
        ?string $token = null,
        array $query = []
    ): array {
        // Reset auth guards between synthetic requests.
        if (function_exists('app')) {
            try {
                app('auth')->forgetGuards();
            } catch (Throwable) {
                // noop
            }
        }

        $fullUri = $uri;
        if ($query !== []) {
            $fullUri .= '?' . http_build_query($query);
        }

        $server = [
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
        ];

        if ($token) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        }

        $request = Request::create(
            $fullUri,
            strtoupper($method),
            [],
            [],
            [],
            $server,
            $payload !== null ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null
        );

        $response = $this->kernel->handle($request);
        $status = $response->getStatusCode();
        $rawBody = $response->getContent();
        if ($rawBody === false && $response instanceof StreamedResponse) {
            ob_start();
            $response->sendContent();
            $rawBody = (string) ob_get_clean();
        }
        $rawBody = is_string($rawBody) ? ltrim($rawBody, "\xEF\xBB\xBF") : '';
        $decoded = json_decode($rawBody, true);
        $body = is_array($decoded) ? $decoded : ['_raw' => $rawBody];
        $this->kernel->terminate($request, $response);

        return [
            'status' => $status,
            'body' => $body,
            'raw' => $rawBody,
        ];
    }

    public function assertStatus(array $response, int|array $expected, string $message): void
    {
        $actual = (int) ($response['status'] ?? 0);
        $expectedList = is_array($expected) ? $expected : [$expected];
        if (!in_array($actual, $expectedList, true)) {
            throw new QaAssertionException($message . " | status={$actual} body=" . ($response['raw'] ?? ''));
        }
    }

    public function assertTrue(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new QaAssertionException($message);
        }
    }

    public function getSummary(): array
    {
        return [
            'passed' => $this->passed,
            'failed' => $this->failed,
            'total' => $this->passed + $this->failed,
            'results' => $this->results,
        ];
    }
}

$qa = new QaRunner($kernel);
$stamp = date('Ymd_His');
$merchantEmail = "qa_merchant_{$stamp}@gmail.com";
$clientEmail = "qa_client_{$stamp}@gmail.com";
$password = 'QaFlow2026!';

$context = [
    'merchant' => ['token' => null, 'id' => null, 'email' => $merchantEmail],
    'client' => ['token' => null, 'id' => null, 'email' => $clientEmail],
    'store_id' => null,
    'category_id' => null,
    'product_id' => null,
    'order_id' => null,
    'order2_id' => null,
];

$qa->run('Infra', 'Health endpoint', function () use ($qa) {
    $res = $qa->request('GET', '/api/health');
    $qa->assertStatus($res, 200, 'Health endpoint no responde 200');
    $qa->assertTrue(($res['body']['status'] ?? null) === 'ok', 'Health payload invalido');
    return $res['body'];
});

$qa->run('Merchant', 'Register merchant', function () use (&$context, $qa, $password) {
    $res = $qa->request('POST', '/api/register', [
        'name' => 'QA Merchant ' . date('His'),
        'email' => $context['merchant']['email'],
        'password' => $password,
        'password_confirmation' => $password,
        'role' => 'merchant',
    ]);
    $qa->assertStatus($res, 201, 'Registro de merchant fallo');
    $token = $res['body']['token'] ?? null;
    $id = $res['body']['user']['id'] ?? null;
    $qa->assertTrue(is_string($token) && $token !== '', 'No se recibio token merchant');
    $qa->assertTrue(is_numeric($id), 'No se recibio id merchant');
    $context['merchant']['token'] = $token;
    $context['merchant']['id'] = (int) $id;
    return ['merchant_id' => $context['merchant']['id']];
});

$qa->run('Merchant', 'Me before store', function () use (&$context, $qa) {
    $res = $qa->request('GET', '/api/me', null, $context['merchant']['token']);
    $qa->assertStatus($res, 200, 'GET /api/me merchant fallo');
    $qa->assertTrue(($res['body']['role'] ?? '') === 'merchant', 'Rol merchant invalido');
    $qa->assertTrue(($res['body']['has_store'] ?? true) === false, 'has_store debe ser false antes de crear tienda');
    return $res['body'];
});

$qa->run('Merchant', 'My store returns 404 before create', function () use (&$context, $qa) {
    $res = $qa->request('GET', '/api/my/store', null, $context['merchant']['token']);
    $qa->assertStatus($res, 404, 'GET /api/my/store antes de crear tienda debe retornar 404');
    return $res['body']['message'] ?? '';
});

$qa->run('Merchant', 'Create store', function () use (&$context, $qa) {
    $res = $qa->request('POST', '/api/stores', [
        'name' => 'QA Store ' . date('His'),
        'description' => 'Tienda creada por QA automatizado',
        'phone' => '+57 300 000 0000',
        'support_email' => 'qa-store@gmail.com',
        'address' => 'Bogota',
        'is_visible' => true,
    ], $context['merchant']['token']);
    $qa->assertStatus($res, 201, 'Crear tienda fallo');
    $storeId = $res['body']['id'] ?? null;
    $qa->assertTrue(is_numeric($storeId), 'No se recibio id de tienda');
    $context['store_id'] = (int) $storeId;
    return ['store_id' => $context['store_id']];
});

$qa->run('Merchant', 'My store after create', function () use (&$context, $qa) {
    $res = $qa->request('GET', '/api/my/store', null, $context['merchant']['token']);
    $qa->assertStatus($res, 200, 'GET /api/my/store despues de crear fallo');
    $qa->assertTrue((int) ($res['body']['id'] ?? 0) === $context['store_id'], 'Store id inconsistente');
    return ['store_name' => $res['body']['name'] ?? ''];
});

$qa->run('Merchant', 'Tax settings GET', function () use (&$context, $qa) {
    $res = $qa->request('GET', "/api/stores/{$context['store_id']}/tax-settings", null, $context['merchant']['token']);
    $qa->assertStatus($res, 200, 'GET tax-settings fallo');
    $qa->assertTrue(isset($res['body']['data']['tax_rate_percent']), 'tax_rate_percent no existe');
    return $res['body']['data'];
});

$qa->run('Merchant', 'Tax settings PUT', function () use (&$context, $qa) {
    $res = $qa->request('PUT', "/api/stores/{$context['store_id']}/tax-settings", [
        'enable_tax' => true,
        'tax_name' => 'IVA',
        'tax_rate_percent' => 19,
        'prices_include_tax' => true,
        'tax_rounding_mode' => 'HALF_UP',
    ], $context['merchant']['token']);
    $qa->assertStatus($res, 200, 'PUT tax-settings fallo');
    $qa->assertTrue(isset($res['body']['preview']['total']), 'Preview de IVA no presente');
    return $res['body']['preview'];
});

$qa->run('Merchant', 'Create category', function () use (&$context, $qa) {
    $res = $qa->request('POST', '/api/categories', [
        'name' => 'QA Categoria ' . date('His'),
        'description' => 'Categoria QA',
    ], $context['merchant']['token']);
    $qa->assertStatus($res, 201, 'Crear categoria fallo');
    $categoryId = $res['body']['id'] ?? null;
    $qa->assertTrue(is_numeric($categoryId), 'No se recibio category id');
    $context['category_id'] = (int) $categoryId;
    return ['category_id' => $context['category_id']];
});

$qa->run('Merchant', 'Create product', function () use (&$context, $qa) {
    $res = $qa->request('POST', '/api/products', [
        'name' => 'QA Producto ' . date('His'),
        'price' => 119000,
        'stock' => 25,
        'category_id' => $context['category_id'],
        'description' => 'Producto QA',
        'status' => 'active',
    ], $context['merchant']['token']);
    $qa->assertStatus($res, 201, 'Crear producto fallo');
    $productId = $res['body']['data']['id'] ?? null;
    $qa->assertTrue(is_numeric($productId), 'No se recibio product id');
    $context['product_id'] = (int) $productId;
    return ['product_id' => $context['product_id']];
});

$qa->run('Merchant', 'Merchant endpoints after store', function () use (&$context, $qa) {
    $orders = $qa->request('GET', '/api/merchant/orders', null, $context['merchant']['token']);
    $customers = $qa->request('GET', '/api/merchant/customers', null, $context['merchant']['token']);
    $inventory = $qa->request('GET', '/api/inventory/summary', null, $context['merchant']['token'], ['per_page' => 100]);
    $reports = $qa->request('GET', '/api/reports/summary', null, $context['merchant']['token']);
    $qa->assertStatus($orders, 200, 'merchant/orders no responde 200');
    $qa->assertStatus($customers, 200, 'merchant/customers no responde 200');
    $qa->assertStatus($inventory, 200, 'inventory/summary no responde 200');
    $qa->assertStatus($reports, 200, 'reports/summary no responde 200');
    return [
        'orders_count' => count($orders['body']['data'] ?? []),
        'customers_status' => $customers['status'],
        'inventory_status' => $inventory['status'],
        'reports_status' => $reports['status'],
    ];
});

$qa->run('Client', 'Register client', function () use (&$context, $qa, $password) {
    $res = $qa->request('POST', '/api/register', [
        'name' => 'QA Client ' . date('His'),
        'email' => $context['client']['email'],
        'password' => $password,
        'password_confirmation' => $password,
        'role' => 'client',
    ]);
    $qa->assertStatus($res, 201, 'Registro de client fallo');
    $token = $res['body']['token'] ?? null;
    $id = $res['body']['user']['id'] ?? null;
    $qa->assertTrue(is_string($token) && $token !== '', 'No se recibio token client');
    $qa->assertTrue(is_numeric($id), 'No se recibio id client');
    $context['client']['token'] = $token;
    $context['client']['id'] = (int) $id;
    return ['client_id' => $context['client']['id']];
});

$qa->run('Client', 'Client me + public catalog', function () use (&$context, $qa) {
    $me = $qa->request('GET', '/api/me', null, $context['client']['token']);
    $catalog = $qa->request('GET', '/api/products', null, $context['client']['token'], ['store_id' => $context['store_id']]);
    $qa->assertStatus($me, 200, 'GET /api/me client fallo');
    $qa->assertTrue(($me['body']['role'] ?? '') === 'client', 'Rol client invalido. payload=' . ($me['raw'] ?? ''));
    $qa->assertStatus($catalog, 200, 'GET /api/products fallo para cliente');
    return [
        'client_role' => $me['body']['role'] ?? null,
        'catalog_items' => count($catalog['body']['data'] ?? []),
    ];
});

$qa->run('Client', 'Register customer visit', function () use (&$context, $qa) {
    $visit = $qa->request('POST', '/api/stores/register-customer', [
        'store_id' => $context['store_id'],
    ], $context['client']['token']);
    $qa->assertStatus($visit, 200, 'Registro de visita cliente fallo');
    return $visit['body'];
});

$qa->run('Client', 'Create order via /api/orders', function () use (&$context, $qa) {
    $res = $qa->request('POST', '/api/orders', [
        'store_id' => $context['store_id'],
        'items' => [
            [
                'product_id' => $context['product_id'],
                'quantity' => 2,
            ],
        ],
        'payment_method' => 'CARD',
        'status' => 'pending',
    ], $context['client']['token']);
    $qa->assertStatus($res, 201, 'Creacion de orden cliente fallo');
    $orderId = $res['body']['data']['id'] ?? null;
    $qa->assertTrue(is_numeric($orderId), 'No se recibio order id');
    $context['order_id'] = (int) $orderId;
    $qa->assertTrue(((float) ($res['body']['data']['total'] ?? 0)) > 0, 'Total de orden invalido');
    return [
        'order_id' => $context['order_id'],
        'total' => $res['body']['data']['total'] ?? null,
    ];
});

$qa->run('Client', 'Create order via /api/orders/create (checkout flow)', function () use (&$context, $qa) {
    $res = $qa->request('POST', '/api/orders/create', [
        'items' => [
            [
                'productId' => $context['product_id'],
                'name' => 'QA Producto Checkout',
                'quantity' => 1,
                'storeId' => $context['store_id'],
            ],
        ],
        'customer' => [
            'email' => $context['client']['email'],
            'name' => 'QA Client Checkout',
            'phone' => '3000000000',
            'address' => 'Bogota',
            'city' => 'Bogota',
        ],
        'paymentMethod' => 'CARD',
    ], $context['client']['token']);
    $qa->assertStatus($res, 201, 'Checkout /orders/create fallo');
    $orderId = $res['body']['orderId'] ?? null;
    $qa->assertTrue(is_numeric($orderId), 'No se recibio orderId en checkout');
    $context['order2_id'] = (int) $orderId;
    return ['order2_id' => $context['order2_id']];
});

$qa->run('Merchant', 'Merchant sees orders and marks first as paid', function () use (&$context, $qa) {
    $ordersBefore = $qa->request('GET', '/api/merchant/orders', null, $context['merchant']['token']);
    $qa->assertStatus($ordersBefore, 200, 'merchant/orders fallo');
    $orderIds = array_map(
        fn (array $row) => (int) ($row['id'] ?? 0),
        is_array($ordersBefore['body']['data'] ?? null) ? $ordersBefore['body']['data'] : []
    );
    $qa->assertTrue(in_array($context['order_id'], $orderIds, true), 'La orden del cliente no aparece para merchant');

    $paid = $qa->request('PUT', "/api/merchant/orders/{$context['order_id']}/status", [
        'status' => 'paid',
    ], $context['merchant']['token']);
    $qa->assertStatus($paid, 200, 'No se pudo cambiar orden a paid');

    // Repetimos paid para validar no duplicacion de movimientos.
    $paidAgain = $qa->request('PUT', "/api/merchant/orders/{$context['order_id']}/status", [
        'status' => 'paid',
    ], $context['merchant']['token']);
    $qa->assertStatus($paidAgain, 200, 'No se pudo repetir estado paid');

    return ['order_paid' => $context['order_id']];
});

$qa->run('Merchant', 'Inventory + invoices + reports after paid order', function () use (&$context, $qa) {
    $movements = $qa->request('GET', '/api/inventory/movements', null, $context['merchant']['token'], [
        'product_id' => $context['product_id'],
        'per_page' => 100,
    ]);
    $invoices = $qa->request('GET', '/api/inventory/invoices', null, $context['merchant']['token'], ['per_page' => 50]);
    $summary = $qa->request('GET', '/api/reports/summary', null, $context['merchant']['token']);
    $sales = $qa->request('GET', '/api/reports/sales', null, $context['merchant']['token'], ['group' => 'day']);
    $tax = $qa->request('GET', '/api/reports/tax', null, $context['merchant']['token'], ['group' => 'day']);
    $top = $qa->request('GET', '/api/reports/top-products', null, $context['merchant']['token'], ['limit' => 10, 'sort' => 'revenue']);
    $inventory = $qa->request('GET', '/api/reports/inventory', null, $context['merchant']['token']);
    $csvSales = $qa->request('GET', '/api/reports/export/sales.csv', null, $context['merchant']['token']);
    $csvTax = $qa->request('GET', '/api/reports/export/tax.csv', null, $context['merchant']['token']);

    $qa->assertStatus($movements, 200, 'inventory/movements fallo');
    $qa->assertStatus($invoices, 200, 'inventory/invoices fallo');
    $qa->assertStatus($summary, 200, 'reports/summary fallo');
    $qa->assertStatus($sales, 200, 'reports/sales fallo');
    $qa->assertStatus($tax, 200, 'reports/tax fallo');
    $qa->assertStatus($top, 200, 'reports/top-products fallo');
    $qa->assertStatus($inventory, 200, 'reports/inventory fallo');
    $qa->assertStatus($csvSales, 200, 'export sales csv fallo');
    $qa->assertStatus($csvTax, 200, 'export tax csv fallo');

    $movementRows = is_array($movements['body']['data'] ?? null) ? $movements['body']['data'] : [];
    $saleRowsForOrder = array_values(array_filter($movementRows, function ($row) use ($context) {
        return (string) ($row['type'] ?? '') === 'sale'
            && (int) ($row['order_id'] ?? 0) === (int) $context['order_id'];
    }));
    $qa->assertTrue(count($saleRowsForOrder) === 1, 'Debe existir exactamente 1 movimiento sale para la orden paid');

    $qa->assertTrue(((float) ($summary['body']['data']['gross_sales'] ?? 0)) > 0, 'gross_sales debe ser > 0');
    $qa->assertTrue(((float) ($summary['body']['data']['tax_total'] ?? 0)) > 0, 'tax_total debe ser > 0');

    return [
        'movement_rows' => count($movementRows),
        'sale_rows_for_order' => count($saleRowsForOrder),
        'gross_sales' => $summary['body']['data']['gross_sales'] ?? 0,
        'tax_total' => $summary['body']['data']['tax_total'] ?? 0,
    ];
});

$qa->run('Client/Merchant', 'Order visibility and detail', function () use (&$context, $qa) {
    $clientList = $qa->request('GET', '/api/orders', null, $context['client']['token']);
    $clientShow = $qa->request('GET', "/api/orders/{$context['order_id']}", null, $context['client']['token']);
    $merchantShow = $qa->request('GET', "/api/orders/{$context['order_id']}", null, $context['merchant']['token']);
    $qa->assertStatus($clientList, 200, 'Client list orders fallo');
    $qa->assertStatus($clientShow, 200, 'Client show order fallo');
    $qa->assertStatus($merchantShow, 200, 'Merchant show order fallo');
    return [
        'client_orders' => count($clientList['body']['data'] ?? []),
        'order_status' => $merchantShow['body']['data']['status'] ?? null,
    ];
});

$qa->run('DB', 'Persistencia en tablas clave', function () use (&$context, $qa) {
    $merchant = DB::table('users')->where('id', $context['merchant']['id'])->first();
    $client = DB::table('users')->where('id', $context['client']['id'])->first();
    $store = DB::table('stores')->where('id', $context['store_id'])->first();
    $tax = DB::table('store_tax_settings')->where('store_id', $context['store_id'])->first();
    $category = DB::table('categories')->where('id', $context['category_id'])->first();
    $product = DB::table('products')->where('id', $context['product_id'])->first();
    $order = DB::table('orders')->where('id', $context['order_id'])->first();
    $orderLines = DB::table('order_products')->where('order_id', $context['order_id'])->get();
    $movementRows = DB::table('inventory_movements')
        ->where('reference_type', 'order')
        ->where('reference_id', $context['order_id'])
        ->where('type', 'sale')
        ->get();
    $customer = DB::table('customers')
        ->where('store_id', $context['store_id'])
        ->where('user_id', $context['client']['id'])
        ->first();

    $qa->assertTrue((bool) $merchant, 'Merchant no persistido en users');
    $qa->assertTrue((bool) $client, 'Client no persistido en users');
    $qa->assertTrue((bool) $store, 'Store no persistida');
    $qa->assertTrue((bool) $tax, 'store_tax_settings no persistido');
    $qa->assertTrue((bool) $category, 'Category no persistida');
    $qa->assertTrue((bool) $product, 'Product no persistido');
    $qa->assertTrue((bool) $order, 'Order no persistida');
    $qa->assertTrue($orderLines->count() >= 1, 'order_products sin filas');
    $qa->assertTrue($movementRows->count() === 1, 'inventory_movements sale duplicado o ausente');
    $qa->assertTrue((bool) $customer, 'Customer visit no persistida');
    $qa->assertTrue((float) ($order->total ?? 0) > 0, 'orders.total invalido');
    $qa->assertTrue((float) ($order->tax_total ?? 0) >= 0, 'orders.tax_total invalido');

    return [
        'users_ok' => true,
        'store_id' => (int) $store->id,
        'product_id' => (int) $product->id,
        'order_id' => (int) $order->id,
        'order_lines' => $orderLines->count(),
        'inventory_sale_rows' => $movementRows->count(),
    ];
});

$qa->run('Auth', 'Logout merchant and client', function () use (&$context, $qa) {
    $logoutMerchant = $qa->request('POST', '/api/logout', null, $context['merchant']['token']);
    $logoutClient = $qa->request('POST', '/api/logout', null, $context['client']['token']);
    $qa->assertStatus($logoutMerchant, 200, 'Logout merchant fallo');
    $qa->assertStatus($logoutClient, 200, 'Logout client fallo');
    return [
        'merchant' => $logoutMerchant['body']['message'] ?? '',
        'client' => $logoutClient['body']['message'] ?? '',
    ];
});

$summary = $qa->getSummary();
$summary['context'] = [
    'merchant_email' => $context['merchant']['email'],
    'client_email' => $context['client']['email'],
    'store_id' => $context['store_id'],
    'category_id' => $context['category_id'],
    'product_id' => $context['product_id'],
    'order_id' => $context['order_id'],
    'order2_id' => $context['order2_id'],
    'db_host' => config('database.connections.mysql.host'),
    'db_name' => config('database.connections.mysql.database'),
];

$reportMd = [];
$reportMd[] = '# QA Automatica ComercioPlus';
$reportMd[] = '';
$reportMd[] = '- Fecha: ' . date('Y-m-d H:i:s');
$reportMd[] = '- DB host: ' . (string) config('database.connections.mysql.host');
$reportMd[] = '- DB name: ' . (string) config('database.connections.mysql.database');
$reportMd[] = '- Total pruebas: ' . $summary['total'];
$reportMd[] = '- Exitosas: ' . $summary['passed'];
$reportMd[] = '- Fallidas: ' . $summary['failed'];
$reportMd[] = '';
$reportMd[] = '## Contexto generado';
$reportMd[] = '';
$reportMd[] = '- Merchant: ' . $context['merchant']['email'];
$reportMd[] = '- Client: ' . $context['client']['email'];
$reportMd[] = '- Store ID: ' . (string) $context['store_id'];
$reportMd[] = '- Category ID: ' . (string) $context['category_id'];
$reportMd[] = '- Product ID: ' . (string) $context['product_id'];
$reportMd[] = '- Order ID: ' . (string) $context['order_id'];
$reportMd[] = '- Order2 ID: ' . (string) $context['order2_id'];
$reportMd[] = '';
$reportMd[] = '## Resultado detallado';
$reportMd[] = '';
$reportMd[] = '| Modulo | Paso | Estado | Duracion (ms) | Detalle |';
$reportMd[] = '|---|---|---|---:|---|';
foreach ($summary['results'] as $row) {
    $detail = str_replace(["\r", "\n", '|'], [' ', ' ', '/'], (string) ($row['detail'] ?? ''));
    $reportMd[] = '| ' . $row['module'] . ' | ' . $row['step'] . ' | ' . $row['status'] . ' | ' . $row['duration_ms'] . ' | ' . $detail . ' |';
}
$reportMd[] = '';
$reportMd[] = '## Estado final';
$reportMd[] = '';
$reportMd[] = $summary['failed'] === 0
    ? 'PASS - Todos los flujos validados correctamente.'
    : 'FAIL - Existen pruebas fallidas que requieren correccion.';

$reportPath = __DIR__ . '/../QA_REPORT_AUTOMATICO_FULLFLOW.md';
file_put_contents($reportPath, implode(PHP_EOL, $reportMd));

echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo 'REPORT_FILE=' . $reportPath . PHP_EOL;

exit($summary['failed'] === 0 ? 0 : 2);
