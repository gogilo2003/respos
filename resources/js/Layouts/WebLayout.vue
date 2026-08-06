<script lang="ts" setup>
import WebMenuBar from '@/Layouts/WebMenuBar.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    title: string;
}>();
</script>

<template>
    <Head :title="title" />
    <div class="flex min-h-screen w-full">
        <WebMenuBar />
        <div class="flex-1 bg-gray-50">
            <slot />
        </div>
    </div>
</template>
