<!DOCTYPE html>
<html>
<head>
    <title>Low Stock Alert</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .alert {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .product-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .product-details p {
            margin: 5px 0;
        }
        .warning {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Low Stock Alert</h2>
        <div class="product-details">
            <h3>Product Information</h3>
            <p><strong>Product Name:</strong> {{ $product->name }}</p>
            <p><strong>Current Stock:</strong> <span class="warning">{{ $product->stock_quantity }} units</span></p>
            <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p></div>
    </div>
</body>
</html>
