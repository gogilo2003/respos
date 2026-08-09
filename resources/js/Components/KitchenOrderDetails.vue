<script setup lang="ts">
import OrderStatusBadge from '@/Components/OrderStatusBadge.vue';

interface OrderItem {
    name: string;
    quantity: number;
    notes?: string;
    allergies?: string;
    specialInstructions?: string;
}

interface Props {
    orderNumber: number | string;
    table: string;
    customer?: string;
    status?: string;
    items: OrderItem[];
}

const props = defineProps<Props>();
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Order #{{ orderNumber }}</p>
                <p class="text-base font-semibold text-gray-900">Table {{ table }}</p>
                <p v-if="customer" class="text-sm text-gray-600">Customer: {{ customer }}</p>
            </div>
            <div v-if="status">
                <OrderStatusBadge :status="status" />
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-900">Ordered Items</h3>
            <div class="mt-3 space-y-4">
                <div
                    v-for="item in items"
                    :key="`${item.name}-${item.quantity}`"
                    class="rounded-lg border border-gray-200 p-4"
                >
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-900">{{ item.name }}</p>
                        <span class="text-sm font-medium text-gray-700">Qty: {{ item.quantity }}</span>
                    </div>

                    <div v-if="item.notes" class="mt-2 text-sm text-gray-600">
                        <span class="font-medium">Notes:</span> {{ item.notes }}
                    </div>

                    <div v-if="item.allergies" class="mt-1 text-sm text-red-700">
                        <span class="font-medium">Allergies:</span> {{ item.allergies }}
                    </div>

                    <div v-if="item.specialInstructions" class="mt-1 text-sm text-blue-700">
                        <span class="font-medium">Special Instructions:</span> {{ item.specialInstructions }}
                    </div>
                </div>

                <div v-if="!items.length" class="text-sm text-gray-500">
                    No items in this order.
                </div>
            </div>
        </div>
    </div>
</template>
