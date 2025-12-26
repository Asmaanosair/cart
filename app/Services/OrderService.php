<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private StockService $stockService
    ) {}

    public function createOrderFromCart(User $user): Order
    {
        $cart = $user->cart;

        if (!$cart || $cart->isEmpty()) {
            throw new \Exception('Cart is empty');
        }

        return DB::transaction(function () use ($user, $cart) {
            $order = $user->orders()->create([
                'total' => $cart->getTotal(),
            ]);

            foreach ($cart->items as $cartItem) {
                $order->items()->create([
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                ]);

                $this->stockService->decrementStock(
                    $cartItem->product,
                    $cartItem->quantity
                );
            }

            $cart->clear();

            return $order->load('items.product');
        });
    }

    public function getTodaysSales(): array
    {
        $orders = Order::whereDate('created_at', today())
            ->with('items.product')
            ->get();

        $totalRevenue = $orders->sum('total');
        $totalOrders = $orders->count();

        $productsSold = [];

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $productId = $item->product_id;

                if (!isset($productsSold[$productId])) {
                    $productsSold[$productId] = [
                        'product_name' => $item->product->name,
                        'quantity_sold' => 0,
                        'revenue' => 0,
                    ];
                }

                $productsSold[$productId]['quantity_sold'] += $item->quantity;
                $productsSold[$productId]['revenue'] += $item->getSubtotal();
            }
        }

        return [
            'date' => today()->format('Y-m-d'),
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'products_sold' => array_values($productsSold),
        ];
    }
}
