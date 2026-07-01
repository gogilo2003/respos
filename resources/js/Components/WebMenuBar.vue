<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const links = ref([
    { name: 'Home', href: '/' },
    { name: 'Categories', href: '/categories' },
    { name: 'Menu', href: '/menu' },
    { name: 'About', href: '/about' },
    { name: 'Contact', href: '/contact' },
]);
</script>

<template>
    <nav class="flex min-w-full flex-col items-center gap-6">
        <Link
            v-for="link in links"
            :key="link.name"
            :href="link.href"
            class="text-sm font-semibold hover:underline"
        >
            {{ link.name }}
        </Link>
    </nav>
</template>
