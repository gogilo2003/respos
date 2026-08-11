<script setup lang="ts">
import ToastContainer from '@/Components/ToastContainer.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface OrderItem {
    orderItemId: number;
    name: string;
    quantity: number;
    status: string;
}

interface ItemCounts {
    pending: number;
    accepted: number;
    preparing: number;
    ready: number;
}

interface Props {
    orderId: number;
    orderNumber: number | string;
    table: string;
    customer?: string;
    orderTime: string;
    waitingDuration: string;
    items: OrderItem[];
    itemCounts: ItemCounts;
}

const props = defineProps<Props>();
const loading = ref(false);
const toastRef = ref<InstanceType<typeof ToastContainer> | null>(null);

/** All items are in a terminal-for-kitchen state (ready or served). */
const allItemsReady = computed(
    () =>
        props.itemCounts.pending === 0 &&
        props.itemCounts.accepted === 0 &&
        props.itemCounts.preparing === 0,
);

const markOrderReady = () => {
    loading.value = true;
    router.patch(
        `/kitchen/orders/${props.orderId}/ready`,
        {},
        {
            onSuccess: () => {
                toastRef.value?.show(
                    `Order #${props.orderNumber} marked as ready.`,
                    'success',
                );
            },
            onError: () => {
                toastRef.value?.show('Failed to mark order as ready.', 'error');
            },
            onFinish: () => {
                loading.value = false;
            },
        },
    );
};
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div
            class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <p class="text-sm font-medium text-gray-500">
                    Order #{{ orderNumber }}
                </p>
                <p class="text-base font-semibold text-gray-900">
                    Table {{ table }}
                </p>
                <p v-if="customer" class="text-sm text-gray-600">
                    Customer: {{ customer }}
                </p>
                <p class="text-xs text-gray-500">Order Time: {{ orderTime }}</p>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-sm font-medium text-gray-500">Waiting</span>
                <p class="text-sm font-semibold text-gray-900">
                    {{ waitingDuration }}
                </p>
            </div>
        </div>

        <div class="mt-4 border-t border-gray-200 pt-4">
            <h4 class="text-sm font-medium text-gray-900">Order Items</h4>
            <ul class="mt-2 space-y-1">
                <li
                    v-for="item in items"
                    :key="`${item.orderItemId}`"
                    class="flex items-center justify-between text-sm text-gray-700"
                >
                    <span>{{ item.name }} x{{ item.quantity }}</span>
                    <span class="text-xs text-gray-500">{{ item.status }}</span>
                </li>
            </ul>
        </div>

        <div class="mt-4 border-t border-gray-200 pt-4">
            <button
                type="button"
                class="w-full rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm transition disabled:opacity-50"
                :class="
                    allItemsReady
                        ? 'bg-green-600 hover:bg-green-700'
                        : 'cursor-not-allowed bg-gray-300'
                "
                :disabled="!allItemsReady || loading"
                @click="markOrderReady"
            >
                {{ loading ? 'Updating...' : 'Mark Order Ready' }}
            </button>
        </div>
    </div>

    <ToastContainer ref="toastRef" />
</template>
