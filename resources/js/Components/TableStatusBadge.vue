<script setup lang="ts">
interface Props {
    status: string;
}

const props = defineProps<Props>();

const statusClass = (status: string) => {
    const map: Record<string, string> = {
        available: 'bg-green-100 text-green-800',
        occupied: 'bg-red-100 text-red-800',
        reserved: 'bg-orange-100 text-orange-800',
        cleaning: 'bg-gray-100 text-gray-800',
    };

    return map[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <span
        :class="[
            'inline-flex rounded-full px-2 text-xs font-semibold leading-5',
            statusClass(status),
        ]"
    >
        {{ status }}
    </span>
</template>
