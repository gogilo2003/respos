<script setup lang="ts">
interface Props {
    priority: string;
}

const props = defineProps<Props>();

const statusClass = (priority: string) => {
    const map: Record<string, string> = {
        low: 'bg-green-100 text-green-800',
        medium: 'bg-yellow-100 text-yellow-800',
        high: 'bg-orange-100 text-orange-800',
        critical: 'bg-red-100 text-red-800',
    };

    return map[priority] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <span
        :class="[
            'inline-flex rounded-full px-2 text-xs font-semibold leading-5',
            statusClass(priority),
        ]"
    >
        {{ priority }}
    </span>
</template>
