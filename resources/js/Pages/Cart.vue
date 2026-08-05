<script lang="ts" setup>
import WebLayout from '@/Layouts/WebLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Link, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

interface CartItem {
    id: number;
    title: string;
    description: string;
    price: number;
    image: string;
    quantity: number;
}

const cartItems = ref<CartItem[]>([]);
const isLoading = ref(true);

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(price);
};

const getTotal = () => {
    return cartItems.value.reduce((total, item) => total + (item.price * item.quantity), 0);
};

const updateQuantity = (itemId: number, quantity: number) => {
    if (quantity <= 0) {
        removeItem(itemId);
        return;
    }
    const item = cartItems.value.find(i => i.id === itemId);
    if (item) {
        item.quantity = quantity;
    }
};

const removeItem = (itemId: number) => {
    cartItems.value = cartItems.value.filter(item => item.id !== itemId);
};

const checkout = () => {
    router.visit('/checkout');
};

const clearCart = () => {
    if (confirm('Are you sure you want to clear your cart?')) {
        cartItems.value = [];
    }
};

onMounted(() => {
    const storedCart = localStorage.getItem('cart');
    if (storedCart) {
        cartItems.value = JSON.parse(storedCart);
    }
    isLoading.value = false;
});
</script>

<template>
    <WebLayout title="Shopping Cart">
        <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl">
                <h1 class="text-3xl font-bold text-gray-900 mb-6">
                    Your Shopping Cart
                </h1>

                <div v-if="isLoading" class="text-center py-12">
                    <p class="text-gray-500">Loading cart...</p>
                </div>

                <div v-else-if="cartItems.length === 0" class="text-center py-12">
                    <div class="mx-auto h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center mb-4">
                        <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Your cart is empty</h2>
                    <p class="text-gray-500 mb-4">Add items to your cart from the menu.</p>
                    <a
                        href="/categories"
                        class="inline-block rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        Browse Menu
                    </a>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="item in cartItems" :key="item.id" class="bg-white rounded-lg shadow-md p-4">
                        <div class="flex items-center space-x-4">
                            <img 
                                v-if="item.image"
                                :src="item.image" 
                                :alt="item.title"
                                class="h-16 w-16 rounded-md object-cover"
                            />
                            <div v-else class="h-16 w-16 rounded-md bg-gray-200 flex items-center justify-center">
                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16h16M4 8h16"></path>
                                </svg>
                            </div>

                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">{{ item.title }}</h3>
                                <p class="text-sm text-gray-500">{{ item.description }}</p>
                                <p class="text-lg font-bold text-gray-900 mt-1">{{ formatPrice(item.price) }}</p>
                            </div>

                            <div class="flex items-center space-x-2">
                                <button
                                    @click="updateQuantity(item.id, item.quantity - 1)"
                                    class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center hover:bg-gray-300"
                                >
                                    -
                                </button>
                                <span class="px-2">{{ item.quantity }}</span>
                                <button
                                    @click="updateQuantity(item.id, item.quantity + 1)"
                                    class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center hover:bg-gray-300"
                                >
                                    +
                                </button>
                            </div>

                            <button
                                @click="removeItem(item.id)"
                                class="text-red-500 hover:text-red-700"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-4 flex justify-between items-center">
                        <span class="text-xl font-bold text-gray-900">Total:</span>
                        <span class="text-xl font-bold text-gray-900">{{ formatPrice(getTotal()) }}</span>
                    </div>

                    <div class="space-y-3">
                        <button
                            @click="checkout"
                            class="w-full rounded-md bg-green-600 px-4 py-3 text-lg font-semibold text-white hover:bg-green-700"
                        >
                            Proceed to Checkout
                        </button>
                        <button
                            @click="clearCart"
                            class="w-full rounded-md bg-gray-100 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-200"
                        >
                            Clear Cart
                        </button>
                        <Link
                            :href="route('categories')"
                            class="block w-full text-center rounded-md bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700"
                        >
                            Continue Shopping
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </WebLayout>
</template>