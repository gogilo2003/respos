<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

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

const scrollToTop = () => window.scrollTo({ top: 0, behavior: 'smooth' });

const addToCart = async (item: { id?: number }) => {
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
    <Head title="Welcome Menu" />

    <div class="min-h-screen bg-[#FFE680]">
        <div class="mx-auto max-w-7xl px-4 py-10">
            <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <a href="/" class="inline-flex items-center gap-2 text-lg font-bold text-gray-900 hover:underline" @click.prevent="scrollToTop">
                        ResPos
                    </a>
                    <h2 class="text-2xl font-bold text-gray-900">Food Menu</h2>
                    <p class="mt-1 text-sm text-gray-600">Add items to your cart</p>
                </div>
                <div class="text-sm text-gray-700">
                    Cart: <span class="font-semibold">{{ cartCount }}</span>
                </div>
            </div>

            <div
                v-if="!props.menuItems || props.menuItems.length === 0"
                class="rounded-2xl border border-gray-200 bg-gray-50 p-6 text-sm text-gray-600"
            >
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

                        <p v-if="item.description" class="mt-2 text-sm text-gray-600">{{ item.description }}</p>
                        <p v-else class="mt-2 text-sm text-gray-500">No description</p>

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

            <div class="mt-10 flex justify-end">
                <button class="text-sm font-semibold text-gray-700 hover:underline" @click="scrollToTop">Back to top</button>
            </div>
        </div>
    </div>
</template>

