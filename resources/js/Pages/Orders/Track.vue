<script setup lang="ts">
import { formatCurrency } from '@/utils/currency';
import WebLayout from '@/Layouts/WebLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface OrderItem {
    id: number;
    name: string;
    quantity: number;
    unit_price: number;
    total_price: number;
    special_instructions?: string;
    selected_modifiers?: any[];
    status: string;
}

interface OrderData {
    id: number;
    status: string;
    table_number: string;
    placed_at: string;
    items: OrderItem[];
    total_amount: number;
}

const props = defineProps<{
    order: OrderData;
}>();

const steps = [
    { key: 'pending', label: 'Order Placed', desc: 'Received by restaurant' },
    { key: 'accepted', label: 'Accepted', desc: 'Acknowledged by kitchen' },
    { key: 'preparing', label: 'Preparing', desc: 'Food is being cooked' },
    { key: 'ready', label: 'Ready', desc: 'Plated & ready to serve' },
    { key: 'served', label: 'Served', desc: 'Delivered to your table' },
];

const getStepIndex = (status: string) => {
    const map: Record<string, number> = {
        pending: 0,
        accepted: 1,
        preparing: 2,
        ready: 3,
        served: 4,
        completed: 4,
    };
    return map[status] ?? 0;
};
</script>

<template>
    <Head :title="`Order #${order.id} Status`" />

    <WebLayout :title="'Order #' + order.id + ' Tracking'">
        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-2xl px-4 sm:px-6">
                <!-- Status Banner -->
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-200 p-6 text-center mb-6">
                    <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-2xl text-blue-600 mb-3">
                        🍳
                    </div>
                    <h1 class="text-xl font-bold text-gray-900">Order #{{ order.id }} Tracking</h1>
                    <p class="text-xs text-gray-500 mt-1">Table {{ order.table_number }} • Placed at {{ order.placed_at }}</p>

                    <!-- Stepper -->
                    <div class="mt-8 relative">
                        <div class="grid grid-cols-5 gap-2 text-center">
                            <div
                                v-for="(step, idx) in steps"
                                :key="step.key"
                                class="flex flex-col items-center"
                            >
                                <div
                                    class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold transition"
                                    :class="
                                        getStepIndex(order.status) >= idx
                                            ? 'bg-blue-600 text-white ring-4 ring-blue-100'
                                            : 'bg-gray-100 text-gray-400'
                                    "
                                >
                                    {{ idx + 1 }}
                                </div>
                                <span class="mt-2 text-[11px] font-semibold text-gray-800">{{ step.label }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Itemized Summary -->
                <div class="rounded-2xl bg-white shadow-sm border border-gray-200 p-6">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Your Ordered Items</h2>

                    <div class="divide-y divide-gray-100">
                        <div v-for="item in order.items" :key="item.id" class="py-3 flex items-start justify-between">
                            <div>
                                <div class="font-bold text-gray-900 text-sm">
                                    {{ item.quantity }}x {{ item.name }}
                                </div>
                                <div v-if="item.selected_modifiers && item.selected_modifiers.length > 0" class="text-xs text-indigo-600 mt-0.5">
                                    + {{ item.selected_modifiers.map(m => m.name).join(', ') }}
                                </div>
                                <div v-if="item.special_instructions" class="text-xs text-gray-500 italic mt-0.5">
                                    "{{ item.special_instructions }}"
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-gray-900 text-sm">{{ formatCurrency(item.total_price) }}</div>
                                <span class="inline-block text-[10px] uppercase font-bold text-gray-400 mt-0.5">{{ item.status }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 mt-4 pt-4 flex justify-between items-center text-base font-bold text-gray-900">
                        <span>Total Amount:</span>
                        <span>{{ formatCurrency(order.total_amount) }}</span>
                    </div>

                    <div class="mt-6 text-center">
                        <Link :href="route('menu')" class="text-xs font-semibold text-blue-600 hover:underline">
                            ← Back to Food Menu
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </WebLayout>
</template>
