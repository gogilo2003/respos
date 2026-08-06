<script setup lang="ts">
import KitchenStatusBadge from '@/Components/KitchenStatusBadge.vue';
import ToastContainer from '@/Components/ToastContainer.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

interface Props {
    orderItemId: number;
    name: string;
    quantity: number;
    notes?: string;
    status: string;
}

const props = defineProps<Props>();
const loading = ref(false);
const toastRef = ref<InstanceType<typeof ToastContainer> | null>(null);

const updateStatus = (newStatus: 'preparing' | 'ready') => {
    loading.value = true;
    router.patch(
        `/kitchen/order-items/${props.orderItemId}`,
        { status: newStatus },
        {
            onSuccess: () => {
                const label = newStatus === 'ready' ? 'marked as ready' : 'now being prepared';
                toastRef.value?.show(`${props.name} ${label}.`, 'success');
            },
            onError: () => {
                toastRef.value?.show('Failed to update item status.', 'error');
            },
            onFinish: () => {
                loading.value = false;
            },
        },
    );
};

const startPreparing = () => {
    if (props.status !== 'accepted' && props.status !== 'pending') return;
    updateStatus('preparing');
};

const markReady = () => {
    if (props.status !== 'preparing') return;
    updateStatus('ready');
};
</script>

<template>
    <div class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex-1 min-w-0">
            <p class="truncate text-sm font-semibold text-gray-900">{{ name }}</p>
            <p class="text-xs text-gray-500">Qty: {{ quantity }}</p>
            <p v-if="notes" class="mt-1 text-xs text-gray-600">Notes: {{ notes }}</p>
        </div>

        <div class="flex items-center gap-2">
            <div class="shrink-0">
                <KitchenStatusBadge :status="status" />
            </div>

            <button
                v-if="status === 'accepted' || status === 'pending'"
                type="button"
                class="rounded-md bg-gray-900 px-3 py-1.5 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800 disabled:opacity-50"
                :disabled="loading"
                @click="startPreparing"
            >
                {{ loading ? 'Updating...' : 'Start Preparing' }}
            </button>

            <button
                v-if="status === 'preparing'"
                type="button"
                class="rounded-md bg-green-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 disabled:opacity-50"
                :disabled="loading"
                @click="markReady"
            >
                {{ loading ? 'Updating...' : 'Mark Ready' }}
            </button>
        </div>
    </div>

    <ToastContainer ref="toastRef" />
</template>
