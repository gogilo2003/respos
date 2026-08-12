<script setup lang="ts">
import WebLayout from '@/Layouts/WebLayout.vue';
import { useCartStore } from '@/Stores/cartStore';
import { Link } from '@inertiajs/vue3';
import { formatCurrency } from '@/utils/currency';
import { ref, onMounted, onUnmounted } from 'vue';

const cartStore = useCartStore();

defineProps<{
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
    currentIndex.value = (currentIndex.value + 1) % featuredMenuItems.length;
};

const startAutoPlay = () => {
    if (featuredMenuItems.length > 1) {
        autoPlayTimer = window.setInterval(next, 5000);
    }
};

const stopAutoPlay = () => {
    if (autoPlayTimer) {
        clearInterval(autoPlayTimer);
        autoPlayTimer = null;
    }
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
                <div class="pointer-events-none absolute inset-0 z-10 flex items-start justify-center px-4 pt-24 sm:pt-32 lg:pt-40">
                    <div class="text-center">
                        <p
                            class="text-4xl font-extrabold uppercase tracking-wide text-yellow-100 drop-shadow-lg sm:text-4xl md:text-5xl lg:text-6xl"
                        >
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
                <div v-if="featuredMenuItems.length" class="absolute inset-0 z-20">
                    <div class="mx-auto flex h-full max-w-7xl flex-col md:flex-row">
                        <!-- Main slide -->
                        <div class="flex-1 px-4 pb-64 sm:px-6 lg:px-8 md:pb-8 md:pt-48 lg:pt-52">
                            <div class="flex h-full flex-col rounded-3xl bg-black/30 p-5 backdrop-blur-md border border-white/10 md:p-8">
                                <div class="grid flex-1 grid-cols-1 gap-6 md:grid-cols-2">
                                    <div class="overflow-hidden rounded-2xl bg-gray-900/40">
                                        <img
                                            v-if="featuredMenuItems[currentIndex]?.image"
                                            :src="featuredMenuItems[currentIndex]?.image"
                                            :alt="featuredMenuItems[currentIndex]?.title"
                                            class="h-64 w-full object-cover sm:h-72 md:h-full"
                                        />
                                        <div
                                            v-else
                                            class="flex h-64 w-full items-center justify-center text-xs text-gray-400 sm:h-72 md:h-full"
                                        >
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
                                        <button
                                            @click="handleAddToCart(featuredMenuItems[currentIndex])"
                                            class="mt-4 w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 md:w-auto"
                                        >
                                            Add to cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thumbnails (md+) -->
                        <div class="hidden md:flex md:w-72 lg:w-80 flex-col gap-3 overflow-y-auto p-4 lg:p-6">
                            <button
                                v-for="(item, index) in featuredMenuItems"
                                :key="item.id"
                                @click="goTo(index)"
                                class="flex items-center gap-3 rounded-xl border p-2 text-left transition"
                                :class="currentIndex === index ? 'border-yellow-300 bg-white/20' : 'border-white/10 bg-white/5 hover:bg-white/10'"
                            >
                                <div class="h-14 w-14 flex-shrink-0 overflow-hidden rounded-lg bg-gray-700">
                                    <img
                                        v-if="item.image"
                                        :src="item.image"
                                        :alt="item.title"
                                        class="h-full w-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex h-full w-full items-center justify-center text-xs text-gray-400"
                                    >
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
                    <div v-if="featuredMenuItems.length > 1" class="flex justify-center gap-2 pb-6 md:hidden">
                        <button
                            v-for="(item, index) in featuredMenuItems"
                            :key="item.id"
                            @click="goTo(index)"
                            class="h-2 rounded-full transition"
                            :class="currentIndex === index ? 'w-6 bg-yellow-300' : 'w-2 bg-white/60'"
                        ></button>
                    </div>
                </div>

                <!-- See Menu button -->
                <div class="absolute inset-0 z-30 flex items-end justify-center px-4 pb-24">
                    <Link
                        href="/categories"
                        class="rounded-full bg-[#ffea95] px-6 py-3 font-semibold text-black shadow-md transition hover:bg-yellow-300"
                    >
                        See Menu
                    </Link>
                </div>
            </div>
        </div>

        <!-- About anchor for navbar -->
        <div class="sr-only" aria-hidden="true">
            <div id="about"></div>
        </div>
    </WebLayout>
</template>
