<script lang="ts" setup>
import WebLayout from '@/Layouts/WebLayout.vue';
import { useCartStore } from '@/Stores/cartStore';
import { formatCurrency } from '@/utils/currency';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

interface TableOption {
    id: number;
    table_number: string;
    location?: string;
}

interface ActiveSessionData {
    id: number;
    table_id: number;
    table_number: string;
}

const props = defineProps<{
    tables?: TableOption[];
    activeSession?: ActiveSessionData | null;
}>();

const cartStore = useCartStore();
const selectedTableId = ref<number | ''>(props.activeSession?.table_id || '');
const isSubmitting = ref(false);
const submitError = ref<string | null>(null);

const handleCheckout = async () => {
    if (cartStore.isEmpty) return;

    if (!props.activeSession && !selectedTableId.value) {
        submitError.value =
            'Please select your table number before submitting your order.';
        return;
    }

    isSubmitting.value = true;
    submitError.value = null;

    try {
        const itemsPayload = cartStore.items.map((item) => ({
            menu_item_id: item.id,
            quantity: item.quantity,
            selected_modifiers: item.selected_modifiers || [],
            special_instructions: item.special_instructions || '',
        }));

        const payload: Record<string, any> = {
            items: itemsPayload,
        };

        if (props.activeSession?.id) {
            payload.session_id = props.activeSession.id;
        } else if (selectedTableId.value) {
            payload.table_id = selectedTableId.value;
        }

        const res = await axios.post(route('cart.complete'), payload);

        if (res.data?.ok && res.data?.track_url) {
            if (res.data?.order_id) {
                cartStore.setActiveOrderId(res.data.order_id);
            }
            cartStore.clearCart();
            router.visit(res.data.track_url);
        } else {
            router.visit('/menu');
        }
    } catch (e: any) {
        submitError.value =
            e?.response?.data?.message ||
            'Failed to place order. Please select your table number or scan table QR code.';
    } finally {
        isSubmitting.value = false;
    }
};

const handleClearCart = () => {
    if (confirm('Are you sure you want to clear your cart?')) {
        cartStore.clearCart();
    }
};
</script>

<template>
    <WebLayout title="Shopping Cart">
        <div class="min-h-screen bg-gray-50 px-4 py-8 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl">
                <h1 class="mb-6 text-3xl font-bold text-gray-900">
                    Your Shopping Cart
                </h1>

                <!-- Active Session Banner or Table Selector -->
                <div
                    v-if="!cartStore.isEmpty"
                    class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
                >
                    <div
                        v-if="props.activeSession"
                        class="flex items-center justify-between"
                    >
                        <div class="flex items-center space-x-3">
                            <span
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-green-100 text-base font-bold text-green-800"
                            >
                                🪑
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">
                                    Seated at Table
                                    {{ props.activeSession.table_number }}
                                </h3>
                                <p class="text-xs text-gray-500">
                                    Active table session in progress
                                </p>
                            </div>
                        </div>
                        <span
                            class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800"
                            >Active Session</span
                        >
                    </div>

                    <div v-else class="space-y-2">
                        <div class="flex items-center space-x-2">
                            <span class="text-lg">📍</span>
                            <label
                                for="table_select"
                                class="text-sm font-bold text-gray-900"
                            >
                                Select Your Table Number:
                            </label>
                        </div>
                        <p class="text-xs text-gray-500">
                            No QR code scanned. Choose your table number to
                            route your order to the kitchen.
                        </p>
                        <select
                            id="table_select"
                            v-model="selectedTableId"
                            class="mt-2 block w-full rounded-lg border-gray-300 text-sm font-medium shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="" disabled>
                                -- Select a Table --
                            </option>
                            <option
                                v-for="table in props.tables"
                                :key="table.id"
                                :value="table.id"
                            >
                                Table {{ table.table_number }}
                                {{
                                    table.location ? `(${table.location})` : ''
                                }}
                            </option>
                        </select>
                    </div>
                </div>

                <div
                    v-if="submitError"
                    class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700"
                >
                    {{ submitError }}
                </div>

                <div v-if="cartStore.isEmpty" class="py-12 text-center">
                    <div
                        class="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-full bg-gray-200"
                    >
                        <svg
                            class="h-12 w-12 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4"
                            ></path>
                        </svg>
                    </div>
                    <h2 class="mb-2 text-xl font-semibold text-gray-900">
                        Your cart is empty
                    </h2>
                    <p class="mb-4 text-gray-500">
                        Add items to your cart from the menu.
                    </p>
                    <Link
                        href="/categories"
                        class="inline-block rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        Browse Menu
                    </Link>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="item in cartStore.items"
                        :key="item.id"
                        class="rounded-lg bg-white p-4 shadow-md"
                    >
                        <div class="flex items-center space-x-4">
                            <img
                                v-if="item.image"
                                :src="item.image"
                                :alt="item.title"
                                class="h-16 w-16 rounded-md object-cover"
                            />
                            <div
                                v-else
                                class="flex h-16 w-16 items-center justify-center rounded-md bg-gray-200"
                            >
                                <svg
                                    class="h-8 w-8 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16h16M4 8h16"
                                    ></path>
                                </svg>
                            </div>

                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ item.title }}
                                </h3>
                                <p
                                    v-if="item.description"
                                    class="text-sm text-gray-500"
                                >
                                    {{ item.description }}
                                </p>
                                <p
                                    v-if="
                                        item.selected_modifiers &&
                                        item.selected_modifiers.length > 0
                                    "
                                    class="mt-1 text-xs text-indigo-600"
                                >
                                    +
                                    {{
                                        item.selected_modifiers
                                            .map((m) => m.name)
                                            .join(', ')
                                    }}
                                </p>
                                <p class="mt-1 text-lg font-bold text-gray-900">
                                    {{ formatCurrency(item.price) }}
                                </p>
                            </div>

                            <div class="flex items-center space-x-2">
                                <button
                                    @click="
                                        cartStore.updateQuantity(
                                            item.id,
                                            item.quantity - 1,
                                        )
                                    "
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300"
                                >
                                    -
                                </button>
                                <span class="px-2 font-bold">{{
                                    item.quantity
                                }}</span>
                                <button
                                    @click="
                                        cartStore.updateQuantity(
                                            item.id,
                                            item.quantity + 1,
                                        )
                                    "
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300"
                                >
                                    +
                                </button>
                            </div>

                            <button
                                @click="cartStore.removeItem(item.id)"
                                class="text-red-500 hover:text-red-700"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-between rounded-lg bg-white p-4 shadow-md"
                    >
                        <span class="text-xl font-bold text-gray-900"
                            >Total:</span
                        >
                        <span class="text-xl font-bold text-gray-900">{{
                            formatCurrency(cartStore.subtotal)
                        }}</span>
                    </div>

                    <div class="space-y-3">
                        <button
                            @click="handleCheckout"
                            :disabled="isSubmitting"
                            class="w-full rounded-md bg-green-600 px-4 py-3 text-lg font-semibold text-white hover:bg-green-700 disabled:opacity-60"
                        >
                            {{
                                isSubmitting
                                    ? 'Submitting Order...'
                                    : 'Place Order & Send to Kitchen'
                            }}
                        </button>
                        <button
                            @click="handleClearCart"
                            class="w-full rounded-md bg-gray-100 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-200"
                        >
                            Clear Cart
                        </button>
                        <Link
                            :href="route('categories')"
                            class="block w-full rounded-md bg-blue-600 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-blue-700"
                        >
                            Continue Shopping
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </WebLayout>
</template>
