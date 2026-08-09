import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import KitchenOrderCard from '@/Components/KitchenOrderCard.vue';

vi.mock('@inertiajs/vue3', () => ({
    router: {
        patch: vi.fn(() => Promise.resolve()),
    },
}));

vi.mock('@/Components/ToastContainer.vue', () => ({
    default: {
        template: '<div />',
    },
}));

describe('KitchenOrderCard', () => {
    const baseProps = {
        orderId: 1,
        orderNumber: 1,
        table: 'A1',
        orderTime: '10:00',
        waitingDuration: '5m 30s',
        items: [
            { orderItemId: 1, name: 'Pasta', quantity: 2, status: 'pending' },
            { orderItemId: 2, name: 'Salad', quantity: 1, status: 'preparing' },
        ],
        itemCounts: {
            pending: 1,
            accepted: 0,
            preparing: 1,
            ready: 0,
        },
    };

    it('renders order metadata', () => {
        const wrapper = mount(KitchenOrderCard, {
            props: baseProps,
        });

        expect(wrapper.text()).toContain('Order #1');
        expect(wrapper.text()).toContain('Table A1');
        expect(wrapper.text()).toContain('10:00');
        expect(wrapper.text()).toContain('5m 30s');
    });

    it('renders optional customer when provided', () => {
        const wrapper = mount(KitchenOrderCard, {
            props: {
                ...baseProps,
                customer: 'John',
            },
        });

        expect(wrapper.text()).toContain('Customer: John');
    });

    it('hides customer when not provided', () => {
        const wrapper = mount(KitchenOrderCard, {
            props: baseProps,
        });

        expect(wrapper.text()).not.toContain('Customer:');
    });

    it('renders all order items', () => {
        const wrapper = mount(KitchenOrderCard, {
            props: baseProps,
        });

        expect(wrapper.text()).toContain('Pasta');
        expect(wrapper.text()).toContain('Salad');
        expect(wrapper.text()).toContain('x2');
        expect(wrapper.text()).toContain('x1');
    });

    it('disables Mark Order Ready button when items are not ready', () => {
        const wrapper = mount(KitchenOrderCard, {
            props: baseProps,
        });

        const button = wrapper.find('button');
        expect(button.attributes('disabled')).toBeDefined();
        expect(wrapper.text()).toContain('Mark Order Ready');
    });

    it('enables Mark Order Ready button when all items are ready', () => {
        const wrapper = mount(KitchenOrderCard, {
            props: {
                ...baseProps,
                itemCounts: {
                    pending: 0,
                    accepted: 0,
                    preparing: 0,
                    ready: 2,
                },
            },
        });

        const button = wrapper.find('button');
        expect(button.attributes('disabled')).toBeUndefined();
    });
});
