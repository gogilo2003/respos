<script setup lang="ts">
interface Props {
    tableNumber: string;
    request: string;
    priority: string;
    time: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    click: [];
}>();
</script>

<template>
    <button
        type="button"
        class="w-full rounded-xl border border-gray-200 bg-white p-5 text-left shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        @click="emit('click')"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <p class="text-sm font-medium text-gray-500">Table {{ tableNumber }}</p>
                <p class="text-base font-semibold text-gray-900">{{ request }}</p>
            </div>

            <span
                class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
                :class="{
                    'bg-red-100 text-red-800': priority === 'high',
                    'bg-yellow-100 text-yellow-800': priority === 'medium',
                    'bg-green-100 text-green-800': priority === 'low',
                }"
            >
                {{ priority }}
            </span>
        </div>

        <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
            <span>Assistance request</span>
            <span>{{ time }}</span>
        </div>
    </button>
</template>
