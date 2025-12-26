<?php

use App\Jobs\SendLowStockNotification;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->stockService = app(StockService::class);
    Queue::fake();
});

test('decrement stock reduces product quantity', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->stockService->decrementStock($product, 3);

    expect($product->fresh()->stock_quantity)->toBe(7);
});

test('decrement stock dispatches low stock notification when threshold is met', function () {
    config(['cart.low_stock_threshold' => 5]);

    $product = Product::factory()->create(['stock_quantity' => 7]);

    $this->stockService->decrementStock($product, 3); // Now stock = 4

    Queue::assertPushed(SendLowStockNotification::class, function ($job) use ($product) {
        return $job->product->id === $product->id;
    });
});

test('decrement stock does not dispatch notification when stock is above threshold', function () {
    config(['cart.low_stock_threshold' => 5]);

    $product = Product::factory()->create(['stock_quantity' => 20]);

    $this->stockService->decrementStock($product, 3); // Now stock = 17

    Queue::assertNotPushed(SendLowStockNotification::class);
});

test('increment stock increases product quantity', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->stockService->incrementStock($product, 5);

    expect($product->fresh()->stock_quantity)->toBe(15);
});

test('get low stock products returns products below threshold', function () {
    config(['cart.low_stock_threshold' => 5]);

    Product::factory()->create(['stock_quantity' => 3]);
    Product::factory()->create(['stock_quantity' => 10]);
    Product::factory()->create(['stock_quantity' => 5]);

    $lowStockProducts = $this->stockService->getLowStockProducts();

    expect($lowStockProducts)->toHaveCount(2);
});

test('check and notify low stock dispatches notification job', function () {
    config(['cart.low_stock_threshold' => 5]);

    $product = Product::factory()->create(['stock_quantity' => 3]);

    $this->stockService->checkAndNotifyLowStock($product);

    Queue::assertPushed(SendLowStockNotification::class);
});

test('check and notify low stock does not dispatch when stock is sufficient', function () {
    config(['cart.low_stock_threshold' => 5]);

    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->stockService->checkAndNotifyLowStock($product);

    Queue::assertNotPushed(SendLowStockNotification::class);
});
