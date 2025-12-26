<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('authenticated user can view their cart', function () {
    $response = $this->actingAs($this->user)->getJson('/api/cart');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'cart',
                'total',
            ],
        ])
        ->assertJson([
            'success' => true,
        ]);
});

test('unauthenticated user cannot view cart', function () {
    $response = $this->getJson('/api/cart');

    $response->assertStatus(401);
});

test('can add product to cart', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $response = $this->actingAs($this->user)->postJson('/api/cart/add', [
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Product added to cart',
        ])
        ->assertJsonStructure([
            'data' => [
                'cart_item' => ['id', 'product_id', 'quantity'],
            ],
        ]);
});

test('cannot add product with insufficient stock', function () {
    $product = Product::factory()->create(['stock_quantity' => 5]);

    $response = $this->actingAs($this->user)->postJson('/api/cart/add', [
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('adding product validates required fields', function () {
    $response = $this->actingAs($this->user)->postJson('/api/cart/add', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['product_id', 'quantity']);
});

test('can update cart item quantity', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $cartItem = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $response = $this->actingAs($this->user)->putJson("/api/cart/items/{$cartItem->id}", [
        'quantity' => 5,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Cart item updated',
            'data' => [
                'cart_item' => [
                    'id' => $cartItem->id,
                    'quantity' => 5,
                ],
            ],
        ]);
});

test('cannot update cart item with insufficient stock', function () {
    $product = Product::factory()->create(['stock_quantity' => 3]);
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $cartItem = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $response = $this->actingAs($this->user)->putJson("/api/cart/items/{$cartItem->id}", [
        'quantity' => 10,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('can remove item from cart', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $cartItem = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $response = $this->actingAs($this->user)->deleteJson("/api/cart/items/{$cartItem->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Item removed from cart',
        ]);

    expect(CartItem::find($cartItem->id))->toBeNull();
});

test('can clear entire cart', function () {
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $product = Product::factory()->create(['stock_quantity' => 10]);

    CartItem::factory()->count(3)->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
    ]);

    $response = $this->actingAs($this->user)->deleteJson('/api/cart/clear');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Cart cleared',
        ]);

    expect($cart->fresh()->items)->toHaveCount(0);
});

test('can checkout and create order', function () {
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $product = Product::factory()->create(['price' => 50.00, 'stock_quantity' => 10]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $response = $this->actingAs($this->user)->postJson('/api/cart/checkout');

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Order placed successfully',
        ])
        ->assertJsonStructure([
            'data' => [
                'order' => ['id', 'user_id', 'total', 'items'],
            ],
        ]);

    expect($cart->fresh()->items)->toHaveCount(0);
});

test('cannot checkout with empty cart', function () {
    Cart::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user)->postJson('/api/cart/checkout');

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('checkout decrements product stock', function () {
    $cart = Cart::factory()->create(['user_id' => $this->user->id]);
    $product = Product::factory()->create(['price' => 50.00, 'stock_quantity' => 10]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 3,
    ]);

    $this->actingAs($this->user)->postJson('/api/cart/checkout');

    expect($product->fresh()->stock_quantity)->toBe(7);
});
