<script setup lang="ts">
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import OrderStatusBadge from '@/Components/OrderStatusBadge.vue';
import Paginator from '@/Components/Paginator.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
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
const createForm = useForm({
    table_session_id: '',
    items: [
        {
            menu_item_id: '',
            quantity: 1,
            special_instructions: '',
        },
    ],
});

const addItemForm = useForm({
    menu_item_id: '',
    quantity: 1,
    special_instructions: '',
});

const statusTabs = [
    { label: 'All Orders', value: 'all' },
    { label: 'Pending', value: 'pending' },
    { label: 'Accepted', value: 'accepted' },
    { label: 'Preparing', value: 'preparing' },
    { label: 'Ready', value: 'ready' },
    { label: 'Served', value: 'served' },
    { label: 'Completed', value: 'completed' },
    { label: 'Cancelled', value: 'cancelled' },
];

const openCreateModal = () => {
    createForm.reset();
    createForm.clearErrors();
    createForm.table_session_id = props.activeSessions[0]?.id
        ? String(props.activeSessions[0].id)
        : '';
    createForm.items = [
        {
            menu_item_id: props.menuItems[0]?.id
                ? String(props.menuItems[0].id)
                : '',
            quantity: 1,
            special_instructions: '',
        },
    ];
    showCreateModal.value = true;
};

const addCreateItemRow = () => {
    createForm.items.push({
        menu_item_id: props.menuItems[0]?.id
            ? String(props.menuItems[0].id)
            : '',
        quantity: 1,
        special_instructions: '',
    });
};

const removeCreateItemRow = (index: number) => {
    if (createForm.items.length > 1) {
        createForm.items.splice(index, 1);
    }
};

