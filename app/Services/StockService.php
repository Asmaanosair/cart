<?php

namespace App\Services;

use App\Jobs\SendLowStockNotification;
use App\Models\Product;

class StockService
{
    public function checkAndNotifyLowStock(Product $product): void
    {
        $threshold = config('cart.low_stock_threshold', 5);

        if ($product->isLowStock($threshold)) {
            SendLowStockNotification::dispatch($product);
        }
    }

    public function decrementStock(Product $product, int $quantity): void
    {
        $product->decrementStock($quantity);
        $product->refresh();

        $this->checkAndNotifyLowStock($product);
    }

    public function incrementStock(Product $product, int $quantity): void
    {
        $product->incrementStock($quantity);
    }

}
