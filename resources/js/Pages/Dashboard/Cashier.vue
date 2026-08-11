<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Statistics {
    open_bills_count: number;
    paid_bills_today_count: number;
    cash_collected_today: number;
    voided_bills_today_count: number;
    open_sessions_count: number;
}

interface OpenBill {
    id: number;
    bill_number: string;
    table_number: string;
    grand_total: number;
    status: string;
    created_at: string;
}

defineProps<{
    statistics: Statistics;
    recent_open_bills: OpenBill[];
}>();
</script>

<template>
    <Head title="Cashier Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Billing & Cash Management
                </h2>
                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                    Cashier Station
                </span>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Stat Grid -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Cash Collected -->
                    <div class="overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">Collected Today</span>
                            <span class="rounded-lg bg-green-50 p-2 text-green-600">💵</span>
                        </div>
                        <div class="mt-3 text-2xl font-bold text-gray-900">
                            ${{ statistics.cash_collected_today.toFixed(2) }}
                        </div>
                    </div>

                    <!-- Paid Bills Count -->
                    <div class="overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">Bills Paid Today</span>
                            <span class="rounded-lg bg-blue-50 p-2 text-blue-600">✅</span>
                        </div>
                        <div class="mt-3 text-2xl font-bold text-gray-900">
                            {{ statistics.paid_bills_today_count }}
                        </div>
                    </div>

                    <!-- Open Bills Count -->
                    <div class="overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">Open / Pending Bills</span>
                            <span class="rounded-lg bg-amber-50 p-2 text-amber-600">🧾</span>
                        </div>
                        <div class="mt-3 text-2xl font-bold text-gray-900">
                            {{ statistics.open_bills_count }}
                        </div>
                    </div>

                    <!-- Voided Bills Count -->
                    <div class="overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">Voided Today</span>
                            <span class="rounded-lg bg-red-50 p-2 text-red-600">🚫</span>
                        </div>
                        <div class="mt-3 text-2xl font-bold text-gray-900">
                            {{ statistics.voided_bills_today_count }}
                        </div>
                    </div>
                </div>

                <!-- Recent Open Bills Settlement Section -->
                <div class="mt-8 rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-semibold text-gray-900">Open Bills Awaiting Payment</h3>
                        <Link :href="route('bills.index')" class="text-xs font-semibold text-blue-600 hover:underline">
                            View All Bills →
                        </Link>
                    </div>

                    <div v-if="recent_open_bills.length === 0" class="py-8 text-center text-sm text-gray-500">
                        No open bills pending settlement.
                    </div>
                    <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="bill in recent_open_bills"
                            :key="bill.id"
                            class="rounded-lg border border-gray-200 p-4 flex flex-col justify-between"
                        >
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-gray-900">{{ bill.bill_number }}</span>
                                    <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">
                                        Table {{ bill.table_number }}
                                    </span>
                                </div>
                                <div class="mt-2 text-xl font-bold text-gray-900">
                                    ${{ bill.grand_total.toFixed(2) }}
                                </div>
                            </div>
                            <div class="mt-4 flex items-center justify-between pt-3 border-t border-gray-100 text-xs">
                                <span class="text-gray-500">{{ bill.created_at }}</span>
                                <Link
                                    :href="route('bills.show', bill.id)"
                                    class="font-semibold text-blue-600 hover:underline"
                                >
                                    Settle Bill →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
