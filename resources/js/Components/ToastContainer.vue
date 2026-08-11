<script setup lang="ts">
import { ref } from 'vue';

interface Toast {
    id: number;
    message: string;
    type: 'success' | 'error' | 'info';
}

const toasts = ref<Toast[]>([]);
let nextId = 0;

const show = (message: string, type: Toast['type'] = 'success') => {
    const id = nextId++;
    toasts.value.push({ id, message, type });

    setTimeout(() => {
        toasts.value = toasts.value.filter((toast) => toast.id !== id);
    }, 3000);
};

defineExpose({ show });
</script>

<template>
    <div
        class="pointer-events-none fixed inset-0 z-50 flex flex-col items-end justify-start gap-2 p-4"
    >
        <TransitionGroup
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-2"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto rounded-md border px-4 py-3 text-sm font-medium shadow-sm"
                :class="{
                    'border-green-200 bg-green-50 text-green-800':
                        toast.type === 'success',
                    'border-red-200 bg-red-50 text-red-800':
                        toast.type === 'error',
                    'border-blue-200 bg-blue-50 text-blue-800':
                        toast.type === 'info',
                }"
            >
                {{ toast.message }}
            </div>
        </TransitionGroup>
    </div>
</template>
