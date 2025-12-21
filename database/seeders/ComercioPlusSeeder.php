<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ComercioPlusSeeder extends Seeder
{
    /**
     * Make a username unique across profiles table.
     */
    protected function makeUniqueUsername(string $username, int $user_id): string
    {
        // If profiles table doesn't exist, just return username
        if (!Schema::hasTable('profiles')) {
            return $username;
        }

        $exists = DB::table('profiles')->where('username', $username)->first();
        if (!$exists) {
            return $username;
        }

        // If exists and belongs to same user_id, keep it
        if (isset($exists->user_id) && (int)$exists->user_id === $user_id) {
            return $username;
        }

        // Otherwise generate unique variant
        $attempt = 0;
        do {
            $suffix = '_' . Str::lower(Str::random(3));
            $candidate = $username . $suffix;
            $attempt++;
            if ($attempt > 10) {
                // fallback long suffix
                $candidate = $username . '_' . time() . '_' . rand(100, 999);
                break;
            }
            $found = DB::table('profiles')->where('username', $candidate)->first();
        } while ($found);

        return $candidate;
    }

    /**
     * Helper to set column only if exists.
     */
    protected function setIfColumnExists(string $table, array $data)
    {
        $result = [];
        if (!Schema::hasTable($table)) {
            return $result;
        }
        foreach ($data as $col => $value) {
            if (Schema::hasColumn($table, $col)) {
                $result[$col] = $value;
            }
        }
        return $result;
    }

    public function run()
    {
        // Use DB transactions per major block could be heavy on SQLite, so keep simple.

        // -------------------------
        // ROLES
        // -------------------------
        if (Schema::hasTable('roles')) {
            $roles = [
                ['name' => 'admin'],
                ['name' => 'comerciante'],
                ['name' => 'cliente'],
            ];
            foreach ($roles as $role) {
                DB::table('roles')->updateOrInsert(
                    ['name' => $role['name']],
                    array_merge(
                        $this->setIfColumnExists('roles', ['guard_name' => 'web']),
                        ['created_at' => now(), 'updated_at' => now()]
                    )
                );
            }
        }

        // -------------------------
        // PERMISSIONS
        // -------------------------
        if (Schema::hasTable('permissions')) {
            $permissions = [
                'manage_products',
                'manage_orders',
                'manage_users',
                'view_reports',
            ];
            foreach ($permissions as $perm) {
                DB::table('permissions')->updateOrInsert(
                    ['name' => $perm],
                    array_merge(
                        $this->setIfColumnExists('permissions', ['guard_name' => 'web']),
                        ['created_at' => now(), 'updated_at' => now()]
                    )
                );
            }
        }

        // -------------------------
        // ASIGNAR PERMISOS A ROLES (Spatie tables) -- CORREGIDO OPCIÓN A
        // -------------------------
        if (Schema::hasTable('roles') && Schema::hasTable('permissions') && Schema::hasTable('role_has_permissions')) {
            $roleAdminId = DB::table('roles')->where('name', 'admin')->value('id');
            $roleComercianteId = DB::table('roles')->where('name', 'comerciante')->value('id');

            $allPermissions = DB::table('permissions')->pluck('id')->toArray();
            foreach ($allPermissions as $permId) {
                // build update data only with columns that exist in role_has_permissions
                $updateData = $this->setIfColumnExists('role_has_permissions', [
                    'role_id' => $roleAdminId,
                    'permission_id' => $permId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // if table has none of those extra columns, fallback to minimal payload
                if (empty($updateData)) {
                    $updateData = ['role_id' => $roleAdminId, 'permission_id' => $permId];
                }

                DB::table('role_has_permissions')->updateOrInsert(
                    ['role_id' => $roleAdminId, 'permission_id' => $permId],
                    $updateData
                );
            }

            $comerciantePermissions = DB::table('permissions')
                ->whereIn('name', ['manage_products', 'manage_orders'])
                ->pluck('id')->toArray();

            foreach ($comerciantePermissions as $permId) {
                $updateData = $this->setIfColumnExists('role_has_permissions', [
                    'role_id' => $roleComercianteId,
                    'permission_id' => $permId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (empty($updateData)) {
                    $updateData = ['role_id' => $roleComercianteId, 'permission_id' => $permId];
                }

                DB::table('role_has_permissions')->updateOrInsert(
                    ['role_id' => $roleComercianteId, 'permission_id' => $permId],
                    $updateData
                );
            }
        }

        // -------------------------
        // USERS (10 users)
        // -------------------------
        if (Schema::hasTable('users')) {
            $users = [
                ['name' => 'Carlos Admin', 'email' => 'carlos@admin.com', 'role_name' => 'admin'],
                ['name' => 'Juan Comerciante', 'email' => 'juan@comercio.com', 'role_name' => 'comerciante'],
                ['name' => 'Pedro Comerci1', 'email' => 'pedro1@comercio.com', 'role_name' => 'comerciante'],
                ['name' => 'Laura Comerci2', 'email' => 'laura2@comercio.com', 'role_name' => 'comerciante'],
                ['name' => 'Ana Cliente', 'email' => 'ana@cliente.com', 'role_name' => 'cliente'],
                ['name' => 'Luis Cliente1', 'email' => 'luis1@cliente.com', 'role_name' => 'cliente'],
                ['name' => 'Marta Cliente2', 'email' => 'marta2@cliente.com', 'role_name' => 'cliente'],
                ['name' => 'Diego Cliente3', 'email' => 'diego3@cliente.com', 'role_name' => 'cliente'],
                ['name' => 'Sofia Cliente4', 'email' => 'sofia4@cliente.com', 'role_name' => 'cliente'],
                ['name' => 'Jorge Cliente5', 'email' => 'jorge5@cliente.com', 'role_name' => 'cliente'],
            ];

            foreach ($users as $user) {
                $role_id = Schema::hasTable('roles') ? DB::table('roles')->where('name', $user['role_name'])->value('id') : null;

                // Build data fields, only include columns that exist in users table
                $userData = array_merge(
                    $this->setIfColumnExists('users', [
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'password' => Hash::make('password123'),
                        'phone' => '3001234567',
                        'address' => 'Calle de ejemplo',
                        'status' => 1,
                        'role_id' => $role_id,
                    ]),
                    ['created_at' => now(), 'updated_at' => now()]
                );

                DB::table('users')->updateOrInsert(
                    ['email' => $user['email']],
                    $userData
                );

                // assign spatie model_has_roles row if table exists
                if (Schema::hasTable('model_has_roles') && $role_id) {
                    $uId = DB::table('users')->where('email', $user['email'])->value('id');
                    // ensure we don't insert columns that don't exist
                    $mhrData = $this->setIfColumnExists('model_has_roles', [
                        'role_id' => $role_id,
                        'model_type' => 'App\\Models\\User',
                        'model_id' => $uId,
                    ]);
                    if (empty($mhrData)) {
                        // minimal fallback (some pivot tables may have different columns),
                        // try direct updateOrInsert with known keys
                        DB::table('model_has_roles')->updateOrInsert(
                            ['role_id' => $role_id, 'model_type' => 'App\\Models\\User', 'model_id' => $uId],
                            ['role_id' => $role_id]
                        );
                    } else {
                        DB::table('model_has_roles')->updateOrInsert(
                            ['role_id' => $role_id, 'model_type' => 'App\\Models\\User', 'model_id' => $uId],
                            $mhrData
                        );
                    }
                }
            }
        }

        // -------------------------
        // PROFILES (10)
        // -------------------------
        if (Schema::hasTable('profiles') && Schema::hasTable('users')) {
            $profiles = [
                ['username' => 'carlos_admin', 'email' => 'carlos@admin.com', 'image' => 'avatar1.png'],
                ['username' => 'juan_comercio', 'email' => 'juan@comercio.com', 'image' => 'avatar2.png'],
                ['username' => 'pedro_comercio1', 'email' => 'pedro1@comercio.com', 'image' => 'avatar3.png'],
                ['username' => 'laura_comercio2', 'email' => 'laura2@comercio.com', 'image' => 'avatar4.png'],
                ['username' => 'ana_cliente', 'email' => 'ana@cliente.com', 'image' => 'avatar5.png'],
                ['username' => 'luis_cliente1', 'email' => 'luis1@cliente.com', 'image' => 'avatar6.png'],
                ['username' => 'marta_cliente2', 'email' => 'marta2@cliente.com', 'image' => 'avatar7.png'],
                ['username' => 'diego_cliente3', 'email' => 'diego3@cliente.com', 'image' => 'avatar8.png'],
                ['username' => 'sofia_cliente4', 'email' => 'sofia4@cliente.com', 'image' => 'avatar9.png'],
                ['username' => 'jorge_cliente5', 'email' => 'jorge5@cliente.com', 'image' => 'avatar10.png'],
            ];

            foreach ($profiles as $profile) {
                $user_id = DB::table('users')->where('email', $profile['email'])->value('id');

                // ensure username unique across table
                $usernameToUse = $this->makeUniqueUsername($profile['username'], $user_id);

                $profileData = array_merge(
                    $this->setIfColumnExists('profiles', [
                        'user_id' => $user_id,
                        'username' => $usernameToUse,
                        'image' => $profile['image'],
                        'birthdate' => '1990-01-01',
                        'other_info' => 'Info de ejemplo'
                    ]),
                    ['created_at' => now(), 'updated_at' => now()]
                );

                // updateOrInsert by user_id (profile per user)
                DB::table('profiles')->updateOrInsert(
                    ['user_id' => $user_id],
                    $profileData
                );
            }
        }

        // -------------------------
        // CATEGORIES (10)
        // -------------------------
        if (Schema::hasTable('categories')) {
            $categories = [
                ['name' => 'Motos', 'slug' => 'motos', 'description' => 'Motocicletas'],
                ['name' => 'Repuestos', 'slug' => 'repuestos', 'description' => 'Piezas y accesorios'],
                ['name' => 'Ropa Moto', 'slug' => 'ropa-moto', 'description' => 'Ropa para motociclistas'],
                ['name' => 'Aceites y Lubricantes', 'slug' => 'aceites-lubricantes', 'description' => 'Aceites y lubricantes para motos'],
                ['name' => 'Frenos', 'slug' => 'frenos', 'description' => 'Sistema de frenos'],
                ['name' => 'Transmisión', 'slug' => 'transmision', 'description' => 'Cadenas y transmisión'],
                ['name' => 'Iluminación', 'slug' => 'iluminacion', 'description' => 'Luces y accesorios eléctricos'],
                ['name' => 'Accesorios', 'slug' => 'accesorios', 'description' => 'Accesorios varios'],
                ['name' => 'Herramientas', 'slug' => 'herramientas', 'description' => 'Herramientas para mantenimiento'],
                ['name' => 'Llantas y Rines', 'slug' => 'llantas-rines', 'description' => 'Llantas y rines para motos'],
            ];
            foreach ($categories as $cat) {
                DB::table('categories')->updateOrInsert(
                    ['slug' => $cat['slug']],
                    ['name' => $cat['name'], 'description' => $cat['description'], 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // -------------------------
        // STORES (10)
        // -------------------------
        if (Schema::hasTable('stores') && Schema::hasTable('users')) {
            $stores = [
                ['name' => 'Tienda Motos Carlos', 'email' => 'juan@comercio.com', 'theme' => 'naranja', 'logo' => 'logo1.png', 'cover' => 'cover1.jpg', 'phone' => '3001234567', 'address' => 'Calle 1 #1-1'],
                ['name' => 'Tienda Pedro', 'email' => 'pedro1@comercio.com', 'theme' => 'azul', 'logo' => 'logo2.png', 'cover' => 'cover2.jpg', 'phone' => '3001234568', 'address' => 'Calle 2 #2-2'],
                ['name' => 'Tienda Laura', 'email' => 'laura2@comercio.com', 'theme' => 'verde', 'logo' => 'logo3.png', 'cover' => 'cover3.jpg', 'phone' => '3001234569', 'address' => 'Calle 3 #3-3'],
                ['name' => 'Tienda Demo 1', 'email' => 'juan@comercio.com', 'theme' => 'rojo', 'logo' => 'logo4.png', 'cover' => 'cover4.jpg', 'phone' => '3001234570', 'address' => 'Calle 4 #4-4'],
                ['name' => 'Tienda Demo 2', 'email' => 'pedro1@comercio.com', 'theme' => 'morado', 'logo' => 'logo5.png', 'cover' => 'cover5.jpg', 'phone' => '3001234571', 'address' => 'Calle 5 #5-5'],
                ['name' => 'MotoRepuestos Express', 'email' => 'juan@comercio.com', 'theme' => 'azul', 'logo' => 'logo6.png', 'cover' => 'cover6.jpg', 'phone' => '3001234572', 'address' => 'Calle 6 #6-6'],
                ['name' => 'Accesorios MotoPro', 'email' => 'pedro1@comercio.com', 'theme' => 'verde', 'logo' => 'logo7.png', 'cover' => 'cover7.jpg', 'phone' => '3001234573', 'address' => 'Calle 7 #7-7'],
                ['name' => 'Tienda Central Moto', 'email' => 'laura2@comercio.com', 'theme' => 'rojo', 'logo' => 'logo8.png', 'cover' => 'cover8.jpg', 'phone' => '3001234574', 'address' => 'Calle 8 #8-8'],
                ['name' => 'Repuestos del Valle', 'email' => 'juan@comercio.com', 'theme' => 'morado', 'logo' => 'logo9.png', 'cover' => 'cover9.jpg', 'phone' => '3001234575', 'address' => 'Calle 9 #9-9'],
                ['name' => 'MotoAccesorios Plus', 'email' => 'pedro1@comercio.com', 'theme' => 'naranja', 'logo' => 'logo10.png', 'cover' => 'cover10.jpg', 'phone' => '3001234576', 'address' => 'Calle 10 #10-10'],
            ];

            foreach ($stores as $index => $store) {
                $user_id = DB::table('users')->where('email', $store['email'])->value('id');

                if ($user_id) {
                    $storeData = $this->setIfColumnExists('stores', [
                        'user_id' => $user_id,
                        'name' => $store['name'],
                        'theme' => $store['theme'],
                        'logo' => $store['logo'],
                        'cover' => $store['cover'],
                        'phone' => $store['phone'],
                        'address' => $store['address'],
                        'is_public' => true  // Make all stores public for testing
                    ]);
                    $storeData = array_merge($storeData, ['created_at' => now(), 'updated_at' => now()]);
                    DB::table('stores')->updateOrInsert(['user_id' => $user_id], $storeData);
                }
            }
        }

        // -------------------------
        // PRODUCTS (20)
        // -------------------------
        if (Schema::hasTable('products')) {
            // collect available stores and categories
            $storeNames = Schema::hasTable('stores') ? DB::table('stores')->pluck('name')->toArray() : [];
            $categorySlugs = Schema::hasTable('categories') ? DB::table('categories')->pluck('slug')->toArray() : [];

            // fallback values if none exist
            if (empty($storeNames)) {
                $storeNames = ['Default Store'];
            }
            if (empty($categorySlugs)) {
                $categorySlugs = ['motos'];
            }

            $products = [];
            for ($i = 1; $i <= 20; $i++) {
                $products[] = [
                    'name' => "Producto $i",
                    'category_slug' => $categorySlugs[$i % count($categorySlugs)],
                    'store_name' => $storeNames[$i % count($storeNames)],
                    'price' => rand(50000, 1000000),
                    'is_promo' => rand(0, 1),
                    'promo_price' => rand(25000, 900000),
                ];
            }

            foreach ($products as $prod) {
                $category_id = Schema::hasTable('categories') ? DB::table('categories')->where('slug', $prod['category_slug'])->value('id') : null;
                $store_id = Schema::hasTable('stores') ? DB::table('stores')->where('name', $prod['store_name'])->value('id') : null;

                $baseData = [
                    'name' => $prod['name'],
                    'description' => 'Producto de prueba extendido',
                    'category_id' => $category_id,
                    'price' => $prod['price'],
                    'stock_quantity' => rand(5, 50),
                    'status' => 1,
                    'slug' => Str::slug($prod['name'] . '-' . ($store_id ?? '0')),
                    'is_promo' => $prod['is_promo'],
                    'promo_price' => $prod['is_promo'] ? $prod['promo_price'] : 0,
                ];

                // include only columns that exist
                $productData = array_merge($this->setIfColumnExists('products', $baseData), ['created_at' => now(), 'updated_at' => now()]);

                // identifier: name + store_id if store_id exists
                $where = ['name' => $prod['name']];
                if ($store_id) {
                    $where['store_id'] = $store_id;
                    $productData['store_id'] = $store_id;
                }

                DB::table('products')->updateOrInsert($where, $productData);
            }
        }

        // -------------------------
        // CARTS (10)
        // -------------------------
        if (Schema::hasTable('carts') && Schema::hasTable('users')) {
            $clientEmails = ['ana@cliente.com', 'luis1@cliente.com', 'marta2@cliente.com', 'diego3@cliente.com', 'sofia4@cliente.com', 'jorge5@cliente.com'];

            foreach ($clientEmails as $email) {
                $user_id = DB::table('users')->where('email', $email)->value('id');
                if ($user_id) {
                    $cartData = $this->setIfColumnExists('carts', [
                        'user_id' => $user_id,
                        'status' => 'active'
                    ]);
                    $cartData = array_merge($cartData, ['created_at' => now(), 'updated_at' => now()]);
                    DB::table('carts')->updateOrInsert(['user_id' => $user_id], $cartData);
                }
            }
        }

        // -------------------------
        // CART_PRODUCTS (10)
        // -------------------------
        if (Schema::hasTable('cart_products') && Schema::hasTable('carts') && Schema::hasTable('products')) {
            $products = DB::table('products')->take(10)->get();
            $carts = DB::table('carts')->take(10)->get();

            foreach ($carts as $index => $cart) {
                $product = $products[$index % count($products)];
                $cartProductData = $this->setIfColumnExists('cart_products', [
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => rand(1, 5),
                    'unit_price' => $product->price ?? 0
                ]);
                $cartProductData = array_merge($cartProductData, ['created_at' => now(), 'updated_at' => now()]);
                DB::table('cart_products')->updateOrInsert(
                    ['cart_id' => $cart->id, 'product_id' => $product->id],
                    $cartProductData
                );
            }
        }

        // -------------------------
        // ORDERS, ORDER_PRODUCTS, ORDER_ITEMS
        // -------------------------
        if (Schema::hasTable('orders') && Schema::hasTable('users')) {
            $clientEmail = 'ana@cliente.com';
            $user_id_cliente = DB::table('users')->where('email', $clientEmail)->value('id');

            // pick a store and a product if exist
            $store_id = Schema::hasTable('stores') ? DB::table('stores')->value('id') : null;
            $product = Schema::hasTable('products') ? DB::table('products')->first() : null;

            if ($user_id_cliente && $store_id && $product) {
                $orderBase = [
                    'user_id' => $user_id_cliente,
                    'store_id' => $store_id,
                    'total' => (float) ((isset($product->price) ? $product->price : 0) * 2),
                    'date' => now(),
                    'payment_method' => 'efectivo',
                ];

                // include status only if column exists (some migrations may have CHECK constraints)
                if (Schema::hasColumn('orders', 'status')) {
                    $orderBase['status'] = 'pendiente';
                }

                $orderData = array_merge($this->setIfColumnExists('orders', $orderBase), ['created_at' => now(), 'updated_at' => now()]);

                DB::table('orders')->updateOrInsert(
                    ['user_id' => $user_id_cliente, 'store_id' => $store_id],
                    $orderData
                );

                $order_id = DB::table('orders')
                    ->where('user_id', $user_id_cliente)
                    ->where('store_id', $store_id)
                    ->value('id');

                if ($order_id) {
                    if (Schema::hasTable('order_products')) {
                        $opData = $this->setIfColumnExists('order_products', [
                            'order_id' => $order_id,
                            'product_id' => $product->id,
                            'quantity' => 2,
                            'unit_price' => $product->price ?? 0
                        ]);
                        $opData = array_merge($opData, ['created_at' => now(), 'updated_at' => now()]);
                        DB::table('order_products')->updateOrInsert(['order_id' => $order_id, 'product_id' => $product->id], $opData);
                    }
                    if (Schema::hasTable('order_items')) {
                        $oiData = $this->setIfColumnExists('order_items', [
                            'order_id' => $order_id,
                            'product_id' => $product->id,
                            'quantity' => 2,
                            'price' => $product->price ?? 0
                        ]);
                        $oiData = array_merge($oiData, ['created_at' => now(), 'updated_at' => now()]);
                        DB::table('order_items')->updateOrInsert(['order_id' => $order_id, 'product_id' => $product->id], $oiData);
                    }
                }
            }
        }

        // -------------------------
        // RATINGS (10)
        // -------------------------
        if (Schema::hasTable('ratings') && Schema::hasTable('products') && Schema::hasTable('users')) {
            $products = DB::table('products')->take(10)->get();
            $clientEmails = ['ana@cliente.com', 'luis1@cliente.com', 'marta2@cliente.com', 'diego3@cliente.com', 'sofia4@cliente.com', 'jorge5@cliente.com'];

            $comments = [
                'Excelente producto, muy recomendado',
                'Buena calidad, llegó a tiempo',
                'Producto como se describe',
                'Muy satisfecho con la compra',
                'Calidad superior, volveré a comprar',
                'Perfecto estado, excelente servicio',
                'Me encantó, superó expectativas',
                'Buen precio por la calidad',
                'Producto funcional y resistente',
                'Recomiendo ampliamente'
            ];

            foreach ($clientEmails as $index => $email) {
                $user_id = DB::table('users')->where('email', $email)->value('id');
                $product = $products[$index % count($products)];

                if ($user_id && $product) {
                    DB::table('ratings')->updateOrInsert(
                        ['user_id' => $user_id, 'product_id' => $product->id],
                        [
                            'rating' => rand(3, 5),
                            'comment' => $comments[$index % count($comments)],
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                }
            }
        }

        // -------------------------
        // NOTIFICATIONS
        // -------------------------
        if (Schema::hasTable('notifications') && Schema::hasTable('users')) {
            $user_id = DB::table('users')->where('email', 'ana@cliente.com')->value('id');
            if ($user_id) {
                // only include 'type' if column exists
                $notif = $this->setIfColumnExists('notifications', [
                    'user_id' => $user_id,
                    'type' => 'order',
                    'message' => 'Tu pedido ha sido recibido',
                    'read' => 0
                ]);
                $notif = array_merge($notif, ['created_at' => now(), 'updated_at' => now()]);
                // use compound unique keys if available: user_id + type + message
                DB::table('notifications')->updateOrInsert(
                    ['user_id' => $user_id, 'message' => 'Tu pedido ha sido recibido'],
                    $notif
                );
            }
        }

        // -------------------------
        // SETTINGS
        // -------------------------
        if (Schema::hasTable('settings')) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'site_name'],
                ['value' => 'ComercioPlus', 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // -------------------------
        // CLAIMS (10)
        // -------------------------
        if (Schema::hasTable('claims') && Schema::hasTable('users')) {
            $clientEmails = ['ana@cliente.com', 'luis1@cliente.com', 'marta2@cliente.com', 'diego3@cliente.com', 'sofia4@cliente.com', 'jorge5@cliente.com'];
            $titles = [
                'Producto defectuoso',
                'Producto no llegó',
                'Producto diferente al pedido',
                'Problema con el embalaje',
                'Producto dañado en envío',
                'Talla incorrecta',
                'Color diferente al mostrado',
                'Falta accesorio',
                'Producto no funciona',
                'Problema con la garantía'
            ];
            $descriptions = [
                'El casco llegó con rasguños',
                'El pedido nunca llegó a mi dirección',
                'Recibí un producto diferente al que ordené',
                'El embalaje estaba muy dañado',
                'El producto llegó roto por el envío',
                'La talla no corresponde a la pedida',
                'El color es diferente al de la foto',
                'Faltaba un accesorio importante',
                'El producto no enciende ni funciona',
                'Problemas con la garantía del fabricante'
            ];

            foreach ($clientEmails as $index => $email) {
                $user_id = DB::table('users')->where('email', $email)->value('id');
                if ($user_id) {
                    $claimData = $this->setIfColumnExists('claims', [
                        'user_id' => $user_id,
                        'title' => $titles[$index % count($titles)],
                        'description' => $descriptions[$index % count($descriptions)],
                        'status' => Schema::hasColumn('claims', 'status') ? 'pendiente' : null
                    ]);
                    $claimData = array_filter($claimData, function ($v) {
                        return $v !== null;
                    });
                    $claimData = array_merge($claimData, ['created_at' => now(), 'updated_at' => now()]);
                    DB::table('claims')->updateOrInsert(['user_id' => $user_id, 'title' => $titles[$index % count($titles)]], $claimData);
                }
            }
        }

        // -------------------------
        // CHANNELS
        // -------------------------
        if (Schema::hasTable('channels')) {
            DB::table('channels')->updateOrInsert(
                ['type' => 'WhatsApp'],
                ['link' => 'https://wa.me/3001234567', 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // -------------------------
        // TUTORIALS (10)
        // -------------------------
        if (Schema::hasTable('tutorials')) {
            $tutorials = [
                ['language' => 'es', 'content' => 'Tutorial básico para registrarse en la plataforma'],
                ['language' => 'es', 'content' => 'Cómo crear y gestionar tu tienda en línea'],
                ['language' => 'es', 'content' => 'Guía para agregar productos a tu catálogo'],
                ['language' => 'es', 'content' => 'Tutorial de gestión de pedidos y envíos'],
                ['language' => 'es', 'content' => 'Cómo configurar métodos de pago'],
                ['language' => 'es', 'content' => 'Guía de promociones y descuentos'],
                ['language' => 'es', 'content' => 'Tutorial de atención al cliente'],
                ['language' => 'es', 'content' => 'Cómo analizar reportes de ventas'],
                ['language' => 'es', 'content' => 'Guía de seguridad y privacidad'],
                ['language' => 'es', 'content' => 'Tutorial avanzado de personalización de tienda'],
            ];

            foreach ($tutorials as $index => $tutorial) {
                $tutorialData = $this->setIfColumnExists('tutorials', [
                    'language' => $tutorial['language'],
                    'content' => $tutorial['content']
                ]);
                $tutorialData = array_merge($tutorialData, ['created_at' => now(), 'updated_at' => now()]);
                DB::table('tutorials')->updateOrInsert(['language' => $tutorial['language'], 'content' => $tutorial['content']], $tutorialData);
            }
        }

        // -------------------------
        // PUBLIC STORES (mirror some stores)
        // -------------------------
        if (Schema::hasTable('public_stores') && Schema::hasTable('stores')) {
            $stores = DB::table('stores')->limit(5)->get();
            foreach ($stores as $st) {
                $pub = [
                    'store_id' => $st->id,
                    'user_id' => Schema::hasTable('stores') ? DB::table('stores')->where('id', $st->id)->value('user_id') : null,
                    'name' => $st->name,
                    'nombre_tienda' => $st->name,
                    'slug' => Str::slug($st->name),
                    'descripcion' => 'Tienda pública de ejemplo',
                    'logo' => $st->logo ?? null,
                    'cover' => $st->cover ?? null,
                    'direccion' => $st->address ?? null,
                    'telefono' => $st->phone ?? null,
                    'estado' => 'activa',
                    'horario_atencion' => '8am - 6pm',
                    'categoria_principal' => 'Motos',
                    'calificacion_promedio' => 0,
                ];
                $pub = array_filter($pub, function ($v) {
                    return $v !== null;
                });
                $pub = array_merge($this->setIfColumnExists('public_stores', $pub), ['created_at' => now(), 'updated_at' => now()]);
                DB::table('public_stores')->updateOrInsert(['store_id' => $st->id], $pub);
            }
        }

        // -------------------------
        // ACTIVITY LOGS
        // -------------------------
        if (Schema::hasTable('activity_logs')) {
            $user_id = DB::table('users')->where('email', 'ana@cliente.com')->value('id');
            if ($user_id) {
                $act = $this->setIfColumnExists('activity_logs', [
                    'action' => 'Inició sesión',
                    'model_type' => 'User',
                    'model_id' => $user_id,
                    'user_id' => $user_id,
                    'user_name' => 'Ana Cliente'
                ]);
                $act = array_merge($act, ['created_at' => now(), 'updated_at' => now()]);
                DB::table('activity_logs')->updateOrInsert(['user_id' => $user_id, 'action' => 'Inició sesión'], $act);
            }
        }

        // -------------------------
        // SESSIONS
        // -------------------------
        if (Schema::hasTable('sessions')) {
            $user_id = DB::table('users')->where('email', 'ana@cliente.com')->value('id');

            // Build base session data only for columns that exist
            $sessionBase = [
                'id' => 'session1',
                'user_id' => $user_id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Chrome',
                'payload' => '{}',
                'last_activity' => time()
            ];

            // Keep only the cols that actually exist in sessions table
            $sessionData = $this->setIfColumnExists('sessions', $sessionBase);

            // Add timestamps ONLY if the table has those columns
            if (Schema::hasColumn('sessions', 'created_at') || Schema::hasColumn('sessions', 'updated_at')) {
                $ts = [];
                if (Schema::hasColumn('sessions', 'created_at')) {
                    $ts['created_at'] = now();
                }
                if (Schema::hasColumn('sessions', 'updated_at')) {
                    $ts['updated_at'] = now();
                }
                $sessionData = array_merge($sessionData, $ts);
            }

            // Upsert by primary id if exists (for SQLite id may be varchar)
            if (isset($sessionData['id'])) {
                DB::table('sessions')->updateOrInsert(['id' => $sessionData['id']], $sessionData);
            } elseif ($user_id) {
                DB::table('sessions')->updateOrInsert(['user_id' => $user_id], $sessionData);
            }
        }

        // -------------------------
        // USER SUBSCRIPTIONS (10)
        // -------------------------
        if (Schema::hasTable('user_subscriptions') && Schema::hasTable('users')) {
            $clientEmails = ['ana@cliente.com', 'luis1@cliente.com', 'marta2@cliente.com', 'diego3@cliente.com', 'sofia4@cliente.com', 'jorge5@cliente.com'];
            $subscriptionTypes = ['newsletter', 'promotions', 'updates', 'tips'];

            foreach ($clientEmails as $index => $email) {
                $user_id = DB::table('users')->where('email', $email)->value('id');
                if ($user_id) {
                    $subData = $this->setIfColumnExists('user_subscriptions', [
                        'user_id' => $user_id,
                        'type' => $subscriptionTypes[$index % count($subscriptionTypes)],
                        'status' => 'active',
                        'subscribed_at' => now()
                    ]);
                    $subData = array_merge($subData, ['created_at' => now(), 'updated_at' => now()]);
                    DB::table('user_subscriptions')->updateOrInsert(['user_id' => $user_id, 'type' => $subscriptionTypes[$index % count($subscriptionTypes)]], $subData);
                }
            }
        }

        // -------------------------
        // LOCATIONS (10)
        // -------------------------
        if (Schema::hasTable('locations')) {
            $locations = [
                ['name' => 'Bogotá', 'type' => 'city', 'parent_id' => null],
                ['name' => 'Medellín', 'type' => 'city', 'parent_id' => null],
                ['name' => 'Cali', 'type' => 'city', 'parent_id' => null],
                ['name' => 'Barranquilla', 'type' => 'city', 'parent_id' => null],
                ['name' => 'Cartagena', 'type' => 'city', 'parent_id' => null],
                ['name' => 'Cúcuta', 'type' => 'city', 'parent_id' => null],
                ['name' => 'Bucaramanga', 'type' => 'city', 'parent_id' => null],
                ['name' => 'Pereira', 'type' => 'city', 'parent_id' => null],
                ['name' => 'Santa Marta', 'type' => 'city', 'parent_id' => null],
                ['name' => 'Ibagué', 'type' => 'city', 'parent_id' => null],
            ];

            foreach ($locations as $location) {
                $locData = $this->setIfColumnExists('locations', $location);
                $locData = array_merge($locData, ['created_at' => now(), 'updated_at' => now()]);
                DB::table('locations')->updateOrInsert(['name' => $location['name'], 'type' => $location['type']], $locData);
            }
        }

        // -------------------------
        // SALES (10)
        // -------------------------
        if (Schema::hasTable('sales') && Schema::hasTable('products') && Schema::hasTable('users')) {
            $products = DB::table('products')->take(10)->get();
            $clientEmails = ['ana@cliente.com', 'luis1@cliente.com', 'marta2@cliente.com', 'diego3@cliente.com', 'sofia4@cliente.com', 'jorge5@cliente.com'];

            foreach ($clientEmails as $index => $email) {
                $user_id = DB::table('users')->where('email', $email)->value('id');
                $product = $products[$index % count($products)];

                if ($user_id && $product) {
                    $saleData = $this->setIfColumnExists('sales', [
                        'user_id' => $user_id,
                        'product_id' => $product->id,
                        'quantity' => rand(1, 3),
                        'unit_price' => $product->price ?? 0,
                        'total' => ($product->price ?? 0) * rand(1, 3),
                        'date' => now()->subDays(rand(1, 30))
                    ]);
                    $saleData = array_merge($saleData, ['created_at' => now(), 'updated_at' => now()]);
                    DB::table('sales')->updateOrInsert(['user_id' => $user_id, 'product_id' => $product->id, 'date' => $saleData['date']], $saleData);
                }
            }
        }

        $this->command->info('Seeder extendido ComercioPlus ejecutado correctamente (idempotente, compatible SQLite).');
    }
}
