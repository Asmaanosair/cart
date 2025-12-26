<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function getUserCart(User $user): Cart
    {
        return $user->getOrCreateCart()->load('items.product');
    }

    /**
     * @throws InsufficientStockException
     */
    public function addToCart(User $user, int $productId, int $quantity): CartItem
    {
        $product = Product::findOrFail($productId);
        if (!$product->hasStock($quantity)) {
            throw new InsufficientStockException(
                "Insufficient stock for {$product->name}. Available: {$product->stock_quantity}"
            );
        }

        $cart = $user->getOrCreateCart();

        $cartItem = $cart->items()->where('product_id', $productId)->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;

            if (!$product->hasStock($newQuantity)) {
                throw new InsufficientStockException(
                    "Insufficient stock for {$product->name}. Available: {$product->stock_quantity}"
                );
            }

            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            $cartItem = $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        return $cartItem->load('product');
    }

    /**
     * @throws InsufficientStockException
     */
    public function updateCartItem(User $user, int $cartItemId, int $quantity): CartItem
    {
        $cart = $user->getOrCreateCart();

        $cartItem = $cart->items()->findOrFail($cartItemId);

        if (!$cartItem->product->hasStock($quantity)) {
            throw new InsufficientStockException(
                "Insufficient stock for {$cartItem->product->name}. Available: {$cartItem->product->stock_quantity}"
            );
        }

        $cartItem->update(['quantity' => $quantity]);

        return $cartItem->load('product');
    }

    public function removeFromCart(User $user, int $cartItemId): void
    {
        $cart = $user->getOrCreateCart();

        $cart->items()->findOrFail($cartItemId)->delete();
    }

    public function clearCart(User $user): void
    {
        $cart = $user->getOrCreateCart();
        $cart->clear();
    }

    public function getCartTotal(User $user): float
    {
        $cart = $user->getOrCreateCart();
        return $cart->getTotal();
    }
}
