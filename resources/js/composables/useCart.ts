import { ref } from 'vue';
import axios from 'axios';

export interface Product {
    id: number;
    name: string;
    price: number;
    stock_quantity: number;
    description?: string;
}

export interface CartItem {
    id: number;
    product_id: number;
    quantity: number;
    product: Product;
}

export interface Cart {
    id: number;
    user_id: number;
    items: CartItem[];
}

export function useCart() {
    const cart = ref<Cart | null>(null);
    const loading = ref(false);
    const error = ref<string | null>(null);

    const fetchCart = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get('/api/cart');
            cart.value = response.data.data.cart;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to fetch cart';
        } finally {
            loading.value = false;
        }
    };

    const addToCart = async (productId: number, quantity: number = 1) => {
        loading.value = true;
        error.value = null;

        try {
            await axios.post('/api/cart/add', {
                product_id: productId,
                quantity,
            });
            await fetchCart();
            return true;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to add to cart';
            return false;
        } finally {
            loading.value = false;
        }
    };

    const updateCartItem = async (cartItemId: number, quantity: number) => {
        loading.value = true;
        error.value = null;

        try {
            await axios.put(`/api/cart/items/${cartItemId}`, { quantity });
            await fetchCart();
            return true;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to update cart';
            return false;
        } finally {
            loading.value = false;
        }
    };

    const removeFromCart = async (cartItemId: number) => {
        loading.value = true;
        error.value = null;

        try {
            await axios.delete(`/api/cart/items/${cartItemId}`);
            await fetchCart();
            return true;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to remove from cart';
            return false;
        } finally {
            loading.value = false;
        }
    };

    const clearCart = async () => {
        loading.value = true;
        error.value = null;

        try {
            await axios.delete('/api/cart/clear');
            await fetchCart();
            return true;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to clear cart';
            return false;
        } finally {
            loading.value = false;
        }
    };
    const getCartTotal = () => {
        if (!cart.value?.items) return 0;

        return cart.value.items.reduce((total, item) => {
            return total + (Number(item.product.price) * item.quantity);
        }, 0);
    };
    const getCartItemsCount = () => {
        if (!cart.value?.items) return 0;

        return cart.value.items.reduce((count, item) => {
            return count + item.quantity;
        }, 0);
    };

    const checkout = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.post('/api/cart/checkout');
            await fetchCart();
            return { success: true, order: response.data.order };
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to checkout';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    };

    return {
        cart,
        loading,
        error,
        fetchCart,
        addToCart,
        updateCartItem,
        removeFromCart,
        clearCart,
        checkout,
        getCartTotal,
        getCartItemsCount,
    };
}
