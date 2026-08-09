<script setup lang="ts">
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface BillItem {
    name: string;
    quantity: number;
    unit_price: number;
    total_price: number;
}

interface BillData {
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
    bill: BillData;
}>();

const showPaymentModal = ref(false);
const paymentForm = useForm({
    amount_received: props.bill.grand_total.toString(),
});

const openPaymentModal = () => {
    paymentForm.amount_received = props.bill.grand_total.toString();
    showPaymentModal.value = true;
};

const processPayment = () => {
    paymentForm.post(route('payments.store', props.bill.bill_number), {
        onSuccess: () => {
            showPaymentModal.value = false;
            router.reload();
        },
    });
};

const voidBill = () => {
    if (confirm(`Are you sure you want to void bill #${props.bill.bill_number}?`)) {
        router.patch(route('bills.void', props.bill.bill_number));
    }
};

const printReceipt = () => {
    window.print();
};
</script>

<template>
    <Head :title="`Bill #${bill.bill_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Bill #{{ bill.bill_number }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Table {{ bill.table?.table_number || 'N/A' }} • Generated {{ bill.created_at }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('bills.index')" class="text-xs font-semibold text-gray-600 hover:underline">
                        ← Back to Bills
                    </Link>
                    <button
                        @click="printReceipt"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                    >
                        🖨️ Print Receipt
                    </button>
                    <PrimaryButton
                        v-if="bill.status !== 'paid' && bill.status !== 'voided'"
                        @click="openPaymentModal"
                    >
                        💵 Process Payment
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <!-- Receipt Card -->
                <div class="overflow-hidden bg-white shadow-sm rounded-xl border border-gray-200 p-8 print:shadow-none print:border-none">
                    <div class="flex items-center justify-between pb-6 border-b border-gray-200">
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-gray-900">RES POS</h1>
                            <p class="text-xs text-gray-500">Official Dining Invoice & Receipt</p>
                        </div>
                        <div class="text-right">
                            <span
                                class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider"
                                :class="{
                                    'bg-amber-100 text-amber-800': bill.status === 'draft' || bill.status === 'open',
                                    'bg-green-100 text-green-800': bill.status === 'paid',
                                    'bg-red-100 text-red-800': bill.status === 'voided',
                                }"
                            >
                                {{ bill.status }}
                            </span>
                            <div class="mt-1 text-xs text-gray-500">Invoice: {{ bill.bill_number }}</div>
                        </div>
                    </div>

                    <!-- Meta Information -->
                    <div class="grid grid-cols-2 gap-4 py-4 text-xs border-b border-gray-100">
                        <div>
                            <span class="text-gray-500">Table Number:</span>
                            <span class="ml-1 font-bold text-gray-900">Table {{ bill.table?.table_number || 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Session ID:</span>
                            <span class="ml-1 font-bold text-gray-900">#{{ bill.session_id }}</span>
                        </div>
                    </div>

                    <!-- Itemized List -->
                    <div class="py-6">
                        <h3 class="text-xs font-bold uppercase text-gray-500 tracking-wider mb-3">Order Breakdown</h3>
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="py-2 px-3 font-semibold">Item</th>
                                    <th class="py-2 px-3 text-center font-semibold">Qty</th>
                                    <th class="py-2 px-3 text-right font-semibold">Unit Price</th>
                                    <th class="py-2 px-3 text-right font-semibold">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="(item, idx) in bill.items" :key="idx">
                                    <td class="py-3 px-3 font-medium text-gray-900">{{ item.name }}</td>
                                    <td class="py-3 px-3 text-center text-gray-700">{{ item.quantity }}</td>
                                    <td class="py-3 px-3 text-right text-gray-700">${{ item.unit_price.toFixed(2) }}</td>
                                    <td class="py-3 px-3 text-right font-semibold text-gray-900">${{ item.total_price.toFixed(2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Financial Totals -->
                    <div class="border-t border-gray-200 pt-4 space-y-2 text-xs">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal:</span>
                            <span>${{ bill.subtotal.toFixed(2) }}</span>
                        </div>
                        <div v-if="bill.discount > 0" class="flex justify-between text-green-600">
                            <span>Discount:</span>
                            <span>-${{ bill.discount.toFixed(2) }}</span>
                        </div>
                        <div v-if="bill.tax > 0" class="flex justify-between text-gray-600">
                            <span>Tax:</span>
                            <span>+${{ bill.tax.toFixed(2) }}</span>
                        </div>
                        <div v-if="bill.service_charge > 0" class="flex justify-between text-gray-600">
                            <span>Service Charge:</span>
                            <span>+${{ bill.service_charge.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-gray-900 border-t border-gray-200 pt-3">
                            <span>Grand Total:</span>
                            <span>${{ bill.grand_total.toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Footer / Void action -->
                    <div v-if="bill.status !== 'voided' && bill.status !== 'paid'" class="mt-8 pt-4 border-t border-gray-100 flex justify-end">
                        <DangerButton @click="voidBill">
                            Void Bill
                        </DangerButton>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <Modal :show="showPaymentModal" @close="showPaymentModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Settle Bill #{{ bill.bill_number }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Total Amount Due: <span class="font-bold text-gray-900">${{ bill.grand_total.toFixed(2) }}</span>
                </p>

                <form @submit.prevent="processPayment" class="mt-6 space-y-4">
                    <div>
                        <InputLabel for="amount_received" value="Amount Received ($)" />
                        <TextInput
                            id="amount_received"
                            v-model="paymentForm.amount_received"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="paymentForm.errors.amount_received" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="showPaymentModal = false">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton
                            class="ms-3"
                            :class="{ 'opacity-25': paymentForm.processing }"
                            :disabled="paymentForm.processing"
                        >
                            Complete Payment
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
