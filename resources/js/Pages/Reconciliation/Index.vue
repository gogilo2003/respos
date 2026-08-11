<script setup lang="ts">
import { formatCurrency } from '@/utils/currency';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Reconciliation {
    id: number;
    reconciliation_date: string;
    prepared_by: string;
    approved_by?: string;
    system_total: number;
    physical_count: number;
    variance_amount: number;
    variance_pct: number;
    flagged: boolean;
    notes?: string;
    created_at: string;
}

const props = defineProps<{
    reconciliations: Reconciliation[];
    selected_date: string;
    system_cash_total: number;
}>();

const form = useForm({
    reconciliation_date: props.selected_date,
    physical_count: '',
    notes: '',
});

const calculatedVariance = computed(() => {
    const physical = parseFloat(form.physical_count || '0');
    return physical - props.system_cash_total;
});

const isVarianceFlagged = computed(() => {
    if (props.system_cash_total === 0) return false;
    const pct = (calculatedVariance.value / props.system_cash_total) * 100;
    return Math.abs(pct) > 0.5;
});

const submit = () => {
    form.post(route('reconciliations.store'), {
        onSuccess: () => {
            form.reset('physical_count', 'notes');
        },
    });
};

const approve = (id: number) => {
    router.post(route('reconciliations.approve', id), {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="End of Day Cash Reconciliation" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                End-of-Day Cash Reconciliation
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Submit Reconciliation Form -->
                <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg border border-gray-200">
                    <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 mb-4">
                        💵 Daily Cash Count Closeout
                    </h3>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <InputLabel for="reconciliation_date" value="Reconciliation Date" />
                                <TextInput
                                    id="reconciliation_date"
                                    v-model="form.reconciliation_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.reconciliation_date" class="mt-2" />
                            </div>

                            <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 flex flex-col justify-center">
                                <span class="text-xs font-semibold text-blue-600 uppercase">System Cash Expected</span>
                                <span class="text-2xl font-bold text-blue-950 mt-1">{{ formatCurrency(system_cash_total) }}</span>
                            </div>

                            <div>
                                <InputLabel for="physical_count" value="Physical Cash Counted" />
                                <TextInput
                                    id="physical_count"
                                    v-model="form.physical_count"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="Enter counted cash in drawer"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.physical_count" class="mt-2" />
                            </div>
                        </div>

                        <!-- Computed Variance Preview -->
                        <div v-if="form.physical_count" class="p-4 rounded-xl border flex items-center justify-between" :class="isVarianceFlagged ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-green-50 border-green-200 text-green-900'">
                            <div>
                                <span class="text-xs font-bold uppercase">Calculated Drawer Variance:</span>
                                <span class="ml-2 font-extrabold">{{ formatCurrency(calculatedVariance) }}</span>
                                <span v-if="isVarianceFlagged" class="ml-2 text-xs font-bold text-amber-700 bg-amber-200 px-2 py-0.5 rounded">
                                    ⚠️ Flagged (>0.5% Variance - Requires Manager Approval)
                                </span>
                            </div>
                        </div>

                        <div>
                            <InputLabel for="notes" value="Notes / Discrepancy Explanation" />
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                placeholder="Optional notes regarding cash drawer count..."
                            ></textarea>
                            <InputError :message="form.errors.notes" class="mt-2" />
                        </div>

                        <div class="flex justify-end">
                            <PrimaryButton :disabled="form.processing">
                                Submit Cash Count
                            </PrimaryButton>
                        </div>
                    </form>
                </div>

                <!-- Reconciliations History Log -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-base font-bold text-gray-900 mb-4">Reconciliation History</h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Date</th>
                                        <th class="px-4 py-3 text-left">Prepared By</th>
                                        <th class="px-4 py-3 text-right">System Expected</th>
                                        <th class="px-4 py-3 text-right">Physical Count</th>
                                        <th class="px-4 py-3 text-right">Variance</th>
                                        <th class="px-4 py-3 text-center">Status</th>
                                        <th class="px-4 py-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 text-sm">
                                    <tr v-for="r in reconciliations" :key="r.id">
                                        <td class="px-4 py-3 font-semibold text-gray-900">{{ r.reconciliation_date }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ r.prepared_by }}</td>
                                        <td class="px-4 py-3 text-right font-medium">{{ formatCurrency(r.system_total) }}</td>
                                        <td class="px-4 py-3 text-right font-bold">{{ formatCurrency(r.physical_count) }}</td>
                                        <td class="px-4 py-3 text-right font-bold" :class="r.variance_amount < 0 ? 'text-red-600' : 'text-green-600'">
                                            {{ formatCurrency(r.variance_amount) }} ({{ r.variance_pct.toFixed(2) }}%)
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span v-if="!r.flagged" class="bg-green-100 text-green-800 text-xs font-bold px-2 py-0.5 rounded-full">
                                                Balanced
                                            </span>
                                            <span v-else-if="r.approved_by" class="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-0.5 rounded-full">
                                                Approved ({{ r.approved_by }})
                                            </span>
                                            <span v-else class="bg-amber-100 text-amber-800 text-xs font-bold px-2 py-0.5 rounded-full">
                                                Flagged Variance
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button
                                                v-if="r.flagged && !r.approved_by"
                                                @click="approve(r.id)"
                                                class="text-xs bg-indigo-600 text-white font-bold px-2.5 py-1 rounded hover:bg-indigo-700"
                                            >
                                                Approve
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="reconciliations.length === 0">
                                        <td colspan="7" class="py-8 text-center text-sm text-gray-400">
                                            No cash reconciliations submitted yet.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
