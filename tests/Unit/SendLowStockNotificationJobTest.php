<?php

use App\Jobs\SendLowStockNotification;
use App\Mail\LowStockNotification;
use App\Models\Product;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    config(['cart.admin_email' => 'admin@example.com']);
});

test('job sends low stock notification email', function () {
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'stock_quantity' => 2,
    ]);

    $job = new SendLowStockNotification($product);
    $job->handle();

    Mail::assertSent(LowStockNotification::class, function ($mail) use ($product) {
        return $mail->hasTo('admin@example.com')
            && $mail->product->id === $product->id;
    });
});

test('job can be dispatched to queue', function () {
    $product = Product::factory()->create(['stock_quantity' => 2]);

    SendLowStockNotification::dispatch($product);

    Mail::assertNothingSent(); // Not sent until queue is processed
});

test('mailable contains correct product data', function () {
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 99.99,
        'stock_quantity' => 2,
    ]);

    $mailable = new LowStockNotification($product);

    $mailable->assertHasSubject("Low Stock Alert: {$product->name}");
    $mailable->assertSeeInHtml($product->name);
    $mailable->assertSeeInHtml((string) $product->stock_quantity);
});
