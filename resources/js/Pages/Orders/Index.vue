<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { formatCurrency } from '@/utils/currency';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface MenuItemOption {
    id: number;
    name: string;
    price: number;
    category_name?: string;
}

interface TableSessionOption {
    id: number;
    table_number: string;
}

interface OrderItemData {
    id: number;
    order_id: number;
    menu_item_id: number;
    quantity: number;
    unit_price: number;
    special_instructions: string | null;
    status: string;
    menu_item?: MenuItemOption;
}

interface OrderData {
    id: number;
    session_id: number;
    placed_by_role: string;
    placed_by_user: number | null;
    status: string;
    created_at: string;
    session?: {
        id: number;
        table_id: number;
        table?: {
            id: number;
            table_number: string;
        };
    };
    placed_by?: {
        id: number;
        name: string;
    };
    items?: OrderItemData[];
}

const props = defineProps<{
    orders: {
        data: OrderData[];
        links: any[];
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        status: string;
        search: string;
        table_id: string;
    };
    activeSessions: TableSessionOption[];
    menuItems: MenuItemOption[];
}>();

const userRole = computed(
    () => (usePage().props.auth as any)?.user?.role?.name || 'guest',
);

// Filter states
const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'all');

const updateFilters = () => {
    router.get(
        route('orders.index'),
        {
            search: search.value || undefined,
            status:
                selectedStatus.value !== 'all'
                    ? selectedStatus.value
                    : undefined,
        },
        { preserveState: true, replace: true },
    );
};

watch([selectedStatus], () => {
    updateFilters();
});

// Modals
const showCreateModal = ref(false);
const showViewModal = ref(false);
const showEditItemsModal = ref(false);

const selectedOrder = ref<OrderData | null>(null);

// Forms
const createOrderForm = useForm({
    table_session_id: '' as string | number,
    items: [] as {
        menu_item_id: number;
        quantity: number;
        special_instructions: string;
    }[],
});

const addItemForm = useForm({
    menu_item_id: '' as string | number,
    quantity: 1,
    special_instructions: '',
});

// Item selection helper for order creation
const newItemSelection = ref({
    menu_item_id: '' as string | number,
    quantity: 1,
    special_instructions: '',
});

const addCreateItem = () => {
    if (!newItemSelection.value.menu_item_id) return;
    createOrderForm.items.push({
        menu_item_id: Number(newItemSelection.value.menu_item_id),
        quantity: Number(newItemSelection.value.quantity) || 1,
        special_instructions: newItemSelection.value.special_instructions,
    });
    newItemSelection.value = {
        menu_item_id: '',
        quantity: 1,
        special_instructions: '',
    };
};

const removeCreateItem = (index: number) => {
    createOrderForm.items.splice(index, 1);
};

const submitCreateOrder = () => {
    createOrderForm.post(route('orders.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createOrderForm.reset();
        },
    });
};

const openViewModal = (order: OrderData) => {
    selectedOrder.value = order;
    showViewModal.value = true;
};

const openEditItemsModal = (order: OrderData) => {
    selectedOrder.value = order;
    showEditItemsModal.value = true;
};

const submitAddItem = () => {
    if (!selectedOrder.value || !addItemForm.menu_item_id) return;
    addItemForm.post(
        route('orders.items.add', { order: selectedOrder.value.id }),
        {
            onSuccess: () => {
                addItemForm.reset();
            },
        },
    );
};

const updateItemQuantity = (item: OrderItemData, newQty: number) => {
    if (!selectedOrder.value || newQty < 1) return;
    router.patch(
        route('orders.items.update', {
            order: selectedOrder.value.id,
            item: item.id,
        }),
        { quantity: newQty },
        { preserveScroll: true },
    );
};

const removeItem = (item: OrderItemData) => {
    if (!selectedOrder.value) return;
    if (!confirm('Are you sure you want to remove this item?')) return;
    router.delete(
        route('orders.items.remove', {
            order: selectedOrder.value.id,
            item: item.id,
        }),
        { preserveScroll: true },
    );
};

const updateOrderStatus = (order: OrderData, status: string) => {
    router.patch(
        route('orders.status.update', { order: order.id }),
        { status },
        { preserveScroll: true },
    );
};

const updateOrderItemStatus = (
    order: OrderData,
    item: OrderItemData,
    status: string,
) => {
    router.patch(
        route('orders.items.update', { order: order.id, item: item.id }),
        { status },
        { preserveScroll: true },
    );
};

