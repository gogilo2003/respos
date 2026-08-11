<script setup lang="ts">
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import type { RestaurantTable } from '@/interfaces/table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    tables: RestaurantTable[];
}>();

const confirmingTableDeletion = ref(false);
const tableToDelete = ref<number | null>(null);
const editingTable = ref<RestaurantTable | null>(null);
const showTableModal = ref(false);

const selectedQrTable = ref<RestaurantTable | null>(null);
const showQrModal = ref(false);

const form = useForm({
    table_number: '',
    capacity: '2',
    location: '',
    status: 'available',
    is_active: true,
});

const openCreateModal = () => {
    editingTable.value = null;
    form.reset();
    form.status = 'available';
    form.is_active = true;
    form.capacity = '2';
    showTableModal.value = true;
};

const openEditModal = (table: RestaurantTable) => {
    editingTable.value = table;
    form.clearErrors();
    form.table_number = table.table_number;
    form.capacity = table.capacity.toString();
    form.location = table.location || '';
    form.status = table.status;
    form.is_active = table.is_active;
    showTableModal.value = true;
};

const openQrModal = (table: RestaurantTable) => {
    selectedQrTable.value = table;
    showQrModal.value = true;
};

const regenerateQr = (table: RestaurantTable) => {
    router.post(
        route('tables.regenerate-qr', table.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedQrTable.value =
                    props.tables.find((t) => t.id === table.id) || table;
            },
        },
    );
};

