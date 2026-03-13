<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reports:generate --type=weekly')
    ->weeklyOn(1, '07:00')
    ->withoutOverlapping();

Schedule::command('reports:generate --type=monthly')
    ->monthlyOn(1, '07:10')
    ->withoutOverlapping();

Schedule::command('reports:generate --type=yearly')
    ->yearlyOn(1, 1, '07:20')
    ->withoutOverlapping();

Schedule::command('inventory:check-reorder')
    ->dailyAt('06:00')
    ->withoutOverlapping();
