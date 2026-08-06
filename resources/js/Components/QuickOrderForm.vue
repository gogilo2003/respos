<script setup lang="ts">
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import ToastContainer from '@/Components/ToastContainer.vue';

interface MenuItem {
    id: number;
    name: string;
    price: number;
}

interface SelectedItem {
    menuItemId: number;
    name: string;
    quantity: number;
    unitPrice: number;
}

interface Props {
    tables: { id: number; table_number: string }[];
    menuItems: MenuItem[];
}

const props = defineProps<Props>();
const emit = defineEmits<{
    submit: [data: {
        tableSessionId: number;
        items: SelectedItem[];
    }];
}>();

const selectedTableId = ref<number | ''>('');
const menuQuery = ref('');
const selectedItems = ref<SelectedItem[]>([]);
const toastRef = ref<InstanceType<typeof ToastContainer> | null>(null);

const filteredMenuItems = computed(() => {
    const query = menuQuery.value.trim().toLowerCase();

    if (!query) {
        return props.menuItems;
    }

    return props.menuItems.filter((item) =>
        item.name.toLowerCase().includes(query)
    );
});

const addMenuItem = (item: MenuItem) => {
    const existing = selectedItems.value.find(
        (entry) => entry.menuItemId === item.id
    );

    if (existing) {
        existing.quantity += 1;
    } else {
        selectedItems.value.push({
            menuItemId: item.id,
            name: item.name,
            quantity: 1,
            unitPrice: item.price,
        });
    }
};

const updateQuantity = (menuItemId: number, quantity: number) => {
    if (quantity <= 0) {
        selectedItems.value = selectedItems.value.filter(
            (item) => item.menuItemId !== menuItemId
        );
        return;
    }

    const item = selectedItems.value.find(
        (entry) => entry.menuItemId === menuItemId
    );

    if (item) {
        item.quantity = quantity;
    }
};

const removeItem = (menuItemId: number) => {
    selectedItems.value = selectedItems.value.filter(
        (item) => item.menuItemId !== menuItemId
    );
};

const form = useForm<{
    table_session_id: number | '';
    items: { menu_item_id: number; quantity: number }[];
}>({
    table_session_id: selectedTableId.value,
    items: [],
});

const submitOrder = () => {
    if (selectedTableId.value === '' || selectedItems.value.length === 0) {
        return;
    }

    form.table_session_id = selectedTableId.value;
    form.items = selectedItems.value.map((item) => ({
        menu_item_id: item.menuItemId,
        quantity: item.quantity,
    }));

    form.post('/waiter/orders', {
        onSuccess: () => {
            selectedTableId.value = '';
            selectedItems.value = [];
            menuQuery.value = '';
            toastRef.value?.show('Order submitted successfully.', 'success');
        },
        onError: () => {
            toastRef.value?.show('Failed to submit order.', 'error');
        },
    });
};
</script>

<template>
    <form class="space-y-6" @submit.prevent="submitOrder">
        <div>
            <label class="block text-sm font-medium text-gray-700">Table</label>
            <select
                v-model="selectedTableId"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
                <option value="">Select a table</option>
                <option
                    v-for="table in tables"
                    :key="table.id"
                    :value="table.id"
                >
                    {{ table.table_number }}
                </option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Menu</label>
            <input
                v-model="menuQuery"
                type="text"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                placeholder="Search menu..."
            />

            <div class="mt-2 max-h-48 overflow-y-auto rounded-md border border-gray-200">
                <button
                    v-for="item in filteredMenuItems"
                    :key="item.id"
                    type="button"
                    class="flex w-full items-center justify-between px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    @click="addMenuItem(item)"
                >
                    <span>{{ item.name }}</span>
                    <span class="text-gray-500">
                        {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(item.price) }}
                    </span>
                </button>
                <div
                    v-if="filteredMenuItems.length === 0"
                    class="px-3 py-4 text-sm text-gray-500"
                >
                    No menu items found.
                </div>
            </div>
        </div>

        <div v-if="selectedItems.length > 0">
            <h3 class="text-sm font-medium text-gray-700">Selected Items</h3>
            <div class="mt-2 space-y-2">
                <div
                    v-for="item in selectedItems"
                    :key="item.menuItemId"
                    class="flex items-center justify-between rounded-md border border-gray-200 px-3 py-2"
                >
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ item.name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(item.unitPrice) }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            type="number"
                            :value="item.quantity"
                            min="1"
                            class="w-16 rounded-md border border-gray-300 px-2 py-1 text-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                            @input="updateQuantity(item.menuItemId, Number(($event.target as HTMLInputElement).value))"
                        />
                        <button
                            type="button"
                            class="text-sm text-red-600 hover:text-red-800"
                            @click="removeItem(item.menuItemId)"
                        >
                            Remove
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="form.errors.table_session_id" class="text-sm text-red-600">
            {{ form.errors.table_session_id }}
        </div>
        <div v-if="form.errors.items" class="text-sm text-red-600">
            {{ form.errors.items }}
        </div>

        <button
            type="submit"
            class="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800 disabled:opacity-50"
            :disabled="selectedTableId === '' || selectedItems.length === 0 || form.processing"
        >
            {{ form.processing ? 'Submitting...' : 'Submit Order' }}
        </button>
    </form>

    <ToastContainer ref="toastRef" />
</template>

