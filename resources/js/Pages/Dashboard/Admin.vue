<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Statistics {
    today_sales: number;
    today_orders_count: number;
    active_sessions_count: number;
    pending_kitchen_orders_count: number;
    total_users_count: number;
    active_categories_count: number;
    available_items_count: number;
}

interface RecentOrder {
    id: number;
    table_number: string;
    status: string;
    placed_by_role: string;
    created_at: string;
}

defineProps<{
    statistics: Statistics;
    recent_orders: RecentOrder[];
}>();
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    System Overview & Analytics
                </h2>
                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800">
                    Admin / Manager
                </span>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto  px-4 sm:px-6 lg:px-8">
                <!-- Stat Cards Grid -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Today Sales -->
                    <div class="overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">Today's Revenue</span>
                            <span class="rounded-lg bg-green-50 p-2 text-green-600">
                                💵
                            </span>
                        </div>
                        <div class="mt-3 text-2xl font-bold text-gray-900">
                            ${{ statistics.today_sales.toFixed(2) }}
                        </div>
                    </div>

                    <!-- Today Orders -->
                    <div class="overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">Orders Placed Today</span>
                            <span class="rounded-lg bg-blue-50 p-2 text-blue-600">
                                🛒
                            </span>
                        </div>
                        <div class="mt-3 text-2xl font-bold text-gray-900">
                            {{ statistics.today_orders_count }}
                        </div>
                    </div>

                    <!-- Active Sessions -->
                    <div class="overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">Active Tables / Sessions</span>
                            <span class="rounded-lg bg-purple-50 p-2 text-purple-600">
                                🪑
                            </span>
                        </div>
                        <div class="mt-3 text-2xl font-bold text-gray-900">
                            {{ statistics.active_sessions_count }}
                        </div>
                    </div>

                    <!-- Pending Kitchen Orders -->
                    <div class="overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">Kitchen Active Queue</span>
                            <span class="rounded-lg bg-amber-50 p-2 text-amber-600">
                                🍳
                            </span>
                        </div>
                        <div class="mt-3 text-2xl font-bold text-gray-900">
                            {{ statistics.pending_kitchen_orders_count }}
                        </div>
                    </div>
                </div>

                <!-- Secondary Cards & Quick Actions -->
                <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <!-- Recent Orders Overview -->
                    <div class="lg:col-span-2 rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-semibold text-gray-900">Recent Orders</h3>
                            <Link :href="route('tables')" class="text-xs font-semibold text-blue-600 hover:underline">
                                View Tables →
                            </Link>
                        </div>
                        <div v-if="recent_orders.length === 0" class="py-8 text-center text-sm text-gray-500">
                            No orders placed today.
                        </div>
                        <div v-else class="divide-y divide-gray-100">
                            <div
                                v-for="order in recent_orders"
                                :key="order.id"
                                class="flex items-center justify-between py-3"
                            >
                                <div>
                                    <span class="font-medium text-gray-900">Order #{{ order.id }}</span>
                                    <span class="ml-2 text-xs text-gray-500">(Table {{ order.table_number }})</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-gray-500">{{ order.created_at }}</span>
                                    <span
                                        class="rounded-full px-2.5 py-0.5 text-xs font-medium uppercase"
                                        :class="{
                                            'bg-amber-100 text-amber-800': order.status === 'pending',
                                            'bg-blue-100 text-blue-800': order.status === 'preparing',
                                            'bg-green-100 text-green-800': order.status === 'ready' || order.status === 'completed',
                                        }"
                                    >
                                        {{ order.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Management Quick Links -->
                    <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Quick Management</h3>
                        <div class="space-y-3">
                            <Link
                                :href="route('users')"
                                class="flex items-center justify-between rounded-lg border border-gray-200 p-3 hover:bg-gray-50 transition"
                            >
                                <span class="text-sm font-medium text-gray-800">Users & Staff</span>
                                <span class="text-xs font-bold text-gray-500">{{ statistics.total_users_count }} active</span>
                            </Link>
                            <Link
                                :href="route('menu-categories')"
                                class="flex items-center justify-between rounded-lg border border-gray-200 p-3 hover:bg-gray-50 transition"
                            >
                                <span class="text-sm font-medium text-gray-800">Menu Categories</span>
                                <span class="text-xs font-bold text-gray-500">{{ statistics.active_categories_count }} active</span>
                            </Link>
                            <Link
                                :href="route('menu-items')"
                                class="flex items-center justify-between rounded-lg border border-gray-200 p-3 hover:bg-gray-50 transition"
                            >
                                <span class="text-sm font-medium text-gray-800">Menu Items</span>
                                <span class="text-xs font-bold text-gray-500">{{ statistics.available_items_count }} available</span>
                            </Link>
                            <Link
                                :href="route('bills.index')"
                                class="flex items-center justify-between rounded-lg border border-gray-200 p-3 hover:bg-gray-50 transition"
                            >
                                <span class="text-sm font-medium text-gray-800">Bills & Payment Logs</span>
                                <span class="text-xs font-bold text-blue-600">View All →</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
