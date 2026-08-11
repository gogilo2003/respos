<script setup lang="ts">
import QuantitySelector from '@/Components/QuantitySelector.vue';
import { formatCurrency } from '@/utils/currency';
import { computed } from 'vue';

interface CartItem {
    id: number;
    name: string;
    unitPrice: number;
    quantity: number;
}

interface Props {
    items: CartItem[];
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:quantity': [itemId: number, quantity: number];
    remove: [itemId: number];
}>();

const totalQuantity = computed(() =>
    props.items.reduce((sum, item) => sum + item.quantity, 0),
);

const subtotal = computed(() =>
    props.items.reduce((sum, item) => sum + item.unitPrice * item.quantity, 0),
);

const formattedSubtotal = computed(() => formatCurrency(subtotal.value));
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-4 py-3">
            <h3 class="text-sm font-semibold text-gray-900">Your Order</h3>
        </div>

        <div
            v-if="items.length === 0"
            class="p-6 text-center text-sm text-gray-500"
        >
            No items in your order yet.
        </div>

        <div v-else class="divide-y divide-gray-200">
            <div
                v-for="item in items"
                :key="item.id"
                class="flex items-center justify-between gap-4 px-4 py-3"
            >
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-gray-900">
                        {{ item.name }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ formatCurrency(item.unitPrice) }} each
                    </p>
                </div>

                <QuantitySelector
                    :model-value="item.quantity"
                    :min="1"
                    @update:model-value="
                        emit('update:quantity', item.id, $event)
                    "
                />

                <div class="w-24 text-right text-sm font-medium text-gray-900">
                    {{ formatCurrency(item.unitPrice * item.quantity) }}
                </div>

                <button
                    type="button"
                    class="text-sm text-red-600 hover:text-red-800"
                    @click="emit('remove', item.id)"
                >
                    Remove
                </button>
            </div>
        </div>

        <div v-if="items.length > 0" class="border-t border-gray-200 px-4 py-3">
            <div
                class="flex items-center justify-between text-sm text-gray-600"
            >
                <span>Total Items</span>
                <span class="font-medium text-gray-900">{{
                    totalQuantity
                }}</span>
            </div>
            <div class="mt-1 flex items-center justify-between text-sm">
                <span class="font-medium text-gray-900">Subtotal</span>
                <span class="font-semibold text-gray-900">{{
                    formattedSubtotal
                }}</span>
            </div>
        </div>
    </div>
</template>
