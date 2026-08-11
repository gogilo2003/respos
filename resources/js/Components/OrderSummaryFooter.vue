<script setup lang="ts">
import { formatCurrency } from '@/utils/currency';
import { computed } from 'vue';

interface Props {
    items: { price: number; quantity: number; prepTimeMin?: number }[];
}

const props = defineProps<Props>();

const totalItems = computed(() =>
    props.items.reduce((sum, item) => sum + item.quantity, 0),
);

const grandTotal = computed(() =>
    formatCurrency(
        props.items.reduce((sum, item) => sum + item.price * item.quantity, 0),
    ),
);

const maxPrepTime = computed(() => {
    const times = props.items
        .map((item) => item.prepTimeMin)
        .filter(
            (value): value is number =>
                typeof value === 'number' && Number.isFinite(value),
        );

    if (times.length === 0) {
        return null;
    }

    return Math.max(...times);
});
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="grid grid-cols-1 gap-4 px-4 py-4 sm:grid-cols-3">
            <div>
                <p
                    class="text-xs font-medium uppercase tracking-wide text-gray-500"
                >
                    Total Items
                </p>
                <p class="mt-1 text-lg font-semibold text-gray-900">
                    {{ totalItems }}
                </p>
            </div>

            <div>
                <p
                    class="text-xs font-medium uppercase tracking-wide text-gray-500"
                >
                    Estimated Prep Time
                </p>
                <p class="mt-1 text-lg font-semibold text-gray-900">
                    {{ maxPrepTime !== null ? `${maxPrepTime} min` : '—' }}
                </p>
            </div>

            <div class="sm:text-right">
                <p
                    class="text-xs font-medium uppercase tracking-wide text-gray-500"
                >
                    Grand Total
                </p>
                <p class="mt-1 text-2xl font-bold text-gray-900">
                    {{ grandTotal }}
                </p>
            </div>
        </div>
    </div>
</template>
