<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import ProductCard from '@/components/ProductCard.vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Spinner } from '@/components/ui/spinner';
import { useCart, type Product } from '@/composables/useCart';
import axios from 'axios';

const products = ref<Product[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);
const successMessage = ref<string | null>(null);

const { addToCart, error: cartError } = useCart();

const fetchProducts = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get('/api/products');

        products.value = response.data.data.items;
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Failed to fetch products';
    } finally {
        loading.value = false;
    }
};

const handleAddToCart = async (productId: number, quantity: number) => {
    successMessage.value = null;
    const success = await addToCart(productId, quantity);

    if (success) {
        successMessage.value = 'Product added to cart successfully!';
        setTimeout(() => {
            successMessage.value = null;
        }, 3000);
    }
};

onMounted(() => {
    fetchProducts();
});
</script>

<template>
    <Head title="Products" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold">Products</h1>
                </div>

                <Alert v-if="error" variant="destructive" class="mb-6">
                    <AlertDescription>{{ error }}</AlertDescription>
                </Alert>

                <Alert
                    v-if="successMessage"
                    class="mb-6 border-green-200 bg-green-50"
                >
                    <AlertDescription class="text-green-800">{{
                        successMessage
                    }}</AlertDescription>
                </Alert>

                <Alert v-if="cartError" variant="destructive" class="mb-6">
                    <AlertDescription>{{ cartError }}</AlertDescription>
                </Alert>

                <div v-if="loading" class="flex justify-center py-12">
                    <Spinner />
                </div>

                <div
                    v-else-if="products.length === 0"
                    class="py-12 text-center"
                >
                    <p class="text-muted-foreground">No products available</p>
                </div>

                <div
                    v-else
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4"
                >
                    <ProductCard
                        v-for="product in products"
                        :key="product.id"
                        :product="product"
                        @add-to-cart="handleAddToCart"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
