<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';

interface Request {
    id: number;
    status: string;
    notes: string | null;
    requested_at: string;
    session: {
        table: {
            table_number: string;
        };
    };
}

const props = defineProps<{
    tables: any[];
}>();

const requests = ref<Request[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);

const fetchAssistanceRequests = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await fetch('/waiter/assistance?status=open', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = await response.json();
        requests.value = data.requests || [];
    } catch (e) {
        error.value = 'Failed to fetch assistance requests.';
    } finally {
        loading.value = false;
    }
};

let intervalId: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    fetchAssistanceRequests();
    intervalId = setInterval(fetchAssistanceRequests, 10000);
});

onBeforeUnmount(() => {
    if (intervalId) {
        clearInterval(intervalId);
    }
});

const resolveRequest = async (id: number, notes: string = '') => {
    try {
        const response = await fetch(`/waiter/assistance/${id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ status: 'resolved', notes }),
        });
        if (!response.ok) {
            throw new Error('Failed to resolve request');
        }
        await fetchAssistanceRequests();
    } catch (e) {
        error.value = 'Failed to resolve assistance request.';
    }
};
</script>

<template>
    <Head title="Waiter Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Waiter Dashboard
                </h2>
                <div v-if="error" class="text-sm text-red-600">
                    {{ error }}
                </div>
                <div v-if="loading" class="text-sm text-gray-500">
                    Refreshing...
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-medium text-gray-900">Tables</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div v-for="table in props.tables" :key="table.table_id" 
                                 class="border p-4 rounded-lg shadow-sm">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold">Table {{ table.table_name }}</span>
                                    <span :class="{'text-green-600': table.active_session?.status === 'open', 'text-gray-500': table.active_session?.status !== 'open'}">
                                        {{ table.active_session?.status || 'No Session' }}
                                    </span>
                                </div>
                                <div v-if="table.assistance?.open_requests_count > 0" class="mt-2 text-sm text-red-600">
                                    {{ table.assistance.open_requests_count }} assistance request(s)
                                </div>
                                <div v-if="table.order_summary?.active_orders_count > 0" class="mt-2 text-sm text-gray-600">
                                    {{ table.order_summary.active_orders_count }} active order(s)
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900">Assistance Requests</h3>
                        <div v-if="loading" class="mt-4 text-gray-500">
                            Loading requests...
                        </div>
                        <div v-else-if="requests.length === 0" class="mt-4 text-gray-500">
                            No pending assistance requests.
                        </div>
                        <div v-else class="mt-4 space-y-4">
                            <div v-for="request in requests" :key="request.id" class="border p-4 rounded-lg shadow-sm">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="font-bold">Table {{ request.session.table.table_number }}</span>
                                        <span class="text-sm text-gray-500 ml-2">
                                            Requested at: {{ request.requested_at }}
                                        </span>
                                    </div>
                                    <button 
                                        @click="resolveRequest(request.id)"
                                        class="text-sm text-blue-600 hover:text-blue-800"
                                    >
                                        Mark Resolved
                                    </button>
                                </div>
                                <div v-if="request.notes" class="mt-2 text-sm text-gray-600">
                                    Notes: {{ request.notes }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
