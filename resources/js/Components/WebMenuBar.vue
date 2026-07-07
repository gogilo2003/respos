<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const collapsed = ref(false);

const toggleNavbar = () => {
    collapsed.value = !collapsed.value;
};

const links = ref([
    { name: 'Home', href: '/' },
    { name: 'Categories', href: '/categories' },
    { name: 'Menu', href: '/menu' },
    { name: 'About', href: '/about' },
    { name: 'Cart', href: '/cart' },
]);
</script>

<template>
    <!--   
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
    -->
    <aside
        :class="[
            'h-screen overflow-hidden bg-gray-900 text-white transition-all duration-300',
            collapsed ? 'w-20' : 'w-64',
        ]"
    >
        <button
            @click="toggleNavbar"
            class="m-4 rounded bg-gray-800 p-2 hover:bg-gray-700"
        >
            ☰
        </button>

        <nav class="flex flex-col gap-6 px-4">
            <Link
                v-for="link in links"
                :key="link.name"
                :href="link.href"
                class="whitespace-nowrap font-semibold hover:underline"
            >
                <span v-if="!collapsed">
                    {{ link.name }}
                </span>

                <span v-else>
                    {{ link.name.charAt(0) }}
                </span>
            </Link>
        </nav>
    </aside>
</template>
