<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';

interface Props {
    station: string;
    userName: string;
    currentTime: string;
    refreshing?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    refreshing: false,
});

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
                Kitchen Dashboard
            </h2>
            <div class="mt-1 flex flex-wrap items-center gap-3 text-sm text-gray-600">
                <span>Station: {{ station }}</span>
                <span class="hidden h-4 w-px bg-gray-300 sm:inline" aria-hidden="true" />
                <span>User: {{ userName }}</span>
                <span class="hidden h-4 w-px bg-gray-300 sm:inline" aria-hidden="true" />
                <span>{{ currentTime }}</span>
            </div>
        </div>

        <button
            type="button"
            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:opacity-50"
            :disabled="refreshing"
            @click="emit('refresh')"
        >
            <svg
                v-if="refreshing"
                class="mr-2 h-4 w-4 animate-spin"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                />
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                />
            </svg>
            {{ refreshing ? 'Refreshing...' : 'Refresh' }}
        </button>
    </div>
</template>
