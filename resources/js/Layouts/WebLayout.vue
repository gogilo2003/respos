<script lang="ts" setup>
import WebMenuBar from '@/Components/WebMenuBar.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    title: string;
}>();
</script>

<template>
    <Head :title="title" />
    <div class="flex min-h-screen w-full bg-yellow-300">
        <div class="w-64 flex-none bg-purple-400">
            <WebMenuBar />
        </div>
        <div class="flex-1 bg-gray-50">
            <slot />
        </div>
    </div>
</template>
