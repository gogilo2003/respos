<script setup lang="ts">
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface AuditLog {
    id: number;
    user: string;
    action: string;
    entity_type: string;
    entity_id: number;
    old_value?: any;
    new_value?: any;
    reason?: string;
    ip_address?: string;
    created_at: string;
}

interface PaginatedLogs {
    data: AuditLog[];
    current_page: number;
    last_page: number;
}

const props = defineProps<{
    logs: PaginatedLogs;
    filters: {
        search?: string;
        action?: string;
    };
}>();

const search = ref(props.filters.search || '');
const selectedLog = ref<AuditLog | null>(null);
const showDetailModal = ref(false);

watch(search, (value) => {
    router.get(
        route('audit-logs.index'),
        { search: value },
        { preserveState: true, replace: true }
    );
});

const openDetails = (log: AuditLog) => {
    selectedLog.value = log;
    showDetailModal.value = true;
};
</script>

<template>
    <Head title="Security & Audit Logs" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Security &amp; Operational Audit Trail
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Search Filter Bar -->
                <div class="overflow-hidden bg-white p-4 shadow-sm sm:rounded-lg border border-gray-200 flex items-center justify-between">
                    <div class="w-full max-w-md">
                        <TextInput
                            v-model="search"
                            type="search"
                            placeholder="Search by action, entity, or reason..."
                            class="w-full text-sm"
                        />
                    </div>
                    <span class="text-xs text-gray-500 font-medium">
                        Showing page {{ logs.current_page }} of {{ logs.last_page }}
                    </span>
                </div>

                <!-- Audit Log Table -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Timestamp</th>
                                        <th class="px-4 py-3 text-left">User</th>
                                        <th class="px-4 py-3 text-left">Action</th>
                                        <th class="px-4 py-3 text-left">Target Entity</th>
                                        <th class="px-4 py-3 text-left">Reason / Note</th>
                                        <th class="px-4 py-3 text-left">IP Address</th>
                                        <th class="px-4 py-3 text-right">Details</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 text-sm">
                                    <tr v-for="log in logs.data" :key="log.id">
                                        <td class="px-4 py-3 text-xs text-gray-500 font-mono">{{ log.created_at }}</td>
                                        <td class="px-4 py-3 font-semibold text-gray-900">{{ log.user }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-800 uppercase">
                                                {{ log.action.replace(/_/g, ' ') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-700">
                                            {{ log.entity_type }} #{{ log.entity_id }}
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-600 truncate max-w-xs">
                                            {{ log.reason || '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-xs font-mono text-gray-400">
                                            {{ log.ip_address || '127.0.0.1' }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button
                                                @click="openDetails(log)"
                                                class="text-xs text-indigo-600 font-bold hover:underline"
                                            >
                                                View Diff
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="logs.data.length === 0">
                                        <td colspan="7" class="py-8 text-center text-sm text-gray-400">
                                            No audit logs found matching criteria.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Modal -->
        <Modal :show="showDetailModal" @close="showDetailModal = false">
            <div v-if="selectedLog" class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-2">
                    Audit Log Details - #{{ selectedLog.id }}
                </h3>
                <p class="text-xs text-gray-500 mb-4">
                    Action: <span class="font-bold text-gray-800 uppercase">{{ selectedLog.action }}</span> •
                    Target: <span class="font-bold text-gray-800">{{ selectedLog.entity_type }} #{{ selectedLog.entity_id }}</span>
                </p>

                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="font-bold text-gray-700 block mb-1">Previous State (Old Value):</span>
                        <pre class="bg-gray-900 text-green-400 p-3 rounded-lg overflow-x-auto text-[11px] font-mono">{{ JSON.stringify(selectedLog.old_value, null, 2) || 'null' }}</pre>
                    </div>
                    <div>
                        <span class="font-bold text-gray-700 block mb-1">Updated State (New Value):</span>
                        <pre class="bg-gray-900 text-blue-400 p-3 rounded-lg overflow-x-auto text-[11px] font-mono">{{ JSON.stringify(selectedLog.new_value, null, 2) || 'null' }}</pre>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showDetailModal = false">Close</SecondaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
