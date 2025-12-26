<?php

use App\Jobs\SendDailySalesReport;
use App\Jobs\SendLowStockNotification;
use App\Models\Product;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

test('sales report daily command dispatches job', function () {
    $this->artisan('sales:report-daily')
        ->assertSuccessful();

    Queue::assertPushed(SendDailySalesReport::class);
});

test('stock notify low command dispatches jobs for low stock products', function () {
    config(['cart.low_stock_threshold' => 5]);

    Product::factory()->create(['stock_quantity' => 2]);
    Product::factory()->create(['stock_quantity' => 3]);
    Product::factory()->create(['stock_quantity' => 10]); // Not low stock

    $this->artisan('stock:notify-low --queue')
        ->expectsOutput('Checking for products with stock <= 5...')
        ->expectsOutput('Found 2 low stock product(s):')
        ->assertSuccessful();

    Queue::assertPushed(SendLowStockNotification::class, 2);
});

test('stock notify low command shows message when no low stock products', function () {
    config(['cart.low_stock_threshold' => 5]);

    Product::factory()->create(['stock_quantity' => 10]);
    Product::factory()->create(['stock_quantity' => 15]);

    $this->artisan('stock:notify-low')
        ->expectsOutput('Checking for products with stock <= 5...')
        ->expectsOutput('No low stock products found.')
        ->assertSuccessful();

    Queue::assertNotPushed(SendLowStockNotification::class);
});
