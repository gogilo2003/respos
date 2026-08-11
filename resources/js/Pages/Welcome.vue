<script setup lang="ts">
import WebLayout from '@/Layouts/WebLayout.vue';
import { useCartStore } from '@/Stores/cartStore';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const cartStore = useCartStore();

defineProps<{
    menuItems: {
        id?: number;
        title: string;
        description: string;
        price: number;
        image: string;
    }[];
}>();

const addError = ref<string | null>(null);

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
</script>

<template>
    <WebLayout title="Welcome">
        <!-- Blurred image background -->
        <div class="relative min-h-screen overflow-hidden">
            <!-- Foreground content -->
            <div class="relative">
                <!-- Center hero title over the image -->
                <div
                    class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center px-4"
                >
                    <div class="mt-24 text-center sm:mt-32 lg:mt-40">
                        <p
                            class="text-4xl font-extrabold uppercase tracking-wide text-yellow-100 drop-shadow-lg sm:text-4xl md:text-5xl lg:text-6xl"
                        >
                            KARIBU RESPOS
                        </p>
                        <p
                            class="mt-3 max-w-xl text-sm text-white/90 sm:text-base"
                        >
                            ResPos is your restaurant companion—browse the menu,
                            add meals to your cart, and manage your orders with
                            ease.
                        </p>
                    </div>
                </div>

                <!-- See Menu button (centered on the image) -->
                <div
                    class="absolute inset-0 z-10 flex items-end justify-center px-4 pb-24"
                >
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
