<?php

use App\Jobs\SendDailySalesReport;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule daily sales report to run every evening at 6:00 PM
Schedule::job(new SendDailySalesReport())
    ->dailyAt('18:00')
    ->timezone('Africa/Cairo')
    ->name('send-daily-sales-report')
    ->emailOutputOnFailure(config('cart.admin_email'));
