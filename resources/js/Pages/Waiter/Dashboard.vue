<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import TableGrid from '@/Components/TableGrid.vue';
import TableFilters from '@/Components/TableFilters.vue';
import TableSearch from '@/Components/TableSearch.vue';
import WaiterDashboardHeader from '@/Components/WaiterDashboardHeader.vue';
import StatisticsCards from '@/Components/StatisticsCards.vue';
import ActiveOrders from '@/Components/ActiveOrders.vue';
import AssistanceList from '@/Components/AssistanceList.vue';
import { ref, computed } from 'vue';
import type { WaiterStatistics } from '@/interfaces/waiter';

interface ActiveSession {
    table_session_id: number;
    status: string;
}

interface OrderSummary {
    active_orders_count: number;
    latest_order_status: string | null;
}

interface AssistanceSummary {
    open_requests_count: number;
}

interface WaiterTable {
    table_id: number;
    table_name: string;
    active_session: ActiveSession | null;
    order_summary: OrderSummary | null;
    assistance: AssistanceSummary | null;
}

interface WaiterOrder {
    orderNumber: number | string;
    table: string;
    customer?: string;
    time: string;
    status: string;
}

interface WaiterAssistance {
    tableNumber: string;
    request: string;
    priority: string;
    time: string;
}

const props = defineProps<{
    tables: WaiterTable[];
    orders: WaiterOrder[];
    statistics: WaiterStatistics;
    assistance_requests: WaiterAssistance[];
}>();

const activeFilter = ref('');
const searchQuery = ref('');

const filteredTables = computed(() => {
    let tables = props.tables;

    if (activeFilter.value) {
        tables = tables.filter((table) => table.active_session?.status === activeFilter.value);
    }

    if (searchQuery.value.trim()) {
        const query = searchQuery.value.trim().toLowerCase();
        tables = tables.filter((table) => table.table_name.toLowerCase().includes(query));
    }

    return tables;
});

const handleSelectTable = (tableNumber: string) => {
    console.log('Selected table:', tableNumber);
};

const handleRefresh = () => {
    console.log('Refresh waiter dashboard');
};
</script>

<template>
    <Head title="Waiter Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <WaiterDashboardHeader
                waiter-name="Waiter"
                shift="Current Shift"
                :current-time="new Date().toLocaleTimeString()"
                @refresh="handleRefresh"
            />
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <StatisticsCards :stats="statistics" />
                    </div>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 space-y-4">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Tables</h3>
                            <TableSearch v-model="searchQuery" placeholder="Search tables..." />
                        </div>
                        <TableFilters v-model="activeFilter" />
                        <TableGrid
                            :tables="filteredTables.map((table) => ({
                                tableNumber: table.table_name,
                                capacity: 0,
                                status: table.active_session?.status ?? 'available',
                            }))"
                            @select-table="handleSelectTable"
                        />
                    </div>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Active Orders</h3>
                        <ActiveOrders :orders="orders" />
                    </div>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Assistance Requests</h3>
                        <AssistanceList :requests="assistance_requests" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
