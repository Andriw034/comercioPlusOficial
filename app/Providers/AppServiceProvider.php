<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use App\Services\Ai\AiTextGenerator;
use App\Services\Ai\AnthropicGenerator;
use App\Services\Ai\GeminiGenerator;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Que IA responde el chat de la tienda, segun AI_PROVIDER. Se resuelve solo
        // cuando alguien pregunta, asi que un valor invalido no tumba la aplicacion:
        // deja el asistente en 503 diciendo exactamente que esta mal configurado.
        $this->app->bind(AiTextGenerator::class, function ($app): AiTextGenerator {
            $provider = (string) config('services.ai.provider');

            return match ($provider) {
                'gemini'    => $app->make(GeminiGenerator::class),
                'anthropic' => $app->make(AnthropicGenerator::class),
                default     => throw new RuntimeException(
                    "Proveedor de IA desconocido: '{$provider}'. AI_PROVIDER debe ser 'gemini' o 'anthropic'."
                ),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        Order::observe(OrderObserver::class);
    }
}
