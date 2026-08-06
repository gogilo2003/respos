<script setup lang="ts">
import { ref } from 'vue';

interface Props {
    statusFilter?: string;
    priorityFilter?: string;
}

const props = withDefaults(defineProps<Props>(), {
    statusFilter: '',
    priorityFilter: '',
});

const emit = defineEmits<{
    'update:statusFilter': [value: string];
    'update:priorityFilter': [value: string];
}>();

const statuses = [
    { label: 'All', value: '' },
    { label: 'Pending', value: 'pending' },
    { label: 'Preparing', value: 'preparing' },
    { label: 'Ready', value: 'ready' },
];

const priorities = [
    { label: 'All', value: '' },
    { label: 'Normal', value: 'normal' },
    { label: 'High', value: 'high' },
    { label: 'Urgent', value: 'urgent' },
];
</script>

<template>
    <div class="space-y-3">
        <div class="flex flex-wrap gap-2">
            <button
                v-for="status in statuses"
                :key="status.value"
                type="button"
                class="rounded-full border px-3 py-1.5 text-sm font-medium transition"
                :class="props.statusFilter === status.value
                    ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                    : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
                @click="emit('update:statusFilter', status.value)"
            >
                {{ status.label }}
            </button>
        </div>

        <div class="flex flex-wrap gap-2">
            <button
                v-for="priority in priorities"
                :key="priority.value"
                type="button"
                class="rounded-full border px-3 py-1.5 text-sm font-medium transition"
                :class="props.priorityFilter === priority.value
                    ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                    : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
                @click="emit('update:priorityFilter', priority.value)"
            >
                {{ priority.label }}
            </button>
        </div>
    </div>
</template>
