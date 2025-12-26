<?php

use App\Models\Product;

test('product has correct fillable attributes', function () {
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 99.99,
        'stock_quantity' => 10,
        'description' => 'Test description',
    ]);

    expect($product->name)->toBe('Test Product')
        ->and($product->price)->toBe('99.99')
        ->and($product->stock_quantity)->toBe(10)
        ->and($product->description)->toBe('Test description');
});

test('product can check if it has sufficient stock', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);

    expect($product->hasStock(5))->toBeTrue()
        ->and($product->hasStock(10))->toBeTrue()
        ->and($product->hasStock(11))->toBeFalse();
});

test('product can check if stock is low', function () {
    $product = Product::factory()->create(['stock_quantity' => 3]);

    expect($product->isLowStock(5))->toBeTrue();

    $product->update(['stock_quantity' => 10]);

    expect($product->isLowStock(5))->toBeFalse();
});

test('product can decrement stock', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $product->decrementStock(3);

    expect($product->fresh()->stock_quantity)->toBe(7);
});

test('product can increment stock', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $product->incrementStock(5);

    expect($product->fresh()->stock_quantity)->toBe(15);
});

test('product cannot decrement stock below zero', function () {
    $product = Product::factory()->create(['stock_quantity' => 5]);

    $product->decrementStock(10);

    expect($product->fresh()->stock_quantity)->toBe(0);
});

test('product price is cast to decimal', function () {
    $product = Product::factory()->create(['price' => 99.99]);

    expect($product->price)->toBeString()
        ->and((float) $product->price)->toBe(99.99);
});
