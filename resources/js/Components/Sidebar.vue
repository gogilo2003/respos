<script setup lang="ts">
import { ref, defineExpose } from 'vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const isOpen = ref(false);

const toggle = () => {
    isOpen.value = !isOpen.value;
};

const close = () => {
    isOpen.value = false;
};

defineExpose({
    toggle,
    close,
    isOpen,
});

const appName = ref(usePage().props.appName || 'Laravel');
</script>

<template>
    <!-- Desktop Sidebar (md and up) -->
    <div class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0">
        <div class="flex-1 flex flex-col min-h-0 bg-gray-800">
            <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
                <div class="flex items-center flex-shrink-0 px-4 text-gray-200">
                    <Link :href="route('dashboard')" class="flex gap-2 items-center">
                        <svg class="h-8 w-auto fill-current text-gray-100" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor"
                                stroke-width="2" fill="none" />
                        </svg>
                        <div v-text="appName"></div>
                    </Link>
                </div>
                <nav class="mt-5 flex-1 gap-1 flex flex-col pl-3">
                    <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                        Dashboard
                    </NavLink>
                    <NavLink v-if="$page.props.auth.user.role === 'admin'" :href="route('users')"
                        :active="route().current('users*')">
                        Users
                    </NavLink>
                    <!-- Add more navigation links here -->
                </nav>
            </div>
        </div>
    </div>

    <!-- Mobile Overlay -->
    <div v-if="isOpen" class="fixed inset-0 z-40 flex md:hidden" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity" @click="close"></div>

        <!-- Sidebar panel -->
        <div class="relative flex w-full max-w-xs flex-1 flex-col bg-white pt-5 pb-4">
            <!-- Close button -->
            <div class="absolute top-0 right-0 -mr-12 pt-2">
                <button @click="close"
                    class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                    <span class="sr-only">Close sidebar</span>
                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex flex-shrink-0 items-center px-4">
                <Link :href="route('dashboard')" @click="close">
                    <svg class="h-8 w-auto fill-current text-gray-800" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor"
                            stroke-width="2" fill="none" />
                    </svg>
                </Link>
            </div>

            <div class="mt-5 h-0 flex-1 overflow-y-auto">
                <nav class="space-y-1 px-2">
                    <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')" @click="close">
                        Dashboard
                    </ResponsiveNavLink>
                    <ResponsiveNavLink v-if="$page.props.auth.user.role === 'admin'" :href="route('users')"
                        :active="route().current('users*')" @click="close">
                        Users
                    </ResponsiveNavLink>
                    <!-- Add more navigation links here -->
                </nav>
            </div>
        </div>

        <div class="w-14 flex-shrink-0"></div>
    </div>
</template>
