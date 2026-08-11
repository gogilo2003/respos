<script setup lang="ts">
import { ref, watch } from 'vue';

interface Props {
    modelValue?: string;
    placeholder?: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:modelValue': [value: string];
    search: [value: string];
}>();

const query = ref(props.modelValue ?? '');

watch(
    () => props.modelValue,
    (value) => {
        query.value = value ?? '';
    },
);

const onInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const value = target.value;
    query.value = value;
    emit('update:modelValue', value);
    emit('search', value);
};
</script>

<template>
    <div>
        <input
            :value="query"
            type="text"
            class="block w-full rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            :placeholder="placeholder || 'Search by table number...'"
            @input="onInput"
        />
    </div>
</template>
