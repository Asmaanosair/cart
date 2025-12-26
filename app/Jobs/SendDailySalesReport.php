<?php

namespace App\Jobs;

use App\Mail\DailySalesReport;
use App\Services\OrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendDailySalesReport implements ShouldQueue
{
    use Queueable;

    public function handle(OrderService $orderService): void
    {
        $salesData = $orderService->getTodaysSales();
        $adminEmail = config('cart.admin_email');

        Mail::to($adminEmail)->send(
            new DailySalesReport($salesData)
        );
    }
}
