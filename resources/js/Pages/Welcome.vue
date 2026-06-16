<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    menuItems: {
        title: string;
        description: string;
        price: number;
        image: string;
    }[]
}>();

</script>

<template>

    <Head title="Welcome" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <div class="mx-auto max-w-7xl py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-base font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Welcome
                    to ResPos</h2>
                <p class="mt-1 text-4xl font-extrabold sm:text-5xl sm:tracking-tight lg:text-6xl">Your Restaurant POS
                    Solution</p>
                <p class="mx-auto mt-5 max-w-xl text-xl text-gray-500 dark:text-gray-400">Manage your restaurant with
                    ease and efficiency using our powerful POS system.</p>
            </div>
        </div>
        <hr>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="item in menuItems" :key="item.title" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <img :src="item.image" alt="" class="w-full h-48 object-cover rounded-md mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ item.title }}</h3>
                <p class="mt-2 text-gray-600 dark:text-gray-300">{{ item.description }}</p>
                <p class="mt-4 text-xl font-bold text-indigo-600 dark:text-indigo-400">${{ item.price.toFixed(2) }}</p>
            </div>
        </div>
    </div>
</template>
