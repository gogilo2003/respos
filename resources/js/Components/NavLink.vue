<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    href: string;
    active?: boolean;
}>();

const classes = computed(() =>
    props.active
        ? 'border-indigo-400 bg-gray-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
        : 'border-transparent text-sm font-medium leading-5 text-gray-200 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out',
);
</script>

<template>
    <Link class="w-fill inline-flex items-center border-b-2 pl-6 pr-3 py-4 text-lg rounded-l-full hover:bg-gray-100"
        :href="href" :class="classes">
        <slot />
    </Link>
</template>
