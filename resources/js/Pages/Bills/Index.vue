<script setup lang="ts">
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface BillItem {
    name: string;
    quantity: number;
    unit_price: number;
    total_price: number;
}

interface BillData {
    id?: number;
    bill_number: string;
    customer?: any;
    table?: {
        table_number: string;
    };
    order?: any;
    items: BillItem[];
    subtotal: number;
    discount: number;
    tax: number;
    service_charge: number;
    grand_total: number;
    status: string;
    session_id: number;
    created_at: string;
    paid_at?: string;
    voided_at?: string;
}

const props = defineProps<{
    bills: BillData[];
}>();

const searchQuery = ref('');
const statusFilter = ref('');
const confirmingVoidModal = ref(false);
const billToVoid = ref<string | null>(null);

const filteredBills = computed(() => {
    let result = props.bills;

    if (statusFilter.value) {
        result = result.filter((b) => b.status === statusFilter.value);
    }

    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        result = result.filter(
            (b) =>
                b.bill_number.toLowerCase().includes(q) ||
                (b.table?.table_number && b.table.table_number.toLowerCase().includes(q)),
        );
    }

    return result;
});

const confirmVoid = (billNumber: string) => {
    billToVoid.value = billNumber;
    confirmingVoidModal.value = true;
};

const executeVoid = () => {
    if (billToVoid.value) {
        router.patch(
            route('bills.void', billToVoid.value),
            {},
            {
                onSuccess: () => (confirmingVoidModal.value = false),
            },
        );
    }
};
</script>

<template>
    <Head title="Bills & Billing Logs" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Bills & Payments Management
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        View generated bills, settlement status, and invoice history
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto  px-4 sm:px-6 lg:px-8">
                <!-- Search & Filters -->
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full max-w-lg">
                        <div class="relative w-full sm:w-64">
                            <TextInput
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search bill # or table..."
                                class="w-full pl-9 text-sm"
                            />
                            <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                        </div>

                        <select
                            v-model="statusFilter"
                            class="w-full sm:w-44 rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="open">Open</option>
                            <option value="paid">Paid</option>
                            <option value="voided">Voided</option>
                        </select>
                    </div>

                    <div class="text-xs text-gray-500">
                        Total: <span class="font-bold text-gray-800">{{ filteredBills.length }}</span> bills
                    </div>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="overflow-x-auto p-6 text-gray-900">
                        <div v-if="filteredBills.length === 0" class="py-12 text-center text-sm text-gray-500">
                            No bills found matching filters.
                        </div>

                        <table v-else class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Bill Number
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Table / Session
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Grand Total
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Date & Time
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="bill in filteredBills" :key="bill.bill_number">
                                    <td class="whitespace-nowrap px-6 py-4 font-bold text-gray-900">
                                        {{ bill.bill_number }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        <span class="rounded bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-800">
                                            Table {{ bill.table?.table_number || 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-gray-900">
                                        ${{ bill.grand_total.toFixed(2) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase"
                                            :class="{
                                                'bg-amber-100 text-amber-800': bill.status === 'draft' || bill.status === 'open',
                                                'bg-green-100 text-green-800': bill.status === 'paid',
                                                'bg-red-100 text-red-800': bill.status === 'voided',
                                            }"
                                        >
                                            {{ bill.status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-xs text-gray-500">
                                        {{ bill.created_at }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        <Link
                                            :href="route('bills.show', bill.bill_number)"
                                            class="text-indigo-600 hover:text-indigo-900 mr-4 font-semibold"
                                        >
                                            View Bill →
                                        </Link>
                                        <button
                                            v-if="bill.status !== 'voided' && bill.status !== 'paid'"
                                            @click="confirmVoid(bill.bill_number)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Void
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Void Confirmation Modal -->
        <Modal :show="confirmingVoidModal" @close="confirmingVoidModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Are you sure you want to void this bill?
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Voiding a bill cancels payment processing for bill #{{ billToVoid }}. This action requires manager/admin approval.
                </p>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="confirmingVoidModal = false">
                        Cancel
                    </SecondaryButton>
                    <DangerButton class="ms-3" @click="executeVoid">
                        Void Bill
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
