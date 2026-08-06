<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';

interface Props {
    waiterName: string;
    shift: string;
    currentTime: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    refresh: [];
}>();

let timer: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    timer = setInterval(() => {
        emit('refresh');
    }, 30000);
});

onBeforeUnmount(() => {
    if (timer) {
        clearInterval(timer);
    }
});
</script>

<template>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Waiter Dashboard
            </h2>
            <div class="mt-1 flex flex-wrap items-center gap-3 text-sm text-gray-600">
                <span>Waiter: {{ waiterName }}</span>
                <span class="hidden h-4 w-px bg-gray-300 sm:inline" aria-hidden="true" />
                <span>Shift: {{ shift }}</span>
                <span class="hidden h-4 w-px bg-gray-300 sm:inline" aria-hidden="true" />
                <span>{{ currentTime }}</span>
            </div>
        </div>

        <button
            type="button"
            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
            @click="emit('refresh')"
        >
            Refresh
        </button>
    </div>
</template>
