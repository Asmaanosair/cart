<script setup lang="ts">
import { ref } from 'vue';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Product } from '@/composables/useCart';

interface Props {
    product: Product;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    addToCart: [productId: number, quantity: number];
}>();

const quantity = ref(1);

const handleAddToCart = () => {
    if (quantity.value < 1) {
        alert('Quantity must be at least 1');
        return;
    }

    if (quantity.value > props.product.stock_quantity) {
        alert(`Only ${props.product.stock_quantity} units available`);
        return;
    }

    emit('addToCart', props.product.id, quantity.value);
    quantity.value = 1;
};

const isLowStock = () => props.product.stock_quantity <= 5;
const isOutOfStock = () => props.product.stock_quantity === 0;
</script>

<template>
    <Card class="h-full flex flex-col">
        <CardHeader>
            <CardTitle class="text-lg">{{ product.name }}</CardTitle>
            <p v-if="product.description" class="text-sm text-muted-foreground">
                {{ product.description }}
            </p>
        </CardHeader>

        <CardContent class="flex-grow">
            <div class="space-y-2">
                <p class="text-2xl font-bold">${{ Number(product.price).toFixed(2) }}</p>

                <div class="flex items-center gap-2">
                    <span class="text-sm">Stock:</span>
                    <span
                        :class="{
                            'text-red-600 font-semibold': isOutOfStock(),
                            'text-orange-600 font-semibold': isLowStock() && !isOutOfStock(),
                            'text-green-600': !isLowStock() && !isOutOfStock(),
                        }"
                    >
                        {{ product.stock_quantity }} units
                    </span>
                </div>

                <div v-if="isLowStock() && !isOutOfStock()" class="text-xs text-orange-600">
                    Low stock!
                </div>
            </div>
        </CardContent>

        <CardFooter class="flex-col gap-3">
            <div class="flex w-full gap-2">
                <Input
                    v-model.number="quantity"
                    type="number"
                    min="1"
                    :max="product.stock_quantity"
                    class="w-20"
                    :disabled="isOutOfStock()"
                />
                <Button
                    @click="handleAddToCart"
                    :disabled="isOutOfStock()"
                    class="flex-grow"
                >
                    {{ isOutOfStock() ? 'Out of Stock' : 'Add to Cart' }}
                </Button>
            </div>
        </CardFooter>
    </Card>
</template>
