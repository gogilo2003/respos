import { useCartStore } from '@/Stores/cartStore';
import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it } from 'vitest';

describe('cartStore Pinia Store', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        localStorage.clear();
    });

    it('initializes with empty cart items', () => {
        const store = useCartStore();
        expect(store.items).toEqual([]);
        expect(store.totalCount).toBe(0);
        expect(store.subtotal).toBe(0);
        expect(store.isEmpty).toBe(true);
    });

    it('adds items to cart and updates count and subtotal', () => {
        const store = useCartStore();
        store.addItem(
            {
                id: 1,
                title: 'Burger',
                price: 10.5,
                image: '',
                description: 'Delicious burger',
            },
            2,
        );

        expect(store.items.length).toBe(1);
        expect(store.totalCount).toBe(2);
        expect(store.subtotal).toBe(21.0);
        expect(store.isEmpty).toBe(false);
    });

    it('increments quantity when adding same item', () => {
        const store = useCartStore();
        store.addItem({ id: 1, title: 'Burger', price: 10.0 });
        store.addItem({ id: 1, title: 'Burger', price: 10.0 }, 3);

        expect(store.items.length).toBe(1);
        expect(store.totalCount).toBe(4);
        expect(store.subtotal).toBe(40.0);
    });

    it('updates item quantity and removes item when quantity is 0', () => {
        const store = useCartStore();
        store.addItem({ id: 1, title: 'Pizza', price: 15.0 }, 2);

        store.updateQuantity(1, 5);
        expect(store.totalCount).toBe(5);

        store.updateQuantity(1, 0);
        expect(store.items.length).toBe(0);
        expect(store.isEmpty).toBe(true);
    });

    it('removes item and clears cart', () => {
        const store = useCartStore();
        store.addItem({ id: 1, title: 'Fries', price: 4.0 });
        store.addItem({ id: 2, title: 'Soda', price: 2.0 });

        expect(store.items.length).toBe(2);

        store.removeItem(1);
        expect(store.items.length).toBe(1);
        expect(store.items[0].id).toBe(2);

        store.clearCart();
        expect(store.isEmpty).toBe(true);
    });
});
