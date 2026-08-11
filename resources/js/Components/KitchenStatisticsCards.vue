<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    stats: {
        pending_items: number;
        preparing_items: number;
        ready_items: number;
        avg_prep_seconds: number | null;
        avg_prep_label: string | null;
    };
}

const props = defineProps<Props>();

const cards = computed(() => [
    {
        label: 'Pending Items',
        value: props.stats.pending_items,
        color: 'text-yellow-600',
    },
    {
        label: 'Preparing',
        value: props.stats.preparing_items,
        color: 'text-blue-600',
    },
    {
        label: 'Ready',
        value: props.stats.ready_items,
        color: 'text-green-600',
    },
    {
        label: 'Avg Prep Time',
        value: props.stats.avg_prep_label ?? '—',
        color: 'text-gray-900',
    },
]);
</script>

<template>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div
            v-for="card in cards"
            :key="card.label"
            class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
        >
            <p class="text-sm font-medium text-gray-500">{{ card.label }}</p>
            <p class="mt-2 text-3xl font-bold" :class="card.color">
                {{ card.value }}
            </p>
        </div>
    </div>
</template>
