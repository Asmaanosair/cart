<!DOCTYPE html>
<html>
<head>
    <title>Daily Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .summary {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .summary-item {
            display: inline-block;
            margin-right: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .total-row {
            font-weight: bold;
            background-color: #e9ecef;
        }
        .no-sales {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2> Daily Sales Report</h2>
        <p><strong>Date:</strong> {{ $salesData['date'] }}</p>

        <div class="summary">
            <div class="summary-item">
                <strong>Total Orders:</strong> {{ $salesData['total_orders'] }}
            </div>
            <div class="summary-item">
                <strong>Total Revenue:</strong> ${{ number_format($salesData['total_revenue'], 2) }}
            </div>
        </div>

        @if(count($salesData['products_sold']) > 0)
            <h3>Products Sold</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesData['products_sold'] as $product)
                        <tr>
                            <td>{{ $product['product_name'] }}</td>
                            <td>{{ $product['quantity_sold'] }}</td>
                            <td>${{ number_format($product['revenue'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>TOTAL</td>
                        <td>{{ array_sum(array_column($salesData['products_sold'], 'quantity_sold')) }}</td>
                        <td>${{ number_format($salesData['total_revenue'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @else
            <div class="no-sales">
                <p>No sales recorded for this day.</p>
            </div>
        @endif

    </div>
</body>
</html>
