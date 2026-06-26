<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

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
                                <a href="#" class="text-sm font-semibold hover:underline" @click.prevent="scrollToTop">Home</a>
<a href="/welcome-categories" class="text-sm font-semibold hover:underline">Menu</a>
                                <a href="#about" class="text-sm font-semibold hover:underline" @click.prevent="scrollToAnchor('about')">About Us</a>
                                <a href="#contact" class="text-sm font-semibold hover:underline" @click.prevent="scrollToAnchor('contact')">Contact</a>
                            </div>

                        </div>

                        <!-- Mobile panel -->
                        <div v-show="isOpen" class="sm:hidden pb-4">
                            <div class="flex flex-col gap-3">
                                <a href="#" class="rounded-md px-2 py-1 text-sm font-semibold hover:bg-yellow-300" @click.prevent="scrollToTop">Home</a>
<a href="/welcome-categories" class="rounded-md px-2 py-1 text-sm font-semibold hover:bg-yellow-300">Menu</a>
                                <a href="#about" class="rounded-md px-2 py-1 text-sm font-semibold hover:bg-yellow-300" @click.prevent="scrollToAnchor('about')">About Us</a>
                                <a href="#contact" class="rounded-md px-2 py-1 text-sm font-semibold hover:bg-yellow-300" @click.prevent="scrollToAnchor('contact')">Contact</a>
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

    <!-- Category cards (LANDING PAGE ONLY)
         NOTE: Menu items moved to /welcome-menu (WelcomeMenu.vue)
    -->
    <section class="bg-white py-12">


        <div class="mx-auto max-w-7xl px-4">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Food Menu</h2>
                <p class="mt-1 text-sm text-gray-600">Pick your favorites</p>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <article
                    class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                    v-for="category in categoryCards"
                    :key="category.title"
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
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="text-base font-bold text-gray-900">{{ category.title }}</h3>
                        </div>

                        <button
                            class="mt-4 w-full rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 transition"
@click="goToCategories"
                        >
                            View
                        </button>
                    </div>
                </article>
            </div>

            <!-- Menu items grid moved to /welcome-menu -->
            <div id="menu-section" class="mt-12 hidden">

                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Menu</h3>
                        <p class="mt-1 text-sm text-gray-600">Add items to your cart</p>
                    </div>

                    <div class="text-sm text-gray-700">
                        Cart: <span class="font-semibold">{{ cartCount }}</span>
                    </div>
                </div>

                <div v-if="!props.menuItems || props.menuItems.length === 0" class="rounded-2xl border border-gray-200 bg-gray-50 p-6 text-sm text-gray-600">
                    No menu items found.
                </div>

                <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="item in props.menuItems"
                        :key="item.id ?? item.title"
                        class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                    >
                        <div class="h-44 bg-gray-100">
                            <img
                                v-if="item.image"
                                :src="item.image"
                                :alt="item.title"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center text-gray-400">
                                No image
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="flex items-start justify-between gap-4">
                                <h3 class="text-base font-bold text-gray-900">{{ item.title }}</h3>
                                <div class="text-sm font-semibold text-gray-900">{{ item.price }}</div>
                            </div>

                            <p v-if="item.description" class="mt-2 text-sm text-gray-600">
                                {{ item.description }}
                            </p>
                            <p v-else class="mt-2 text-sm text-gray-500">
                                No description
                            </p>

                            <button
                                class="mt-4 w-full rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 transition disabled:opacity-60"
                                :disabled="!item.id"
                                @click="addToCart(item)"
                            >
                                Add to cart
                            </button>

                            <p v-if="addError" class="mt-3 text-xs text-red-600">{{ addError }}</p>
                        </div>
                    </article>
                </div>
            </div>

        </div>
</section>

<footer class="bg-[#ffea95] py-8">
    <div class="mx-auto max-w-7xl px-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-sm font-semibold text-black">© {{ new Date().getFullYear() }} ResPos. All rights reserved.</div>
            <div class="flex items-center gap-6 text-sm text-black/90">
                <a href="/welcome-categories" class="hover:underline">Food Menu</a>
                <a href="#contact" class="hover:underline" @click.prevent="scrollToAnchor('contact')">Contact</a>
            </div>
        </div>
    </div>
</footer>
</template>









