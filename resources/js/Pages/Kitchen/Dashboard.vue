<script setup lang="ts">
import KitchenDashboardHeader from '@/Components/KitchenDashboardHeader.vue';
import KitchenOrderQueue from '@/Components/KitchenOrderQueue.vue';
import KitchenQueueFilters from '@/Components/KitchenQueueFilters.vue';
import KitchenStatisticsCards from '@/Components/KitchenStatisticsCards.vue';
import { type KitchenDashboardProps, useKitchenDashboard } from '@/Composables/useKitchenDashboard';
import { useKitchenQueue } from '@/Composables/useKitchenQueue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<KitchenDashboardProps>();

const page = usePage();
const authUser = computed(() => (page.props.auth as any)?.user);

// Data fetching, shaping, and refresh
const { loading, refreshing, error, currentTime, allOrders, stats, refresh } =
    useKitchenDashboard(props);

// Filtering, sorting, and searching over the shaped order list
const {
    statusFilter,
    searchQuery,
    sortBy,
    sortDirection,
    sortedOrders,
    orderCount,
    isEmpty,
    hasActiveFilters,
    clearFilters,
    setSort,
    QUEUE_STATUS_OPTIONS,
    QUEUE_SORT_OPTIONS,
} = useKitchenQueue(allOrders);
</script>

<template>
    <Head title="Kitchen Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <KitchenDashboardHeader
                station="Kitchen"
                :user-name="authUser?.name ?? 'Staff'"
                :current-time="currentTime"
                :refreshing="refreshing"
                @refresh="refresh"
            />
        </template>

        <div class="py-6">
            <div class="mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

                <!-- Error banner -->
                <div
                    v-if="error"
                    class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                    role="alert"
                >
                    {{ error }}
                </div>

                <!-- Statistics -->
                <KitchenStatisticsCards :stats="stats" />

                <!-- Filters -->
                <KitchenQueueFilters
                    v-model:status-filter="statusFilter"
                    v-model:priority-filter="sortBy"
                />

                <!-- Search + sort controls -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <input
                        v-model="searchQuery"
                        type="search"
                        placeholder="Search by order #, table, or item…"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:max-w-xs"
                    />

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-medium text-gray-500">Sort:</span>
                        <button
                            v-for="opt in QUEUE_SORT_OPTIONS"
                            :key="opt.key"
                            type="button"
                            class="rounded-full border px-3 py-1 text-xs font-medium transition"
                            :class="sortBy === opt.key
                                ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                                : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
                            @click="setSort(opt.key)"
                        >
                            {{ opt.label }}
                            <span v-if="sortBy === opt.key" class="ml-0.5">
                                {{ sortDirection === 'asc' ? '↑' : '↓' }}
                            </span>
                        </button>

                        <button
                            v-if="hasActiveFilters"
                            type="button"
                            class="rounded-full border border-red-300 px-3 py-1 text-xs font-medium text-red-600 transition hover:bg-red-50"
                            @click="clearFilters"
                        >
                            Clear filters
                        </button>
                    </div>
                </div>

                <!-- Order Queue -->
                <section>
                    <h3 class="mb-4 text-lg font-medium text-gray-900">
                        Order Queue
                        <span class="ml-2 text-sm font-normal text-gray-500">
                            ({{ orderCount }} order{{ orderCount === 1 ? '' : 's' }})
                        </span>
                        <span
                            v-if="refreshing"
                            class="ml-2 text-xs font-normal text-gray-400"
                        >
                            Refreshing…
                        </span>
                    </h3>

                    <KitchenOrderQueue
                        :orders="sortedOrders"
                        :loading="loading || refreshing"
                    />

                    <p
                        v-if="isEmpty && !loading && !refreshing"
                        class="py-12 text-center text-sm text-gray-500"
                    >
                        {{ hasActiveFilters ? 'No orders match the active filters.' : 'No active orders.' }}
                    </p>
                </section>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
