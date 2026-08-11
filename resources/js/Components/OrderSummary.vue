<script setup lang="ts">
import { formatCurrency } from '@/utils/currency';
import { computed } from 'vue';

interface OrderItem {
    name: string;
    price: number;
    quantity: number;
    notes?: string;
}

interface Props {
    items: OrderItem[];
    notes?: string;
}

const props = defineProps<Props>();

const subtotal = computed(() =>
    props.items.reduce((sum, item) => sum + item.price * item.quantity, 0),
);

const formattedSubtotal = computed(() => formatCurrency(subtotal.value));
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-4 py-3">
            <h3 class="text-sm font-semibold text-gray-900">Order Summary</h3>
        </div>

        <div class="divide-y divide-gray-200">
            <div
                v-for="item in items"
                :key="item.name"
                class="flex items-start justify-between gap-4 px-4 py-3"
            >
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-gray-900">
                        {{ item.name }}
                    </p>
                    <p v-if="item.notes" class="mt-1 text-xs text-gray-500">
                        Notes: {{ item.notes }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-900">
                        {{ item.quantity }} x {{ formatCurrency(item.price) }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ formatCurrency(item.price * item.quantity) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 px-4 py-3">
            <div class="flex items-center justify-between text-sm">
                <span class="font-medium text-gray-900">Subtotal</span>
                <span class="font-semibold text-gray-900">{{
                    formattedSubtotal
                }}</span>
            </div>
        </div>
    </div>
</template>
