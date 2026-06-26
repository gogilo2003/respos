<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Icon from './Icons/Icon.vue';

defineProps({
    items: Object,
});

const lt = ref('&laquo; Previous');
const gt = ref('Next &raquo;');
</script>

<template>
    <div class="my-3 rounded-xl border bg-gray-50 px-4 py-3 shadow">
        <nav aria-label="Pagination" class="">
            <ul
                class="flex flex-wrap items-center justify-center gap-1 -space-x-px text-sm md:h-8 md:flex-nowrap"
            >
                <li v-for="link in items?.links">
                    <component
                        :is="link.url ? Link : 'span'"
                        v-if="link.label == lt"
                        :href="link.url"
                        class="ml-0 flex h-8 items-center justify-center rounded-l-3xl rounded-r-lg border border-gray-300 bg-white px-3 leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                    >
                        <span class="sr-only">Previous</span>
                        <Icon
                            class="h-4 w-4 object-contain"
                            type="arrow-back"
                        />
                    </component>
                    <component
                        :is="link.url ? Link : 'span'"
                        v-else-if="link.label == gt"
                        :href="link.url"
                        class="flex h-8 items-center justify-center rounded-l-lg rounded-r-3xl border border-gray-300 bg-white px-3 leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                    >
                        <span class="sr-only">Next</span>
                        <Icon
                            class="h-4 w-4 object-contain"
                            type="arrow-forward"
                        />
                    </component>
                    <component
                        :is="link.url ? Link : 'span'"
                        v-else
                        :href="link.url"
                        class="flex h-8 w-8 items-center justify-center rounded-full border border-gray-300 bg-white px-3 leading-tight hover:bg-gray-100 hover:text-gray-700"
                        :class="{
                            'text-secondary-600 border-secondary-300 bg-secondary-50 hover:bg-secondary-100 hover:text-secondary-700 z-10':
                                link.active,
                            'border-gray-300 bg-white text-gray-500 hover:bg-gray-100 hover:text-gray-700':
                                !link.active,
                        }"
                        v-text="link.label"
                    >
                    </component>
                </li>
            </ul>
        </nav>
    </div>
</template>
