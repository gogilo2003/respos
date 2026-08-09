import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import KitchenOrderQueue from '@/Components/KitchenOrderQueue.vue';
import KitchenOrderCard from '@/Components/KitchenOrderCard.vue';

describe('KitchenOrderQueue', () => {
    const orders = [
        {
            orderId: 1,
            orderNumber: 1,
            table: 'A1',
            orderTime: '10:00',
            waitingDuration: '5m',
            items: [{ orderItemId: 1, name: 'Pasta', quantity: 1, status: 'pending' }],
            itemCounts: { pending: 1, accepted: 0, preparing: 0, ready: 0 },
        },
        {
            orderId: 2,
            orderNumber: 2,
            table: 'B2',
            orderTime: '10:05',
            waitingDuration: '2m',
            items: [{ orderItemId: 2, name: 'Soup', quantity: 2, status: 'preparing' }],
            itemCounts: { pending: 0, accepted: 0, preparing: 1, ready: 0 },
        },
    ];

    it('renders multiple order cards', () => {
        const wrapper = mount(KitchenOrderQueue, {
            props: { orders },
        });

        expect(wrapper.text()).toContain('Order #1');
        expect(wrapper.text()).toContain('Order #2');
        expect(wrapper.text()).toContain('Table A1');
        expect(wrapper.text()).toContain('Table B2');
    });

    it('shows loading state', () => {
        const wrapper = mount(KitchenOrderQueue, {
            props: { orders: [], loading: true },
        });

        expect(wrapper.text()).toContain('Loading orders...');
    });

    it('shows empty state when no orders', () => {
        const wrapper = mount(KitchenOrderQueue, {
            props: { orders: [] },
        });

        expect(wrapper.text()).toContain('No active orders.');
    });

    it('renders a card for each order', () => {
        const wrapper = mount(KitchenOrderQueue, {
            props: { orders },
        });

        const cards = wrapper.findAllComponents(KitchenOrderCard);
        expect(cards.length).toBe(2);
    });
});
