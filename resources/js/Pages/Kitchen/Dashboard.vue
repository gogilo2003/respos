<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Kitchen Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Kitchen Dashboard
                </h2>
                <div class="text-sm text-gray-500">
                    Last refreshed: {{ new Date().toLocaleTimeString() }}
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 space-y-6">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500">
                                No statistics loaded
                            </div>
                            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500">
                                No statistics loaded
                            </div>
                            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500">
                                No statistics loaded
                            </div>
                            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500">
                                No statistics loaded
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Filters</h3>
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full border border-gray-300 px-3 py-1.5 text-sm text-gray-500">All</span>
                                <span class="rounded-full border border-gray-300 px-3 py-1.5 text-sm text-gray-500">Pending</span>
                                <span class="rounded-full border border-gray-300 px-3 py-1.5 text-sm text-gray-500">Preparing</span>
                                <span class="rounded-full border border-gray-300 px-3 py-1.5 text-sm text-gray-500">Ready</span>
                            </div>
                        </div>

                        <div>
                            <h3 class="mb-4 text-lg font-medium text-gray-900">Order Queue</h3>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500">
                                    No active orders
                                </div>
                                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500">
                                    No active orders
                                </div>
                                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500">
                                    No active orders
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="mb-4 text-lg font-medium text-gray-900">Preparation Timeline</h3>
                            <div class="space-y-3">
                                <div class="h-2 w-full rounded-full bg-gray-100" />
                                <div class="h-2 w-5/6 rounded-full bg-gray-100" />
                                <div class="h-2 w-4/6 rounded-full bg-gray-100" />
                                <div class="text-xs text-gray-500">No timeline data available</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
