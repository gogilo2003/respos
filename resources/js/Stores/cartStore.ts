import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';

export interface CartModifier {
    id?: number;
    name: string;
    price?: number;
}

export interface CartItem {
    id: number;
    title: string;
    description: string;
    price: number;
    image: string;
    quantity: number;
    selected_modifiers?: CartModifier[];
    special_instructions?: string;
}

const STORAGE_KEY = 'cart';
const ORDER_KEY = 'active_order_id';

function getInitialCart(): CartItem[] {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
            const parsed = JSON.parse(stored);
            if (Array.isArray(parsed)) {
                return parsed;
            }
        }
    } catch (e) {
        console.error('Failed to parse stored cart from localStorage:', e);
    }
    return [];
}

function getInitialOrderId(): number | null {
    try {
        const stored = localStorage.getItem(ORDER_KEY);
        if (stored) {
            const parsed = Number(stored);
            if (!isNaN(parsed) && parsed > 0) {
                return parsed;
            }
        }
    } catch (e) {
        console.error('Failed to parse stored active_order_id from localStorage:', e);
    }
    return null;
}

export const useCartStore = defineStore('cart', () => {
    const items = ref<CartItem[]>(getInitialCart());
    const activeOrderId = ref<number | null>(getInitialOrderId());

    watch(
        items,
        (newItems) => {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(newItems));
            } catch (e) {
                console.error('Failed to save cart to localStorage:', e);
            }
        },
        { deep: true },
    );

    watch(
        activeOrderId,
        (newOrderId) => {
            try {
                if (newOrderId) {
                    localStorage.setItem(ORDER_KEY, String(newOrderId));
                } else {
                    localStorage.removeItem(ORDER_KEY);
                }
            } catch (e) {
                console.error('Failed to save active_order_id to localStorage:', e);
            }
        },
    );

    const totalCount = computed(() => {
        return items.value.reduce((sum, item) => sum + (item.quantity || 0), 0);
    });

    const subtotal = computed(() => {
        return items.value.reduce((total, item) => {
            const extraPrice = (item.selected_modifiers || []).reduce(
                (mSum, mod) => mSum + (mod.price || 0),
                0,
            );
            return total + (item.price + extraPrice) * item.quantity;
        }, 0);
    });

    const isEmpty = computed(() => items.value.length === 0);

    function addItem(
        item: {
            id: number;
            title: string;
            description?: string;
            price: number;
            image?: string;
        },
        quantity: number = 1,
        selectedModifiers: CartModifier[] = [],
        specialInstructions: string = '',
    ) {
        const existing = items.value.find((i) => i.id === item.id);
        if (existing) {
            existing.quantity += quantity;
            if (selectedModifiers.length > 0) {
                existing.selected_modifiers = selectedModifiers;
            }
            if (specialInstructions) {
                existing.special_instructions = specialInstructions;
            }
        } else {
            items.value.push({
                id: item.id,
                title: item.title,
                description: item.description || '',
                price: Number(item.price),
                image: item.image || '',
                quantity: Math.max(1, quantity),
                selected_modifiers: selectedModifiers,
                special_instructions: specialInstructions,
            });
        }
    }

    function updateQuantity(itemId: number, quantity: number) {
        if (quantity <= 0) {
            removeItem(itemId);
            return;
        }
        const item = items.value.find((i) => i.id === itemId);
        if (item) {
            item.quantity = quantity;
        }
    }

    function removeItem(itemId: number) {
        items.value = items.value.filter((i) => i.id !== itemId);
    }

    function clearCart() {
        items.value = [];
    }

    function setActiveOrderId(id: number | null) {
        activeOrderId.value = id;
    }

    function clearActiveOrderId() {
        activeOrderId.value = null;
    }

    return {
        items,
        activeOrderId,
        totalCount,
        subtotal,
        isEmpty,
        addItem,
        updateQuantity,
        removeItem,
        clearCart,
        setActiveOrderId,
        clearActiveOrderId,
    };
});
