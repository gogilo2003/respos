import { router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

// ---------------------------------------------------------------------------
// Types — backend payload (snake_case, matches KitchenDashboardService)
// ---------------------------------------------------------------------------

export interface BackendOrderItem {
    order_item_id: number;
    menu_item_id: number;
    name: string | null;
    quantity: number;
    status: string;
    unit_price: string;
    sla_seconds_total: number | null;
}

export interface BackendItemCounts {
    pending: number;
    preparing: number;
    ready: number;
    served: number;
}

export interface BackendOrder {
    order_id: number;
    session: {
        table_session_id: number;
        table_number: string | null;
    };
    placed_at: string;
    items: BackendOrderItem[];
    item_counts: BackendItemCounts;
}

export interface KitchenStatistics {
    pending_items: number;
    preparing_items: number;
    ready_items: number;
    avg_prep_seconds: number | null;
    avg_prep_label: string | null;
}

export interface KitchenDashboardProps {
    pending_orders: BackendOrder[];
    preparing_orders: BackendOrder[];
    ready_orders: BackendOrder[];
    statistics: KitchenStatistics;
}

// ---------------------------------------------------------------------------
// Types — component-facing (camelCase, passed to KitchenOrderQueue / KitchenOrderCard)
// ---------------------------------------------------------------------------

export interface ShapedOrderItem {
    orderItemId: number;
    name: string;
    quantity: number;
    status: string;
}

export interface ShapedItemCounts {
    pending: number;
    accepted: number;
    preparing: number;
    ready: number;
    served: number;
}

export interface ShapedOrder {
    orderId: number;
    orderNumber: number;
    table: string;
    orderTime: string;
    waitingDuration: string;
    items: ShapedOrderItem[];
    itemCounts: ShapedItemCounts;
}

// ---------------------------------------------------------------------------
// Partial-reload keys — must match Inertia::render prop names in the controller
// ---------------------------------------------------------------------------

const RELOAD_KEYS = ['pending_orders', 'preparing_orders', 'ready_orders', 'statistics'] as const;

// ---------------------------------------------------------------------------
// Composable
// ---------------------------------------------------------------------------

export function useKitchenDashboard(props: KitchenDashboardProps) {

    // -----------------------------------------------------------------------
    // State
    // -----------------------------------------------------------------------

    const loading = ref(false);
    const refreshing = ref(false);
    const error = ref<string | null>(null);
    const statusFilter = ref('');
    const priorityFilter = ref('');
    const currentTime = ref(formatClock());

    // -----------------------------------------------------------------------
    // Pure formatting helpers (no reactivity — safe to call in computed)
    // -----------------------------------------------------------------------

    function formatClock(): string {
        return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    function formatOrderTime(placedAt: string): string {
        return new Date(placedAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function formatWaitingDuration(placedAt: string): string {
        const diffMs = Date.now() - new Date(placedAt).getTime();
        const totalSeconds = Math.max(0, Math.floor(diffMs / 1000));
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        return minutes > 0 ? `${minutes}m ${seconds}s` : `${seconds}s`;
    }

    // -----------------------------------------------------------------------
    // Shape a single backend order into the component-facing structure
    // -----------------------------------------------------------------------

    function shapeOrder(order: BackendOrder): ShapedOrder {
        return {
            orderId: order.order_id,
            orderNumber: order.order_id,
            table: order.session.table_number ?? '—',
            orderTime: formatOrderTime(order.placed_at),
            waitingDuration: formatWaitingDuration(order.placed_at),
            items: order.items.map(
                (item): ShapedOrderItem => ({
                    orderItemId: item.order_item_id,
                    name: item.name ?? '(unknown)',
                    quantity: item.quantity,
                    status: item.status,
                }),
            ),
            itemCounts: {
                // Backend merges pending + accepted into item_counts.pending
                pending: order.item_counts.pending,
                accepted: 0,
                preparing: order.item_counts.preparing,
                ready: order.item_counts.ready,
                served: order.item_counts.served,
            },
        };
    }

    // -----------------------------------------------------------------------
    // Computed values
    // -----------------------------------------------------------------------

    /** All three order lists merged and shaped, preserving placement order. */
    const allOrders = computed<ShapedOrder[]>(() => [
        ...props.pending_orders.map(shapeOrder),
        ...props.preparing_orders.map(shapeOrder),
        ...props.ready_orders.map(shapeOrder),
    ]);

    /**
     * Orders after applying the active status filter.
     * An order is included when at least one of its items matches the filter,
     * or when no filter is active.
     */
    const filteredOrders = computed<ShapedOrder[]>(() => {
        if (!statusFilter.value) return allOrders.value;

        return allOrders.value.filter((order) =>
            order.items.some((item) => item.status === statusFilter.value),
        );
    });

    /** Total number of orders currently visible after filtering. */
    const orderCount = computed(() => filteredOrders.value.length);

    /** Statistics passed through directly — no re-shaping needed. */
    const stats = computed(() => props.statistics);

    /** True when the queue is empty after applying the active filter. */
    const isEmpty = computed(() => !loading.value && filteredOrders.value.length === 0);

    // -----------------------------------------------------------------------
    // Refresh action
    // -----------------------------------------------------------------------

    function refresh(): void {
        if (refreshing.value) return;   // debounce concurrent refreshes

        refreshing.value = true;
        error.value = null;
        currentTime.value = formatClock();

        router.reload({
            only: [...RELOAD_KEYS],
            onSuccess: () => {
                error.value = null;
            },
            onError: () => {
                error.value = 'Failed to refresh dashboard. Please try again.';
            },
            onFinish: () => {
                refreshing.value = false;
            },
        });
    }

    // -----------------------------------------------------------------------
    // Auto-refresh (30-second interval, matches KitchenDashboardHeader timer)
    // -----------------------------------------------------------------------

    let autoRefreshTimer: ReturnType<typeof setInterval> | null = null;

    onMounted(() => {
        autoRefreshTimer = setInterval(refresh, 30_000);
    });

    onBeforeUnmount(() => {
        if (autoRefreshTimer !== null) {
            clearInterval(autoRefreshTimer);
            autoRefreshTimer = null;
        }
    });

    // -----------------------------------------------------------------------
    // Public API
    // -----------------------------------------------------------------------

    return {
        // State
        loading,
        refreshing,
        error,
        statusFilter,
        priorityFilter,
        currentTime,

        // Computed
        allOrders,
        filteredOrders,
        orderCount,
        stats,
        isEmpty,

        // Actions
        refresh,
    };
}
