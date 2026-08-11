<script setup lang="ts">
import { computed } from 'vue';

interface Stat {
    label: string;
    value: number;
    icon?: string;
}

interface Props {
    stats: {
        activeTables: number;
        pendingOrders: number;
        readyOrders: number;
        assistanceRequests: number;
    };
}

const props = defineProps<Props>();

const items = computed<Stat[]>(() => [
    { label: 'Active Tables', value: props.stats.activeTables },
    { label: 'Pending Orders', value: props.stats.pendingOrders },
    { label: 'Ready Orders', value: props.stats.readyOrders },
    { label: 'Assistance Requests', value: props.stats.assistanceRequests },
]);
</script>

<template>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div
            v-for="item in items"
            :key="item.label"
            class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
        >
            <p class="text-sm font-medium text-gray-500">{{ item.label }}</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ item.value }}
            </p>
        </div>
    </div>
</template>
