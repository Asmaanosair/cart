<?php

use App\Jobs\SendDailySalesReport;
use App\Mail\DailySalesReport;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    config(['cart.admin_email' => 'admin@example.com']);
});

test('job sends daily sales report email', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100.00, 'stock_quantity' => 20]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'total' => 200.00,
        'created_at' => now(),
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 100.00,
    ]);

    $job = new SendDailySalesReport();
    $job->handle(app(\App\Services\OrderService::class));

    Mail::assertSent(DailySalesReport::class, function ($mail) {
        return $mail->hasTo('admin@example.com');
    });
});

test('job can be dispatched to queue', function () {
    SendDailySalesReport::dispatch();

    Mail::assertNothingSent(); // Not sent until queue is processed
});

test('mailable contains sales data', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['name' => 'Test Product', 'price' => 100.00, 'stock_quantity' => 20]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'total' => 200.00,
        'created_at' => now(),
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 100.00,
    ]);

    $salesData = app(\App\Services\OrderService::class)->getTodaysSales();
    $mailable = new DailySalesReport($salesData);

    $mailable->assertSeeInHtml('Test Product');
    $mailable->assertSeeInHtml('200');
    $mailable->assertHasSubject('Daily Sales Report - ' . $salesData['date']);
});

test('report includes multiple products', function () {
    $user = User::factory()->create();
    $product1 = Product::factory()->create(['name' => 'Product 1', 'price' => 100.00, 'stock_quantity' => 20]);
    $product2 = Product::factory()->create(['name' => 'Product 2', 'price' => 50.00, 'stock_quantity' => 20]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'total' => 250.00,
        'created_at' => now(),
    ]);

    $order->items()->create([
        'product_id' => $product1->id,
        'quantity' => 2,
        'price' => 100.00,
    ]);

    $order->items()->create([
        'product_id' => $product2->id,
        'quantity' => 1,
        'price' => 50.00,
    ]);

    $salesData = app(\App\Services\OrderService::class)->getTodaysSales();
    $mailable = new DailySalesReport($salesData);

    $mailable->assertSeeInHtml('Product 1');
    $mailable->assertSeeInHtml('Product 2');
});

test('report shows no sales when there are none', function () {
    $salesData = app(\App\Services\OrderService::class)->getTodaysSales();
    $mailable = new DailySalesReport($salesData);

    expect($salesData['total_orders'])->toBe(0)
        ->and($salesData['total_revenue'])->toBe(0);

    $mailable->assertSeeInHtml('No sales recorded');
});
