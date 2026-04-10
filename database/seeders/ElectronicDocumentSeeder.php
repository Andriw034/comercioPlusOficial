<?php

namespace Database\Seeders;

use App\Models\ElectronicDocument;
use App\Models\ElectronicDocumentItem;
use App\Models\ElectronicDocumentLog;
use App\Models\ElectronicDocumentTax;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ElectronicDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::firstOrFail();
        $orderIds = Order::pluck('id')->toArray();
        $products = Product::where('store_id', $store->id)
            ->select(['id', 'name', 'price', 'sku'])
            ->take(20)
            ->get();

        if ($products->isEmpty()) {
            $this->command->warn('No hay productos para la tienda. Seeder abortado.');
            return;
        }

        $customers = [
            ['type' => 'CC',  'id' => '1098765432', 'name' => 'Carlos Andrés Pérez',       'email' => 'carlos.perez@email.com',    'city' => 'Bogotá',       'dept' => 'Cundinamarca'],
            ['type' => 'CC',  'id' => '1045678901', 'name' => 'María Fernanda López',       'email' => 'maria.lopez@email.com',     'city' => 'Medellín',     'dept' => 'Antioquia'],
            ['type' => 'NIT', 'id' => '900123456-1','name' => 'Repuestos El Rápido S.A.S.', 'email' => 'contabilidad@elrapido.co',  'city' => 'Cali',         'dept' => 'Valle del Cauca'],
            ['type' => 'CC',  'id' => '1112223334', 'name' => 'Jorge Enrique Martínez',     'email' => 'jorge.martinez@email.com',  'city' => 'Barranquilla', 'dept' => 'Atlántico'],
            ['type' => 'CE',  'id' => '987654',     'name' => 'Pedro Miguel Santos',        'email' => 'pedro.santos@email.com',    'city' => 'Cartagena',    'dept' => 'Bolívar'],
            ['type' => 'NIT', 'id' => '800456789-3','name' => 'Motopartes del Norte Ltda.', 'email' => 'ventas@motonorte.co',       'city' => 'Bucaramanga',  'dept' => 'Santander'],
            ['type' => 'CC',  'id' => '1076543210', 'name' => 'Ana Lucía Ramírez',          'email' => 'ana.ramirez@email.com',     'city' => 'Pereira',      'dept' => 'Risaralda'],
            ['type' => 'CC',  'id' => '1023456789', 'name' => 'Diego Alejandro Torres',     'email' => 'diego.torres@email.com',    'city' => 'Manizales',    'dept' => 'Caldas'],
            ['type' => 'PP',  'id' => 'AB1234567',  'name' => 'Roberto Méndez',             'email' => 'roberto.mendez@email.com',  'city' => 'Santa Marta',  'dept' => 'Magdalena'],
            ['type' => 'CC',  'id' => '1087654321', 'name' => 'Laura Patricia Gómez',       'email' => 'laura.gomez@email.com',     'city' => 'Ibagué',       'dept' => 'Tolima'],
        ];

        $statuses = [
            // 5 draft
            ['status' => ElectronicDocument::STATUS_DRAFT,    'cufe' => false],
            ['status' => ElectronicDocument::STATUS_DRAFT,    'cufe' => false],
            ['status' => ElectronicDocument::STATUS_DRAFT,    'cufe' => false],
            ['status' => ElectronicDocument::STATUS_DRAFT,    'cufe' => false],
            ['status' => ElectronicDocument::STATUS_DRAFT,    'cufe' => false],
            // 3 approved
            ['status' => ElectronicDocument::STATUS_APPROVED, 'cufe' => true],
            ['status' => ElectronicDocument::STATUS_APPROVED, 'cufe' => true],
            ['status' => ElectronicDocument::STATUS_APPROVED, 'cufe' => true],
            // 2 rejected
            ['status' => ElectronicDocument::STATUS_REJECTED, 'cufe' => false],
            ['status' => ElectronicDocument::STATUS_REJECTED, 'cufe' => false],
        ];

        foreach ($statuses as $i => $config) {
            $customer = $customers[$i];
            $orderId = !empty($orderIds) ? $orderIds[array_rand($orderIds)] : null;

            $doc = ElectronicDocument::create([
                'store_id'                     => $store->id,
                'order_id'                     => $orderId,
                'document_type'                => ElectronicDocument::TYPE_INVOICE,
                'prefix'                       => 'FE',
                'number'                       => 1000 + $i,
                'cufe'                         => $config['cufe'] ? 'CUFE-MOCK-' . Str::uuid() : null,
                'cude'                         => null,
                'dian_status'                  => $config['status'],
                'dian_track_id'                => $config['cufe'] ? Str::uuid() : null,
                'dian_approved_at'             => $config['status'] === ElectronicDocument::STATUS_APPROVED
                                                    ? now()->subDays(rand(0, 3))
                                                    : null,
                'dian_response_message'        => match ($config['status']) {
                    ElectronicDocument::STATUS_APPROVED => 'Documento autorizado por la DIAN.',
                    ElectronicDocument::STATUS_REJECTED => 'Regla: FAD06 - NIT del adquiriente no válido.',
                    default => null,
                },
                'issuer_nit'                   => '901234567-8',
                'issuer_name'                  => $store->name,
                'issuer_email'                 => 'facturacion@comercioplus.co',
                'issuer_phone'                 => '3001234567',
                'issuer_address'               => 'Calle 100 #15-20 Local 5',
                'issuer_city'                  => 'Bogotá',
                'issuer_department'            => 'Cundinamarca',
                'customer_identification_type' => $customer['type'],
                'customer_identification'      => $customer['id'],
                'customer_name'                => $customer['name'],
                'customer_email'               => $customer['email'],
                'customer_city'                => $customer['city'],
                'customer_department'           => $customer['dept'],
                'subtotal'                     => 0,
                'tax_total'                    => 0,
                'discount_total'               => 0,
                'total'                        => 0,
                'currency'                     => 'COP',
                'payment_method'               => $i % 2 === 0 ? 'contado' : 'credito',
                'payment_means'                => ['efectivo', 'transferencia', 'tarjeta'][rand(0, 2)],
                'payment_due_date'             => $i % 2 === 1 ? now()->addDays(30) : null,
                'metadata'                     => ['seeder' => true, 'version' => '1.0'],
            ]);

            // ── Items (2–5 por factura) ──
            $numItems = rand(2, 5);
            $subtotal = 0;
            $taxTotal = 0;
            $discountTotal = 0;

            $selectedProducts = $products->random(min($numItems, $products->count()));

            foreach ($selectedProducts as $lineIdx => $product) {
                $qty = rand(1, 10);
                $unitPrice = $product->price ?: rand(5000, 200000);
                $discount = round($unitPrice * $qty * (rand(0, 10) / 100), 2);
                $taxRate = [0, 5, 19][rand(0, 2)];
                $lineSubtotal = round($unitPrice * $qty - $discount, 2);
                $taxAmount = round($lineSubtotal * $taxRate / 100, 2);
                $lineTotal = round($lineSubtotal + $taxAmount, 2);

                ElectronicDocumentItem::create([
                    'electronic_document_id' => $doc->id,
                    'product_id'             => $product->id,
                    'line_number'            => $lineIdx + 1,
                    'code'                   => $product->sku ?? 'SKU-' . $product->id,
                    'description'            => $product->name,
                    'unit_measure'           => 'EA',
                    'quantity'               => $qty,
                    'unit_price'             => $unitPrice,
                    'discount'               => $discount,
                    'tax_amount'             => $taxAmount,
                    'line_total'             => $lineTotal,
                    'tax_type'               => 'IVA',
                    'tax_rate'               => $taxRate,
                ]);

                $subtotal += $lineSubtotal;
                $taxTotal += $taxAmount;
                $discountTotal += $discount;
            }

            // ── Impuestos consolidados ──
            $taxGroups = ElectronicDocumentItem::where('electronic_document_id', $doc->id)
                ->selectRaw('tax_type, tax_rate, SUM(line_total - tax_amount) as taxable, SUM(tax_amount) as tax_sum')
                ->groupBy('tax_type', 'tax_rate')
                ->get();

            foreach ($taxGroups as $group) {
                ElectronicDocumentTax::create([
                    'electronic_document_id' => $doc->id,
                    'tax_type'               => $group->tax_type,
                    'tax_rate'               => $group->tax_rate,
                    'taxable_amount'         => round($group->taxable, 2),
                    'tax_amount'             => round($group->tax_sum, 2),
                ]);
            }

            // ── Actualizar totales del documento ──
            $doc->update([
                'subtotal'       => round($subtotal, 2),
                'tax_total'      => round($taxTotal, 2),
                'discount_total' => round($discountTotal, 2),
                'total'          => round($subtotal + $taxTotal, 2),
            ]);

            // ── Log de auditoría ──
            ElectronicDocumentLog::create([
                'electronic_document_id' => $doc->id,
                'action'                 => 'created',
                'status_from'            => null,
                'status_to'              => ElectronicDocument::STATUS_DRAFT,
                'message'                => 'Documento creado por seeder de prueba.',
            ]);

            if ($config['status'] !== ElectronicDocument::STATUS_DRAFT) {
                ElectronicDocumentLog::create([
                    'electronic_document_id' => $doc->id,
                    'action'                 => 'sent_to_dian',
                    'status_from'            => ElectronicDocument::STATUS_DRAFT,
                    'status_to'              => ElectronicDocument::STATUS_PENDING,
                    'message'                => 'Enviado a la DIAN para validación.',
                ]);

                ElectronicDocumentLog::create([
                    'electronic_document_id' => $doc->id,
                    'action'                 => $config['status'] === ElectronicDocument::STATUS_APPROVED ? 'approved' : 'rejected',
                    'status_from'            => ElectronicDocument::STATUS_PENDING,
                    'status_to'              => $config['status'],
                    'message'                => $config['status'] === ElectronicDocument::STATUS_APPROVED
                        ? 'Documento autorizado por la DIAN.'
                        : 'Rechazado: FAD06 - NIT del adquiriente no válido.',
                    'payload'                => [
                        'dian_response_code' => $config['status'] === ElectronicDocument::STATUS_APPROVED ? '200' : '99',
                        'timestamp'          => now()->toIso8601String(),
                    ],
                ]);
            }
        }

        $this->command->info('✓ 10 facturas electrónicas creadas con items, impuestos y logs.');
    }
}
