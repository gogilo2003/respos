<script setup lang="ts">
interface Props {
    orderNumber: number | string;
    table: string;
    customer?: string;
    time: string;
    status: string;
}

const props = defineProps<Props>();
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <p class="text-sm font-medium text-gray-500">Order #{{ orderNumber }}</p>
                <p class="text-base font-semibold text-gray-900">Table {{ table }}</p>
                <p v-if="customer" class="text-sm text-gray-600">Customer: {{ customer }}</p>
            </div>

            <div class="flex flex-col items-start gap-2 sm:items-end">
                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-800">
                    {{ status }}
                </span>
                <span class="text-sm text-gray-500">{{ time }}</span>
            </div>
        </div>
    </div>
</template>
