<script setup lang="ts">
import { ref } from 'vue';

const isOpen = ref(false);

const scrollToTop = () => window.scrollTo({ top: 0, behavior: 'smooth' });

const scrollToAnchor = (id: string) => {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
};

const close = () => {
    isOpen.value = false;
};
</script>

<template>
    <header class="sticky top-0 z-50 w-full">
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

                    <!-- Mobile button -->
                    <button
                        class="inline-flex items-center justify-center rounded-md p-2 hover:bg-yellow-300 focus:outline-none focus:ring-2 focus:ring-yellow-200 sm:hidden"
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

                    <!-- Desktop links -->
                    <div class="hidden items-center gap-6 sm:flex">
                        <a
                            href="/"
                            class="text-sm font-semibold hover:underline"
                            @click.prevent="scrollToTop"
                        >
                            Home
                        </a>
                        <a
                            href="/welcome-categories"
                            class="text-sm font-semibold hover:underline"
                        >
                            Menu
                        </a>
                        <a
                            href="/about"
                            class="text-sm font-semibold hover:underline"
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

                <!-- Mobile panel -->
                <div v-show="isOpen" class="pb-4 sm:hidden">
                    <div class="flex flex-col gap-3">
                        <a
                            href="/"
                            class="rounded-md px-2 py-1 text-sm font-semibold hover:bg-yellow-300"
                            @click.prevent="
                                scrollToTop;
                                isOpen = false;
                            "
                        >
                            Home
                        </a>
                        <a
                            href="/welcome-categories"
                            class="rounded-md px-2 py-1 text-sm font-semibold hover:bg-yellow-300"
                            @click="isOpen = false"
                        >
                            Food Menu
                        </a>
                        <a
                            href="/about"
                            class="rounded-md px-2 py-1 text-sm font-semibold hover:bg-yellow-300"
                            @click="isOpen = false"
                        >
                            About Us
                        </a>
                        <a
                            href="#contact"
                            class="rounded-md px-2 py-1 text-sm font-semibold hover:bg-yellow-300"
                            @click.prevent="
                                scrollToAnchor('contact');
                                isOpen = false;
                            "
                        >
                            Contact
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>
</template>