const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'pending':
            return 'bg-amber-100 text-amber-800 border-amber-300';
        case 'accepted':
            return 'bg-blue-100 text-blue-800 border-blue-300';
        case 'preparing':
            return 'bg-purple-100 text-purple-800 border-purple-300';
        case 'ready':
            return 'bg-emerald-100 text-emerald-800 border-emerald-300';
        case 'served':
            return 'bg-teal-100 text-teal-800 border-teal-300';
        case 'completed':
            return 'bg-gray-100 text-gray-800 border-gray-300';
        case 'cancelled':
            return 'bg-red-100 text-red-800 border-red-300';
        default:
            return 'bg-gray-100 text-gray-700 border-gray-300';
    }
};

const calculateOrderTotal = (items?: OrderItemData[]) => {
    if (!items) return 0;
    return items.reduce(
        (sum, item) => sum + Number(item.unit_price) * item.quantity,
        0,
    );
};
</script>

<template>

    <Head title="Orders Workspace" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-gray-800">
                        Orders Management
                    </h2>
                    <p class="text-xs text-gray-500">
                        Manage live restaurant orders, statuses, and items
                    </p>
                </div>
                <div v-if="['admin', 'manager', 'waiter'].includes(userRole)">
                    <button
                        class="rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white shadow hover:bg-gray-800"
                        @click="showCreateModal = true">
                        + Create Order
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6 p-6">
            <!-- Filter Bar & Search -->
            <div
                class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm md:flex-row md:items-center md:justify-between">
                <!-- Status Tabs -->
                <div class="flex flex-wrap items-center gap-1">
                    <button v-for="status in [
                        'all',
                        'pending',
                        'accepted',
                        'preparing',
                        'ready',
                        'served',
                        'completed',
                        'cancelled',
                    ]" :key="status" class="rounded-lg px-3 py-1.5 text-xs font-semibold capitalize transition"
                        :class="selectedStatus === status
                                ? 'bg-gray-900 text-white'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                            " @click="selectedStatus = status">
                        {{ status }}
                    </button>
                </div>

                <!-- Search Input -->
                <div class="flex items-center gap-2">
                    <input v-model="search" type="text" placeholder="Search Order #, Table #, Item..."
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-black focus:ring-black md:w-64"
                        @keyup.enter="updateFilters" />
                    <button
                        class="rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-200"
                        @click="updateFilters">
                        Filter
                    </button>
                </div>
            </div>

            <!-- Orders Grid / List -->
            <div v-if="props.orders.data.length === 0"
                class="rounded-xl border border-gray-200 bg-white p-8 text-center">
                <p class="text-sm font-medium text-gray-500">
                    No orders match your filter criteria.
                </p>
            </div>

            <div v-else class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="order in props.orders.data" :key="order.id"
                    class="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                    <div>
                        <!-- Header -->
                        <div class="flex items-start justify-between border-b pb-3">
                            <div>
                                <span class="text-xs font-bold text-gray-400">ORDER #{{ order.id }}</span>
                                <h3 class="text-lg font-bold text-gray-900">
                                    {{
                                        order.session?.table?.table_number ||
                                        'Table #' + order.session_id
                                    }}
                                </h3>
                                <p class="text-xs text-gray-500">
                                    By:
                                    <span class="font-medium text-gray-700 capitalize">{{
                                        order.placed_by?.name ||
                                        order.placed_by_role
                                        }}</span>
                                </p>
                            </div>
                            <span class="rounded-full border px-2.5 py-0.5 text-xs font-bold capitalize"
                                :class="getStatusBadgeClass(order.status)">
                                {{ order.status }}
                            </span>
                        </div>

                        <!-- Items Summary -->
                        <div class="my-3 space-y-1.5">
                            <div v-for="item in order.items" :key="item.id"
                                class="flex items-center justify-between text-xs text-gray-700">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-gray-900">{{ item.quantity }}x</span>
                                    <span>{{
                                        item.menu_item?.name || 'Item'
                                        }}</span>
                                </div>
                                <span class="rounded border px-1.5 py-0.2 text-[10px] font-semibold capitalize"
                                    :class="getStatusBadgeClass(item.status)">
                                    {{ item.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer & Actions -->
                    <div class="border-t pt-3">
                        <div class="mb-3 flex items-center justify-between text-xs">
                            <span class="text-gray-500">Total:</span>
                            <span class="font-mono text-sm font-bold text-gray-900">
                                {{
                                    formatCurrency(
                                        calculateOrderTotal(order.items),
                                    )
                                }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                class="flex-1 rounded-lg border border-gray-300 bg-white py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                @click="openViewModal(order)">
                                View / Status
                            </button>
                            <button v-if="
                                ['admin', 'manager', 'waiter'].includes(
                                    userRole,
                                )
                            "
                                class="rounded-lg bg-black px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-800"
                                @click="openEditItemsModal(order)">
                                Edit Items
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View / Manage Modal -->
            <div v-if="showViewModal && selectedOrder"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl">
                    <div class="flex items-center justify-between border-b pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">
                                Order #{{ selectedOrder.id }} Details
                            </h3>
                            <p class="text-xs text-gray-500">
                                {{
                                    selectedOrder.session?.table?.table_number ||
                                    'Table #' + selectedOrder.session_id
                                }}
                            </p>
                        </div>
                        <button class="text-gray-400 hover:text-gray-600" @click="showViewModal = false">
                            ✕
                        </button>
                    </div>

                    <!-- Order Transition Actions -->
                    <div class="my-4 rounded-xl bg-gray-50 p-4 border border-gray-200">
                        <span class="text-xs font-bold uppercase text-gray-500">Order Status Actions</span>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button v-if="
                                selectedOrder.status === 'pending' &&
                                ['admin', 'manager', 'waiter'].includes(
                                    userRole,
                                )
                            "
                                class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-blue-700"
                                @click="
                                    updateOrderStatus(selectedOrder, 'accepted')
                                    ">
                                Accept Order
                            </button>
                            <button v-if="
                                ['accepted', 'pending'].includes(
                                    selectedOrder.status,
                                ) &&
                                [
                                    'admin',
                                    'manager',
                                    'kitchen',
                                ].includes(userRole)
                            "
                                class="rounded-lg bg-purple-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-purple-700"
                                @click="
                                    updateOrderStatus(
                                        selectedOrder,
                                        'preparing',
                                    )
                                    ">
                                Start Preparing
                            </button>
                            <button v-if="
                                selectedOrder.status === 'preparing' &&
                                [
                                    'admin',
                                    'manager',
                                    'kitchen',
                                ].includes(userRole)
                            "
                                class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700"
                                @click="
                                    updateOrderStatus(selectedOrder, 'ready')
                                    ">
                                Mark Ready
                            </button>
                            <button v-if="
                                selectedOrder.status === 'ready' &&
                                ['admin', 'manager', 'waiter'].includes(
                                    userRole,
                                )
                            "
                                class="rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-700"
                                @click="
                                    updateOrderStatus(selectedOrder, 'served')
                                    ">
                                Mark Served
                            </button>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                        <div v-for="item in selectedOrder.items" :key="item.id"
                            class="flex items-center justify-between rounded-lg border p-3">
                            <div>
                                <div class="font-bold text-sm text-gray-900">
                                    {{ item.quantity }}x
                                    {{ item.menu_item?.name }}
                                </div>
                                <div v-if="item.special_instructions" class="text-xs text-amber-600 font-medium">
                                    Note: {{ item.special_instructions }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full border px-2 py-0.5 text-xs font-semibold capitalize"
                                    :class="getStatusBadgeClass(item.status)">
                                    {{ item.status }}
                                </span>
                                <!-- Item status controls for Kitchen -->
                                <button v-if="
                                    item.status === 'pending' &&
                                    [
                                        'admin',
                                        'manager',
                                        'kitchen',
                                    ].includes(userRole)
                                "
                                    class="rounded bg-purple-100 px-2 py-1 text-xs font-bold text-purple-800 hover:bg-purple-200"
                                    @click="
                                        updateOrderItemStatus(
                                            selectedOrder,
                                            item,
                                            'preparing',
                                        )
                                        ">
                                    Prep
                                </button>
                                <button v-if="
                                    item.status === 'preparing' &&
                                    [
                                        'admin',
                                        'manager',
                                        'kitchen',
                                    ].includes(userRole)
                                "
                                    class="rounded bg-emerald-100 px-2 py-1 text-xs font-bold text-emerald-800 hover:bg-emerald-200"
                                    @click="
                                        updateOrderItemStatus(
                                            selectedOrder,
                                            item,
                                            'ready',
                                        )
                                        ">
                                    Ready
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Items Modal (Waiter / Manager) -->
            <div v-if="showEditItemsModal && selectedOrder"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl">
                    <div class="flex items-center justify-between border-b pb-3">
                        <h3 class="text-lg font-bold text-gray-900">
                            Edit Items - Order #{{ selectedOrder.id }}
                        </h3>
                        <button class="text-gray-400 hover:text-gray-600" @click="showEditItemsModal = false">
                            ✕
                        </button>
                    </div>

                    <!-- Add New Item Form -->
                    <form class="my-4 rounded-xl bg-gray-50 p-3 border space-y-3" @submit.prevent="submitAddItem">
                        <span class="text-xs font-bold uppercase text-gray-600">Add Item to Order</span>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                            <select v-model="addItemForm.menu_item_id"
                                class="rounded-lg border-gray-300 text-xs sm:col-span-2" required>
                                <option value="">Select Item...</option>
                                <option v-for="item in props.menuItems" :key="item.id" :value="item.id">
                                    {{ item.name }} ({{
                                        formatCurrency(item.price)
                                    }})
                                </option>
                            </select>
                            <input v-model="addItemForm.quantity" type="number" min="1" placeholder="Qty"
                                class="rounded-lg border-gray-300 text-xs" required />
                        </div>
                        <input v-model="addItemForm.special_instructions" type="text"
                            placeholder="Special instructions (optional)..."
                            class="w-full rounded-lg border-gray-300 text-xs" />
                        <button type="submit"
                            class="w-full rounded-lg bg-black py-2 text-xs font-bold text-white hover:bg-gray-800">
                            + Add Item
                        </button>
                    </form>

                    <!-- Existing Order Items -->
                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        <div v-for="item in selectedOrder.items" :key="item.id"
                            class="flex items-center justify-between border-b pb-2 text-xs">
                            <div>
                                <span class="font-bold">{{
                                    item.menu_item?.name
                                    }}</span>
                                <span class="text-gray-500 ml-2 font-mono">{{ formatCurrency(item.unit_price) }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button class="rounded bg-gray-200 px-2 py-0.5 font-bold" @click="
                                    updateItemQuantity(
                                        item,
                                        item.quantity - 1,
                                    )
                                    ">
                                    -
                                </button>
                                <span class="font-bold">{{
                                    item.quantity
                                    }}</span>
                                <button class="rounded bg-gray-200 px-2 py-0.5 font-bold" @click="
                                    updateItemQuantity(
                                        item,
                                        item.quantity + 1,
                                    )
                                    ">
                                    +
                                </button>
                                <button class="text-red-600 font-bold ml-2 hover:underline" @click="removeItem(item)">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create Order Modal -->
            <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl">
                    <div class="flex items-center justify-between border-b pb-3">
                        <h3 class="text-lg font-bold text-gray-900">
                            Create New Waiter Order
                        </h3>
                        <button class="text-gray-400 hover:text-gray-600" @click="showCreateModal = false">
                            ✕
                        </button>
                    </div>

                    <form class="my-4 space-y-4" @submit.prevent="submitCreateOrder">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Active Table Session</label>
                            <select v-model="createOrderForm.table_session_id"
                                class="w-full rounded-lg border-gray-300 text-xs" required>
                                <option value="">Select Active Table...</option>
                                <option v-for="session in props.activeSessions" :key="session.id" :value="session.id">
                                    {{ session.table_number }}
                                </option>
                            </select>
                        </div>

                        <!-- Add Items to Order -->
                        <div class="rounded-xl bg-gray-50 p-3 border space-y-2">
                            <span class="text-xs font-bold uppercase text-gray-600">Add Menu Items</span>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                                <select v-model="newItemSelection.menu_item_id"
                                    class="rounded-lg border-gray-300 text-xs sm:col-span-2">
                                    <option value="">Select Item...</option>
                                    <option v-for="item in props.menuItems" :key="item.id" :value="item.id">
                                        {{ item.name }} ({{
                                            formatCurrency(item.price)
                                        }})
                                    </option>
                                </select>
                                <input v-model="newItemSelection.quantity" type="number" min="1" placeholder="Qty"
                                    class="rounded-lg border-gray-300 text-xs" />
                            </div>
                            <div class="flex gap-2">
                                <input v-model="newItemSelection.special_instructions
                                    " type="text" placeholder="Special instructions..."
                                    class="flex-1 rounded-lg border-gray-300 text-xs" />
                                <button type="button"
                                    class="rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-bold text-white"
                                    @click="addCreateItem">
                                    Add
                                </button>
                            </div>
                        </div>

                        <!-- Selected Items Preview -->
                        <div v-if="createOrderForm.items.length > 0"
                            class="space-y-1 max-h-40 overflow-y-auto border-t pt-2">
                            <div v-for="(item, idx) in createOrderForm.items" :key="idx"
                                class="flex items-center justify-between text-xs bg-gray-100 p-2 rounded">
                                <span>
                                    {{ item.quantity }}x
                                    {{
                                        props.menuItems.find(
                                            (m) => m.id === item.menu_item_id,
                                        )?.name
                                    }}
                                </span>
                                <button type="button" class="text-red-600 font-bold text-xs"
                                    @click="removeCreateItem(idx)">
                                    Remove
                                </button>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full rounded-lg bg-black py-2.5 text-sm font-bold text-white shadow hover:bg-gray-800 disabled:opacity-50"
                            :disabled="!createOrderForm.table_session_id ||
                                createOrderForm.items.length === 0
                                ">
                            Submit Order
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
