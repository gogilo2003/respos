<script setup lang="ts">
import WebLayout from '@/Layouts/WebLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    categories: {
        id: number;
        name: string;
        image: string;
        description: string;
    }[];
}>();

const isOpen = ref(false);

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const scrollToAnchor = (id: string) => {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
};

const scrollToCategory = () => {
    document
        .getElementById('menu-categories')
        ?.scrollIntoView({ behavior: 'smooth' });
};
// const categoryCards = computed(() => usePage().props.categories)
</script>

<template>

    <WebLayout title="Menu categories">
        <pre>{{ categories }}</pre>
        <div class="relative min-h-screen overflow-hidden bg-[#D2A679]">

            <section id="menu-categories" class="bg-white py-12">
                <div class="mx-auto max-w-7xl px-4">
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900">
                            Food Categories
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">Choose a category</p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <article v-for="category in categories" :key="category.title"
                            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                            <div class="h-48 bg-gray-100">
                                <img v-if="category.image" :src="category.image" :alt="category.title"
                                    class="h-full w-full object-cover" />
                                <div v-else class="flex h-full w-full items-center justify-center text-gray-400">
                                    No image
                                </div>
                            </div>

                            <div class="p-4">
                                <h3 class="text-base font-bold text-gray-900">
                                    {{ category.name }}
                                </h3>
                                <p class="mt-2 text-sm text-gray-600">
                                    {{ category.description }}
                                </p>

                                <button
                                    class="mt-4 w-full rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800"
                                    @click="router.visit('/welcome-menu')">
                                    View
                                </button>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
        </div>
    </WebLayout>
</template>