const submit = () => {
    if (editingTable.value) {
        form.patch(route('tables.update', editingTable.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('tables.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const closeModal = () => {
    showTableModal.value = false;
    form.reset();
};

const confirmTableDeletion = (id: number) => {
    tableToDelete.value = id;
    confirmingTableDeletion.value = true;
};

const deleteTable = () => {
    if (tableToDelete.value) {
        form.delete(route('tables.destroy', tableToDelete.value), {
            onSuccess: () => (confirmingTableDeletion.value = false),
        });
    }
};

const statusClass = (status: string) => {
    const map: Record<string, string> = {
        available: 'bg-green-100 text-green-800',
        occupied: 'bg-red-100 text-red-800',
        ordering: 'bg-yellow-100 text-yellow-800',
        preparing: 'bg-blue-100 text-blue-800',
        served: 'bg-indigo-100 text-indigo-800',
        billing: 'bg-purple-100 text-purple-800',
        paid: 'bg-emerald-100 text-emerald-800',
        cleaning: 'bg-gray-100 text-gray-800',
        reserved: 'bg-orange-100 text-orange-800',
    };

    return map[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Tables Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2
                        class="text-xl font-semibold leading-tight text-gray-800"
                    >
                        Dining Tables &amp; QR Management
                    </h2>
                    <p class="mt-1 text-xs text-gray-500">
                        Manage restaurant floor tables and scannable QR ordering
                        codes
                    </p>
                </div>
                <PrimaryButton @click="openCreateModal">
                    + Add Table
                </PrimaryButton>
            </div>
        </template>

        <div class="py-8">
            <div class="px-4 sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden border border-gray-200 bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="overflow-x-auto p-6 text-gray-900">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Table Number
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Location
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Capacity
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        QR Code
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="table in tables" :key="table.id">
                                    <td
                                        class="whitespace-nowrap px-6 py-4 font-bold text-gray-900"
                                    >
                                        Table {{ table.table_number }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-600"
                                    >
                                        {{ table.location || '-' }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-700"
                                    >
                                        👥 {{ table.capacity }} guests
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm"
                                    >
                                        <span
                                            :class="statusClass(table.status)"
                                            class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                        >
                                            {{ table.status }}
                                        </span>
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm"
                                    >
                                        <button
                                            @click="openQrModal(table)"
                                            class="inline-flex items-center gap-1 rounded border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-600 hover:text-blue-900"
                                        >
                                            📱 View QR Code
                                        </button>
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium"
                                    >
                                        <button
                                            @click="openEditModal(table)"
                                            class="mr-4 text-indigo-600 hover:text-indigo-900"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="
                                                confirmTableDeletion(table.id)
                                            "
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code View Modal -->
        <Modal :show="showQrModal" @close="showQrModal = false">
            <div v-if="selectedQrTable" class="p-6 text-center">
                <h2 class="text-lg font-bold text-gray-900">
                    Table {{ selectedQrTable.table_number }} QR Code
                </h2>
                <p class="mt-1 text-xs text-gray-500">
                    Location: {{ selectedQrTable.location || 'Main Floor' }} •
                    Capacity: {{ selectedQrTable.capacity }} guests
                </p>

                <div class="mt-6 flex justify-center">
                    <div
                        class="rounded-xl border border-gray-200 bg-gray-50 p-4 shadow-inner"
                    >
                        <img
                            :src="route('tables.qr-image', selectedQrTable.id)"
                            :alt="`Table ${selectedQrTable.table_number} QR Code`"
                            class="mx-auto h-64 w-64 object-contain"
                        />
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-center gap-3">
                    <a
                        :href="route('tables.qr-image', selectedQrTable.id)"
                        download
                        class="rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-700"
                    >
                        ⬇️ Download SVG
                    </a>
                    <button
                        @click="regenerateQr(selectedQrTable)"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                    >
                        🔄 Regenerate QR Token
                    </button>
                    <SecondaryButton @click="showQrModal = false">
                        Close
                    </SecondaryButton>
                </div>
            </div>
        </Modal>

        <!-- Table Modal -->
        <Modal :show="showTableModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ editingTable ? 'Edit Table' : 'Add New Table' }}
                </h2>

                <form @submit.prevent="submit" class="mt-6 space-y-4">
                    <div>
                        <InputLabel for="table_number" value="Table Number" />
                        <TextInput
                            id="table_number"
                            v-model="form.table_number"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            maxlength="20"
                        />
                        <InputError
                            :message="form.errors.table_number"
                            class="mt-2"
                        />
                    </div>

                    <div>
                        <InputLabel for="capacity" value="Capacity" />
                        <TextInput
                            id="capacity"
                            v-model="form.capacity"
                            type="number"
                            min="1"
                            max="20"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError
                            :message="form.errors.capacity"
                            class="mt-2"
                        />
                    </div>

                    <div>
                        <InputLabel for="location" value="Location" />
                        <TextInput
                            id="location"
                            v-model="form.location"
                            type="text"
                            class="mt-1 block w-full"
                            maxlength="80"
                        />
                        <InputError
                            :message="form.errors.location"
                            class="mt-2"
                        />
                    </div>

                    <div>
                        <InputLabel for="status" value="Status" />
                        <select
                            id="status"
                            v-model="form.status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="ordering">Ordering</option>
                            <option value="preparing">Preparing</option>
                            <option value="served">Served</option>
                            <option value="billing">Billing</option>
                            <option value="paid">Paid</option>
                            <option value="cleaning">Cleaning</option>
                            <option value="reserved">Reserved</option>
                        </select>
                        <InputError
                            :message="form.errors.status"
                            class="mt-2"
                        />
                    </div>

                    <div class="flex items-center">
                        <input
                            type="checkbox"
                            id="is_active"
                            v-model="form.is_active"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        />
                        <label
                            for="is_active"
                            class="ml-2 block text-sm text-gray-900"
                            >Active</label
                        >
                    </div>

                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="closeModal">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton
                            class="ms-3"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            {{ editingTable ? 'Update Table' : 'Create Table' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal
            :show="confirmingTableDeletion"
            @close="confirmingTableDeletion = false"
        >
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Are you sure you want to delete this table?
                </h2>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="confirmingTableDeletion = false">
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteTable"
                    >
                        Delete Table
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
