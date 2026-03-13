<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSuppliersTestSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Asegura que los primeros 10 productos tengan stock crítico ──
        $products = DB::table('products')
            ->orderBy('id')
            ->limit(10)
            ->get();

        foreach ($products as $p) {
            DB::table('products')->where('id', $p->id)->update([
                'reorder_point' => rand(5, 20),
                'stock'         => rand(0, 2),
            ]);
        }

        $alertable = DB::table('products')
            ->where('reorder_point', '>', 0)
            ->where('stock', '<=', DB::raw('reorder_point * 2'))
            ->count();

        $this->command->info("Productos alertables: {$alertable}");

        // ── 2. Inserta proveedores de prueba para los primeros 5 productos ──
        DB::table('product_suppliers')->truncate();

        $ids = DB::table('products')->orderBy('id')->limit(5)->pluck('id');

        $suppliers = [
            [
                'nombre'         => 'Distribuidora Nacional S.A.S',
                'phone'          => '+573001234567',
                'purchase_price' => 12500.00,
                'delivery_days'  => 2,
                'is_primary'     => true,
            ],
            [
                'nombre'         => 'Importaciones Andes Ltda',
                'phone'          => '+573109876543',
                'purchase_price' => 11800.00,
                'delivery_days'  => 5,
                'is_primary'     => false,
            ],
            [
                'nombre'         => 'Proveedor Express Colombia',
                'phone'          => '+573207654321',
                'purchase_price' => 13200.00,
                'delivery_days'  => 1,
                'is_primary'     => false,
            ],
        ];

        $now = now();
        $rows = [];

        foreach ($ids as $productId) {
            // Cada producto recibe 2 proveedores (1 principal + 1 alternativo)
            $rows[] = [
                'product_id'     => $productId,
                'supplier_name'  => $suppliers[0]['nombre'],
                'supplier_phone' => $suppliers[0]['phone'],
                'purchase_price' => $suppliers[0]['purchase_price'] * (1 + rand(-10, 10) / 100),
                'delivery_days'  => $suppliers[0]['delivery_days'],
                'is_primary'     => true,
                'notes'          => 'Proveedor principal de prueba',
                'created_at'     => $now,
                'updated_at'     => $now,
            ];

            $alt = $suppliers[rand(1, 2)];
            $rows[] = [
                'product_id'     => $productId,
                'supplier_name'  => $alt['nombre'],
                'supplier_phone' => $alt['phone'],
                'purchase_price' => $alt['purchase_price'] * (1 + rand(-10, 10) / 100),
                'delivery_days'  => $alt['delivery_days'],
                'is_primary'     => false,
                'notes'          => 'Proveedor alternativo de prueba',
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        DB::table('product_suppliers')->insert($rows);

        $total = DB::table('product_suppliers')->count();
        $this->command->info("Proveedores insertados: {$total} ({$ids->count()} productos × 2 proveedores)");

        // ── 3. Resumen final ──
        $this->command->newLine();
        $this->command->info('=== Estado del Centro de Reposición ===');

        $byPriority = DB::table('products as p')
            ->selectRaw("
                SUM(CASE WHEN p.stock = 0 THEN 1 ELSE 0 END) as critical,
                SUM(CASE WHEN p.stock > 0 AND p.stock <= p.reorder_point THEN 1 ELSE 0 END) as high,
                COUNT(*) as total
            ")
            ->where('p.reorder_point', '>', 0)
            ->where('p.stock', '<=', DB::raw('p.reorder_point * 2'))
            ->first();

        $this->command->line("  Críticos (stock=0): {$byPriority->critical}");
        $this->command->line("  Alto (stock<=reorder): {$byPriority->high}");
        $this->command->line("  Total alertables: {$byPriority->total}");
        $this->command->line("  Con proveedores: {$ids->count()} productos");
        $this->command->newLine();
        $this->command->info('Listo. Abre el tab "Decisiones IA" para ver el centro de reposición.');
    }
}
