<script setup lang="ts">
interface Props {
    status: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:status': [status: string];
}>();

const statuses = [
    { label: 'Pending', value: 'pending' },
    { label: 'Preparing', value: 'preparing' },
    { label: 'Ready', value: 'ready' },
    { label: 'Completed', value: 'completed' },
];
</script>

<template>
    <div class="flex flex-wrap gap-2">
        <button
            v-for="item in statuses"
            :key="item.value"
            type="button"
            class="rounded-full border px-3 py-1.5 text-sm font-medium transition"
            :class="props.status === item.value
                ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
            @click="emit('update:status', item.value)"
        >
            {{ item.label }}
        </button>
    </div>
</template>
