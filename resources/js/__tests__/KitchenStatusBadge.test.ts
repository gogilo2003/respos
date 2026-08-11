import KitchenStatusBadge from '@/Components/KitchenStatusBadge.vue';
import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';

describe('KitchenStatusBadge', () => {
    it('renders the status text', () => {
        const wrapper = mount(KitchenStatusBadge, {
            props: { status: 'pending' },
        });

        expect(wrapper.text()).toContain('pending');
    });

    it('applies the correct class for pending', () => {
        const wrapper = mount(KitchenStatusBadge, {
            props: { status: 'pending' },
        });

        expect(wrapper.find('span').classes()).toContain('bg-yellow-100');
        expect(wrapper.find('span').classes()).toContain('text-yellow-800');
    });

    it('applies the correct class for preparing', () => {
        const wrapper = mount(KitchenStatusBadge, {
            props: { status: 'preparing' },
        });

        expect(wrapper.find('span').classes()).toContain('bg-blue-100');
        expect(wrapper.find('span').classes()).toContain('text-blue-800');
    });

    it('applies the correct class for ready', () => {
        const wrapper = mount(KitchenStatusBadge, {
            props: { status: 'ready' },
        });

        expect(wrapper.find('span').classes()).toContain('bg-green-100');
        expect(wrapper.find('span').classes()).toContain('text-green-800');
    });

    it('applies the correct class for served', () => {
        const wrapper = mount(KitchenStatusBadge, {
            props: { status: 'served' },
        });

        expect(wrapper.find('span').classes()).toContain('bg-gray-100');
        expect(wrapper.find('span').classes()).toContain('text-gray-800');
    });

    it('applies the correct class for cancelled', () => {
        const wrapper = mount(KitchenStatusBadge, {
            props: { status: 'cancelled' },
        });

        expect(wrapper.find('span').classes()).toContain('bg-red-100');
        expect(wrapper.find('span').classes()).toContain('text-red-800');
    });

    it('falls back to gray for unknown status', () => {
        const wrapper = mount(KitchenStatusBadge, {
            props: { status: 'unknown' },
        });

        expect(wrapper.find('span').classes()).toContain('bg-gray-100');
        expect(wrapper.find('span').classes()).toContain('text-gray-800');
    });
});
