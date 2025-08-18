<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Ejemplos de cron (opcional):
        // $schedule->command('users:clear')->dailyAt('03:00');
        // $schedule->command('passwords:update')->weeklyOn(1, '02:30'); // lunes 02:30
        // $schedule->command('stores:check-columns')->hourly();
        // $schedule->command('products:check-table')->everyThirtyMinutes();
        // $schedule->command('public-stores:create-table')->dailyAt('01:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // Autocargar todo lo que esté en app/Console/Commands
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
