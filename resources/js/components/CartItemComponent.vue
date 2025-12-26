<script setup lang="ts">
import { ref } from 'vue';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Trash2 } from 'lucide-vue-next';
import type { CartItem } from '@/composables/useCart';

interface Props {
    item: CartItem;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    update: [cartItemId: number, quantity: number];
    remove: [cartItemId: number];
}>();

const quantity = ref(props.item.quantity);

const handleUpdate = () => {
    if (quantity.value < 1) return;

    if (quantity.value > props.item.product.stock_quantity) {
        quantity.value = props.item.product.stock_quantity;
    }

    if (quantity.value !== props.item.quantity) {
        emit('update', props.item.id, quantity.value);
    }
};

const handleRemove = () => {
    emit('remove', props.item.id);
};

const getSubtotal = () => {
    return (Number(props.item.product.price) * props.item.quantity).toFixed(2);
};
</script>

<template>
    <Card>
        <CardContent class="p-4">
            <div class="flex items-center gap-4">
                <div class="flex-grow">
                    <h3 class="font-semibold">{{ item.product.name }}</h3>
                    <p class="text-sm text-muted-foreground">
                        ${{ Number(item.product.price).toFixed(2) }} per unit
                    </p>
                    <p class="text-sm text-muted-foreground">
                        Stock: {{ item.product.stock_quantity }} available
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <Input
                            v-model.number="quantity"
                            type="number"
                            min="1"
                            :max="item.product.stock_quantity"
                            class="w-20"
                            @blur="handleUpdate"
                            @keyup.enter="handleUpdate"
                        />
                        <Button
                            v-if="quantity !== item.quantity"
                            @click="handleUpdate"
                            size="sm"
                            variant="outline"
                        >
                            Update
                        </Button>
                    </div>

                    <div class="text-right min-w-[80px]">
                        <p class="font-semibold">${{ getSubtotal() }}</p>
                    </div>

                    <Button
                        @click="handleRemove"
                        variant="destructive"
                        size="icon"
                    >
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
