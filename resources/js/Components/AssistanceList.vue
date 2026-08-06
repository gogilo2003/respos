<script setup lang="ts">
import AssistanceCard from '@/Components/AssistanceCard.vue';

interface Assistance {
    tableNumber: string;
    request: string;
    priority: string;
    time: string;
}

interface Props {
    requests: Assistance[];
    loading?: boolean;
}

const props = defineProps<Props>();
</script>

<template>
    <div class="space-y-4">
        <div v-if="loading" class="py-12 text-center text-sm text-gray-500">
            Loading assistance requests...
        </div>

        <template v-else-if="requests.length > 0">
            <AssistanceCard
                v-for="request in requests"
                :key="`${request.tableNumber}-${request.time}-${request.request}`"
                v-bind="request"
            />
        </template>

        <div v-else class="py-12 text-center text-sm text-gray-500">
            No assistance requests.
        </div>
    </div>
</template>
