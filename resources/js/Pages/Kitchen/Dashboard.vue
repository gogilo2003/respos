<script setup lang="ts">
import KitchenDashboardHeader from '@/Components/KitchenDashboardHeader.vue';
import KitchenOrderQueue from '@/Components/KitchenOrderQueue.vue';
import KitchenQueueFilters from '@/Components/KitchenQueueFilters.vue';
import KitchenStatisticsCards from '@/Components/KitchenStatisticsCards.vue';
import { type KitchenDashboardProps } from '@/Composables/useKitchenDashboard';
import { useKitchenDashboard } from '@/Composables/useKitchenDashboard';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<KitchenDashboardProps>();

const page = usePage();
const authUser = computed(() => (page.props.auth as any)?.user);

const {
    refreshing,
    error,
    statusFilter,
    priorityFilter,
    currentTime,
    filteredOrders,
    orderCount,
    stats,
    isEmpty,
    refresh,
} = useKitchenDashboard(props);
</script>

<template>
    <Head title="Kitchen Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <KitchenDashboardHeader
                station="Kitchen"
                :user-name="authUser?.name ?? 'Staff'"
                :current-time="currentTime"
                @refresh="refresh"
            />
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

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
                    v-model:priority-filter="priorityFilter"
                />

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
                        :orders="filteredOrders"
                        :loading="refreshing"
                    />

                    <p
                        v-if="isEmpty"
                        class="py-12 text-center text-sm text-gray-500"
                    >
                        No active orders.
                    </p>
                </section>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
