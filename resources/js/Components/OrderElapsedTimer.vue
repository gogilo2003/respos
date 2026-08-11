<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

interface Props {
    startTime: string;
}

const props = defineProps<Props>();

const now = ref(new Date());

let timer: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    timer = setInterval(() => {
        now.value = new Date();
    }, 60000);
});

onBeforeUnmount(() => {
    if (timer) {
        clearInterval(timer);
    }
});

const elapsed = computed(() => {
    const start = new Date(props.startTime).getTime();
    const current = now.value.getTime();
    const diffInSeconds = Math.floor((current - start) / 1000);

    const hours = Math.floor(diffInSeconds / 3600);
    const minutes = Math.floor((diffInSeconds % 3600) / 60);
    const seconds = diffInSeconds % 60;

    const parts: string[] = [];

    if (hours > 0) {
        parts.push(`${hours}h`);
    }
    if (minutes > 0 || hours > 0) {
        parts.push(`${minutes}m`);
    }
    parts.push(`${seconds}s`);

    return parts.join(' ');
});
</script>

<template>
    <span class="text-sm text-gray-500">{{ elapsed }}</span>
</template>
