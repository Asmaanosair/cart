<?php

use App\Exceptions\InsufficientStockException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;

beforeEach(function () {
    $this->cartService = app(CartService::class);
    $this->user = User::factory()->create();
});

test('get user cart returns existing cart', function () {
    $existingCart = Cart::factory()->create(['user_id' => $this->user->id]);

    $cart = $this->cartService->getUserCart($this->user);

    expect($cart->id)->toBe($existingCart->id);
});

test('get user cart creates new cart if not exists', function () {
    expect(Cart::where('user_id', $this->user->id)->exists())->toBeFalse();

    $cart = $this->cartService->getUserCart($this->user);

    expect($cart)->toBeInstanceOf(Cart::class)
        ->and($cart->user_id)->toBe($this->user->id)
        ->and(Cart::where('user_id', $this->user->id)->exists())->toBeTrue();
});

test('add to cart creates new cart item', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $cartItem = $this->cartService->addToCart($this->user, $product->id, 2);

    expect($cartItem)->toBeInstanceOf(CartItem::class)
        ->and($cartItem->product_id)->toBe($product->id)
        ->and($cartItem->quantity)->toBe(2);
});

test('add to cart throws exception when insufficient stock', function () {
    $product = Product::factory()->create(['stock_quantity' => 5]);

    $this->cartService->addToCart($this->user, $product->id, 10);
})->throws(InsufficientStockException::class);

test('add to cart updates quantity if item already exists', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $cartItem = $this->cartService->addToCart($this->user, $product->id, 3);

    expect($cartItem->quantity)->toBe(5);
});

test('update cart item changes quantity', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $cartItem = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $updatedItem = $this->cartService->updateCartItem($this->user, $cartItem->id, 5);

    expect($updatedItem->quantity)->toBe(5);
});

test('update cart item throws exception when insufficient stock', function () {
    $product = Product::factory()->create(['stock_quantity' => 3]);
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $cartItem = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $this->cartService->updateCartItem($this->user, $cartItem->id, 10);
})->throws(InsufficientStockException::class);

test('remove from cart deletes cart item', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $cartItem = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    expect(CartItem::find($cartItem->id))->not->toBeNull();

    $this->cartService->removeFromCart($this->user, $cartItem->id);

    expect(CartItem::find($cartItem->id))->toBeNull();
});

test('clear cart removes all items', function () {
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $product = Product::factory()->create(['stock_quantity' => 10]);

    CartItem::factory()->count(3)->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
    ]);

    expect($cart->items)->toHaveCount(3);

    $this->cartService->clearCart($this->user);

    expect($cart->fresh()->items)->toHaveCount(0);
});

test('get cart total returns correct sum', function () {
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
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
        'quantity' => 3,
    ]);

    $total = $this->cartService->getCartTotal($this->user);

    expect($total)->toBe(80.0); // (10 * 2) + (20 * 3) = 20 + 60
});
