<script setup lang="ts">
interface Props {
    id: number;
    name: string;
    price: number;
    imageUrl?: string;
    available: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    add: [item: { id: number; name: string; price: number }];
}>();
</script>

<template>
    <div class="flex h-full flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md">
        <div class="h-40 w-full overflow-hidden bg-gray-100">
            <img
                v-if="imageUrl"
                :src="imageUrl"
                :alt="name"
                class="h-full w-full object-cover"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center text-xs text-gray-400"
            >
                No image
            </div>
        </div>

        <div class="flex flex-1 flex-col p-4">
            <div class="flex items-start justify-between gap-2">
                <h3 class="text-sm font-semibold text-gray-900">{{ name }}</h3>
                <span
                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                    :class="available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                >
                    {{ available ? 'Available' : 'Unavailable' }}
                </span>
            </div>

            <p class="mt-2 text-sm text-gray-500">
                {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(price) }}
            </p>

            <button
                type="button"
                class="mt-auto w-full rounded-md bg-gray-900 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800 disabled:opacity-50"
                :disabled="!available"
                @click="emit('add', { id, name, price })"
            >
                Add
            </button>
        </div>
    </div>
</template>
