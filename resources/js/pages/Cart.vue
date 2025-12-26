<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import CartItemComponent from '@/components/CartItemComponent.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Spinner } from '@/components/ui/spinner';
import { ShoppingCart } from 'lucide-vue-next';
import { useCart } from '@/composables/useCart';

const {
    cart,
    loading,
    error,
    fetchCart,
    updateCartItem,
    removeFromCart,
    clearCart,
    checkout,
    getCartTotal,
    getCartItemsCount,
} = useCart();

const successMessage = ref<string | null>(null);
const isCheckingOut = ref(false);

const showSuccess = (message: string) => {
    successMessage.value = message;
    setTimeout(() => {
        successMessage.value = null;
    }, 3000);
};

const handleUpdateItem = async (cartItemId: number, quantity: number) => {
    const success = await updateCartItem(cartItemId, quantity);

    if (success) {
        showSuccess('Cart updated successfully');
    }
};

const handleRemoveItem = async (cartItemId: number) => {
    const success = await removeFromCart(cartItemId);

    if (success) {
        showSuccess('Item removed from cart');
    }
};

const handleClearCart = async () => {
    if (!confirm('Are you sure you want to clear your cart?')) return;

    const success = await clearCart();

    if (success) {
        showSuccess('Cart cleared successfully');
    }
};

const handleCheckout = async () => {
    if (!confirm('Proceed to checkout and place your order?')) return;

    isCheckingOut.value = true;
    const result = await checkout();
    isCheckingOut.value = false;

    if (result.success) {
        showSuccess('Order placed successfully! Your cart has been cleared.');
    }
};

onMounted(() => {
    fetchCart();
});
</script>

<template>
    <Head title="Shopping Cart" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6 flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold">Shopping Cart</h1>
                        <p class="text-muted-foreground">
                            {{ getCartItemsCount() }} item(s) in your cart
                        </p>
                    </div>

                    <Button
                        v-if="cart?.items && cart.items.length > 0"
                        @click="handleClearCart"
                        variant="outline"
                        :disabled="loading"
                    >
                        Clear Cart
                    </Button>
                </div>

                <Alert v-if="error" variant="destructive" class="mb-6">
                    <AlertDescription>{{ error }}</AlertDescription>
                </Alert>

                <Alert v-if="successMessage" class="mb-6 bg-green-50 border-green-200">
                    <AlertDescription class="text-green-800">{{ successMessage }}</AlertDescription>
                </Alert>

                <div v-if="loading && !cart" class="flex justify-center py-12">
                    <Spinner />
                </div>

                <div v-else-if="!cart?.items || cart.items.length === 0" class="text-center py-12">
                    <div class="flex flex-col items-center gap-4">
                        <ShoppingCart class="h-16 w-16 text-muted-foreground" />
                        <div>
                            <p class="text-xl font-semibold mb-2">Your cart is empty</p>
                            <p class="text-muted-foreground mb-4">
                                Add some products to get started
                            </p>
                            <Button as-child>
                                <Link href="/products">Browse Products</Link>
                            </Button>
                        </div>
                    </div>
                </div>

                <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-4">
                        <CartItemComponent
                            v-for="item in cart.items"
                            :key="item.id"
                            :item="item"
                            @update="handleUpdateItem"
                            @remove="handleRemoveItem"
                        />
                    </div>

                    <div class="lg:col-span-1">
                        <Card class="sticky top-6">
                            <CardHeader>
                                <CardTitle>Order Summary</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="flex justify-between text-sm">
                                    <span>Subtotal</span>
                                    <span>${{ getCartTotal().toFixed(2) }}</span>
                                </div>

                                <div class="flex justify-between font-bold text-lg pt-4 border-t">
                                    <span>Total</span>
                                    <span>${{ getCartTotal().toFixed(2) }}</span>
                                </div>

                                <Button
                                    @click="handleCheckout"
                                    :disabled="isCheckingOut || loading"
                                    class="w-full"
                                    size="lg"
                                >
                                    {{ isCheckingOut ? 'Processing...' : 'Proceed to Checkout' }}
                                </Button>

                                <Button as-child variant="outline" class="w-full">
                                    <Link href="/products">Continue Shopping</Link>
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
