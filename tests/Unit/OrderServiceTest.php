<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;

beforeEach(function () {
    $this->orderService = app(OrderService::class);
    $this->user = User::factory()->create();
});

test('create order from cart creates order with correct total', function () {
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $product = Product::factory()->create(['price' => 50.00, 'stock_quantity' => 10]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $order = $this->orderService->createOrderFromCart($this->user);

    expect($order)->toBeInstanceOf(Order::class)
        ->and($order->total)->toBe('100.00')
        ->and($order->user_id)->toBe($this->user->id);
});

test('create order from cart creates order items', function () {
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $product1 = Product::factory()->create(['price' => 10.00, 'stock_quantity' => 10]);
    $product2 = Product::factory()->create(['price' => 20.00, 'stock_quantity' => 10]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product1->id,
        'quantity' => 2,
    ]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product2->id,
        'quantity' => 1,
    ]);

    $order = $this->orderService->createOrderFromCart($this->user);

    expect($order->items)->toHaveCount(2);
});

test('create order from cart decrements stock', function () {
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $product = Product::factory()->create(['price' => 50.00, 'stock_quantity' => 10]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 3,
    ]);

    $this->orderService->createOrderFromCart($this->user);

    expect($product->fresh()->stock_quantity)->toBe(7);
});

test('create order from cart clears the cart', function () {
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $product = Product::factory()->create(['price' => 50.00, 'stock_quantity' => 10]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    expect($cart->items)->toHaveCount(1);

    $this->orderService->createOrderFromCart($this->user);

    expect($cart->fresh()->items)->toHaveCount(0);
});

test('create order from empty cart throws exception', function () {
    Cart::factory()->create(['user_id' => $this->user->id]);

    $this->orderService->createOrderFromCart($this->user);
})->throws(\Exception::class, 'Cart is empty');

test('get todays sales returns correct data', function () {
    $product1 = Product::factory()->create(['name' => 'Product 1', 'price' => 100.00, 'stock_quantity' => 20]);
    $product2 = Product::factory()->create(['name' => 'Product 2', 'price' => 50.00, 'stock_quantity' => 20]);

    // Create orders for today
    $order1 = Order::factory()->create([
        'user_id' => $this->user->id,
        'total' => 200.00,
        'created_at' => now(),
    ]);

    $order1->items()->create([
        'product_id' => $product1->id,
        'quantity' => 2,
        'price' => 100.00,
    ]);

    $order2 = Order::factory()->create([
        'user_id' => $this->user->id,
        'total' => 100.00,
        'created_at' => now(),
    ]);

    $order2->items()->create([
        'product_id' => $product2->id,
        'quantity' => 2,
        'price' => 50.00,
    ]);

    $salesData = $this->orderService->getTodaysSales();

    expect($salesData['total_orders'])->toBe(2)
        ->and($salesData['total_revenue'])->toBe(300.0)
        ->and($salesData['products_sold'])->toHaveCount(2)
        ->and($salesData['date'])->toBe(today()->format('Y-m-d'));
});

test('get todays sales excludes yesterday orders', function () {
    $product = Product::factory()->create(['price' => 100.00, 'stock_quantity' => 20]);

    // Create order for yesterday
    $yesterdayOrder = Order::factory()->create([
        'user_id' => $this->user->id,
        'total' => 100.00,
        'created_at' => now()->subDay(),
    ]);

    $yesterdayOrder->items()->create([
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 100.00,
    ]);

    $salesData = $this->orderService->getTodaysSales();

    expect($salesData['total_orders'])->toBe(0)
        ->and($salesData['total_revenue'])->toBe(0)
        ->and($salesData['products_sold'])->toHaveCount(0);
});

test('get todays sales aggregates quantities for same product', function () {
    $product = Product::factory()->create(['name' => 'Product 1', 'price' => 100.00, 'stock_quantity' => 20]);

    // Order 1
    $order1 = Order::factory()->create([
        'user_id' => $this->user->id,
        'total' => 200.00,
        'created_at' => now(),
    ]);

    $order1->items()->create([
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 100.00,
    ]);

    // Order 2
    $order2 = Order::factory()->create([
        'user_id' => $this->user->id,
        'total' => 300.00,
        'created_at' => now(),
    ]);

    $order2->items()->create([
        'product_id' => $product->id,
        'quantity' => 3,
        'price' => 100.00,
    ]);

    $salesData = $this->orderService->getTodaysSales();

    expect($salesData['products_sold'])->toHaveCount(1)
        ->and($salesData['products_sold'][0]['quantity_sold'])->toBe(5)
        ->and($salesData['products_sold'][0]['revenue'])->toBe(500.0);
});
