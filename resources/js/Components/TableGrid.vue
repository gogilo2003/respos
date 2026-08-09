<script setup lang="ts">
import TableCard from '@/Components/TableCard.vue';

interface Table {
    tableNumber: string;
    capacity: number;
    status: string;
}

interface Props {
    tables: Table[];
    selectedTableNumber?: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'select-table': [tableNumber: string];
}>();
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <TableCard
            v-for="table in tables"
            :key="table.tableNumber"
            :table-number="table.tableNumber"
            :capacity="table.capacity"
            :status="table.status"
            :class="{
                'ring-2 ring-indigo-500': selectedTableNumber === table.tableNumber,
            }"
            @click="emit('select-table', table.tableNumber)"
        />
    </div>
</template>
