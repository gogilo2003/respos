<script setup lang="ts">
interface Props {
    tableNumber: string;
    capacity: number;
    status: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    click: [];
}>();
</script>

<template>
    <button
        type="button"
        class="w-full rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        @click="emit('click')"
    >
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Table</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">
                    {{ tableNumber }}
                </p>
            </div>
            <div class="text-right">
                <p class="text-sm font-medium text-gray-500">Capacity</p>
                <p class="mt-1 text-lg font-semibold text-gray-900">
                    {{ capacity }}
                </p>
            </div>
        </div>
        <div class="mt-4">
            <span
                class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-800"
            >
                {{ status }}
            </span>
        </div>
    </button>
</template>
