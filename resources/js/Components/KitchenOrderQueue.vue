<script setup lang="ts">
import KitchenOrderCard from '@/Components/KitchenOrderCard.vue';

interface OrderItem {
    name: string;
    quantity: number;
    status: string;
}

interface Order {
    orderNumber: number | string;
    table: string;
    customer?: string;
    orderTime: string;
    waitingDuration: string;
    items: OrderItem[];
}

interface Props {
    orders: Order[];
    loading?: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'select-order': [order: Order];
}>();
</script>

<template>
    <div class="space-y-4">
        <div v-if="loading" class="py-12 text-center text-sm text-gray-500">
            Loading orders...
        </div>

        <template v-else-if="orders.length > 0">
            <KitchenOrderCard
                v-for="order in orders"
                :key="order.orderNumber"
                v-bind="order"
                class="cursor-pointer transition hover:shadow-md"
                @click="emit('select-order', order)"
            />
        </template>

        <div v-else class="py-12 text-center text-sm text-gray-500">
            No active orders.
        </div>
    </div>
</template>
