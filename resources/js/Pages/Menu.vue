<script setup lang="ts">
import WebLayout from '@/Layouts/WebLayout.vue';
import { useCartStore } from '@/Stores/cartStore';
import { ref } from 'vue';

const cartStore = useCartStore();

const props = defineProps<{
    menuItems: {
        id?: number;
        title: string;
        description: string;
        price: number;
        image: string;
    }[];
}>();

const addSuccessMessage = ref<string | null>(null);
const addError = ref<string | null>(null);

const scrollToTop = () => window.scrollTo({ top: 0, behavior: 'smooth' });

const handleAddToCart = (item: {
    id?: number;
    title: string;
    description: string;
    price: number;
    image: string;
}) => {
    addError.value = null;
    addSuccessMessage.value = null;

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

    addSuccessMessage.value = `Added ${item.title} to cart!`;
    setTimeout(() => {
        if (addSuccessMessage.value === `Added ${item.title} to cart!`) {
            addSuccessMessage.value = null;
        }
    }, 2500);
};
</script>

<template>
    <WebLayout title="Menu">
        <div class="min-h-screen">
            <div class="mx-auto max-w-7xl px-4 py-10">
                <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-200">
                            Food Menu
                        </h2>
                        <p class="mt-1 text-sm text-gray-300">
                            Add items to your cart
                        </p>
                    </div>
                    <div class="text-sm text-gray-200 font-semibold bg-gray-800/80 px-3 py-1.5 rounded-full border border-gray-700">
                        Cart items: <span class="text-yellow-400 font-bold">{{ cartStore.totalCount }}</span>
                    </div>
                </div>

                <div v-if="addSuccessMessage" class="mb-6 rounded-lg bg-green-50 p-4 border border-green-200 text-sm text-green-700 font-medium">
                    {{ addSuccessMessage }}
                </div>

                <div v-if="!props.menuItems || props.menuItems.length === 0"
                    class="rounded-2xl border border-gray-200 bg-gray-50 p-6 text-sm text-gray-600">
                    No menu items found.
                </div>

                <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <article v-for="item in props.menuItems" :key="item.id ?? item.title"
                        class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="h-44 bg-gray-100">
                                <img v-if="item.image" :src="item.image" :alt="item.title"
                                    class="h-full w-full object-cover" />
                                <div v-else class="flex h-full w-full items-center justify-center text-gray-400">
                                    No image
                                </div>
                            </div>

                            <div class="p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <h3 class="text-base font-bold text-gray-900">
                                        {{ item.title }}
                                    </h3>
                                    <div class="text-sm font-semibold text-gray-900 font-mono">
                                        ${{ Number(item.price).toFixed(2) }}
                                    </div>
                                </div>

                                <p v-if="item.description" class="mt-2 text-sm text-gray-600">
                                    {{ item.description }}
                                </p>
                                <p v-else class="mt-2 text-sm text-gray-500">
                                    No description
                                </p>
                            </div>
                        </div>

                        <div class="p-4 pt-0">
                            <button
                                class="w-full rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800 disabled:opacity-60"
                                :disabled="!item.id" @click="handleAddToCart(item)">
                                Add to cart
                            </button>

                            <p v-if="addError" class="mt-3 text-xs text-red-600">
                                {{ addError }}
                            </p>
                        </div>
                    </article>
                </div>

                <div class="mt-10 flex justify-end">
                    <button class="text-sm font-semibold text-gray-700 hover:underline" @click="scrollToTop">
                        Back to top
                    </button>
                </div>
            </div>
        </div>
    </WebLayout>
</template>
