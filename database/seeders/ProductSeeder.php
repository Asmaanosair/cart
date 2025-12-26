<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Laptop',
                'description' => 'High-performance laptop for professionals',
                'price' => 1299.99,
                'stock_quantity' => 15,
            ],
            [
                'name' => 'Wireless Mouse',
                'description' => 'Ergonomic wireless mouse with precision tracking',
                'price' => 29.99,
                'stock_quantity' => 50,
            ],
            [
                'name' => 'Mechanical Keyboard',
                'description' => 'RGB mechanical keyboard with Cherry MX switches',
                'price' => 149.99,
                'stock_quantity' => 25,
            ],
            [
                'name' => 'USB-C Hub',
                'description' => '7-in-1 USB-C hub with HDMI and card reader',
                'price' => 49.99,
                'stock_quantity' => 3,
            ],
            [
                'name' => 'Webcam HD',
                'description' => '1080p HD webcam with auto-focus',
                'price' => 79.99,
                'stock_quantity' => 8,
            ],
            [
                'name' => 'Headphones',
                'description' => 'Noise-cancelling over-ear headphones',
                'price' => 199.99,
                'stock_quantity' => 12,
            ],
            [
                'name' => 'Monitor 27"',
                'description' => '4K UHD 27-inch monitor with HDR',
                'price' => 449.99,
                'stock_quantity' => 2,
            ],
            [
                'name' => 'Desk Lamp',
                'description' => 'LED desk lamp with adjustable brightness',
                'price' => 39.99,
                'stock_quantity' => 20,
            ],
            [
                'name' => 'External SSD 1TB',
                'description' => 'Portable SSD with fast read/write speeds',
                'price' => 119.99,
                'stock_quantity' => 0,
            ],
            [
                'name' => 'Phone Stand',
                'description' => 'Adjustable phone stand for desk',
                'price' => 19.99,
                'stock_quantity' => 7,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
