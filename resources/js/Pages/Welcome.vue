<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const isOpen = ref(false);


defineProps<{
    menuItems: {
        title: string;
        description: string;
        price: number;
        image: string;
    }[];
}>();
</script>

<template>
    <Head title="Welcome" />

    <!-- Blurred image background -->
    <div class="relative min-h-screen overflow-hidden">
        <div
            class="absolute inset-0 bg-cover bg-center"
            style="background-image: url('https://static.vecteezy.com/system/resources/thumbnails/021/947/454/small_2x/coffe-illustration-ai-generated-free-photo.jpg');"
        ></div>
        <div
            class="absolute inset-0 bg-black/5 backdrop-blur"
        ></div>

        <!-- Foreground content -->
        <div class="relative">
            <!-- Yellow mobile responsive nav bar -->
            <header class="w-full">
                <nav class="bg-[#ffea95] text-black">
                    <div class="mx-auto max-w-7xl px-4">
                        <div class="flex h-16 items-center justify-between">
                            <a href="#" class="text-lg font-bold tracking-wide">ResPos</a>

                            <!-- Mobile button -->
                            <button
                                class="sm:hidden inline-flex items-center justify-center rounded-md p-2 hover:bg-yellow-300 focus:outline-none focus:ring-2 focus:ring-yellow-200"
                                type="button"
                                aria-label="Open menu"
                                @click="isOpen = !isOpen"
                            >
                                <span class="sr-only">Menu</span>
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path v-if="!isOpen" d="M4 6h16" />
                                    <path v-if="!isOpen" d="M4 12h16" />
                                    <path v-if="!isOpen" d="M4 18h16" />
                                    <path v-if="isOpen" d="M6 18L18 6" />
                                    <path v-if="isOpen" d="M6 6l12 12" />
                                </svg>
                            </button>

                            <!-- Desktop links -->
                            <div class="hidden sm:flex items-center gap-6">
                                <a href="#" class="text-sm font-semibold hover:underline">Home</a>
                                <a href="#" class="text-sm font-semibold hover:underline">Food Menu</a>
                                <a href="#" class="text-sm font-semibold hover:underline">About Us</a>
                                <a href="#" class="text-sm font-semibold hover:underline">Contact</a>
                            </div>
                        </div>

                        <!-- Mobile panel -->
                        <div v-show="isOpen" class="sm:hidden pb-4">
                            <div class="flex flex-col gap-3">
                                <a href="#" class="rounded-md px-2 py-1 text-sm font-semibold hover:bg-yellow-300">Home</a>
                                <a href="#" class="rounded-md px-2 py-1 text-sm font-semibold hover:bg-yellow-300">Food Menu</a>
                                <a href="#" class="rounded-md px-2 py-1 text-sm font-semibold hover:bg-yellow-300">About Us</a>
                                <a href="#" class="rounded-md px-2 py-1 text-sm font-semibold hover:bg-yellow-300">Contact</a>
                            </div>
                        </div>
                        
                    </div>
                </nav>
            </header>
        </div>

        <!-- See Menu button (centered on the image) -->
        <div class="absolute inset-0 z-10 flex items-end justify-center px-4 pb-24">
            <button
                type="button"
                class="rounded-full bg-[#ffea95] px-6 py-3 text-black font-semibold shadow-md hover:bg-yellow-300 transition"
            >
                See Menu
            </button>
        </div>
    </div>
</template>







