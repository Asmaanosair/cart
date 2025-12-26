<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

test('cart belongs to a user', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);

    expect($cart->user)->toBeInstanceOf(User::class)
        ->and($cart->user->id)->toBe($user->id);
});

test('cart has many items', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create();

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    expect($cart->items)->toHaveCount(1)
        ->and($cart->items->first())->toBeInstanceOf(CartItem::class);
});

test('cart can calculate total', function () {
    $cart = Cart::factory()->create();
    $product1 = Product::factory()->create(['price' => 10.00]);
    $product2 = Product::factory()->create(['price' => 20.00]);

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

    expect($cart->getTotal())->toBe(40.0); // (10 * 2) + (20 * 1)
});

test('cart can check if it is empty', function () {
    $cart = Cart::factory()->create();

    expect($cart->isEmpty())->toBeTrue();

    $product = Product::factory()->create();
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
    ]);

    expect($cart->fresh()->isEmpty())->toBeFalse();
});

test('cart can be cleared', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create();

    CartItem::factory()->count(3)->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
    ]);

    expect($cart->items)->toHaveCount(3);

    $cart->clear();

    expect($cart->fresh()->items)->toHaveCount(0);
});

test('user can only have one cart', function () {
    $user = User::factory()->create();

    $cart1 = Cart::factory()->create(['user_id' => $user->id]);
    $cart2 = Cart::factory()->create(['user_id' => $user->id]);

    expect(Cart::where('user_id', $user->id)->count())->toBe(2);
})->throws(\Illuminate\Database\QueryException::class);
