<script setup lang="ts">
interface Props {
    status: string;
}

const props = defineProps<Props>();

    const statusClass = (status: string) => {
        const map: Record<string, string> = {
            pending: 'bg-yellow-100 text-yellow-800',
            preparing: 'bg-blue-100 text-blue-800',
            ready: 'bg-green-100 text-green-800',
            served: 'bg-gray-100 text-gray-800',
            billed: 'bg-purple-100 text-purple-800',
            cancelled: 'bg-red-100 text-red-800',
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
