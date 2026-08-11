<script setup lang="ts">
import { router } from '@inertiajs/vue3';

interface Props {
    orderNumber: number | string;
    table: string;
    customer?: string;
    time: string;
    status: string;
}

const props = defineProps<Props>();

const updateStatus = (targetStatus: string) => {
    router.patch(
        route('orders.status.update', props.orderNumber),
        {
            status: targetStatus,
        },
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="space-y-1">
                <p class="text-sm font-medium text-gray-500">
                    Order #{{ orderNumber }}
                </p>
                <p class="text-base font-semibold text-gray-900">
                    Table {{ table }}
                </p>
                <p v-if="customer" class="text-sm text-gray-600">
                    Customer: {{ customer }}
                </p>
            </div>

            <div class="flex flex-col items-start gap-2 sm:items-end">
                <span
                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase"
                    :class="{
                        'bg-amber-100 text-amber-800': status === 'pending',
                        'bg-blue-100 text-blue-800':
                            status === 'preparing' || status === 'accepted',
                        'bg-green-100 text-green-800': status === 'ready',
                        'bg-emerald-100 text-emerald-800': status === 'served',
                    }"
                >
                    {{ status }}
                </span>
                <span class="text-xs text-gray-500">{{ time }}</span>

                <div class="mt-2 flex items-center gap-2">
                    <button
                        v-if="status === 'pending'"
                        @click="updateStatus('accepted')"
                        class="rounded bg-blue-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-blue-700"
                    >
                        Accept Order
                    </button>
                    <button
                        v-if="status === 'ready' || status === 'preparing'"
                        @click="updateStatus('served')"
                        class="rounded bg-emerald-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"
                    >
                        Mark Served
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
