<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import Icon from './Icons/Icon.vue';

defineProps({
    items: Object
})

const lt = ref('&laquo; Previous');
const gt = ref('Next &raquo;');

</script>

<template>
    <div class="shadow my-3 border bg-gray-50 rounded-xl px-4 py-3">
        <nav aria-label="Pagination" class="">
            <ul class="flex items-center flex-wrap md:flex-nowrap -space-x-px md:h-8 text-sm gap-1 justify-center">
                <li v-for="link in items?.links">
                    <component :is="link.url ? Link : 'span'" v-if="link.label == lt" :href="link.url"
                        class="flex items-center justify-center px-3 h-8 ml-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg rounded-l-3xl hover:bg-gray-100 hover:text-gray-700">
                        <span class="sr-only">Previous</span>
                        <Icon class="h-4 object-contain w-4" type="arrow-back" />
                    </component>
                    <component :is="link.url ? Link : 'span'" v-else-if="link.label == gt" :href="link.url"
                        class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg rounded-r-3xl hover:bg-gray-100 hover:text-gray-700">
                        <span class="sr-only">Next</span>
                        <Icon class="h-4 object-contain w-4" type="arrow-forward" />
                    </component>
                    <component :is="link.url ? Link : 'span'" v-else :href="link.url"
                        class="flex items-center justify-center px-3 h-8 w-8 rounded-full leading-tight bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700"
                        :class="{ 'z-10 text-secondary-600 border-secondary-300 bg-secondary-50 hover:bg-secondary-100 hover:text-secondary-700': link.active, 'text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700': !link.active }"
                        v-text="link.label">
                    </component>
                </li>
            </ul>
        </nav>
    </div>
</template>
