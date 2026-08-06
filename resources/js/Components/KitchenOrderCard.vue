<script setup lang="ts">
import { computed } from 'vue';

interface OrderItem {
    orderItemId: number;
    name: string;
    quantity: number;
    status: string;
}

interface Props {
    orderNumber: number | string;
    table: string;
    customer?: string;
    orderTime: string;
    waitingDuration: string;
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
                <p class="text-xs text-gray-500">Order Time: {{ orderTime }}</p>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-sm font-medium text-gray-500">Waiting</span>
                <p class="text-sm font-semibold text-gray-900">{{ waitingDuration }}</p>
            </div>
        </div>

        <div class="mt-4 border-t border-gray-200 pt-4">
            <h4 class="text-sm font-medium text-gray-900">Order Items</h4>
            <ul class="mt-2 space-y-1">
                <li
                    v-for="item in items"
                    :key="`${item.name}-${item.status}`"
                    class="flex items-center justify-between text-sm text-gray-700"
                >
                    <span>{{ item.name }} x{{ item.quantity }}</span>
                    <span class="text-xs text-gray-500">{{ item.status }}</span>
                </li>
            </ul>
        </div>
    </div>
</template>
