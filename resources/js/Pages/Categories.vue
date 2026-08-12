<script setup lang="ts">
import WebLayout from '@/Layouts/WebLayout.vue';
import { Link } from '@inertiajs/vue3';
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
</script>

<template>
    <WebLayout title="Menu categories">
        <div class="relative min-h-screen overflow-hidden">
            <section id="menu-categories" class="py-12">
                <div class="px-4">
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-200">
                            Food Categories
                        </h2>
                        <p class="mt-1 text-sm text-gray-300">
                            Choose a category
                        </p>
                    </div>

                    <div v-if="!categories.length" class="py-12 text-center">
                        <p class="text-sm text-gray-400">No categories available right now.</p>
                        <Link href="/menu"
                            class="mt-4 inline-block rounded-md bg-black px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                            Browse Menu
                        </Link>
                    </div>

                    <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <article
                            v-for="category in categories"
                            :key="category.name"
                            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                        >
                            <div class="h-48 bg-gray-100">
                                <img
                                    v-if="category.image"
                                    :src="category.image"
                                    :alt="category.name"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center text-gray-400"
                                >
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

                                <Link
                                    :href="route('menu', category.id)"
                                    class="mt-4 flex w-full justify-center rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800"
                                >
                                    View
                                </Link>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
        </div>
    </WebLayout>
</template>
