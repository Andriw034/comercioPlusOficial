<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Store;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompleteTestDataSeeder extends Seeder
{
    public function run()
    {
        // Crear usuarios de prueba con diferentes roles
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@comercioreal.com',
                'password' => Hash::make('your_admin_password'),
                'role' => 'super-admin',
                'phone' => '1234567890',
                'status' => true,
            ],
            [
                'name' => 'Comerciante Ejemplo',
                'email' => 'comerciante@comercioreal.com',
                'password' => Hash::make('your_merchant_password'),
                'role' => 'comerciante',
                'phone' => '0987654321',
                'status' => true,
            ],
            [
                'name' => 'Cliente Ejemplo',
                'email' => 'cliente@comercioreal.com',
                'password' => Hash::make('your_customer_password'),
                'role' => 'cliente',
                'phone' => '5551234567',
                'status' => true,
            ]
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            $user->assignRole($userData['role']);
        }

        // Crear una tienda para el comerciante
        $merchant = User::where('email', 'comerciante@comercioreal.com')->first();
        
        $store = Store::updateOrCreate(
            ['user_id' => $merchant->id],
            [
                'name' => 'Tienda Ejemplo',
                'slug' => 'tienda-ejemplo',
                'description' => 'Una tienda de ejemplo con productos de calidad',
                'direccion' => 'Calle Principal 123',
                'telefono' => '5559876543',
                'logo' => null,
                'cover_image' => null,
                'estado' => 'activa',
                'categoria_principal' => 'Electrónicos',
            ]
        );

        // Crear categorías
        $categories = [
            ['name' => 'Electrónicos'],
            ['name' => 'Ropa'],
            ['name' => 'Hogar'],
        ];

        foreach ($categories as $categoryData) {
            Category::updateOrCreate(
                ['name' => $categoryData['name']],
                array_merge($categoryData, ['store_id' => $store->id])
            );
        }

        // Crear productos
        $electronicsCategory = Category::where('name', 'Electrónicos')->first();
        $ropaCategory = Category::where('name', 'Ropa')->first();

        $products = [
            [
                'name' => 'Smartphone Android',
                'description' => 'Smartphone Android de última generación',
                'price' => 299.99,
                'stock' => 50,
                'category_id' => $electronicsCategory->id,
                'store_id' => $store->id,
                'user_id' => $merchant->id,
                'image' => null,
                'offer' => false,
                'average_rating' => 0,
            ],
            [
                'name' => 'Laptop Gaming',
                'description' => 'Laptop potente para gaming',
                'price' => 899.99,
                'stock' => 15,
                'category_id' => $electronicsCategory->id,
                'store_id' => $store->id,
                'user_id' => $merchant->id,
                'image' => null,
                'offer' => false,
                'average_rating' => 0,
            ],
            [
                'name' => 'Camiseta Básica',
                'description' => 'Camiseta de algodón 100%',
                'price' => 19.99,
                'stock' => 100,
                'category_id' => $ropaCategory->id,
                'store_id' => $store->id,
                'user_id' => $merchant->id,
                'image' => null,
                'offer' => false,
                'average_rating' => 0,
            ],
            [
                'name' => 'Jeans Clásicos',
                'description' => 'Jeans de mezclilla de alta calidad',
                'price' => 49.99,
                'stock' => 75,
                'category_id' => $ropaCategory->id,
                'store_id' => $store->id,
                'user_id' => $merchant->id,
                'image' => null,
                'offer' => false,
                'average_rating' => 0,
            ],
        ];

        foreach ($products as $productData) {
            Product::updateOrCreate(
                ['name' => $productData['name'], 'store_id' => $store->id],
                $productData
            );
        }

        // Crear pedidos
        $customer = User::where('email', 'cliente@comercioreal.com')->first();
        $smartphone = Product::where('name', 'Smartphone Android')->first();
        $camiseta = Product::where('name', 'Camiseta Básica')->first();

        $order = Order::create([
            'user_id' => $customer->id,
            'total' => 319.98,
            'date' => now(),
            'payment_method' => 'credit_card',
        ]);

        // Agregar productos al pedido
        OrderProduct::create([
            'order_id' => $order->id,
            'product_id' => $smartphone->id,
            'quantity' => 1,
            'unit_price' => $smartphone->price,
        ]);

        OrderProduct::create([
            'order_id' => $order->id,
            'product_id' => $camiseta->id,
            'quantity' => 1,
            'unit_price' => $camiseta->price,
        ]);

        $this->command->info('Datos de prueba completos creados exitosamente');
        $this->command->info('Usuarios creados:');
        $this->command->info('- Admin: admin@comercioreal.com');
        $this->command->info('- Comerciante: comerciante@comercioreal.com');
        $this->command->info('- Cliente: cliente@comercioreal.com');
        $this->command->warn('IMPORTANTE: Cambie las contraseñas por defecto inmediatamente después de la instalación');
        $this->command->info('Tienda: Tienda Ejemplo (slug: tienda-ejemplo)');
        $this->command->info('Productos: 4 productos creados');
        $this->command->info('Pedido: 1 pedido de ejemplo creado');
    }
}
