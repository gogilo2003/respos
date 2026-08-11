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
// Types — component-facing (camelCase, consumed by KitchenOrderQueue / KitchenOrderCard)
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

const RELOAD_KEYS = [
    'pending_orders',
    'preparing_orders',
    'ready_orders',
    'statistics',
] as const;

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
    const currentTime = ref(formatClock());

    // -----------------------------------------------------------------------
    // Formatting helpers
    // -----------------------------------------------------------------------

    function formatClock(): string {
        return new Date().toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        });
    }

    function formatOrderTime(placedAt: string): string {
        return new Date(placedAt).toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit',
        });
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
    // Computed
    // -----------------------------------------------------------------------

    /**
     * All three order lists merged and shaped, preserving placement order.
     * Passed to useKitchenQueue for filtering / sorting / searching.
     */
    const allOrders = computed<ShapedOrder[]>(() => [
        ...props.pending_orders.map(shapeOrder),
        ...props.preparing_orders.map(shapeOrder),
        ...props.ready_orders.map(shapeOrder),
    ]);

    /** Statistics passed through directly — no re-shaping needed. */
    const stats = computed(() => props.statistics);

    // -----------------------------------------------------------------------
    // Refresh
    // -----------------------------------------------------------------------

    function refresh(): void {
        if (refreshing.value) return;

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
    // Auto-refresh (15-second interval, paused when hidden)
    // -----------------------------------------------------------------------

    let autoRefreshTimer: ReturnType<typeof setInterval> | null = null;

    const isTabVisible = () =>
        typeof document !== 'undefined' &&
        document.visibilityState === 'visible';

    const startAutoRefresh = () => {
        if (autoRefreshTimer !== null) return;

        autoRefreshTimer = setInterval(() => {
            if (isTabVisible()) {
                refresh();
            }
        }, 15_000);
    };

    const stopAutoRefresh = () => {
        if (autoRefreshTimer !== null) {
            clearInterval(autoRefreshTimer);
            autoRefreshTimer = null;
        }
    };

    const handleVisibilityChange = () => {
        if (!isTabVisible()) {
            stopAutoRefresh();
        } else {
            refresh();
            startAutoRefresh();
        }
    };

    onMounted(() => {
        startAutoRefresh();

        if (typeof document !== 'undefined') {
            document.addEventListener(
                'visibilitychange',
                handleVisibilityChange,
            );
        }
    });

    onBeforeUnmount(() => {
        stopAutoRefresh();

        if (typeof document !== 'undefined') {
            document.removeEventListener(
                'visibilitychange',
                handleVisibilityChange,
            );
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
        currentTime,

        // Computed
        allOrders,
        stats,

        // Actions
        refresh,
    };
}
