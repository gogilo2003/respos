<script setup lang="ts">
import WebLayout from '@/Layouts/WebLayout.vue';
import { useCartStore } from '@/Stores/cartStore';
import { Link } from '@inertiajs/vue3';
import { formatCurrency } from '@/utils/currency';
import { ref, onMounted, onUnmounted } from 'vue';

const cartStore = useCartStore();

const props = defineProps<{
    menuItems: {
        id?: number;
        title: string;
        description: string;
        price: number;
        image: string;
    }[];
    featuredMenuItems: {
        id?: number;
        title: string;
        description: string;
        price: number;
        image: string;
    }[];
}>();

const addError = ref<string | null>(null);
const currentIndex = ref(0);
let autoPlayTimer: number | null = null;

const handleAddToCart = (item: {
    title: string;
    description: string;
    price: number;
    image: string;
    id?: number;
}) => {
    addError.value = null;

    if (!item.id) {
        addError.value = 'This item cannot be added (missing id).';
        return;
    }

    cartStore.addItem({
        id: item.id,
        title: item.title,
        description: item.description,
        price: Number(item.price),
        image: item.image,
    });
};

const goTo = (index: number) => {
    currentIndex.value = index;
};

const next = () => {
    currentIndex.value = (currentIndex.value + 1) % props.featuredMenuItems.length;
};

const startAutoPlay = () => {
    if (props.featuredMenuItems.length > 1) {
        autoPlayTimer = window.setInterval(next, 6000);
    }
};

const stopAutoPlay = () => {
    if (autoPlayTimer) {
        clearInterval(autoPlayTimer);
        autoPlayTimer = null;
    }
};

const pauseAutoPlay = () => {
    stopAutoPlay();
};

const resumeAutoPlay = () => {
    startAutoPlay();
};

onMounted(() => {
    startAutoPlay();
});

onUnmounted(() => {
    stopAutoPlay();
});
</script>

<template>
    <WebLayout title="Welcome">
        <div class="relative min-h-screen overflow-hidden">
            <div class="relative">
                <!-- Hero title -->
                <div class="relative z-10 flex items-start justify-center px-4 pt-16 sm:pt-20 lg:pt-24">
                    <div class="text-center">
                        <p
                            class="text-4xl font-extrabold uppercase tracking-wide text-yellow-100 drop-shadow-lg sm:text-4xl md:text-5xl lg:text-6xl">
                            KARIBU RESPOS
                        </p>
                        <p class="mt-3 max-w-xl text-sm text-white/90 sm:text-base">
                            ResPos is your restaurant companion—browse the menu,
                            add meals to your cart, and manage your orders with
                            ease.
                        </p>
                    </div>
                </div>

                <!-- Featured slideshow -->
                <div v-if="featuredMenuItems.length" class="relative z-20 min-h-[70vh] flex flex-col"
                    @mouseenter="pauseAutoPlay" @mouseleave="resumeAutoPlay" @focusin="pauseAutoPlay"
                    @focusout="resumeAutoPlay">
                    <div
                        class="mx-auto flex h-full w-full max-w-7xl flex-col md:flex-row md:items-center md:justify-center md:flex-1">
                        <!-- Main slide -->
                        <div class="w-full px-4 sm:px-6 lg:px-8 md:flex-1">
                            <div
                                class="flex h-full flex-col rounded-3xl bg-black/30 p-5 backdrop-blur-md border border-white/10 md:p-8">
                                <div class="grid flex-1 grid-cols-1 gap-6 md:grid-cols-2">
                                    <div class="overflow-hidden rounded-2xl bg-gray-900/40">
                                        <div v-if="featuredMenuItems[currentIndex]?.image" class="w-full h-96">
                                            <img :src="featuredMenuItems[currentIndex]?.image"
                                                :alt="featuredMenuItems[currentIndex]?.title"
                                                class="h-full w-full object-cover object-center" />
                                        </div>
                                        <div v-else
                                            class="flex h-64 w-full items-center justify-center text-xs text-gray-400 sm:h-72 md:h-full">
                                            🍲
                                        </div>
                                    </div>
                                    <div class="flex flex-col justify-center">
                                        <h2 class="text-2xl font-bold text-white md:text-3xl">
                                            {{ featuredMenuItems[currentIndex]?.title }}
                                        </h2>
                                        <p class="mt-2 text-sm text-gray-200 md:text-base">
                                            {{ featuredMenuItems[currentIndex]?.description }}
                                        </p>
                                        <p class="mt-4 text-xl font-bold text-yellow-200">
                                            {{ formatCurrency(featuredMenuItems[currentIndex]?.price) }}
                                        </p>
                                        <button @click="handleAddToCart(featuredMenuItems[currentIndex])"
                                            class="mt-4 w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 active:scale-[0.98] md:w-auto">
                                            Add to cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thumbnails (md+) -->
                        <div class="hidden md:flex md:w-72 lg:w-80 flex-col gap-3 overflow-y-auto p-4 lg:p-6">
                            <button v-for="(item, index) in featuredMenuItems" :key="item.id" @click="goTo(index)"
                                class="flex items-center gap-3 rounded-xl border p-2 text-left transition active:scale-[0.98]"
                                :class="currentIndex === index ? 'border-yellow-300 bg-white/20' : 'border-white/10 bg-white/5 hover:bg-white/10'">
                                <div class="h-14 w-14 flex-shrink-0 overflow-hidden rounded-lg bg-gray-700">
                                    <img v-if="item.image" :src="item.image" :alt="item.title"
                                        class="h-full w-full object-cover" />
                                    <div v-else
                                        class="flex h-full w-full items-center justify-center text-xs text-gray-400">
                                        🍲
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-white">{{ item.title }}</p>
                                    <p class="text-xs text-gray-200">{{ formatCurrency(item.price) }}</p>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Dots (mobile) -->
                    <div v-if="featuredMenuItems.length > 1" class="flex justify-center gap-3 pb-8 md:hidden">
                        <button v-for="(item, index) in featuredMenuItems" :key="item.id" @click="goTo(index)"
                            class="h-3 w-3 rounded-full transition active:scale-95"
                            :class="currentIndex === index ? 'w-6 bg-yellow-300' : 'w-3 bg-white/60'"
                            :aria-label="`Show ${item.title}`"></button>
                    </div>
                </div>

                <!-- About anchor for navbar -->
                <div class="sr-only" aria-hidden="true">
                    <div id="about"></div>
                </div>
            </div>
        </div>
    </WebLayout>
</template>