const submitCreateOrder = () => {
    createForm.post(route('orders.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

const openViewModal = (order: OrderData) => {
    selectedOrder.value = order;
    showViewModal.value = true;
};

const openEditItemsModal = (order: OrderData) => {
    selectedOrder.value = order;
    addItemForm.reset();
    addItemForm.clearErrors();
    addItemForm.menu_item_id = props.menuItems[0]?.id
        ? String(props.menuItems[0].id)
        : '';
    showEditItemsModal.value = true;
};

const submitAddItem = () => {
    if (!selectedOrder.value) return;

    addItemForm.post(route('orders.items.add', selectedOrder.value.id), {
        onSuccess: () => {
            addItemForm.reset();
            addItemForm.menu_item_id = props.menuItems[0]?.id
                ? String(props.menuItems[0].id)
                : '';
        },
    });
};

const updateItemQuantity = (item: OrderItemData, newQty: number) => {
    if (!selectedOrder.value || newQty < 1) return;

    router.patch(
        route('orders.items.update', [selectedOrder.value.id, item.id]),
        {
            quantity: newQty,
        },
        { preserveScroll: true },
    );
};

const removeItem = (item: OrderItemData) => {
    if (!selectedOrder.value) return;

    if (confirm('Are you sure you want to remove this item from the order?')) {
        router.delete(
            route('orders.items.remove', [selectedOrder.value.id, item.id]),
            { preserveScroll: true },
        );
    }
};

const transitionOrderStatus = (order: OrderData, newStatus: string) => {
    router.post(
        route('orders.status.update', order.id),
        { status: newStatus },
        {
            onSuccess: () => {
                if (
                    selectedOrder.value &&
                    selectedOrder.value.id === order.id
                ) {
                    selectedOrder.value.status = newStatus;
                }
            },
        },
    );
};

const calculateOrderTotal = (order: OrderData) => {
    if (!order.items || order.items.length === 0) return 0;
    return order.items.reduce(
        (sum, item) => sum + item.quantity * Number(item.unit_price),
        0,
    );
};

const canEditOrder = (order: OrderData) => {
    return (
        ['admin', 'manager', 'waiter'].includes(userRole.value) &&
        !['completed', 'cancelled', 'served'].includes(order.status)
    );
};
</script>

<template>
    <Head title="Orders Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">
                        Orders Listing & Management
                    </h2>
                    <p class="text-xs text-gray-500">
                        Monitor live restaurant orders, update preparation
                        status, and manage items
                    </p>
                </div>
                <PrimaryButton
                    v-if="['admin', 'manager', 'waiter'].includes(userRole)"
                    @click="openCreateModal"
                >
                    + New Waiter Order
                </PrimaryButton>
            </div>
        </template>

        <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Filter Toolbar -->
            <div
                class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-4 justify-between items-center"
            >
                <!-- Status Filter Pills -->
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="tab in statusTabs"
                        :key="tab.value"
                        @click="selectedStatus = tab.value"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-xs font-medium transition-colors',
                            selectedStatus === tab.value
                                ? 'bg-indigo-600 text-white shadow-sm'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
                        ]"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <!-- Search Input -->
                <div class="w-full md:w-64">
                    <TextInput
                        v-model="search"
                        type="text"
                        placeholder="Search order # or table..."
                        class="w-full text-xs"
                        @keyup.enter="updateFilters"
                    />
                </div>
            </div>

            <!-- Orders Grid -->
            <div
                v-if="orders.data.length > 0"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
            >
                <div
                    v-for="order in orders.data"
                    :key="order.id"
                    class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col justify-between hover:shadow-md transition-shadow"
                >
                    <div>
                        <!-- Card Header -->
                        <div
                            class="flex items-center justify-between pb-3 border-b border-gray-100"
                        >
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">
                                    Order #{{ order.id }}
                                </h3>
                                <span class="text-xs text-gray-500"
                                    >Table:
                                    <strong class="text-gray-800">{{
                                        order.session?.table?.table_number ||
                                        'N/A'
                                    }}</strong></span
                                >
                            </div>
                            <OrderStatusBadge :status="order.status" />
                        </div>

                        <!-- Card Meta -->
                        <div
                            class="py-2 text-xs text-gray-500 flex justify-between"
                        >
                            <span>Placed by: {{ order.placed_by_role }}</span>
                            <span>{{ order.created_at }}</span>
                        </div>

                        <!-- Items Summary Preview -->
                        <div class="py-2 space-y-1">
                            <div
                                v-for="item in (order.items || []).slice(0, 3)"
                                :key="item.id"
                                class="flex justify-between text-xs"
                            >
                                <span class="text-gray-700 truncate"
                                    >{{ item.quantity }}x
                                    {{
                                        item.menu_item?.name || 'Item'
                                    }}</span
                                >
                                <span class="font-medium text-gray-900">{{
                                    formatCurrency(
                                        item.quantity * Number(item.unit_price),
                                    )
                                }}</span>
                            </div>
                            <p
                                v-if="(order.items || []).length > 3"
                                class="text-xs text-indigo-600 font-medium pt-1"
                            >
                                +{{ (order.items || []).length - 3 }} more
                                items...
                            </p>
                        </div>
                    </div>

                    <!-- Card Footer & Actions -->
                    <div class="pt-4 border-t border-gray-100">
                        <div
                            class="flex items-center justify-between mb-3 font-semibold text-sm"
                        >
                            <span>Total</span>
                            <span class="text-indigo-600">{{
                                formatCurrency(calculateOrderTotal(order))
                            }}</span>
                        </div>

                        <div class="flex gap-2 justify-end">
                            <SecondaryButton @click="openViewModal(order)">
                                Details
                            </SecondaryButton>
                            <PrimaryButton
                                v-if="canEditOrder(order)"
                                @click="openEditItemsModal(order)"
                            >
                                Edit Items
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center"
            >
                <p class="text-gray-500 font-medium">No orders found.</p>
                <p class="text-xs text-gray-400 mt-1">
                    Try clearing search query or changing status filters.
                </p>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center pt-4">
                <Paginator :links="orders.links" />
            </div>
        </div>

        <!-- Create Order Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    Create New Waiter Order
                </h3>

                <form @submit.prevent="submitCreateOrder" class="space-y-4">
                    <div>
                        <InputLabel
                            for="table_session"
                            value="Select Active Table Session"
                        />
                        <select
                            id="table_session"
                            v-model="createForm.table_session_id"
                            class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                            <option
                                v-for="s in activeSessions"
                                :key="s.id"
                                :value="s.id"
                            >
                                Table {{ s.table_number }} (Session #{{ s.id }})
                            </option>
                        </select>
                    </div>

                    <!-- Items Rows -->
                    <div class="space-y-3 pt-2">
                        <label class="block text-xs font-medium text-gray-700"
                            >Order Items</label
                        >
                        <div
                            v-for="(row, idx) in createForm.items"
                            :key="idx"
                            class="flex flex-col sm:flex-row gap-2 items-end bg-gray-50 p-3 rounded-lg border border-gray-200"
                        >
                            <div class="flex-1">
                                <span class="text-xs text-gray-500 font-medium"
                                    >Item</span
                                >
                                <select
                                    v-model="row.menu_item_id"
                                    class="w-full text-xs rounded-md border-gray-300 shadow-sm"
                                    required
                                >
                                    <option
                                        v-for="m in menuItems"
                                        :key="m.id"
                                        :value="m.id"
                                    >
                                        {{ m.name }} -
                                        {{ formatCurrency(m.price) }}
                                    </option>
                                </select>
                            </div>
                            <div class="w-20">
                                <span class="text-xs text-gray-500 font-medium"
                                    >Qty</span
                                >
                                <TextInput
                                    v-model.number="row.quantity"
                                    type="number"
                                    min="1"
                                    max="50"
                                    class="w-full text-xs"
                                    required
                                />
                            </div>
                            <div class="flex-1">
                                <span class="text-xs text-gray-500 font-medium"
                                    >Notes</span
                                >
                                <TextInput
                                    v-model="row.special_instructions"
                                    type="text"
                                    placeholder="Optional instructions..."
                                    class="w-full text-xs"
                                />
                            </div>
                            <DangerButton
                                type="button"
                                @click="removeCreateItemRow(idx)"
                                v-if="createForm.items.length > 1"
                            >
                                X
                            </DangerButton>
                        </div>

                        <SecondaryButton type="button" @click="addCreateItemRow">
                            + Add Another Item
                        </SecondaryButton>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
                        <SecondaryButton type="button" @click="showCreateModal = false">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="createForm.processing">
                            Submit Order
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- View Details & Status Transition Modal -->
        <Modal :show="showViewModal" @close="showViewModal = false">
            <div v-if="selectedOrder" class="p-6 space-y-4">
                <div class="flex items-center justify-between border-b pb-3">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">
                            Order #{{ selectedOrder.id }} Details
                        </h3>
                        <p class="text-xs text-gray-500">
                            Table:
                            <strong>{{
                                selectedOrder.session?.table?.table_number
                            }}</strong>
                            | Placed by: {{ selectedOrder.placed_by_role }}
                        </p>
                    </div>
                    <OrderStatusBadge :status="selectedOrder.status" />
                </div>

                <!-- Status Transition Action Buttons -->
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                    <span class="block text-xs font-semibold text-gray-700 mb-2"
                        >Update Order Status</span
                    >
                    <div class="flex flex-wrap gap-2">
                        <SecondaryButton
                            v-if="
                                selectedOrder.status === 'pending' &&
                                ['admin', 'manager', 'waiter'].includes(
                                    userRole,
                                )
                            "
                            @click="
                                transitionOrderStatus(selectedOrder, 'accepted')
                            "
                        >
                            Accept Order
                        </SecondaryButton>

                        <SecondaryButton
                            v-if="
                                ['accepted', 'pending'].includes(
                                    selectedOrder.status,
                                ) &&
                                ['admin', 'manager', 'kitchen'].includes(
                                    userRole,
                                )
                            "
                            @click="
                                transitionOrderStatus(
                                    selectedOrder,
                                    'preparing',
                                )
                            "
                        >
                            Start Preparing
                        </SecondaryButton>

                        <SecondaryButton
                            v-if="
                                selectedOrder.status === 'preparing' &&
                                ['admin', 'manager', 'kitchen'].includes(
                                    userRole,
                                )
                            "
                            @click="
                                transitionOrderStatus(selectedOrder, 'ready')
                            "
                        >
                            Mark Ready
                        </SecondaryButton>

                        <SecondaryButton
                            v-if="
                                selectedOrder.status === 'ready' &&
                                ['admin', 'manager', 'waiter'].includes(
                                    userRole,
                                )
                            "
                            @click="
                                transitionOrderStatus(selectedOrder, 'served')
                            "
                        >
                            Mark Served
                        </SecondaryButton>

                        <DangerButton
                            v-if="
                                !['completed', 'cancelled'].includes(
                                    selectedOrder.status,
                                ) &&
                                ['admin', 'manager'].includes(userRole)
                            "
                            @click="
                                transitionOrderStatus(
                                    selectedOrder,
                                    'cancelled',
                                )
                            "
                        >
                            Cancel Order
                        </DangerButton>
                    </div>
                </div>

                <!-- Items Breakdown -->
                <div>
                    <h4 class="text-xs font-bold text-gray-700 uppercase mb-2">
                        Order Items
                    </h4>
                    <div class="divide-y border rounded-lg overflow-hidden">
                        <div
                            v-for="item in selectedOrder.items || []"
                            :key="item.id"
                            class="p-3 flex justify-between items-center text-xs"
                        >
                            <div>
                                <p class="font-bold text-gray-900">
                                    {{ item.quantity }}x
                                    {{ item.menu_item?.name || 'Item' }}
                                </p>
                                <p
                                    v-if="item.special_instructions"
                                    class="text-gray-500 italic"
                                >
                                    Note: {{ item.special_instructions }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="font-semibold text-gray-900">{{
                                    formatCurrency(
                                        item.quantity * Number(item.unit_price),
                                    )
                                }}</span>
                                <span
                                    class="block text-[10px] uppercase font-bold text-indigo-600"
                                >
                                    {{ item.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <SecondaryButton @click="showViewModal = false">
                        Close
                    </SecondaryButton>
                </div>
            </div>
        </Modal>

        <!-- Edit Order Items Modal -->
        <Modal :show="showEditItemsModal" @close="showEditItemsModal = false">
            <div v-if="selectedOrder" class="p-6 space-y-4">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="text-lg font-bold text-gray-900">
                        Edit Items for Order #{{ selectedOrder.id }}
                    </h3>
                    <SecondaryButton @click="showEditItemsModal = false">
                        Done
                    </SecondaryButton>
                </div>

                <!-- Add New Item Section -->
                <form
                    @submit.prevent="submitAddItem"
                    class="bg-gray-50 p-4 rounded-lg border border-gray-200 space-y-3"
                >
                    <h4 class="text-xs font-bold text-gray-700 uppercase">
                        Add New Item to Order
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <select
                            v-model="addItemForm.menu_item_id"
                            class="text-xs rounded-md border-gray-300 shadow-sm"
                            required
                        >
                            <option
                                v-for="m in menuItems"
                                :key="m.id"
                                :value="m.id"
                            >
                                {{ m.name }} - {{ formatCurrency(m.price) }}
                            </option>
                        </select>
                        <TextInput
                            v-model.number="addItemForm.quantity"
                            type="number"
                            min="1"
                            max="50"
                            class="text-xs"
                            placeholder="Qty"
                            required
                        />
                        <TextInput
                            v-model="addItemForm.special_instructions"
                            type="text"
                            placeholder="Notes..."
                            class="text-xs"
                        />
                    </div>
                    <div class="flex justify-end">
                        <PrimaryButton type="submit" :disabled="addItemForm.processing">
                            + Add Item
                        </PrimaryButton>
                    </div>
                </form>

                <!-- Current Items Table -->
                <div class="space-y-2">
                    <h4 class="text-xs font-bold text-gray-700 uppercase">
                        Existing Items
                    </h4>
                    <div class="divide-y border rounded-lg overflow-hidden">
                        <div
                            v-for="item in selectedOrder.items || []"
                            :key="item.id"
                            class="p-3 flex justify-between items-center text-xs"
                        >
                            <div>
                                <p class="font-bold text-gray-900">
                                    {{ item.menu_item?.name || 'Item' }}
                                </p>
                                <p class="text-gray-500">
                                    Unit Price:
                                    {{ formatCurrency(Number(item.unit_price)) }}
                                    | Status:
                                    <span class="font-semibold text-indigo-600">{{
                                        item.status
                                    }}</span>
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <SecondaryButton
                                    @click="
                                        updateItemQuantity(
                                            item,
                                            item.quantity - 1,
                                        )
                                    "
                                    :disabled="item.quantity <= 1"
                                >
                                    -
                                </SecondaryButton>
                                <span class="font-bold text-xs px-1">{{
                                    item.quantity
                                }}</span>
                                <SecondaryButton
                                    @click="
                                        updateItemQuantity(
                                            item,
                                            item.quantity + 1,
                                        )
                                    "
                                >
                                    +
                                </SecondaryButton>
                                <DangerButton @click="removeItem(item)">
                                    Remove
                                </DangerButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
