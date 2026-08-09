<script setup lang="ts">
import { computed } from 'vue';
interface Props {
    name: string;
    price: number;
    quantity: number;
    notes?: string;
}

const props = defineProps<Props>();

const formattedPrice = computed(() =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(props.price)
);

const lineTotal = computed(() =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(props.price * props.quantity)
);
</script>

<template>
    <div class="flex flex-col gap-1 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <p class="truncate text-sm font-semibold text-gray-900">{{ name }}</p>
                <p class="text-xs text-gray-500">{{ formattedPrice }} each</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-medium text-gray-900">{{ lineTotal }}</p>
            </div>
        </div>

        <div class="flex items-center justify-between text-xs text-gray-600">
            <span>Qty: {{ quantity }}</span>
            <span v-if="notes" class="italic text-gray-500">Notes: {{ notes }}</span>
        </div>
    </div>
</template>
