<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    use ApiResponse;

    /**
     * @param CartService $cartService
     * @param OrderService $orderService
     * @param User|null $user
     */
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService,
        private ?User $user = null

    ) {
        $this->user = Auth::user();
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $cart = $this->cartService->getUserCart($this->user);

        return $this->successResponse([
            'cart' => $cart,
            'total' => $cart->getTotal(),
        ]);
    }

    /**
     * @param AddToCartRequest $request
     * @return JsonResponse
     */
    public function add(AddToCartRequest $request): JsonResponse
    {
        try {
            $cartItem = $this->cartService->addToCart(
                $this->user,
                $request->validated('product_id'),
                $request->validated('quantity')
            );

            return $this->createdResponse(
                ['cart_item' => $cartItem],
                'Product added to cart'
            );
        } catch (InsufficientStockException $e) {
            return $this->validationErrorResponse(
                null,
                $e->getMessage()
            );
        }
    }

    /**
     * @param UpdateCartItemRequest $request
     * @param int $cartItemId
     * @return JsonResponse
     */
    public function update(UpdateCartItemRequest $request, int $cartItemId): JsonResponse
    {
        try {
            $cartItem = $this->cartService->updateCartItem(
                $this->user,
                $cartItemId,
                $request->validated('quantity')
            );

            return $this->successResponse(
                ['cart_item' => $cartItem],
                'Cart item updated'
            );
        } catch (InsufficientStockException $e) {
            return $this->validationErrorResponse(
                null,
                $e->getMessage()
            );
        }
    }

    public function remove(int $cartItemId): JsonResponse
    {
        $this->cartService->removeFromCart($this->user, $cartItemId);

        return $this->successResponse(
            null,
            'Item removed from cart'
        );
    }

    public function clear(): JsonResponse
    {
        $this->cartService->clearCart($this->user);

        return $this->successResponse(
            null,
            'Cart cleared'
        );
    }

    public function checkout(): JsonResponse
    {
        try {
            $order = $this->orderService->createOrderFromCart($this->user);

            return $this->createdResponse(
                ['order' => $order],
                'Order placed successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                422
            );
        }
    }
}
