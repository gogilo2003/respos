<script lang="ts" setup>
import WebMenuBar from '@/Layouts/WebMenuBar.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    title: string;
}>();
</script>

<template>

    <Head :title="title" />
    <div
        class="flex min-h-screen w-full bg-[url('/images/coffe-illustration-ai-generated-free-photo.jpg')] bg-fixed bg-cover bg-center pt-24">
        <div class="fixed inset-0 bg-gray-800/35 backdrop-blur"></div>
        <WebMenuBar />
        <div class="flex-1 z-20">
            <slot />
        </div>
    </div>
    <footer class="bg-gray-900 text-gray-50 py-8 z-20 relative">
        <div class="mx-auto max-w-7xl px-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm font-semibold">
                    © {{ new Date().getFullYear() }} ResPos. All rights
                    reserved.
                </div>
                <div class="flex items-center gap-6 text-sm text-black/90">
                    <Link href="/welcome-categories" class="hover:underline">
                    Food Menu
                    </Link>
                    <Link href="/" class="hover:underline">Home</Link>
                </div>
            </div>
        </div>
    </footer>
</template>
