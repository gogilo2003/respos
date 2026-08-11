<script setup lang="ts">
import { ref, watch } from 'vue';

interface Props {
    modelValue?: string;
    maxLength?: number;
    placeholder?: string;
    label?: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const internal = ref(props.modelValue ?? '');

watch(
    () => props.modelValue,
    (value) => {
        internal.value = value ?? '';
    },
);

const onInput = (event: Event) => {
    const target = event.target as HTMLTextAreaElement;
    const next = props.maxLength
        ? target.value.slice(0, props.maxLength)
        : target.value;
    internal.value = next;
    emit('update:modelValue', next);
};
</script>

<template>
    <div>
        <label v-if="label" class="block text-sm font-medium text-gray-700">{{
            label
        }}</label>
        <textarea
            :value="internal"
            rows="3"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            :maxlength="maxLength"
            :placeholder="placeholder || 'Add notes...'"
            @input="onInput"
        />

        <div class="mt-1 flex justify-between text-xs text-gray-500">
            <span v-if="modelValue?.length && maxLength" class="text-right">
                {{ modelValue.length }}/{{ maxLength }}
            </span>
            <span v-else />
        </div>
    </div>
</template>
