<script setup lang="ts">
import LandingNavBar from '@/Components/LandingNavBar.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const isOpen = ref(false);

const props = defineProps<{
    menuItems: {
        id?: number;
        title: string;
        description: string;
        price: number;
        image: string;
    }[];
}>();

const cartCount = ref(0);
const addError = ref<string | null>(null);

const goToCategories = () => {
    window.location.href = '/welcome-categories';
};

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const scrollToAnchor = (id: string) => {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
};

const scrollToCategory = (anchor: string) => {
    document.getElementById(anchor)?.scrollIntoView({ behavior: 'smooth' });
};

// ensure nav click actions work even if not used after replacing header
const scrollToCategoryAnchor = scrollToCategory;

const categoryCards = [
    {
        title: 'Main Meals',
        image: 'https://images.unsplash.com/photo-1543339308-43e59d5b7f36?auto=format&fit=crop&w=1200&q=60',
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

const addToCart = async (item: {
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

    try {
        const res = await axios.post(route('cart.add'), {
            menu_item_id: item.id,
            quantity: 1,
        });

        cartCount.value = res.data.cartCount ?? cartCount.value;
    } catch (e: any) {
        addError.value = e?.response?.data?.message ?? 'Failed to add to cart.';
    }
};
</script>

<template>
    <Head title="Welcome" />

    <!-- Blurred image background -->
    <div class="relative min-h-screen overflow-hidden">
        <div
            class="absolute inset-0 bg-cover bg-center"
            style="
                background-image: url('https://static.vecteezy.com/system/resources/thumbnails/021/947/454/small_2x/coffe-illustration-ai-generated-free-photo.jpg');
            "
        ></div>
        <div class="absolute inset-0 bg-black/5 backdrop-blur"></div>

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
                    <p class="mt-3 max-w-xl text-sm text-white/90 sm:text-base">
                        ResPos is your restaurant companion—browse the menu, add
                        meals to your cart, and manage your orders with ease.
                    </p>
                </div>
            </div>

            <LandingNavBar />
        </div>

        <!-- See Menu button (centered on the image) -->
        <div
            class="absolute inset-0 z-10 flex items-end justify-center px-4 pb-24"
        >
            <button
                type="button"
                class="rounded-full bg-[#ffea95] px-6 py-3 font-semibold text-black shadow-md transition hover:bg-yellow-300"
                @click="goToCategories"
            >
                See Menu
            </button>
        </div>
    </div>

    <!-- About/Contact anchors for navbar -->
    <div class="sr-only" aria-hidden="true">
        <div id="about"></div>
        <div id="contact"></div>
    </div>
</template>
