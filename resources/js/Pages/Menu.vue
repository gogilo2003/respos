<script setup lang="ts">
import WebLayout from '@/Layouts/WebLayout.vue';
import { useCartStore } from '@/Stores/cartStore';
import { formatCurrency } from '@/utils/currency';
import { Link } from '@inertiajs/vue3';
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
    categories?: {
        id: number;
        name: string;
    }[];
    selectedCategoryId?: number | null;
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
            <div class="px-4 py-10">
                <div
                    class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <h2 class="text-2xl font-bold text-gray-200">
                            Food Menu
                        </h2>
                        <p class="mt-1 text-sm text-gray-300">
                            Add items to your cart
                        </p>
                    </div>
                    <div
                        class="rounded-full border border-gray-700 bg-gray-800/80 px-3 py-1.5 text-sm font-semibold text-gray-200"
                    >
                        Cart items:
                        <span class="font-bold text-yellow-400">{{
                            cartStore.totalCount
                        }}</span>
                    </div>
                </div>

                <!-- Category Filter Pills -->
                <div
                    v-if="props.categories && props.categories.length > 0"
                    class="mb-8 flex flex-wrap items-center gap-2"
                >
                    <Link
                        :href="route('menu')"
                        class="rounded-full px-4 py-2 text-xs font-bold transition-all"
                        :class="
                            !props.selectedCategoryId
                                ? 'bg-yellow-400 text-gray-900 shadow-md ring-2 ring-yellow-400/50'
                                : 'bg-gray-800 text-gray-300 hover:bg-gray-700 hover:text-white'
                        "
                    >
                        All Items
                    </Link>
                    <Link
                        v-for="category in props.categories"
                        :key="category.id"
                        :href="route('menu', category.id)"
                        class="rounded-full px-4 py-2 text-xs font-bold transition-all"
                        :class="
                            props.selectedCategoryId === category.id
                                ? 'bg-yellow-400 text-gray-900 shadow-md ring-2 ring-yellow-400/50'
                                : 'bg-gray-800 text-gray-300 hover:bg-gray-700 hover:text-white'
                        "
                    >
                        {{ category.name }}
                    </Link>
                </div>

                <div
                    v-if="addSuccessMessage"
                    class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-700"
                >
                    {{ addSuccessMessage }}
                </div>

                <div
                    v-if="!props.menuItems || props.menuItems.length === 0"
                    class="rounded-2xl border border-gray-200 bg-gray-50 p-6 text-sm text-gray-600"
                >
                    No menu items found.
                </div>

                <div
                    v-else
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <article
                        v-for="item in props.menuItems"
                        :key="item.id ?? item.title"
                        class="flex flex-col justify-between overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                    >
                        <div>
                            <div class="h-44 bg-gray-100">
                                <img
                                    v-if="item.image"
                                    :src="item.image"
                                    :alt="item.title"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center text-gray-400"
                                >
                                    No image
                                </div>
                            </div>

                            <div class="p-4">
                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <h3
                                        class="text-base font-bold text-gray-900"
                                    >
                                        {{ item.title }}
                                    </h3>
                                    <div
                                        class="font-mono text-sm font-semibold text-gray-900"
                                    >
                                        {{ formatCurrency(item.price) }}
                                    </div>
                                </div>

                                <p
                                    v-if="item.description"
                                    class="mt-2 text-sm text-gray-600"
                                >
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
                                :disabled="!item.id"
                                @click="handleAddToCart(item)"
                            >
                                Add to cart
                            </button>

                            <p
                                v-if="addError"
                                class="mt-3 text-xs text-red-600"
                            >
                                {{ addError }}
                            </p>
                        </div>
                    </article>
                </div>

                <div class="mt-10 flex justify-end">
                    <button
                        class="text-sm font-semibold text-gray-700 hover:underline"
                        @click="scrollToTop"
                    >
                        Back to top
                    </button>
                </div>
            </div>
        </div>
    </WebLayout>
</template>
