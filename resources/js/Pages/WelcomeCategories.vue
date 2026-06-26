<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const isOpen = ref(false);

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const scrollToAnchor = (id: string) => {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
};

const scrollToCategory = () => {
    document.getElementById('menu-categories')?.scrollIntoView({ behavior: 'smooth' });
};

const categoryCards = [
    {
        title: 'Main Meals',
        image: '',
        anchor: 'main-meals-section',
    },
    {
        title: 'Breakfast',
        image: 'https://images.unsplash.com/photo-1525351484163-7529414344d8?auto=format&fit=crop&w=1200&q=60',
        anchor: 'breakfast-section',
    },
    {
        title: 'Beverages',
        image: 'https://images.unsplash.com/photo-1563371404-45d9f7f1d52a?auto=format&fit=crop&w=1200&q=60',
        anchor: 'beverages-section',
    },
];
</script>

<template>
    <Head title="Welcome Categories" />

    <div class="relative min-h-screen overflow-hidden bg-[#D2A679]">
        <header class="w-full">
            <nav class="bg-[#ffea95] text-black">
                <div class="mx-auto max-w-7xl px-4">
                    <div class="flex h-16 items-center justify-between">
                        <a
                            href="/"
                            class="text-lg font-bold tracking-wide"
                            @click.prevent="scrollToTop"
                        >
                            ResPos
                        </a>

                        <button
                            class="sm:hidden inline-flex items-center justify-center rounded-md p-2 hover:bg-yellow-300 focus:outline-none focus:ring-2 focus:ring-yellow-200"
                            type="button"
                            aria-label="Open menu"
                            @click="isOpen = !isOpen"
                        >
                            <span class="sr-only">Menu</span>
                            <svg
                                class="h-6 w-6"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path v-if="!isOpen" d="M4 6h16" />
                                <path v-if="!isOpen" d="M4 12h16" />
                                <path v-if="!isOpen" d="M4 18h16" />
                                <path v-if="isOpen" d="M6 18L18 6" />
                                <path v-if="isOpen" d="M6 6l12 12" />
                            </svg>
                        </button>

                        <div class="hidden sm:flex items-center gap-6">
                            <a
                                href="/"
                                class="text-sm font-semibold hover:underline"
                                @click.prevent="scrollToTop"
                            >
                                Home
                            </a>
                            <a
                                href="#menu-categories"
                                class="text-sm font-semibold hover:underline"
                                @click.prevent="scrollToCategory"
                            >
                                Food Menu
                            </a>
                            <a
                                href="#about"
                                class="text-sm font-semibold hover:underline"
                                @click.prevent="scrollToAnchor('about')"
                            >
                                About Us
                            </a>
                            <a
                                href="#contact"
                                class="text-sm font-semibold hover:underline"
                                @click.prevent="scrollToAnchor('contact')"
                            >
                                Contact
                            </a>
                        </div>
                    </div>

                    <div v-show="isOpen" class="sm:hidden pb-4">
                        <div class="flex flex-col gap-3">
                            <a
                                href="/"
                                class="rounded-md px-2 py-1 text-sm font-semibold hover:bg-yellow-300"
                                @click.prevent="scrollToTop"
                            >
                                Home
                            </a>
                            <a
                                href="#menu-categories"
                                class="rounded-md px-2 py-1 text-sm font-semibold hover:bg-yellow-300"
                                @click.prevent="scrollToCategory"
                            >
                                Food Menu
                            </a>
                            <a
                                href="#about"
                                class="rounded-md px-2 py-1 text-sm font-semibold hover:bg-yellow-300"
                                @click.prevent="scrollToAnchor('about')"
                            >
                                About Us
                            </a>
                            <a
                                href="#contact"
                                class="rounded-md px-2 py-1 text-sm font-semibold hover:bg-yellow-300"
                                @click.prevent="scrollToAnchor('contact')"
                            >
                                Contact
                            </a>
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        <div class="sr-only" aria-hidden="true">
            <div id="about"></div>
            <div id="contact"></div>
        </div>

        <section id="menu-categories" class="bg-white py-12">
            <div class="mx-auto max-w-7xl px-4">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900">Food Categories</h2>
                    <p class="mt-1 text-sm text-gray-600">Choose a category</p>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <article
                        v-for="category in categoryCards"
                        :key="category.title"
                        class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                    >
                        <div class="h-48 bg-gray-100">
                            <img
                                v-if="category.image"
                                :src="category.image"
                                :alt="category.title"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center text-gray-400">
                                No image
                            </div>
                        </div>

                        <div class="p-4">
                            <h3 class="text-base font-bold text-gray-900">{{ category.title }}</h3>
<p class="mt-2 text-sm text-gray-600">Menu items go here</p>

                        <button
                            class="mt-4 w-full rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 transition"
                            @click="window.location.href = '/welcome-menu'"
                        >
                            View
                        </button>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </div>
</template>

