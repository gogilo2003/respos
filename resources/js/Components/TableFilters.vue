<script setup lang="ts">
interface Props {
    activeFilter?: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:activeFilter': [filter: string];
}>();

const filters = [
    { label: 'Available', value: 'available' },
    { label: 'Occupied', value: 'occupied' },
    { label: 'Reserved', value: 'reserved' },
    { label: 'Cleaning', value: 'cleaning' },
];

const selected = ref(props.activeFilter ?? '');
</script>

<template>
    <div class="flex flex-wrap gap-2">
        <button
            v-for="filter in filters"
            :key="filter.value"
            type="button"
            class="rounded-full border px-3 py-1.5 text-sm font-medium transition"
            :class="selected === filter.value
                ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
            @click="selected = filter.value; emit('update:activeFilter', filter.value)"
        >
            {{ filter.label }}
        </button>
    </div>
</template>
