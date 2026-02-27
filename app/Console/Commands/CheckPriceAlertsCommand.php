<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\ProductAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckPriceAlertsCommand extends Command
{
    protected $signature = 'alerts:check-prices';

    protected $description = 'Verifica alertas de precio y notifica a usuarios cuando el precio baja al objetivo';

    public function handle(): int
    {
        $triggered = 0;

        ProductAlert::query()
            ->where('is_triggered', false)
            ->with('product:id,name,price,status', 'user:id,name')
            ->chunkById(100, function ($alerts) use (&$triggered) {
                foreach ($alerts as $alert) {
                    if (! $alert->product || $alert->product->status !== 'active') {
                        continue;
                    }

                    if ((float) $alert->product->price <= (float) $alert->target_price) {
                        DB::transaction(function () use ($alert, &$triggered) {
                            $lockedAlert = ProductAlert::query()->lockForUpdate()->find($alert->id);
                            if (! $lockedAlert || $lockedAlert->is_triggered) {
                                return;
                            }

                            $lockedAlert->update([
                                'is_triggered' => true,
                                'triggered_at' => now(),
                            ]);

                            $product = $alert->product;
                            if (! $product) {
                                return;
                            }

                            $priceFormatted = number_format((float) $product->price, 0, ',', '.');
                            Notification::query()->create([
                                'user_id' => $lockedAlert->user_id,
                                'type' => 'price_alert',
                                'message' => "El precio de \"{$product->name}\" bajó a \${$priceFormatted} COP. ¡Es el precio que esperabas!",
                                'read' => false,
                            ]);

                            $triggered++;
                        });
                    }
                }
            });

        if ($triggered > 0) {
            $this->info("Se dispararon {$triggered} alerta(s) de precio.");
        } else {
            $this->info('No hay alertas de precio que notificar.');
        }

        return self::SUCCESS;
    }
}
