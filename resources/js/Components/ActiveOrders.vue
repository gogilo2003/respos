<script setup lang="ts">
import ActiveOrderCard from '@/Components/ActiveOrderCard.vue';

interface Order {
    orderNumber: number | string;
    table: string;
    customer?: string;
    time: string;
    status: string;
}

interface Props {
    orders: Order[];
    loading?: boolean;
}

const props = defineProps<Props>();
</script>

<template>
    <div class="space-y-4">
        <div v-if="loading" class="py-12 text-center text-sm text-gray-500">
            Loading orders...
        </div>

        <template v-else-if="orders.length > 0">
            <ActiveOrderCard
                v-for="order in orders"
                :key="order.orderNumber"
                v-bind="order"
            />

            <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                <button
                    type="button"
                    class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 transition hover:bg-gray-50"
                    disabled
                >
                    Previous
                </button>
                <span class="text-sm text-gray-500">Page 1</span>
                <button
                    type="button"
                    class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 transition hover:bg-gray-50"
                    disabled
                >
                    Next
                </button>
            </div>
        </template>

        <div v-else class="py-12 text-center text-sm text-gray-500">
            No active orders.
        </div>
    </div>
</template>
