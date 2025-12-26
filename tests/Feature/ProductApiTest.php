<?php

use App\Models\Product;

test('can get paginated products list', function () {
    Product::factory()->count(20)->create();

    $response = $this->getJson('/api/products');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'items' => [
                    '*' => ['id', 'name', 'price', 'stock_quantity', 'description'],
                ],
                'pagination' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                    'from',
                    'to',
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
        ]);
});

test('products are paginated with 15 items per page by default', function () {
    Product::factory()->count(20)->create();

    $response = $this->getJson('/api/products');

    $response->assertStatus(200);

    expect($response->json('data.pagination.per_page'))->toBe(15)
        ->and($response->json('data.items'))->toHaveCount(15);
});

test('can customize items per page', function () {
    Product::factory()->count(20)->create();

    $response = $this->getJson('/api/products?per_page=10');

    $response->assertStatus(200);

    expect($response->json('data.pagination.per_page'))->toBe(10)
        ->and($response->json('data.items'))->toHaveCount(10);
});

test('per page is limited to 100 items max', function () {
    Product::factory()->count(150)->create();

    $response = $this->getJson('/api/products?per_page=200');

    $response->assertStatus(200);

    expect($response->json('data.pagination.per_page'))->toBe(100);
});

test('can get products on second page', function () {
    Product::factory()->count(20)->create();

    $response = $this->getJson('/api/products?page=2&per_page=10');

    $response->assertStatus(200);

    expect($response->json('data.pagination.current_page'))->toBe(2)
        ->and($response->json('data.items'))->toHaveCount(10);
});

test('can view single product', function () {
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 99.99,
        'stock_quantity' => 10,
    ]);

    $response = $this->getJson("/api/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'name' => 'Test Product',
                    'price' => '99.99',
                    'stock_quantity' => 10,
                ],
            ],
        ]);
});

test('returns 404 for non-existent product', function () {
    $response = $this->getJson('/api/products/99999');

    $response->assertStatus(404);
});

test('products list is sorted by name', function () {
    Product::factory()->create(['name' => 'Zebra Product']);
    Product::factory()->create(['name' => 'Apple Product']);
    Product::factory()->create(['name' => 'Mango Product']);

    $response = $this->getJson('/api/products');

    $products = $response->json('data.items');

    expect($products[0]['name'])->toBe('Apple Product')
        ->and($products[1]['name'])->toBe('Mango Product');
});
