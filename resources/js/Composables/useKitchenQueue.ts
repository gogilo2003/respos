import type { ShapedOrder } from '@/Composables/useKitchenDashboard';
import { computed, ref, type ComputedRef, type Ref } from 'vue';

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

export type QueueStatusFilter =
    | ''
    | 'pending'
    | 'accepted'
    | 'preparing'
    | 'ready'
    | 'served';

export type QueueSortKey = 'placed_at' | 'waiting' | 'table';

export type QueueSortDirection = 'asc' | 'desc';

export interface QueueSortOption {
    key: QueueSortKey;
    label: string;
}

export const QUEUE_SORT_OPTIONS: QueueSortOption[] = [
    { key: 'placed_at', label: 'Order time' },
    { key: 'waiting', label: 'Waiting longest' },
    { key: 'table', label: 'Table number' },
];

export const QUEUE_STATUS_OPTIONS = [
    { label: 'All', value: '' as QueueStatusFilter },
    { label: 'Pending', value: 'pending' as QueueStatusFilter },
    { label: 'Preparing', value: 'preparing' as QueueStatusFilter },
    { label: 'Ready', value: 'ready' as QueueStatusFilter },
    { label: 'Served', value: 'served' as QueueStatusFilter },
];

// ---------------------------------------------------------------------------
// Composable
// ---------------------------------------------------------------------------

/**
 * useKitchenQueue
 *
 * Pure frontend filtering, sorting, and searching over a shaped order list.
 * Accepts a reactive order source (Ref or ComputedRef) and returns derived
 * state with no side-effects — no router calls, no HTTP requests.
 */
export function useKitchenQueue(
    orders: Ref<ShapedOrder[]> | ComputedRef<ShapedOrder[]>,
) {
    // -----------------------------------------------------------------------
    // Filter / search / sort state
    // -----------------------------------------------------------------------

    const statusFilter = ref<QueueStatusFilter>('');
    const searchQuery = ref('');
    const sortBy = ref<QueueSortKey>('placed_at');
    const sortDirection = ref<QueueSortDirection>('asc');

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Return true when the order matches the active status filter.
     * An order qualifies if at least one of its items has the requested status.
     */
    function matchesStatus(order: ShapedOrder): boolean {
        if (!statusFilter.value) return true;
        return order.items.some((item) => item.status === statusFilter.value);
    }

    /**
     * Return true when the order matches the search query.
     * Searches across: order number and all item names (case-insensitive).
     */
    function matchesSearch(order: ShapedOrder): boolean {
        const q = searchQuery.value.trim().toLowerCase();
        if (!q) return true;

        if (String(order.orderNumber).toLowerCase().includes(q)) return true;
        if (order.table.toLowerCase().includes(q)) return true;

        return order.items.some((item) => item.name.toLowerCase().includes(q));
    }

    /**
     * Extract a numeric sort key from an order for a given sortBy value.
     * orderId serves as a stable proxy for placed_at (higher id = later order).
     * waitingDuration is parsed back to seconds for accurate numeric comparison.
     */
    function numericSortKey(
        order: ShapedOrder,
        key: QueueSortKey,
    ): number | string {
        switch (key) {
            case 'placed_at':
                // Larger orderId = placed later → ascending shows oldest first
                return order.orderId;

            case 'waiting':
                // Parse "Xm Ys" or "Ys" back to total seconds
                return parseDurationToSeconds(order.waitingDuration);

            case 'table':
                // Table numbers may be alphanumeric — sort as string
                return order.table;
        }
    }

    /**
     * Parse a waitingDuration string ("4m 32s" | "45s") back to seconds.
     * Falls back to 0 for unexpected formats.
     */
    function parseDurationToSeconds(duration: string): number {
        const minuteMatch = duration.match(/(\d+)m/);
        const secondMatch = duration.match(/(\d+)s/);
        const minutes = minuteMatch ? parseInt(minuteMatch[1], 10) : 0;
        const seconds = secondMatch ? parseInt(secondMatch[1], 10) : 0;
        return minutes * 60 + seconds;
    }

    /**
     * Compare two orders for sorting. Returns negative/zero/positive.
     */
    function compareOrders(a: ShapedOrder, b: ShapedOrder): number {
        const aKey = numericSortKey(a, sortBy.value);
        const bKey = numericSortKey(b, sortBy.value);

        let result: number;

        if (typeof aKey === 'string' && typeof bKey === 'string') {
            result = aKey.localeCompare(bKey, undefined, {
                numeric: true,
                sensitivity: 'base',
            });
        } else {
            result = (aKey as number) - (bKey as number);
        }

        return sortDirection.value === 'desc' ? -result : result;
    }

    // -----------------------------------------------------------------------
    // Computed pipeline: filter → search → sort
    // -----------------------------------------------------------------------

    /** Orders after status filter applied. */
    const statusFiltered = computed<ShapedOrder[]>(() =>
        orders.value.filter(matchesStatus),
    );

    /** Orders after status filter + search query applied. */
    const searched = computed<ShapedOrder[]>(() =>
        statusFiltered.value.filter(matchesSearch),
    );

    /** Final output: filtered, searched, and sorted. */
    const sortedOrders = computed<ShapedOrder[]>(() =>
        [...searched.value].sort(compareOrders),
    );

    /** Number of orders in the final output. */
    const orderCount = computed(() => sortedOrders.value.length);

    /** True when the final output is empty. */
    const isEmpty = computed(() => sortedOrders.value.length === 0);

    /**
     * True when any filter or search is active.
     * Useful for showing a "clear filters" affordance.
     */
    const hasActiveFilters = computed(
        () => !!statusFilter.value || !!searchQuery.value.trim(),
    );

    // -----------------------------------------------------------------------
    // Actions
    // -----------------------------------------------------------------------

    /** Reset all filters, search, and sort state to their defaults. */
    function clearFilters(): void {
        statusFilter.value = '';
        searchQuery.value = '';
        sortBy.value = 'placed_at';
        sortDirection.value = 'asc';
    }

    /** Toggle sort direction, or set a new sort key (resets to asc). */
    function setSort(key: QueueSortKey): void {
        if (sortBy.value === key) {
            sortDirection.value =
                sortDirection.value === 'asc' ? 'desc' : 'asc';
        } else {
            sortBy.value = key;
            sortDirection.value = 'asc';
        }
    }

    // -----------------------------------------------------------------------
    // Public API
    // -----------------------------------------------------------------------

    return {
        // State (writable — bind directly to inputs / filter controls)
        statusFilter,
        searchQuery,
        sortBy,
        sortDirection,

        // Computed
        sortedOrders,
        orderCount,
        isEmpty,
        hasActiveFilters,

        // Actions
        clearFilters,
        setSort,

        // Constants (for use in filter/sort UI without duplicating values)
        QUEUE_STATUS_OPTIONS,
        QUEUE_SORT_OPTIONS,
    };
}
