<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

interface OrderItem {
    order_item_id: number;
    name: string | null;
    quantity: number;
    status: string;
}

interface Order {
    order_id: number;
    placed_at: string;
    items: OrderItem[];
    item_counts: {
        pending: number;
        accepted: number;
        preparing: number;
        ready: number;
    };
    session: {
        table_session_id: number;
        table_number: string | null;
    };
}

interface Table {
    table_id: number;
    table_name: string;
    active_session: {
        table_session_id: number;
        status: string;
    } | null;
    order_summary: {
        active_orders_count: number;
        latest_order_status: string | null;
    } | null;
    assistance: {
        open_requests_count: number;
    } | null;
}

const props = defineProps<{
    tables: Table[];
}>();

const orders = ref<Order[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);

const fetchOrders = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await fetch('/kitchen/orders', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = await response.json();
        orders.value = data.orders || [];
    } catch (e) {
        error.value = 'Failed to fetch orders.';
    } finally {
        loading.value = false;
    }
};

let intervalId: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    fetchOrders();
    intervalId = setInterval(fetchOrders, 5000);
});

onBeforeUnmount(() => {
    if (intervalId) {
        clearInterval(intervalId);
    }
});

const tablesWithOrders = computed(() => {
    return props.tables.map((table) => {
        const order = orders.value.find((o) => o.session.table_session_id === table.active_session?.table_session_id);
        return {
            ...table,
            order,
        };
    });
});

const updateItemStatus = async (itemId: number, status: string) => {
    try {
        const response = await fetch(`/kitchen/order-items/${itemId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ status }),
        });
        if (!response.ok) {
            throw new Error('Failed to update status');
        }
        await fetchOrders();
    } catch (e) {
        error.value = 'Failed to update item status.';
    }
};
</script>

<template>
    <Head title="Kitchen Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Kitchen Dashboard
                </h2>
                <div v-if="error" class="text-sm text-red-600">
                    {{ error }}
                </div>
                <div v-if="loading" class="text-sm text-gray-500">
                    Refreshing...
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-medium text-gray-900">Active Tables</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div v-for="table in tablesWithOrders" :key="table.table_id" 
                                 class="border p-4 rounded-lg shadow-sm">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold">Table {{ table.table_name }}</span>
                                    <span :class="{'text-green-600': table.active_session?.status === 'open', 'text-gray-500': table.active_session?.status !== 'open'}">
                                        {{ table.active_session?.status || 'No Session' }}
                                    </span>
                                </div>
                                <div v-if="table.assistance?.open_requests_count > 0" class="mt-2 text-sm text-red-600">
                                    {{ table.assistance.open_requests_count }} assistance request(s)
                                </div>
                                <div v-if="table.order" class="mt-2 text-sm text-gray-600">
                                    {{ table.order.item_counts.pending }} pending, {{ table.order.item_counts.accepted }} accepted, {{ table.order.item_counts.preparing }} preparing
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900">Order Queue</h3>
                        <div v-if="loading" class="mt-4 text-gray-500">
                            Loading orders...
                        </div>
                        <div v-else-if="orders.length === 0" class="mt-4 text-gray-500">
                            No active orders.
                        </div>
                        <div v-else class="mt-4 space-y-4">
                            <div v-for="order in orders" :key="order.order_id" class="border p-4 rounded-lg shadow-sm">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="font-bold">Order #{{ order.order_id }}</span>
                                    <span class="text-sm text-gray-500">
                                        Table {{ order.session.table_number }} • {{ order.placed_at }}
                                    </span>
                                </div>
                                <div class="space-y-2">
                                    <div v-for="item in order.items" :key="item.order_item_id" class="flex justify-between items-center">
                                        <span>{{ item.name }} ({{ item.quantity }})</span>
                                        <div class="flex items-center space-x-2">
                                            <span :class="{
                                                'text-yellow-600': item.status === 'pending',
                                                'text-blue-600': item.status === 'accepted',
                                                'text-purple-600': item.status === 'preparing',
                                                'text-green-600': item.status === 'ready'
                                            }">
                                                {{ item.status }}
                                            </span>
                                            <select 
                                                v-if="item.status !== 'ready'" 
                                                @change="updateItemStatus(item.order_item_id, $event.target.value)"
                                                class="text-sm border rounded px-2 py-1"
                                            >
                                                <option value="pending" disabled>Pending</option>
                                                <option value="accepted">Accept</option>
                                                <option value="preparing">Preparing</option>
                                                <option value="ready">Ready</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
