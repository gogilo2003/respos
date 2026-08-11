<script setup lang="ts">
import { ref, watch } from 'vue';

interface Props {
    modelValue?: number;
    min?: number;
    max?: number;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:modelValue': [value: number];
}>();

const quantity = ref(props.modelValue ?? 1);

watch(
    () => props.modelValue,
    (value) => {
        quantity.value = value ?? 1;
    },
);

const clamp = (value: number) => {
    const min = props.min ?? 1;
    const max = props.max ?? Infinity;
    return Math.max(min, Math.min(max, value));
};

const increment = () => {
    quantity.value = clamp(quantity.value + 1);
    emit('update:modelValue', quantity.value);
};

const decrement = () => {
    quantity.value = clamp(quantity.value - 1);
    emit('update:modelValue', quantity.value);
};

const onInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const next = target.value === '' ? 0 : Number(target.value);
    quantity.value = clamp(next);
    emit('update:modelValue', quantity.value);
};
</script>

<template>
    <div class="inline-flex items-center rounded-md border border-gray-300">
        <button
            type="button"
            class="px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:opacity-50"
            :disabled="quantity <= (min ?? 1)"
            @click="decrement"
        >
            -
        </button>

        <input
            :value="quantity"
            type="number"
            class="w-16 border-x border-gray-300 px-2 py-1.5 text-center text-sm text-gray-900 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            @input="onInput"
        />

        <button
            type="button"
            class="px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:opacity-50"
            :disabled="quantity >= (max ?? Infinity)"
            @click="increment"
        >
            +
        </button>
    </div>
</template>
